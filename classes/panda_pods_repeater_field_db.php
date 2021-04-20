<?php
/**
* collection of functions can be used pods panda repeater field database
*
* @package panda_pods_repeater_field
* @author: Dongjie Xu
* @since 20/05/2014 
*/
class panda_pods_repeater_field_db {

	public static $keys_arr = array( 
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
	 * escape_fn function: escape data
	 *
	 * @var string  $table_str targeted table
	 * @var array   $data_arr  posted data, data to save in $_POST format	 
	 * @var array   $where_arr locate the entries to update
	 */
	public function escape_fn( $data_ukn ){
		if( is_array( $data_ukn ) ){
			foreach( $data_ukn as $key_str => $val_ukn ){
				$key_str 	 = esc_sql(  $key_str  );
				if( is_array( $val_ukn ) ) {
					$val_ukn =	$this->escape_fn( $val_ukn );
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
	 * escape_attrs_fn function: escape data using esc_attr
	 *
	 * @var string  $table_str targeted table
	 * @var array   $data_arr  posted data, data to save in $_POST format	 
	 * @var array   $where_arr locate the entries to update
	 */
	public function escape_attrs_fn( $data_ukn ){
		if( is_array( $data_ukn ) ){
			foreach( $data_ukn as $key_str => $val_ukn ){
				$key_str 	 = esc_attr(  $key_str  );
				if( is_array( $val_ukn ) ) {
					$val_ukn =	$this->escape_fn( $val_ukn );
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
	 * @param: $allTables_bln Boolean return all tables or not
	 */
	public function get_tables_fn( $allTables_bln = false )	{
		global $wpdb;

		$podsTb_arr    = array();
		
		$tables_arr = get_option( 'simpods_all_tables', array() ); // integrated with Simpods MVC

		if( empty( $tables_arr ) || ! is_array( $tables_arr ) ){
			$sql_str       = 'SHOW TABLES LIKE "%"';
			$tables_arr    = $wpdb->get_results( $sql_str );		
		}

		//$pods_tables = get_transient( 'pprf_pods_tables' ); // need to hook into Pods
		
		foreach( $tables_arr as $idx_int => $table_obj ) {
			foreach( $table_obj as $tableName_str ) {
				$table_str = str_replace( $wpdb->prefix, '', $tableName_str );
				
				// return all tables
				if( $allTables_bln ){
					array_push( $podsTb_arr, $table_str );
				} else {
					//if( !isset( $pods_tables[ $table_str ] ) ){
						// only return pods tables
						if(  strpos( $tableName_str, $wpdb->prefix . 'pods_' ) === 0 ){
							
							//array_push( $podsTb_arr, $table_str );	
							$tbInfo_arr				 	 = $this->get_pods_tb_info_fn( $table_str );
							$nameField_str				 = get_post_meta( $tbInfo_arr['id'], 'pod_index', true );

						//print_r( $tbInfo_arr );
							$nameLabel_str	=	'';
							if( $nameField_str == '' ){
								
								if( $tbInfo_arr['type'] == 'post_type' ){
									$nameField_str = 'post_title';	
								} else if( $tbInfo_arr['type'] == 'user' ){
									$nameField_str = 'display_name';	
								} else {
									$nameField_str = 'sp_title';	
								}
							} else {
								$query_str 		= $wpdb->prepare(
																'SELECT ps_tb.post_title
																 FROM `' . $wpdb->posts . '` AS ps_tb																		  
																 WHERE ps_tb.`post_name` = %s AND ps_tb.`post_parent` = %d AND ps_tb.`post_type` = "_pods_field" LIMIT 0, 1', 
																 array( 
																 	$nameField_str, 
																 	$tbInfo_arr['id'] 
																 ) 
																);
								$items_arr 		= $wpdb->get_results( $query_str , ARRAY_A ); 	

								if( $items_arr && count( $items_arr ) > 0 ){
									$nameLabel_str	=	 $items_arr[0]['post_title'];
								}							
							}						
							$podsTb_arr[ 'pod_' . $tbInfo_arr['id'] ] = array( 'name' => $table_str, 'pod' => $tbInfo_arr['name'], 'type' => $tbInfo_arr['type'], 'name_field'    => $nameField_str, 'name_label' => $nameLabel_str );
							$podsTb_arr[ $table_str ] = $podsTb_arr[ 'pod_' . $tbInfo_arr['id'] ];
						} else {
							$podsTb_arr[ $table_str ] = array( 'name' => $table_str, 'pod' => '', 'type' => 'wp', 'name_field'    => '', 'name_label' => '' );	
						}
					//}
				}
				
			}
		}
		//if( ! $allTables_bln ){
			//set_transient( 'pprf_pods_tables', $podsTb_arr, 2000 ); 
		//}
		//wp_cache_set ( 'PPRF_ALL_TABLES', serialize( $podsTb_arr ) , 60*60*24 ); 
		//$podsTb_arr = array_values( $podsTb_arr );
		return $podsTb_arr;

	}	

	/**
	 * get_pods_tb_info_fn: get pods table info 
	 */
	public function get_pods_tb_info_fn( $tb_str ){
		global $wpdb;
		$theTb_str	=	$tb_str;
		/*if( !isset( $_GET['page'] ) || ( isset( $_GET['page'] ) && $_GET['page'] != 'pods' && $_GET['page'] != 'pods-add-new' ) ){ // don't return cached if on add/edit pods so new tables will be added
			$saved_str  = wp_cache_get( 'PPRF_' . $theTb_str );

			if( $saved_str ){
				return maybe_unserialize( $saved_str );
			}
		}*/		
		$tbPrefix_str  = $wpdb->prefix;
		// if prefix not found, add it to the target tb
		if( strpos( $tb_str, $wpdb->prefix ) === 0 ){
			$tb_str = substr( $tb_str, strlen( $wpdb->prefix ) );	
		}	else {
			//$tbPrefix_str   = $wpdb->base_prefix;
			//$tb_str 		= substr( $tb_str, strlen( $wpdb->base_prefix ) );	
		}		
			
		if( strpos( $tb_str, 'pods_' ) === 0 ){
			$tb_str = substr( $tb_str, 5 );	
		}		
		$query_str 		= $wpdb->prepare('SELECT ps_tb.*, pm_tb.`meta_value` AS type
										 FROM `' . $wpdb->posts . '` AS ps_tb
										 LEFT JOIN `' . $wpdb->postmeta . '` AS pm_tb ON ps_tb.`ID` = pm_tb.`post_id` AND pm_tb.`meta_key` = "type"					  
										 WHERE ps_tb.`post_name` = "%s" AND ps_tb.`post_type` = "_pods_pod" LIMIT 0, 1', array( $tb_str ) );
		$items_arr 		= $wpdb->get_results( $query_str , ARRAY_A ); 		
		
		$tableInfo_arr  = array( 'id' => 0, 'name' => '', 'type' => '' );
		if( count( $items_arr ) > 0 ){
			$tableInfo_arr['id']   = $items_arr[0]['ID']; 	
			$tableInfo_arr['name'] = $items_arr[0]['post_name']; 	
			$tableInfo_arr['type'] = $items_arr[0]['type'] == ''? 'pod' : $items_arr[0]['type'] ; 				
		}
		
		//wp_cache_set ( 'PPRF_' . $theTb_str, serialize( $tableInfo_arr ) , 60*60*24 ); 
		return $tableInfo_arr;		
	}
	/**
	 * update_columns: check if a table column exists
	 * 
	 * @param string $tb_str table name
	 * @use $this->column_exist_fn() to check if a column exists
	 */
	public function update_columns_fn( $tb_str ){
		global $wpdb;
		$tb_str = esc_sql( $tb_str );
		foreach( self::$keys_arr as $k_str => $v_arr ){
			
			$exist_bln = $this->column_exist_fn( 'pods_' . $tb_str, $k_str );	
			
			if( !$exist_bln ){
			//print_r( $tb_str . $k_str);
				 $query_str = 'ALTER TABLE  `' . $wpdb->prefix . 'pods_' . $tb_str . '` ADD `' . $k_str . '` ' . implode( ' ', $v_arr );
				 $wpdb->query( $query_str ) ;
								
			} else {
				// fix the order as string problem
				/*if( $k_str == 'pandarf_order' ){
					$query_str = 'ALTER TABLE  `' . $wpdb->prefix . 'pods_' . $tb_str . '` CHANGE  `' . $k_str . '`  `' . $k_str . '` INT( 11 ) NOT NULL;';	
					$wpdb->query( $query_str ) ;
				}*/				
			}
		}

		pprf_updated_tables( $tb_str, 'add' );	

	}	
	/**
	 * column_exist_fn: check if a table column exists
	 * 
	 * @param string $tb_str table name
	 * @param string $column_str table name	 
	 */
	public function column_exist_fn( $tb_str, $column_str ){
		global $wpdb;
		
		$result_bln = $wpdb->query( 'SHOW COLUMNS FROM `' . $wpdb->prefix . esc_sql( $tb_str ). '` LIKE "' . esc_sql( $column_str ). '"' );	
		
		// option _transient_pods_field_catitem_testsss
		return $result_bln;		
	}	
	/**
	 * getFields_fn function: get field names of a table
	 *
	 * @var string  $table_str targeted table	 
	 */
	public function get_fields_fn( $table_str, $prefixMe_bln = true , $show_bln = false ){
		global $wpdb;
		
		$table_str  = esc_sql( stripslashes( $table_str ) );

		if( $prefixMe_bln && stripos( $table_str, $wpdb->prefix ) !== 0 ){
			$table_str = $wpdb->prefix . $table_str;
		}				
		//$query_str  = $wpdb->prepare( 'SHOW FIELDS FROM `' . $table_str . '`' , array(  ) );
		$query_str  = 'SHOW FIELDS FROM `' . $table_str . '`';
		if( $show_bln ){
			echo $query_str ;
		}		
		$items_arr  = $wpdb->get_results( $query_str , ARRAY_A );
		
		return $items_arr;
	}	

	/**
	 * If the field applys admin table columns, return the columns and label
	 * @param string $parentTb_str parent table pod name
	 * @param string $childTb_str child table pod name
	 * @param int $fieldID_int the repeater field id
	 * @param int $rowID_int the child table row id
	 * @return array if it valid
	 */
	public function get_admin_columns_fn( $parentTb_str, $childTb_str, $fieldID_int, $rowID_int = 0 ){
		//require_once ABSPATH . '/wp-content/plugins/pods/init.php';
		$return_arr		=	array(
								'valid'		=>	false,
								'columns'	=>	array(),
								'label'		=>	'',
								);
		$admin_columns	=	array(); // if apply admin columns is picked, use admin columns instead of name
		$parent_pod 	=	new pods( $parentTb_str );
		//echo PODS_VERSION . ' - ' . $parentTb_str . ' | ' . $childTb_str . ' | ' . $fieldID_int . ' | ' . $rowID_int;
		//print_r($parent_pod);
		if( $parent_pod ){
			foreach( $parent_pod->fields as $field_arr ){
			
				if( $field_arr['id'] == $fieldID_int ){
					
					if( isset( $field_arr['options']['pandarepeaterfield_apply_admin_columns'] ) && $field_arr['options']['pandarepeaterfield_apply_admin_columns'] == 1 ){
						$child_pod 		= new pods( $childTb_str );
						
						if( $child_pod ){
						
							$admin_columns 	= (array) pods_v( 'ui_fields_manage', $child_pod->pod_data['options'] );					
						}
					}
					break;
				}
			}
		}
		if( count( $admin_columns ) > 0 ){
			$return_arr['valid']	=	true;
			$return_arr['columns']	=	$admin_columns;
			$label_str				=	'';
			if( $rowID_int !== 0 && is_numeric( $rowID_int ) ){
				$id_bln	=	false;
				foreach( $admin_columns as $admin_column_name ){
					if( strtolower( $admin_column_name ) == 'id' ){
						$id_bln	=	true;
						continue;
					}					
					$column_value	=	pods_field( $childTb_str, $rowID_int, $admin_column_name );
					if( is_string( $column_value ) || is_numeric( $column_value ) ){
						$label_str .= '<strong>' . esc_html( $child_pod->fields[ $admin_column_name ]['label'] ) . ':</strong> ' . esc_html( $column_value ) . ' ' ;
					}				
				}	
				if( $id_bln ){
					$label_str = '<strong>ID:</strong> ' . esc_html( $rowID_int ) . ' ' . $label_str;
				}					
			}
			$return_arr['label']	=	$label_str;
		}

		//print_r($return_arr);
		return $return_arr;

	}
}