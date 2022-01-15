<?php
/**
* collection of functions can be used pods panda repeater field database
*
* @package panda_pods_repeater_field
* @author: Dongjie Xu
* @since 20/05/2014 
*/
class panda_pods_repeater_field_db {

	public static $keys = array( 
							  'pandarf_parent_pod_id'	=> array( 'type' => 'int(11)', 		 'settings' => 'NOT NULL', 'default' => '' ), 
							  'pandarf_parent_post_id'	=> array( 'type' => 'int(11)',	 	 'settings' => 'NOT NULL', 'default' => '' ), 
							  'pandarf_pod_field_id'	=> array( 'type' => 'int(11)', 	 	 'settings' => 'NOT NULL', 'default' => '' ), 							  
							  'pandarf_order' 			=> array( 'type' => 'text', 	 	 'settings' => 'NOT NULL', 'default' => '' ),
							  'pandarf_created' 		=> array( 'type' => 'DATETIME',  	 'settings' => 'NOT NULL', 'default' => 'DEFAULT "0000-00-00 00:00:00"' ),
							  'pandarf_modified' 		=> array( 'type' => 'DATETIME',  	 'settings' => 'NOT NULL', 'default' => 'DEFAULT "0000-00-00 00:00:00"' ),		  
							  'pandarf_modified_author' => array( 'type' => 'int(11)', 	 	 'settings' => 'NOT NULL', 'default' => '' ), 
							  'pandarf_author' 			=> array( 'type' => 'int(11)', 	 	 'settings' => 'NOT NULL', 'default' => '' ),		  
							  'pandarf_trash' 			=> array( 'type' => 'int(1)', 	 	 'settings' => 'NOT NULL', 'default' => 'DEFAULT 0' ),	
						  ); 
	/**
	 * escape_sqls function: escape data
	 *
	 * @var string  $table_str targeted table
	 * @var array   $data_arr  posted data, data to save in $_POST format	 
	 * @var array   $wheres locate the entries to update
	 */
	public function escape_sqls( $data_ukn ){
		if( is_array( $data_ukn ) ){
			foreach( $data_ukn as $key_str => $val_ukn ){
				$key_str 	 = esc_sql(  $key_str  );
				if( is_array( $val_ukn ) ) {
					$val_ukn =	$this->escape_sqls( $val_ukn );
				} else {
					// esc_sql a boolean will return error
					if( is_string( $val_ukn ) ){
						$val_ukn = esc_sql(  $val_ukn );
					}
				}
				$data_ukn[ $key_str ] =	$val_ukn;				
			}
		} else {
			if( is_string( $val_ukn ) ){
				$data_ukn = esc_sql( $val_ukn );	
			}
		}
		return $data_ukn;
	}	
	/**
	 * escape_attrs function: escape data using esc_attr
	 *
	 * @var string  $table_str targeted table
	 * @var array   $data_arr  posted data, data to save in $_POST format	 
	 * @var array   $wheres locate the entries to update
	 */
	public function escape_attrs( $data_ukn ){
		if( is_array( $data_ukn ) ){
			foreach( $data_ukn as $key_str => $val_ukn ){
				$key_str 	 = esc_attr(  $key_str  );
				if( is_array( $val_ukn ) ) {
					$val_ukn =	$this->escape_sqls( $val_ukn );
				} else {
					// esc_sql a boolean will return error
					if( is_string( $val_ukn ) ){
						$val_ukn = esc_attr(  $val_ukn );
					}
				}
				$data_ukn[ $key_str ] =	$val_ukn;				
			}
		} else {
			if( is_string( $val_ukn ) ){
				$data_ukn = esc_attr( $val_ukn );	
			}
		}
		return $data_ukn;
	}	

	/**
	 * get_tables: get tables from database, by default, only return wp_posts, wp_users and pods tables
	 * 
	 * @param: $return_all_tables Boolean return all tables or not
	 */
	public function get_tables( $return_all_tables = false )	{
		global $wpdb;

		$pod_tables = array();

		$cache_name = 'simpods_all_tables';

		$tables 	= wp_cache_get( $cache_name, 'simpods_tables' ); 

		if( ! empty( $tables ) ){
			if( isset( $tables['act_tables'] ) ){
				$act_tables = $tables['act_tables'];
				return $act_tables;
			}

		}

		$tables = get_option( 'simpods_all_tables', array() ); // integrated with Simpods MVC


		if( empty( $tables ) || ! is_array( $tables ) ){
			$sql_str       = 'SHOW TABLES LIKE "%"';
			$tables    = $wpdb->get_results( $sql_str );		
		}

		//$pods_tables = get_transient( 'pprf_pods_tables' ); // need to hook into Pods
		
		foreach( $tables as $idx_int => $table_obj ) {
			foreach( $table_obj as $table_name ) {
				$table = str_replace( $wpdb->prefix, '', $table_name );
				
				// return all tables
				if( $return_all_tables ){
					array_push( $pod_tables, $table );
				} else {

					if(  strpos( $table_name, $wpdb->prefix . 'pods_' ) === 0 ){
						
						$table_info	= $this->get_pods_tb_info( $table );
						$name_field	= get_post_meta( $table_info['id'], 'pod_index', true );

					//echo $table . ' - ' . $table_info['id'] . ' - ' .$name_field .'<br/>';
						$name_label	=	'';
						if( $name_field == '' ){
							
							if( $table_info['type'] == 'post_type' ){
								$name_field = 'post_title';	
							} else if( $table_info['type'] == 'user' ){
								$name_field = 'display_name';	
							} else {
								$name_field = 'sp_title';	
							}
						} else {
							$query 		= $wpdb->prepare(
															'SELECT ps_tb.post_title
															 FROM `' . $wpdb->posts . '` AS ps_tb																		  
															 WHERE ps_tb.`post_name` = %s AND ps_tb.`post_parent` = %d AND ps_tb.`post_type` = "_pods_field" LIMIT 0, 1', 
															 array( 
															 	$name_field, 
															 	$table_info['id'] 
															) 
														);
							
							$items		= $wpdb->get_results( $query , ARRAY_A ); 	

							if( $items && count( $items ) > 0 ){
								$name_label	= $items[0]['post_title'];
							} 						
						}						
						$pod_tables[ 'pod_' . $table_info['id'] ] = array( 'name' => $table, 'pod' => $table_info['name'], 'type' => $table_info['type'], 'name_field'    => $name_field, 'name_label' => $name_label );
						$pod_tables[ $table ] = $pod_tables[ 'pod_' . $table_info['id'] ];
					} else {
						$pod_tables[ $table ] = array( 'name' => $table, 'pod' => '', 'type' => 'wp', 'name_field'    => '', 'name_label' => '' );	
					}
		
				}
				
			}
		}
		// echo '<pre>';
		// print_r($pod_tables);
		// echo '</pre>';
		return $pod_tables;

	}	

	/**
	 * get_pods_tb_info: get pods table info 
	 */
	public function get_pods_tb_info( $table ){
		global $wpdb;


		// if prefix not found, add it to the target tb
		if( strpos( $table, $wpdb->prefix ) === 0 ){
			$table = substr( $table, strlen( $wpdb->prefix ) );	
		}	
			
		if( strpos( $table, 'pods_' ) === 0 ){
			$table = substr( $table, 5 );	
		}	

		$table_data = wp_cache_get( $table, 'pprf_table_data' );

		if ( false === $table_data ) {
			$table_info = wp_cache_get( $table, 'simpods_pods_tables_info' ); 	// integrated with Simpods	
			if( false !== $table_info ){			
				return $table_info;		
			}	
			$query 		= $wpdb->prepare('SELECT ps_tb.*, pm_tb.`meta_value` AS type
											 FROM `' . $wpdb->posts . '` AS ps_tb
											 LEFT JOIN `' . $wpdb->postmeta . '` AS pm_tb ON ps_tb.`ID` = pm_tb.`post_id` AND pm_tb.`meta_key` = "type"					  
											 WHERE ps_tb.`post_name` = "%s" AND ps_tb.`post_type` = "_pods_pod" LIMIT 0, 1', array( $table ) );
			$items_arr 		= $wpdb->get_results( $query , ARRAY_A ); 		
			
			$table_data  = array( 'id' => 0, 'name' => '', 'type' => '' );
			if( count( $items_arr ) > 0 ){
				$table_data['id']   = $items_arr[0]['ID']; 	
				$table_data['name'] = $items_arr[0]['post_name']; 	
				$table_data['type'] = $items_arr[0]['type'] == ''? 'pod' : $items_arr[0]['type'] ; 				
			}

			wp_cache_add( $table, $table_data, 'pprf_table_data' );

		}
		return $table_data;		
	}
	/**
	 * update_columns: check if a table column exists
	 * 
	 * @param string $table table name
	 * @use $this->check_column_existence() to check if a column exists
	 */
	public function update_columns( $table ){
		global $wpdb;
		$table = esc_sql( $table );
		foreach( self::$keys as $k_str => $v_arr ){
			
			$existing = $this->check_column_existence( 'pods_' . $table, $k_str );	
			
			if( ! $existing ){			
				 $query = 'ALTER TABLE  `' . $wpdb->prefix . 'pods_' . $table . '` ADD `' . $k_str . '` ' . implode( ' ', $v_arr );
				 $wpdb->query( $query ) ;
								
			} 
		}

		pprf_updated_tables( $table, 'add' );	

	}	
	/**
	 * backward compatibility
	 */ 
	public function update_columns_fn( $table ){
		$this->update_columns( $table );
	}	
	/**
	 * check_column_existence: check if a table column exists
	 * 
	 * @param string $table table name
	 * @param string $column table name	 
	 */
	public function check_column_existence( $table, $column ){
		global $wpdb;
		
		$result = $wpdb->query( 'SHOW COLUMNS FROM `' . $wpdb->prefix . esc_sql( $table ). '` LIKE "' . esc_sql( $column ). '"' );	
		
		// option _transient_pods_field_catitem_testsss
		return $result;		
	}	
	/**
	 * getFields_fn function: get field names of a table
	 *
	 * @var string  $table targeted table	 
	 */
	public function get_fields( $table, $add_prefix = true , $shown = false ){
		global $wpdb;
		
		$table  = esc_sql( stripslashes( $table ) );

		if( $add_prefix && stripos( $table, $wpdb->prefix ) !== 0 ){
			$table = $wpdb->prefix . $table;
		}				
		
		$query  = 'SHOW FIELDS FROM `' . $table . '`';
		if( $shown ){
			echo $query ;
		}		
		$items  = $wpdb->get_results( $query , ARRAY_A );
		
		return $items;
	}	

	/**
	 * If the field applys admin table columns, return the columns and label
	 * @param string $parent_table parent table pod name
	 * @param string $child_table child table pod name
	 * @param int $field_id the repeater field id
	 * @param int $row_id the child table row id
	 * @return array if it valid
	 */
	public function get_admin_columns( $parent_table, $child_table, $field_id, $row_id = 0 ){
		//require_once ABSPATH . '/wp-content/plugins/pods/init.php';
		$return_data		=	array(
								'valid'		=>	false,
								'columns'	=>	array(),
								'label'		=>	'',
								);
		$admin_columns	=	array(); // if apply admin columns is picked, use admin columns instead of name
		$parent_pod 	=	new pods( $parent_table );

		if( $parent_pod ){
			foreach( $parent_pod->fields as $field_data ){
			
				if( $field_data['id'] == $field_id ){
					
					if( isset( $field_data['options']['pandarepeaterfield_apply_admin_columns'] ) && $field_data['options']['pandarepeaterfield_apply_admin_columns'] == 1 ){
						$child_pod 		= new pods( $child_table );
						
						if( $child_pod ){
						
							$admin_columns 	= (array) pods_v( 'ui_fields_manage', $child_pod->pod_data['options'] );				

						}
					}
					break;
				}
			}
		}

		if( count( $admin_columns ) > 0 ){
			$return_data['valid']	=	true;
			$return_data['columns']	=	$admin_columns;
			$label_html				=	'';
			if( $row_id !== 0 && is_numeric( $row_id ) ){
				$is_id	=	false;
				foreach( $admin_columns as $admin_column_name ){
					if( strtolower( $admin_column_name ) == 'id' ){
						$is_id	=	true;
						continue;
					}					
					$column_value	=	pods_field( $child_table, $row_id, $admin_column_name );
					if( is_string( $column_value ) || is_numeric( $column_value ) ){
						$label_html .= '<strong>' . esc_html( $child_pod->fields[ $admin_column_name ]['label'] ) . ':</strong> ' . esc_html( $column_value ) . ' ' ;
					}				
				}	
				if( $is_id ){
					$label_html = '<strong>ID:</strong> ' . esc_html( $row_id ) . ' ' . $label_html;
				}					
			}
			$return_data['label']	=	$label_html;
		}

		return $return_data;

	}

}