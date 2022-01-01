<?php
if ( preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF']) ) { die('You are not allowed to call this page directly.'); }
/**
* AJax class, collection for AJax functions
*
* @package panda-pods-repeater-field
* @author Dongjie Xu
* @since 09/02/2016 
*/
class Panda_Pods_Repeater_Field_Ajax {

	function __construct() {
		$this->define_pprf_all_tables();
		$this->actions();		
		
	}

    protected function actions(){			
		// login user only, for everyone, use wp_ajax_nopriv_ example: add_action( 'wp_ajax_function-name', 			array( $this, 'function-name') );				
		//if( is_user_logged_in() && is_admin() ){
			add_action( 'wp_ajax_admin_pprf_load_newly_added', 		array( $this, 'admin_pprf_load_newly_added') );	
			add_action( 'wp_ajax_admin_pprf_delete_item', 			array( $this, 'admin_pprf_delete_item') );	
			add_action( 'wp_ajax_admin_pprf_update_order', 			array( $this, 'admin_pprf_update_order') );							
			add_action( 'wp_ajax_admin_pprf_load_more', 				array( $this, 'admin_pprf_load_more') );				
			add_action( 'wp_ajax_admin_pprf_reassign', 				array( $this, 'admin_pprf_reassign') );				
						
			// frontend

			//add_action( 'wp_ajax_front_pprf_load_newly_added', 		array( $this, 'front_pprf_load_newly_added') );	
			//add_action( 'wp_ajax_front_pprf_delete_item', 			array( $this, 'front_pprf_delete_item') );	
			//add_action( 'wp_ajax_front_pprf_update_order', 			array( $this, 'front_pprf_update_order') );					
		//}	

		add_action( 'wp_ajax_nopriv_admin_pprf_load_newly_added', 		array( $this, 'admin_pprf_load_newly_added') );	
		add_action( 'wp_ajax_nopriv_admin_pprf_delete_item', 				array( $this, 'admin_pprf_delete_item') );	
		add_action( 'wp_ajax_nopriv_admin_pprf_update_order', 			array( $this, 'admin_pprf_update_order') );							
		add_action( 'wp_ajax_nopriv_admin_pprf_load_more', 				array( $this, 'admin_pprf_load_more') );				
		add_action( 'wp_ajax_nopriv_admin_pprf_reassign', 				array( $this, 'admin_pprf_reassign') );							
/*		add_action( 'wp_ajax_nopriv_front_pprf_load_newly_added', 		array( $this, 'front_pprf_load_newly_added') );	
		add_action( 'wp_ajax_nopriv_front_pprf_delete_item', 			array( $this, 'front_pprf_delete_item') );	
		add_action( 'wp_ajax_nopriv_front_pprf_update_order', 			array( $this, 'front_pprf_update_order') );		*/		

	}
	

	public function define_pprf_all_tables(){
		if( ! defined( 'PPRF_ALL_TABLES' ) ){				
			$db_cla      = new panda_pods_repeater_field_db();
			$tables_arr  = $db_cla->get_tables();
			define( 'PPRF_ALL_TABLES', maybe_serialize( $tables_arr ) );	
		} 
	}
	/**
	 * find out the last inserted id
	 */
	public function pprf_load_newly_added(){
	
		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {

			$data_arr	=	array( 'security' => false );
		    wp_send_json_error( $data_arr );

		} 		
		global $wpdb, $current_user;

        $tables_arr  = maybe_unserialize( PPRF_ALL_TABLES );
		
		if( isset( $_POST['podid'] ) && is_numeric( $_POST['podid'] ) && isset( $_POST['cpodid'] ) && is_numeric( $_POST['cpodid'] ) && isset( $_POST['postid'] )  && isset( $_POST['authorid'] ) && is_numeric( $_POST['authorid'] ) && isset( $_POST['poditemid'] ) && is_numeric( $_POST['poditemid'] ) ){
			// update panda keys
			if( array_key_exists( 'pod_' . $_POST['cpodid'], $tables_arr ) ){
				
				//$now		= date('Y-m-d H:i:s');
				$table_str 	 	= $wpdb->prefix . $tables_arr['pod_' . $_POST['cpodid'] ]['name'] ;	
				$title		= '';
				if( $tables_arr['pod_' . $_POST['cpodid'] ]['name_field'] != '' ){
					$title		= '' . $tables_arr['pod_' . $_POST['cpodid'] ]['name_field'] ;		
					  
				}
				// if it is a wordpress post type, join wp_posts table
				$join_sql  = '';
				
				if( $tables_arr['pod_' . $_POST['cpodid'] ]['type'] == 'post_type' ){
					$join_sql = 'INNER JOIN  `' . $wpdb->prefix . 'posts` AS post_tb ON post_tb.ID = t.id';
				}

				// fetch the child item data
				$where_sql   	= '   `t`.`pandarf_parent_pod_id`  = %d
							   	  AND `t`.`pandarf_parent_post_id` = %d
							   	  AND `t`.`pandarf_pod_field_id`   = %d '; 				
				//$query_str  	= $wpdb->prepare( 'SELECT id FROM `' . $table_str . '` AS t WHERE `t`.`pandarf_categories` REGEXP "(:\"' . $_POST['podid'] . '.' . $_POST['postid'] . '.' . $_POST['poditemid'] . '\";{1,})" AND `t`.`pandarf_author` = %d ORDER BY `t`.`id` DESC LIMIT 0, 1;' , array( $_POST['authorid'] ) );	
				$where_arr		= array( $_POST['podid'], $_POST['postid'],  $_POST['poditemid'], $_POST['authorid'] );
				$qtitle_str		= $title != ''? ', `' . $title . '`' : '';
				$query_str  	= $wpdb->prepare( 'SELECT t.`id` ' . $qtitle_str . ' 
													FROM `' . $table_str . '` AS t 
													' . $join_sql . '
													WHERE ' . $where_sql . ' AND `t`.`pandarf_author` = %d 
													ORDER BY `t`.`id` DESC LIMIT 0, 1;' , 
													$where_arr );	
				//echo $query_str;
				$item_arr   	= $wpdb->get_results( $query_str, ARRAY_A );	
				if( is_array( $item_arr ) && isset( $item_arr[0]['id'] ) ){
					if( !isset( $item_arr[0][ $title ] ) ){
						$item_arr[0][ $title ] = '';
					}

					$data_arr	=	array( 
										'id' 				=> $item_arr[0]['id'], 
										'title' 			=> substr( preg_replace( '/\[.*?\]/is', '',  wp_strip_all_tags( $item_arr[0][ $title ] ) ), 0, 80 ) . pprf_check_media_in_content( $item_arr[0][ $title ] ) , 
										'pprf_name_label' 	=> $tables_arr['pod_' . $_POST['cpodid'] ]['name_label'], 										
										'label' 			=> '' 
									);
					wp_send_json_success( $data_arr ); 	
				} else {
					$data_arr	=	array( 'id' => '', 'title' => '', 'name_label' => '', 'label' => '' );
					wp_send_json_error( $data_arr );	
				}
				
			}
		}
		die();
	}
	public function admin_pprf_load_newly_added(){
		
		if( $_POST['action'] != 'admin_pprf_load_newly_added' ){
			wp_send_json_error( );	
		}
		$this->pprf_load_newly_added();
	}	
	public function front_pprf_load_newly_added(){
		
		if( $_POST['action'] != 'front_pprf_load_newly_added' ){
			wp_send_json_error( );	
		}
		$this->pprf_load_newly_added();
	}		
	/**
	 * delete item
	 */
	public function pprf_delete_item(){	

		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {
			$data_arr	=	array( 'security' => false );
		    wp_send_json_error( $data_arr );
		} 				
		global $wpdb, $current_user;

        $tables_arr  = maybe_unserialize( PPRF_ALL_TABLES );

		if( isset( $_POST['podid'] ) && is_numeric( $_POST['podid'] ) && isset( $_POST['cpodid'] ) && is_numeric( $_POST['cpodid'] ) && isset( $_POST['postid'] ) && isset( $_POST['authorid'] ) && is_numeric( $_POST['authorid'] ) && isset( $_POST['itemid'] ) && is_numeric( $_POST['itemid'] ) && isset( $_POST['poditemid'] ) && is_numeric( $_POST['poditemid'] ) ){
			// update panda keys
			if( array_key_exists( 'pod_' . $_POST['cpodid'], $tables_arr ) ){
				
				//$now		= date('Y-m-d H:i:s');
				$table_str 	 	= $wpdb->prefix . $tables_arr['pod_' . $_POST['cpodid'] ]['name'] ;			  

				$where_arr		= array();
			    $join_sql		= 	'';        
				//check it is an Advanced Content Type or normal post type
			    $pDetails_arr	=	pprf_pod_details( $_POST['cpodid'] );
			    			            
			    if( $pDetails_arr ){
				    //normal post type fetch all published and draft posts
				    if( $pDetails_arr['type'] == 'post_type' ){
				    	 $join_sql	=	'INNER JOIN `' . $wpdb->prefix . 'posts` AS ps_tb ON ps_tb.`ID` = t.`id`';				    	
				    }
				}					
				// fetch the child item data and see if the item belong to the current post
				$where_sql   	= '   `t`.`pandarf_parent_pod_id`  = %d
							   	  AND `t`.`pandarf_parent_post_id` = %d
							   	  AND `t`.`pandarf_pod_field_id`   = %d '; 						
				//$query_str  	= $wpdb->prepare( 'SELECT id FROM `' . $table_str . '` AS t WHERE `t`.`pandarf_categories` REGEXP "(:\"' . $_POST['podid'] . '.' . $_POST['postid'] . '.' . $_POST['poditemid'] . '\";{1,})" AND `t`.`id` = %d ORDER BY `t`.`id` DESC LIMIT 0, 1;' , array( $_POST['itemid'] ) );	
				$where_arr		= array( $_POST['podid'], $_POST['postid'],  $_POST['poditemid'], $_POST['itemid'] );				   	  

			
				$query_str  	= $wpdb->prepare( 'SELECT * FROM `' . $table_str . '` AS t ' . $join_sql . ' WHERE ' . $where_sql . ' AND `t`.`id` = %d ORDER BY `t`.`id` DESC LIMIT 0, 1;' , $where_arr );	
			
				$item_arr   	= $wpdb->get_results( $query_str, ARRAY_A );	
				if( is_array( $item_arr ) && isset( $item_arr[0]['id'] ) && $_POST['itemid'] === $item_arr[0]['id'] ){
					
					//$query_str  	= $wpdb->prepare( 'DELETE FROM `' . $table_str . '` WHERE `id` = %d;' , array( $item_arr[0]['id'] ) );	
				//echo $query_str;
					//$deleted_bln   	= $wpdb->query( $query_str );
					$del_str	=	'delete';
					if( isset( $_POST['trash'] ) && $_POST['trash'] === '0' )	{
						
						$query_str  	= $wpdb->prepare( 'UPDATE `' . $table_str . '` SET `pandarf_trash` = 0 WHERE `id` = %d;' , array( $_POST['itemid'] ) );
						$deleted_bln 	=	$wpdb->query( $query_str );
						$del_str		=	'restore';
					}					
					if( isset( $_POST['trash'] ) && $_POST['trash'] === '1' )	{ // if $_POST['trash'] == 1, the table should be already updated
						
						$query_str  	= $wpdb->prepare( 'UPDATE `' . $table_str . '` SET `pandarf_trash` = 1 WHERE `id` = %d;' , array( $_POST['itemid'] ) );
						$deleted_bln 	=	$wpdb->query( $query_str );
						$del_str		=	'trash';
					}
					if( !isset( $_POST['trash'] ) || $_POST['trash'] === '2' )	{
						$pod_obj	 = pods( $tables_arr['pod_' . $_POST['cpodid'] ]['pod'], absint( $_POST['itemid'] ) ); 
						if ( $pod_obj->exists() ) { 
							$deleted_bln = $pod_obj->delete( $_POST['itemid'] );
						}
					
					}
					if( $deleted_bln ){
						$data_arr	= 	array( 
											'id'		 	=> $item_arr[0]['id'], 
											'pod_idx'	 	=> $tables_arr['pod_' . $_POST['cpodid'] ]['name_field'] , 
											'pod_idx_val' 	=> $item_arr[0][ $tables_arr['pod_' . $_POST['cpodid'] ]['name_field'] ],
											'ppod_fie_id'	=> $_POST['poditemid'],		
											'action'		=> $del_str,								
										) ;	

						wp_send_json_success( $data_arr ); 
					}					
				} else {
					$data_arr	=	array( 
										'id'		 	=> '', 
										'pod_idx'	 	=> '' , 
										'pod_idx_val' 	=> '',
										'ppod_fie_id'	=> '',		
										'action'		=> '',								
									) ;
					wp_send_json_error( $data_arr );	
				}
				
			}
		}
		die();
	}
	public function admin_pprf_delete_item(){
		if( $_POST['action'] !== 'admin_pprf_delete_item' ){
			die();
		}			
		$this->pprf_delete_item();
		
	}
	public function front_pprf_delete_item(){
		if( $_POST['action'] !== 'front_pprf_delete_item' ){
			die();
		}			
		$this->pprf_delete_item();
		
	}	
	/**
	 * update order
	 */
	public function pprf_update_order(){
	
		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {

			$data_arr	=	array( 'security' => false );
		    wp_send_json_error( $data_arr );

		} 		
		global $wpdb, $current_user;

        $tables_arr  = maybe_unserialize( PPRF_ALL_TABLES );
		$pprfID_str	= '';
		$return_data	= array( 'pprf_id'	=> '' );
		if( isset( $_POST['order'] ) ){
			for( $i = 0; $i < count( $_POST['order'] ); $i ++ ){
				$ids_arr = explode( '-', $_POST['order'][ $i ] );
				if( count( $ids_arr ) >= 3 ){
					// if the pods table is listed
					if( isset( $tables_arr[ 'pod_' . $ids_arr[1] ] ) ){						
						if( $pprfID_str === '' ){
							$pprfID_str	=	$ids_arr[1] . '-' . $ids_arr[3];
						}						
						// remove li and table id from ids_arr
						//$ids_arr = array_values( array_slice( $ids_arr, 2 ) );
						$query_str = $wpdb->prepare( 'UPDATE `' . $wpdb->prefix . esc_sql( $tables_arr[ 'pod_' . $ids_arr[1] ]['name'] ) . '`
														SET  `pandarf_order` =  %d 
													  WHERE  `id` = %d;', 
													  array( $i, $ids_arr[2] )
													);	
						$wpdb->query( $query_str );							
						//echo $query_str;
						$return_data['pprf_id']	=	$pprfID_str;
					}
					
				}
			}
			if( $pprfID_str !== '' ){
				wp_send_json_success( $return_data ); 
			} else {
				wp_send_json_error( $return_data );
			}
		} else {
			wp_send_json_error( $return_data );
		}
	}
	public function admin_pprf_update_order(){
		if( $_POST['action'] !== 'admin_pprf_update_order' ){
			$data_arr	=	array( 'action' => false );
		    wp_send_json_error( $data_arr );
		}	
		$this->pprf_update_order();
	}	

	/**
	 * load more
	 */
	public function pprf_load_more(){
		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {
			$data_arr	=	array( 'security' => false );
		    wp_send_json_error( $data_arr );
		} 		
		global $wpdb, $current_user;

        $tables_arr  = maybe_unserialize( PPRF_ALL_TABLES );

		$tb_str 	 = '';
		if( is_numeric( $_POST['saved_tb'] ) ){			
			$post_arr	 = get_post( absint( $_POST['saved_tb'] ), ARRAY_A );
			
			if( is_array( $post_arr ) && $post_arr['post_type'] == '_pods_pod' ){
				$tb_str 	 = $post_arr['post_name'];

			} else {
				wp_send_json_error(  );
			}
		} else {
			wp_send_json_error( );
		} 


		$where_sql  =   '   `pandarf_parent_pod_id`  = %d
					   	  AND `pandarf_parent_post_id` = %d
					   	  AND `pandarf_pod_field_id`   = %d '; 		
		$searches = 	array( $_POST['pod_id'], $_POST['post_id'], $_POST['pod_item_id'] );				

		// order
		$orderBy_str=	'CAST( `pandarf_order` AS UNSIGNED )';
		if( $_POST['order_by'] !== 'pandarf_order' ){
			$orderBy_str	=	'`' . esc_sql( $_POST['order_by'] ) . '`';			
		}

		$order_sql	=	'ASC';
		if( $_POST['order'] === 'DESC' ){
			$order_sql	=	'DESC';
		}		

		// limit
		$limit_sql	=	'';
		$limited	=	false;
			
		$limit_sql	=	'LIMIT %d, %d';			
		array_push( $searches, $_POST['start'], $_POST['amount'] );	

		// if it is a wordpress post type, join wp_posts table
		$join_sql  	= '';
		//print_r (self::$tables['pod_' . $saved_table_id ]);
		if( $tables_arr['pod_' . intval( $_POST['saved_tb'] ) ]['type'] == 'post_type' ){
			$join_sql = 'INNER JOIN  `' . $wpdb->prefix . 'posts` AS post_tb ON post_tb.ID = main_tb.id';
		}		

/*		$loaded_str	=	'';
		if( !empty( $_POST['loaded'] ) ){
			$loaded_str	=	rtrim( trim( $_POST['loaded'] ), ',' );
			$where_sql  .=   ' AND !FIND_IN_SET(main_tb.`id`, "' . $loaded_str . '") ';
		}*/

		$query_str  	= $wpdb->prepare( 'SELECT 
										   main_tb.`id`, CONCAT( "' . $tables_arr['pod_' . intval( $_POST['saved_tb'] ) ]['name_label'] . '" ) AS pprf_name_label, 
											`' . $tables_arr['pod_' . intval( $_POST['saved_tb'] ) ]['name_field'] . '` AS title,
										   main_tb.`pandarf_trash` AS trashed														
										   FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` AS main_tb
										   ' . $join_sql  . '
										   WHERE ' . $where_sql . ' 
										   ORDER BY ' . $orderBy_str . ' ' . $order_sql . '
										   ' . $limit_sql . '; ' , 
										   $searches 
										);	
		
		$entries   	= $wpdb->get_results( $query_str, ARRAY_A );	

		wp_send_json_success( $entries );
		//die();
	}
	public function admin_pprf_load_more(){
		if( $_POST['action'] != 'admin_pprf_load_more' ){
			$data_arr	=	array( 'action' => false );
		    wp_send_json_error( $data_arr );
		}	
		$this->pprf_load_more();
	}	

	public function admin_pprf_reassign(){
		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {
			$data_arr	=	array( 'security' => false, 'updated' => false  );
		    wp_send_json_error( $data_arr );
		} 		
		if( $_POST['action'] != 'admin_pprf_reassign' ){
			$data_arr	=	array( 'security' => true, 'updated' => false  );
		    wp_send_json_error( $data_arr );			
		}
		global $wpdb, $current_user;

        $tables_arr = maybe_unserialize( PPRF_ALL_TABLES );
		$query_str 	= $wpdb->prepare( 'UPDATE `' . $wpdb->prefix . esc_sql( $tables_arr[ 'pod_' . $_POST['cpodid'] ]['name'] ) . '`
									  	SET  `pandarf_parent_post_id` 	=  %d,
											 `pandarf_pod_field_id` 	=  %d
									  	WHERE  `id` = %d', 
									  array( $_POST['postid'], $_POST['poditemid'], $_POST['itemid'] )
									);	
		$done_bln	= $wpdb->query( $query_str );	
		if( $done_bln ){
			$data_arr	= array( 'security' => true, 'updated' => $done_bln );
	    	wp_send_json_success( $data_arr );					
	    } else {
			$data_arr	=	array( 'security' => true, 'updated' => false  );
		    wp_send_json_error( $data_arr );	    	
	    }
	}	
/*	public function front_pprf_load_more(){
		if( $_POST['action'] != 'front_pprf_load_more' ){
			die();
		}	
		$this->pprf_load_more();
	}	*/		
}