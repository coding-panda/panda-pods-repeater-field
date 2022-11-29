<?php
/**
* collection of functions can be used pods panda repeater field database
*
* @package panda_pods_repeater_field
* @author Dongjie Xu
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
			$items 		= $wpdb->get_results( $query , ARRAY_A ); 		
			
			$table_data  = array( 'id' => 0, 'name' => '', 'type' => '' );
			if( count( $items ) > 0 ){
				$table_data['id']   = $items[0]['ID']; 	
				$table_data['name'] = $items[0]['post_name']; 	
				$table_data['type'] = $items[0]['type'] == ''? 'pod' : $items[0]['type'] ; 				
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

	/**
	 * Duplicate the data of a repeater field 	 
	 */ 
    public function duplicate( $params = array() ){
    	global $wpdb;
    	$defaults = array(
    		'pod_name'  			=> '',    		
    		'parent_pod_id'			=> 0,    	
    		'parent_id' 			=> 0,    	
    		'parent_pod_field_id'	=> 0,    
    		'new_parent_id' 		=> 0,	
    		'item_id' 				=> 0,
    	);
    	$args 		= wp_parse_args( $params, $defaults );
    	if( empty( $args['pod_name'] ) ){
    		return false;
    	}
    	$now 	= date( 'Y-m-d H:i:s' );   
    	$args['pod_name'] 				= esc_sql( $args['pod_name'] );
    	$args['parent_pod_id'] 			= (int) $args['parent_pod_id'];
    	$args['parent_id'] 				= (int) $args['parent_id'];
    	$args['parent_pod_field_id'] 	= (int) $args['parent_pod_field_id'];
    	$args['new_parent_id'] 			= (int) $args['new_parent_id'];
    	$args['item_id'] 				= (int) $args['item_id'];


    	$return = array(
    		'new_id' => 0,
    	);
		$pod    = pods( $args['pod_name'] ); 


		if( $pod ){

	        $pod_fields 	= $pod->fields();    

	        $panda_fields 	= array();
	        // find out the repeater fields
	        if( $pod_fields ){
	        	foreach( $pod_fields as $field_name => $field_data ){
	        		if( 'pandarepeaterfield' == $field_data->type ){

	        			$panda_fields[ $field_name ] = array(
	        				'pod_name' 				=> $field_data->pandarepeaterfield_table,
	        				'parent_pod_id'			=> $pod->pod_data->id,    	
	        				'parent_pod_field_id'	=> $field_data->id,    	
	        			);
	      
	        		}
	        	}			
	        }

			$table  = 'pods_' . $args['pod_name'];

			if( $args['item_id'] !== 0 ){ // only duplicate one
				$data 	= array(
					$args['item_id']
				);

				$query 	= $wpdb->prepare('SELECT * 
					FROM `' . $wpdb->prefix . $table .'` 				  
					WHERE `id` = %d', 
					$data
				);

			} else { // duplicate all children
				$data 	= array(
					$args['parent_pod_id'],
					$args['parent_id'],
					$args['parent_pod_field_id'],
				);

				$query 	= $wpdb->prepare('SELECT * 
					FROM `' . $wpdb->prefix . $table .'` 				  
					WHERE `pandarf_parent_pod_id` = %d AND 
						`pandarf_parent_post_id` = %d AND 
						`pandarf_pod_field_id` = %d', 
					$data
				);
			}

			$rows 			= $wpdb->get_results( $query , ARRAY_A ); 	

			$date_fields 	= array(
				'pandarf_created',
				'pandarf_modified',
				'sp_created',
				'sp_modified',
			);
			$to_unset = array(
				'id',
				'sp_start',
				'sp_end',
				'deadline',
			);


			foreach( $rows as $i => $row ){
				//
				$old_id 		= $row['id'];							
				$new_id 		= $pod->duplicate( $row['id'] );    
		    	$return['new_id'] = $new_id;
		
				$row['pandarf_parent_post_id'] = $args['new_parent_id'];											

				foreach( $date_fields as $date_field ){
					if( isset( $row[ $date_field ] ) ){
						$row[ $date_field ] = $now;
					}
				}	
				foreach( $to_unset as $unset_field ){
					if( isset( $row[ $unset_field ] ) ){
						unset( $row[ $unset_field ] );
					}
				}				
	            $where  =   array(
	                'id' 	=> $new_id,                    
	            );

	            $updated  	= $this->update( $wpdb->prefix . $table, $row, $where );    

				foreach( $row as $key => $value ){
		            if( isset( $panda_fields[ $key ] ) ){

	        			$panda_fields[ $key ]['parent_id'] 		= $old_id;
	        			$panda_fields[ $key ]['new_parent_id'] 	= $new_id;		    		

	        			$this->duplicate( $panda_fields[ $key ] );	            	
		            }
	        	}
			} 			
	        // use pods to duplicate in case there are some pods relationship fields
	         	
			//get all items from the database directly, don't use methods from classes as they may alter the data
   	
		}
		return $return;
    }
	/**
	 * Update the data of a repeater field 	 
	 */ 
    public function update( $table, $data, $where ){
    	global $wpdb;
    	if( ! is_string( $table ) || !is_array( $data ) || ! is_array( $where ) || empty( $data ) || empty( $where )  ){
    		return false;
    	}
    	$table 		= esc_sql( $table );
		$updates 	= array();	
		$values 	= array();	
		
		foreach ( $data as $key => $value ) {	
			if ( is_numeric( $value ) ) {
				$type = strpos( $value, '.' ) !== false ? '%f' : '%d';
			} else {
				$type = '%s';
			}
			array_push( $updates, '`' . $key . '` = ' . $type );
			array_push( $values, $value );
		}
		$update_sql = implode( ',', $updates );   

		$searches 	= array();
		$count 		= count( $where ); 	
		foreach ( $where as $key => $value ) {			
			if ( is_numeric( $value ) ) {
				strpos( $value, '.' ) !== false ? $type = '%d' : $value_type = '%f';
			} else {
				$type = '%s';
			}
			array_push( $searches, '`' . $key . '` = ' . $type );
			array_push( $values, $value );
		}

		$search_sql = implode( ' AND ', $searches );
		
		$query 		= $wpdb->prepare( 'UPDATE `' . $table . '` SET ' . $update_sql . ' WHERE ' . $search_sql, $values );
		$updated 	= $wpdb->query( $query );


		return $updated;
		
    }

	/**
	 * Delete all data in all posterity if the "Delete item descendants" option is picked
	 */ 
   public function delete_item_descendants( $params = array() ){
    	global $wpdb;
    	// a filter to decide if you want to carry on
    	$carry_on = true;
    	$carry_on = apply_filters( 'pprf_carry_on_delete_item_descendants', $carry_on, $params );

    	if( $carry_on != true ){
    		return false;
    	}

    	$defaults = array(
    		'pod_name'  			=> '',    		       		
    		'item_id' 				=> 0,
    	);
    	$args 		= wp_parse_args( $params, $defaults );
    	if( empty( $args['pod_name'] ) || empty( $args['item_id'] ) ){
    		return false;
    	}
    	$now 	= date( 'Y-m-d H:i:s' );   
    	$args['pod_name'] 				= esc_sql( $args['pod_name'] );
    	$args['item_id'] 				= (int) $args['item_id'];

		$pod    	= pods( $args['pod_name'] ); 

        $pod_fields = $pod->fields();   

        if( $pod_fields ){
        	foreach( $pod_fields as $field_name => $field_data ){
        		if( 'pandarepeaterfield' == $field_data->type ){
        			
        			if( ! empty( $field_data->pandarepeaterfield_delete_data_tree ) ){
        				
						$for_child_data = array(
							'child_pod_name' 	  => $field_data->pandarepeaterfield_table,	
							'parent_pod_id'		  => $pod->pod_data->id,    	
							'parent_pod_post_id'  => $args['item_id'], 
							'parent_pod_field_id' => $field_data->id, 					    	
				    	);													
	        			
	        			$for_pandarf_items = array(
	        				'include_trashed' => true,
	        			);
	        			$child_data = get_pandarf_items( $for_child_data, $for_pandarf_items );
						$for_repeater_pod = array(
				    		'pod_name'  			=> $field_data->pandarepeaterfield_table,			       		
				    		'item_id' 				=> 0,
	        			);         			
	        			// delete data   
	        			if( ! empty( $child_data ) ){
	        				foreach( $child_data as $child ){        							
			        			// Send the child pod into the same procedure. Do it before deleting the parent item so if something goes wrong, the parent item is still available.
			        			$for_repeater_pod['item_id'] = $child['id'];
			        			$this->delete_item_descendants( $for_repeater_pod );        					
								$table = $wpdb->prefix . 'pods_' . $field_data->pandarepeaterfield_table;
								$query = $wpdb->prepare( 'DELETE FROM `' . $table . '` WHERE `id` = %d', $child['id'] );

								$wpdb->query( $query );
	        				}
	        			}   
        			}
        		}
        	}
        }  		    	
    }    
}