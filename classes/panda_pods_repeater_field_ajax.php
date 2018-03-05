<?php
if ( preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF']) ) { die('You are not allowed to call this page directly.'); }
/**
* AJax class, collection for AJax functions
*
* @version: 1.0.0
* @package panda-pods-repeater-field
* @author Dongjie Xu
* @since 09/02/2016 
*/
class Panda_Pods_Repeater_Field_Ajax {

	function __construct() {
		$this->actions_fn();
		$this->filters_fn();	
		$this->enqueue_fn();			
	}
    protected function actions_fn(){			
		// login user only, for everyone, use wp_ajax_nopriv_ example: add_action( 'wp_ajax_function-name', 			array( $this, 'function-name') );				
		if( is_user_logged_in() ){
			add_action( 'wp_ajax_admin_load_newly_added_fn', 			array( $this, 'admin_load_newly_added_fn') );	
			add_action( 'wp_ajax_admin_delete_item_fn', 				array( $this, 'admin_delete_item_fn') );	
			add_action( 'wp_ajax_admin_pprf_update_order_fn', 			array( $this, 'admin_pprf_update_order_fn') );							
		}			
	}
	
    protected function filters_fn(){
		
	}
	
    protected function enqueue_fn(){
		
	}	
	/**
	 * find out the last inserted id
	 */
	public function admin_load_newly_added_fn(){
		
		if( $_POST['action'] != 'admin_load_newly_added_fn' ){
			die();
		}		
		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {

		     die(); 

		} 		
		global $wpdb, $table_prefix, $current_user;

		$db_cla      = new panda_pods_repeater_field_db();
		$tables_arr  = $db_cla->get_tables_fn();
		
		if( isset( $_POST['podid'] ) && is_numeric( $_POST['podid'] ) && isset( $_POST['cpodid'] ) && is_numeric( $_POST['cpodid'] ) && isset( $_POST['postid'] )  && isset( $_POST['authorid'] ) && is_numeric( $_POST['authorid'] ) && isset( $_POST['poditemid'] ) && is_numeric( $_POST['poditemid'] ) ){
			// update panda keys
			if( array_key_exists( 'pod_' . $_POST['cpodid'], $tables_arr ) ){
				
				//$now_str		= date('Y-m-d H:i:s');
				$table_str 	 	= $table_prefix . $tables_arr['pod_' . $_POST['cpodid'] ]['name'] ;	
				$title_str		= '';
				if( $tables_arr['pod_' . $_POST['cpodid'] ]['name_field'] != '' ){
					$title_str		= '' . $tables_arr['pod_' . $_POST['cpodid'] ]['name_field'] ;		
					  
				}
				// if it is a wordpress post type, join wp_posts table
				$join_str  = '';
				
				if( $tables_arr['pod_' . $_POST['cpodid'] ]['type'] == 'post_type' ){
					$join_str = 'INNER JOIN  `' . $table_prefix . 'posts` AS post_tb ON post_tb.ID = t.id';
				}

				// fetch the child item data
				$where_str   	= '   `t`.`pandarf_parent_pod_id`  = ' . intval(  $_POST['podid'] ) . '
							   	  AND `t`.`pandarf_parent_post_id` = "' . esc_sql( $_POST['postid'] ) . '"
							   	  AND `t`.`pandarf_pod_field_id`   = ' . intval(  $_POST['poditemid'] ) . ' '; 				
				//$query_str  	= $wpdb->prepare( 'SELECT id FROM `' . $table_str . '` AS t WHERE `t`.`pandarf_categories` REGEXP "(:\"' . $_POST['podid'] . '.' . $_POST['postid'] . '.' . $_POST['poditemid'] . '\";{1,})" AND `t`.`pandarf_author` = %d ORDER BY `t`.`id` DESC LIMIT 0, 1;' , array( $_POST['authorid'] ) );	
				$qtitle_str		= $title_str != ''? ', `' . $title_str . '`' : '';
				$query_str  	= $wpdb->prepare( 'SELECT t.`id` ' . $qtitle_str . ' 
													FROM `' . $table_str . '` AS t 
													' . $join_str . '
													WHERE ' . $where_str . ' AND `t`.`pandarf_author` = %d 
													ORDER BY `t`.`id` DESC LIMIT 0, 1;' , 
													array( $_POST['authorid'] ) );	
				//echo $query_str;
				$item_arr   	= $wpdb->get_results( $query_str, ARRAY_A );	
				if( is_array( $item_arr ) && isset( $item_arr[0]['id'] ) ){
					if( !isset( $item_arr[0][ $title_str ] ) ){
						$item_arr[0][ $title_str ] = '';
					}
					echo json_encode( array( 'id' => $item_arr[0]['id'], 'title' => $item_arr[0][ $title_str ] ) );
				} else {
					echo '';	
				}
				
			}
		}
		die();
	}
	/**
	 * delete item
	 */
	public function admin_delete_item_fn(){
		if( $_POST['action'] != 'admin_delete_item_fn' ){
			die();
		}	
		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {

		     die(); 

		} 				
		global $wpdb, $table_prefix, $current_user;

		$db_cla      = new panda_pods_repeater_field_db();
		$tables_arr  = $db_cla->get_tables_fn();

		if( isset( $_POST['podid'] ) && is_numeric( $_POST['podid'] ) && isset( $_POST['cpodid'] ) && is_numeric( $_POST['cpodid'] ) && isset( $_POST['postid'] ) && isset( $_POST['authorid'] ) && is_numeric( $_POST['authorid'] ) && isset( $_POST['itemid'] ) && is_numeric( $_POST['itemid'] ) && isset( $_POST['poditemid'] ) && is_numeric( $_POST['poditemid'] ) ){
			// update panda keys
			if( array_key_exists( 'pod_' . $_POST['cpodid'], $tables_arr ) ){
				
				//$now_str		= date('Y-m-d H:i:s');
				$table_str 	 	= $table_prefix . $tables_arr['pod_' . $_POST['cpodid'] ]['name'] ;			  
				// fetch the child item data and see if the item belong to the current post
				$where_str   	= '   `t`.`pandarf_parent_pod_id`  = ' . intval(  $_POST['podid'] ) . '
							   	  AND `t`.`pandarf_parent_post_id` = "' . esc_sql( $_POST['postid'] ) . '"
							   	  AND `t`.`pandarf_pod_field_id`   = ' . intval(  $_POST['poditemid'] ) . ' '; 						
				//$query_str  	= $wpdb->prepare( 'SELECT id FROM `' . $table_str . '` AS t WHERE `t`.`pandarf_categories` REGEXP "(:\"' . $_POST['podid'] . '.' . $_POST['postid'] . '.' . $_POST['poditemid'] . '\";{1,})" AND `t`.`id` = %d ORDER BY `t`.`id` DESC LIMIT 0, 1;' , array( $_POST['itemid'] ) );	
				$query_str  	= $wpdb->prepare( 'SELECT * FROM `' . $table_str . '` AS t WHERE ' . $where_str . ' AND `t`.`id` = %d ORDER BY `t`.`id` DESC LIMIT 0, 1;' , array( $_POST['itemid'] ) );	
				
				$item_arr   	= $wpdb->get_results( $query_str, ARRAY_A );	
				if( is_array( $item_arr ) && isset( $item_arr[0]['id'] ) && $_POST['itemid'] == $item_arr[0]['id'] ){
					
					//$query_str  	= $wpdb->prepare( 'DELETE FROM `' . $table_str . '` WHERE `id` = %d;' , array( $item_arr[0]['id'] ) );	
				//echo $query_str;
					//$deleted_bln   	= $wpdb->query( $query_str );
					$del_str	=	'delete';
					if( isset( $_POST['trash'] ) && $_POST['trash'] == 0 )	{
						//$db_cla->update_columns_fn( $tables_arr['pod_' . $_POST['cpodid'] ]['pod'] );
						$query_str  	= $wpdb->prepare( 'UPDATE `' . $table_str . '` SET `pandarf_trash` = 0 WHERE `id` = %d;' , array( $_POST['itemid'] ) );
						$deleted_bln 	=	$wpdb->query( $query_str );
						$del_str		=	'restore';
					}					
					if( isset( $_POST['trash'] ) && $_POST['trash'] == 1 )	{ // if $_POST['trash'] == 1, the table should be already updated
						//$db_cla->update_columns_fn( $tables_arr['pod_' . $_POST['cpodid'] ]['pod'] );
						$query_str  	= $wpdb->prepare( 'UPDATE `' . $table_str . '` SET `pandarf_trash` = 1 WHERE `id` = %d;' , array( $_POST['itemid'] ) );
						$deleted_bln 	=	$wpdb->query( $query_str );
						$del_str		=	'trash';
					}
					if( !isset( $_POST['trash'] ) || $_POST['trash'] == 2 )	{
						$pod_obj	 = pods( $tables_arr['pod_' . $_POST['cpodid'] ]['pod'], $_POST['itemid'] ); 
						
						$deleted_bln = $pod_obj->delete( $_POST['itemid'] );

					}
					if( $deleted_bln ){
						echo json_encode( array( 
												 'id'		 	=> $item_arr[0]['id'], 
												 'pod_idx'	 	=> $tables_arr['pod_' . $_POST['cpodid'] ]['name_field'] , 
												 'pod_idx_val' 	=> $item_arr[0][ $tables_arr['pod_' . $_POST['cpodid'] ]['name_field'] ],
												 'ppod_fie_id'	=> $_POST['poditemid'],		
												 'action'		=> $del_str,								
												) 
										 );	
					}					
				} else {
					echo '';	
				}
				
			}
		}
		die();
	}
	/**
	 * update order
	 */
	public function admin_pprf_update_order_fn(){
		if( $_POST['action'] != 'admin_pprf_update_order_fn' ){
			die();
		}			
		if ( ! wp_verify_nonce( $_POST['security'], 'panda-pods-repeater-field-nonce' ) ) {

		     die(); 

		} 		
		global $wpdb, $table_prefix, $current_user;

		$db_cla      = new panda_pods_repeater_field_db();
		$tables_arr  = $db_cla->get_tables_fn();
		
		if( isset( $_POST['order'] ) ){
			for( $i = 0; $i < count( $_POST['order'] ); $i ++ ){
				$ids_arr = explode( '-', $_POST['order'][ $i ] );
				if( count( $ids_arr ) >= 3 ){
					
					// if the pods table is listed
					if( isset( $tables_arr[ 'pod_' . $ids_arr[1] ] ) ){
						
						// remove li and table id from ids_arr
						//$ids_arr = array_values( array_slice( $ids_arr, 2 ) );
						$query_str = $wpdb->prepare( 'UPDATE `' . $table_prefix . $tables_arr[ 'pod_' . $ids_arr[1] ]['name'] . '`
														SET  `pandarf_order` =  "' . $i . '" 
													  WHERE  `id` = "%d";', 
													  array( $ids_arr[2] )
													);	
						$wpdb->query( $query_str );							
						//echo $query_str;
					}
					
				}
			}
		}
		die();
	}
}