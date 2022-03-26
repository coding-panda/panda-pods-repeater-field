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
			add_action( 'wp_ajax_admin_pprf_load_more', 			array( $this, 'admin_pprf_load_more') );				
			add_action( 'wp_ajax_admin_pprf_reassign', 				array( $this, 'admin_pprf_reassign') );				
						
			// frontend

			//add_action( 'wp_ajax_front_pprf_load_newly_added', 		array( $this, 'front_pprf_load_newly_added') );	
			//add_action( 'wp_ajax_front_pprf_delete_item', 			array( $this, 'front_pprf_delete_item') );	
			//add_action( 'wp_ajax_front_pprf_update_order', 			array( $this, 'front_pprf_update_order') );					
		//}	

		add_action( 'wp_ajax_nopriv_admin_pprf_load_newly_added', 		array( $this, 'admin_pprf_load_newly_added') );	
		add_action( 'wp_ajax_nopriv_admin_pprf_delete_item', 			array( $this, 'admin_pprf_delete_item') );	
		add_action( 'wp_ajax_nopriv_admin_pprf_update_order', 			array( $this, 'admin_pprf_update_order') );							
		add_action( 'wp_ajax_nopriv_admin_pprf_load_more', 				array( $this, 'admin_pprf_load_more') );				
		add_action( 'wp_ajax_nopriv_admin_pprf_reassign', 				array( $this, 'admin_pprf_reassign') );							
/*		add_action( 'wp_ajax_nopriv_front_pprf_load_newly_added', 		array( $this, 'front_pprf_load_newly_added') );	
		add_action( 'wp_ajax_nopriv_front_pprf_delete_item', 			array( $this, 'front_pprf_delete_item') );	
		add_action( 'wp_ajax_nopriv_front_pprf_update_order', 			array( $this, 'front_pprf_update_order') );		*/		

	}
	

	public function define_pprf_all_tables(){
		if( ! defined( 'PPRF_ALL_TABLES' ) ){				
			$db_cla  = new panda_pods_repeater_field_db();
			$tables  = $db_cla->get_tables();
			define( 'PPRF_ALL_TABLES', maybe_serialize( $tables ) );	
		} 
	}
	/**
	 * find out the last inserted id
	 */
	public function pprf_load_newly_added(){
	
		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {

			$data	=	array( 'security' => false );
		    wp_send_json_error( $data );

		} 		
		global $wpdb, $current_user;

        $tables  = maybe_unserialize( PPRF_ALL_TABLES );
		
		if( isset( $_POST['podid'] ) && is_numeric( $_POST['podid'] ) && isset( $_POST['cpodid'] ) && is_numeric( $_POST['cpodid'] ) && isset( $_POST['postid'] )  && isset( $_POST['authorid'] ) && is_numeric( $_POST['authorid'] ) && isset( $_POST['poditemid'] ) && is_numeric( $_POST['poditemid'] ) ){
			// update panda keys

			if( array_key_exists( 'pod_' . $_POST['cpodid'], $tables ) ){
				$field_options 		= array();
				$parent_pod_name 	= '';
				// get the parent field 
				if( array_key_exists( 'pod_' . $_POST['podid'], $tables ) ){ // custom settings don't have a parent table.
					$parent_pod_name = $tables['pod_' . $_POST['podid'] ]['pod'];							
				} else {
					$parent_post = get_post( $_POST['podid'], ARRAY_A );
					if( ! empty( $parent_post ) && '_pods_pod' == $parent_post['post_type'] ){
						$parent_pod_name = $parent_post['post_name'];
					}
				}
				if( '' != $parent_pod_name ){
					$parent_pod 	= pods( $parent_pod_name );
					
					foreach ( $parent_pod->fields as $key => $value_data ) {
						
						if ( $value_data['id'] == $_POST['poditemid'] ) {
							// get the conditions
							$field_options = $value_data['options'] ;
							break;
						}
					}				
				}
				
				$apply_admin_columns = false;
				if( isset(  $field_options['pandarepeaterfield_apply_admin_columns'] ) && $field_options['pandarepeaterfield_apply_admin_columns'] ){		
					$apply_admin_columns = true; 
				}	

				$panda_repeater_field = new PodsField_Pandarepeaterfield();
				$admin_columns	= array(); // if apply admin columns is picked, use admin columns instead of name

				$child_pod_name = $tables['pod_' . $_POST['cpodid'] ]['pod'];														
								
				//$now		= date('Y-m-d H:i:s');
				$table 	 	= $wpdb->prefix . $tables['pod_' . $_POST['cpodid'] ]['name'] ;	

				//$child_pod 		=  pods( $tables['pod_' . $_POST['cpodid'] ]['name'] );	

				$title		= '';

				if( $tables['pod_' . $_POST['cpodid'] ]['name_field'] != '' && $tables['pod_' . $_POST['cpodid'] ]['name_label'] != '' ){
					$title	= '' . $tables['pod_' . $_POST['cpodid'] ]['name_field'] ;	

					// if( isset( $child_pod->fields[ $tables['pod_' . $_POST['cpodid'] ]['name_field'] ] ) && is_numeric( $title )  ){
					// 	$title		= $panda_repeater_field->simpods_area_field_value( $child_pod->fields[ $tables['pod_' . $_POST['cpodid'] ]['name_field'] ], $title );
					// }					  
				}

				// if it is a wordpress post type, join wp_posts table
				$join_sql  = '';
				
				if( $tables['pod_' . $_POST['cpodid'] ]['type'] == 'post_type' ){
					$join_sql = 'INNER JOIN  `' . $wpdb->prefix . 'posts` AS post_tb ON post_tb.ID = t.id';
				}

				// fetch the child item data
				$where_sql   	= '   `t`.`pandarf_parent_pod_id`  = %d
							   	  AND `t`.`pandarf_parent_post_id` = %d
							   	  AND `t`.`pandarf_pod_field_id`   = %d '; 				

				$wheres			= array( $_POST['podid'], $_POST['postid'],  $_POST['poditemid'], $_POST['authorid'] );
				$title_sql		= $title != ''? ', `' . $title . '`' : '';
				$query  		= $wpdb->prepare( 'SELECT t.`id` ' . $title_sql . ' 
													FROM `' . $table . '` AS t 
													' . $join_sql . '
													WHERE ' . $where_sql . ' AND `t`.`pandarf_author` = %d 
													ORDER BY `t`.`id` DESC LIMIT 0, 1;' , 
													$wheres );	
	
				$items   	= $wpdb->get_results( $query, ARRAY_A );	
				if( is_array( $items ) && isset( $items[0]['id'] ) ){
					if( !isset( $items[0][ $title ] ) ){
						$items[0][ $title ] = '';
					}
					$label_html = '';
					if( $apply_admin_columns ){
						$label_html = $panda_repeater_field->create_label_with_admin_columns( $child_pod_name, $items[0]['id'] );	
					}

					if( $label_html == '' ){
						$name_field_html = '';
						if( ! empty( $tables['pod_' . $_POST['cpodid'] ]['name_label'] ) ){


							$name_field_html = ' <strong>' . $tables['pod_' . $_POST['cpodid'] ]['name_label']  . ': </strong>' . substr( preg_replace( '/\[.*?\]/is', '',  wp_strip_all_tags( $items[0][ $title ] ) ), 0, 80 ) . pprf_check_media_in_content( $items[0][ $title ] );

						}
						$label_html = '<strong>ID:</strong> ' . $items[0]['id'] . $name_field_html;
					}
					$data_arr	=	array( 
										'id' 				=> $items[0]['id'], 
										'title' 			=> $label_html, 
										'pprf_name_label' 	=> $tables['pod_' . $_POST['cpodid'] ]['name_label'], 										
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

        $tables  = maybe_unserialize( PPRF_ALL_TABLES );

		if( isset( $_POST['podid'] ) && is_numeric( $_POST['podid'] ) && isset( $_POST['cpodid'] ) && is_numeric( $_POST['cpodid'] ) && isset( $_POST['postid'] ) && isset( $_POST['authorid'] ) && is_numeric( $_POST['authorid'] ) && isset( $_POST['itemid'] ) && is_numeric( $_POST['itemid'] ) && isset( $_POST['poditemid'] ) && is_numeric( $_POST['poditemid'] ) ){
			// update panda keys
			if( array_key_exists( 'pod_' . $_POST['cpodid'], $tables ) ){
				
				//$now		= date('Y-m-d H:i:s');
				$table_str 	 	= $wpdb->prefix . $tables['pod_' . $_POST['cpodid'] ]['name'] ;			  

				$wheres		= array();
			    $join_sql		= 	'';        
				//check it is an Advanced Content Type or normal post type
			    $pod_details	=	pprf_pod_details( $_POST['cpodid'] );
			    			            
			    if( $pod_details ){
				    //normal post type fetch all published and draft posts
				    if( $pod_details['type'] == 'post_type' ){
				    	 $join_sql	=	'INNER JOIN `' . $wpdb->prefix . 'posts` AS ps_tb ON ps_tb.`ID` = t.`id`';				    	
				    }
				}					
				// fetch the child item data and see if the item belong to the current post
				$where_sql   	= '   `t`.`pandarf_parent_pod_id`  = %d
							   	  AND `t`.`pandarf_parent_post_id` = %d
							   	  AND `t`.`pandarf_pod_field_id`   = %d '; 						

				$wheres		= array( $_POST['podid'], $_POST['postid'],  $_POST['poditemid'], $_POST['itemid'] );				   	  

			
				$query  		= $wpdb->prepare( 'SELECT * FROM `' . $table_str . '` AS t ' . $join_sql . ' WHERE ' . $where_sql . ' AND `t`.`id` = %d ORDER BY `t`.`id` DESC LIMIT 0, 1;' , $wheres );	
			
				$item_arr   	= $wpdb->get_results( $query, ARRAY_A );	
				if( is_array( $item_arr ) && isset( $item_arr[0]['id'] ) && $_POST['itemid'] === $item_arr[0]['id'] ){
					
					$delete_action	=	'delete';
					if( isset( $_POST['trash'] ) && $_POST['trash'] === '0' )	{
						
						$query  		= $wpdb->prepare( 'UPDATE `' . $table_str . '` SET `pandarf_trash` = 0 WHERE `id` = %d;' , array( $_POST['itemid'] ) );
						$deleted_bln 	= $wpdb->query( $query );
						$delete_action	= 'restore';
					}					
					if( isset( $_POST['trash'] ) && $_POST['trash'] === '1' )	{ // if $_POST['trash'] == 1, the table should be already updated
						
						$query  		= $wpdb->prepare( 'UPDATE `' . $table_str . '` SET `pandarf_trash` = 1 WHERE `id` = %d;' , array( $_POST['itemid'] ) );
						$deleted_bln 	=	$wpdb->query( $query );
						$delete_action	=	'trash';
					}
					if( !isset( $_POST['trash'] ) || $_POST['trash'] === '2' )	{
						$pod_obj	 = pods( $tables['pod_' . $_POST['cpodid'] ]['pod'], absint( $_POST['itemid'] ) ); 
						if ( $pod_obj->exists() ) { 
							$deleted_bln = $pod_obj->delete( $_POST['itemid'] );
						}
					
					}
					if( $deleted_bln ){
						$data_arr	= 	array( 
											'id'		 	=> $item_arr[0]['id'], 
											'pod_idx'	 	=> $tables['pod_' . $_POST['cpodid'] ]['name_field'] , 
											'pod_idx_val' 	=> '',
											'ppod_fie_id'	=> $_POST['poditemid'],		
											'action'		=> $delete_action,								
										) ;	
						if( ! empty( $item_arr[0][ $tables['pod_' . $_POST['cpodid'] ]['name_label'] ] ) ) {
							$data_arr['pod_idx_val']	=	$item_arr[0][ $tables['pod_' . $_POST['cpodid'] ]['name_field'] ];
						}
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

        $tables  = maybe_unserialize( PPRF_ALL_TABLES );
		$pprf_id	= '';
		$return_data	= array( 'pprf_id'	=> '' );
		if( isset( $_POST['order'] ) ){
			for( $i = 0; $i < count( $_POST['order'] ); $i ++ ){
				$ids_arr = explode( '-', $_POST['order'][ $i ] );
				if( count( $ids_arr ) >= 3 ){
					// if the pods table is listed
					if( isset( $tables[ 'pod_' . $ids_arr[1] ] ) ){						
						if( $pprf_id === '' ){
							$pprf_id	=	$ids_arr[1] . '-' . $ids_arr[3];
						}						
						// remove li and table id from ids_arr
						//$ids_arr = array_values( array_slice( $ids_arr, 2 ) );
						$query = $wpdb->prepare( 'UPDATE `' . $wpdb->prefix . esc_sql( $tables[ 'pod_' . $ids_arr[1] ]['name'] ) . '`
														SET  `pandarf_order` =  %d 
													  WHERE  `id` = %d;', 
													  array( $i, $ids_arr[2] )
													);	
						$wpdb->query( $query );							
						//echo $query;
						$return_data['pprf_id']	=	$pprf_id;
					}
					
				}
			}
			if( $pprf_id !== '' ){
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

        $tables  = maybe_unserialize( PPRF_ALL_TABLES );

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
		$order_by_sql=	'CAST( `pandarf_order` AS UNSIGNED )';
		if( $_POST['order_by'] !== 'pandarf_order' ){
			$order_by_sql	=	'`' . esc_sql( $_POST['order_by'] ) . '`';			
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

		if( $tables['pod_' . intval( $_POST['saved_tb'] ) ]['type'] == 'post_type' ){
			$join_sql = 'INNER JOIN  `' . $wpdb->prefix . 'posts` AS post_tb ON post_tb.ID = main_tb.id';
		}		

/*		$loaded_str	=	'';
		if( !empty( $_POST['loaded'] ) ){
			$loaded_str	=	rtrim( trim( $_POST['loaded'] ), ',' );
			$where_sql  .=   ' AND !FIND_IN_SET(main_tb.`id`, "' . $loaded_str . '") ';
		}*/

		$query  	= $wpdb->prepare( 'SELECT 
										   main_tb.`id`, CONCAT( "' . $tables['pod_' . intval( $_POST['saved_tb'] ) ]['name_label'] . '" ) AS pprf_name_label, 
											`' . $tables['pod_' . intval( $_POST['saved_tb'] ) ]['name_field'] . '` AS title,
										   main_tb.`pandarf_trash` AS trashed														
										   FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` AS main_tb
										   ' . $join_sql  . '
										   WHERE ' . $where_sql . ' 
										   ORDER BY ' . $order_by_sql . ' ' . $order_sql . '
										   ' . $limit_sql . '; ' , 
										   $searches 
										);	
		
		$entries   	= $wpdb->get_results( $query, ARRAY_A );	

		wp_send_json_success( $entries );
		//die();
	}
	public function admin_pprf_load_more(){
		if( $_POST['action'] != 'admin_pprf_load_more' ){
			$data	=	array( 'action' => false );
		    wp_send_json_error( $data );
		}	
		$this->pprf_load_more();
	}	

	public function admin_pprf_reassign(){
		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {
			$data	=	array( 'security' => false, 'updated' => false  );
		    wp_send_json_error( $data );
		} 		
		if( $_POST['action'] != 'admin_pprf_reassign' ){
			$data	=	array( 'security' => true, 'updated' => false  );
		    wp_send_json_error( $data );			
		}
		global $wpdb, $current_user;

        $tables = maybe_unserialize( PPRF_ALL_TABLES );
		$query 	= $wpdb->prepare( 'UPDATE `' . $wpdb->prefix . esc_sql( $tables[ 'pod_' . $_POST['cpodid'] ]['name'] ) . '`
									  	SET  `pandarf_parent_post_id` 	=  %d,
											 `pandarf_pod_field_id` 	=  %d
									  	WHERE  `id` = %d', 
									  array( $_POST['postid'], $_POST['poditemid'], $_POST['itemid'] )
									);	
		$done	= $wpdb->query( $query );	
		if( $done ){
			$data	= array( 'security' => true, 'updated' => $done );
	    	wp_send_json_success( $data );					
	    } else {
			$data	=	array( 'security' => true, 'updated' => false  );
		    wp_send_json_error( $data );	    	
	    }
	}	
	
}