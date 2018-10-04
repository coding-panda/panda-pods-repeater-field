<?php
/**
* collection of functions can be used pods panda repeater field database
*
* @version: 1.0.0
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
	 * get_tables_fn: get tables from database, by default, only return wp_posts, wp_users and pods tables
	 * 
	 * @param: $allTables_bln Boolean return all tables or not
	 */
	public function get_tables_fn( $allTables_bln = false )	{
		global $wpdb;
		$podsTb_arr    = array();
		$sql_str       = 'SHOW TABLES LIKE "%"';
		$tables_arr    = $wpdb->get_results( $sql_str );				
		
		
		foreach( $tables_arr as $idx_int => $table_obj ) {
			foreach( $table_obj as $tableName_str ) {
				$table_str = str_replace( $wpdb->prefix, '', $tableName_str );
				
				// return all tables
				if( $allTables_bln ){
					array_push( $podsTb_arr, $table_str );
				} else {
					// only return pods tables
					if(  strpos( $tableName_str, $wpdb->prefix . 'pods_' ) === 0 ){
						//array_push( $podsTb_arr, $table_str );	
						$tbInfo_arr				 	 = $this->get_pods_tb_info_fn( $table_str );
						$nameField_str				 = get_post_meta( $tbInfo_arr['id'], 'pod_index', true );
					//print_r( $tbInfo_arr );
						if( $nameField_str == '' ){
							
							if( $tbInfo_arr['type'] == 'post_type' ){
								$nameField_str = 'post_title';	
							} else if( $tbInfo_arr['type'] == 'user' ){
								$nameField_str = 'display_name';	
							} else {
								$nameField_str = 'sp_title';	
							}
						}						
						$podsTb_arr[ 'pod_' . $tbInfo_arr['id'] ] = array( 'name' => $table_str, 'pod' => $tbInfo_arr['name'], 'type' => $tbInfo_arr['type'], 'name_field'    => $nameField_str, );
					} else {
						$podsTb_arr[ $table_str ] = array( 'name' => $table_str, 'pod' => '', 'type' => 'wp', 'name_field'    => '', );	
					}
				}
				
			}
		}
		
		//$podsTb_arr = array_values( $podsTb_arr );
		return $podsTb_arr;

	}	

	/**
	 * get_pods_tb_info_fn: get pods table info 
	 */
	public function get_pods_tb_info_fn( $tb_str ){
		global $wpdb;
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
		
		return $tableInfo_arr;		
	}
	/**
	 * update_columns_fn: check if a table column exists
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
}
?>