<?php
/**
* Class to create a new Pods field type: PodsField_Pandarepeaterfield
*
* @package panda-pods-repeater-field
* @author Dongjie Xu
* @since 09/02/2016 
*/
class PodsField_Pandarepeaterfield extends PodsField {

	/**
	 * Whether this field is running under 1.x deprecated forms
	 *
	 * @var bool
	 * @since 1.0
	 */
	public static $deprecated = false;

	/**
	 * Field Type Identifier, has to be one word
	 *
	 * @var string
	 * @since 1.0
	 */
	public static $type 	  = 'pandarepeaterfield';
	/**
	 * Option Name to save to postmeta table
	 *
	 * @var string
	 * @since 1.0
	 */	
	public static $typeTb_str = 'pandarepeaterfield_table';
	/**
	 * input name
	 *
	 * @var string
	 * @since 1.0
	 */
	 
	public static $input_str = 'panda-pods-repeater-field';
	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 1.0
	 */	 
	public static $label = 'Pods Table As Repeater Field';

	/**
	 * Field Type Preparation
	 *
	 * @var string
	 * @since 1.0
	 */
	public static $prepare = '%s';

	/**
	 * Pod Types supported on (true for all, false for none, or give array of specific types supported)
	 *
	 * @var array|bool
	 * @since 1.0.0
	 */
	public static $pod_types = true;

	/**
	 * API caching for fields that need it during validate/save
	 *
	 * @var \PodsAPI
	 * @since 1.0.0
	 */
	private static $api = false;
	/**
	 * Tables caching for Advanced Custom Type tables
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public static $actTbs_arr = array();
	public static $tbs_arr 	  = array();
	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @return \PodsField
	 *
	 * @since 1.0
	 */
	 
	public function __construct () {
		if( !class_exists( 'panda_pods_repeater_field_db' ) ){
			include_once( PANDA_PODS_REPEATER_DIR . 'classes/panda_pods_repeater_field_db.php' );
		}

		if( !defined( 'PPRF_PODS_TABLES' ) ){
			self::$actTbs_arr = $this->pods_tables_fn();		
		} else {
			self::$actTbs_arr = unserialize( PPRF_PODS_TABLES );
		}
		
	}


	/**
	 * Add options and set defaults for field type, shows in admin area
	 *
	 * @return array $options
	 *
	 * @since 1.0
	 * @see PodsField::ui_options
	 * @use pods_tables_fn() has to call the function rather than using the static one. The static one doesn't include all tables after saving
	 */
	public function options () {

		global $wpdb, $wp_roles;
		$tables_arr = $this->pods_tables_fn( 2 );
		
		
		$roles_arr	= array(); //
		foreach( $wp_roles->roles as $role_str => $details_arr ){ //Only a user role with edit_posts capability can access the field. Grand the access right to more roles here.
			
			if( ! isset( $details_arr['capabilities']['edit_posts'] ) || $details_arr['capabilities']['edit_posts'] == 0 ){				
				//array_push( $roles_arr, $role_str );
				$roles_arr[ $role_str ] = array(
											'label'      => $details_arr['name'],
											'default'    => 0,
											'type'       => 'boolean',											
										  );
			}
		}
		//$roles_arr	= array_keys( $wp_roles->roles );
		
		if( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ){
			if( isset( $tables_arr['pod_' . $_GET['id'] ] ) ){
				unset( $tables_arr['pod_' . $_GET['id'] ] )	;
			}

			$query_str = $wpdb->prepare( 'SELECT `post_name` FROM `' . $wpdb->posts . '` WHERE `ID` = %d LIMIT 0, 1', array( $_GET['id'] ) ) ;
			
			$items_arr = $wpdb->get_results( $query_str, ARRAY_A );			
			if( count( $items_arr ) && isset( $tables_arr[ $items_arr[0]['post_name'] ] ) ){
				unset( $tables_arr[ $items_arr[0]['post_name'] ] )	;	
			}
		}
	
		$wids_arr   = array(
							'100' => '100%',	
							'50'  => '50%',							
							'25'  => '25%',													
							); 
		$bln_arr   	= array(
							'0' 	=> __('No', 'panda-pods-repeater-field' ),	
							'1'  	=> __('Yes', 'panda-pods-repeater-field' ),																							
							); 		
		$options = array( 
           
            self::$type . '_table' => array(
                'label' 	 => __( 'Pods Table', 'panda-pods-repeater-field' ),
                'default' 	 => 0,
                'type' 		 => 'pick',
                'data' 		 => $tables_arr,
				'dependency' => true
            ),		
            self::$type . '_field_width' => array(
                'label' 	 => __( 'Field Width', 'panda-pods-repeater-field' ),
                'default' 	 => 100,
                'type' 		 => 'pick',
                'data' 		 => $wids_arr,
				'dependency' => true
            ),		
            self::$type . '_entry_limit' => array(
                'label' 	 => __( 'Entry Limit', 'panda-pods-repeater-field' ),
                'default' 	 => 0,
                'type' 		 => 'number',
                'data' 		 => '',
				'dependency' => true,
				'description'=> __( 'Leave it to 0 if you want to Enable Load More', 'panda-pods-repeater-field' ),
            ),	
            self::$type . '_enable_load_more' => array(
                'label' 	 => __( 'Enable Load More', 'panda-pods-repeater-field' ),
                'depends-on' => array( self::$type . '_entry_limit' => '0' ),
                'default' 	 => '0',
                'type' 		 => 'pick',
                'data' 		 => $bln_arr,
				'dependency' => true				
            ),            
            self::$type . '_initial_amount' => array(
                'label' 	 => __( 'Initial Amount', 'panda-pods-repeater-field' ),
                'depends-on' => array( self::$type . '_enable_load_more' => 1 ),
                'type' 		 => 'number',
                'default' 	 => '10',
                'data' 		 => '',	
                'description'=> __( 'Default amount to load, no negative number.', 'panda-pods-repeater-field' ),
            ),            
            self::$type . '_enable_trash' => array(
                'label' 	 => __( 'Enable Trash', 'panda-pods-repeater-field' ),
                'default' 	 => '0',
                'type' 		 => 'pick',
                'data' 		 => $bln_arr,
				'dependency' => true
            ),	       
            self::$type . '_order_by' => array(
                'label' 	 => __( 'Order By', 'panda-pods-repeater-field' ),
                'default' 	 => 'pandarf_order',
                'type' 		 => 'text',
                'data' 		 => '',
				'description'=> __( 'Enter a field of the table. Default to pandarf_order. If not pandarf_order, re-order will be disabled. Min PHP version 5.5.', 'panda-pods-repeater-field' ),
            ),	  
            self::$type . '_order' => array(
                'label' 	 => __( 'Order', 'panda-pods-repeater-field' ),
                'default' 	 => 'ASC',
                'type' 		 => 'pick',
                'data' 		 => array('ASC' => __('Ascending', 'panda-pods-repeater-field' ), 'DESC' => __('Descending', 'panda-pods-repeater-field' ) ),
				'description'=> __( 'Default to Ascending', 'panda-pods-repeater-field' ),
            ),	
            self::$type . '_display_order_info' => array(
                'label' 	 => __( 'Display Order Info', 'panda-pods-repeater-field' ),
                'default' 	 => '0',
                'type' 		 => 'pick',
                'data' 		 => $bln_arr,				
            ),    
            self::$type . '_apply_admin_columns' => array(
                'label' 	 => __( 'Apply Admin Table Columns', 'panda-pods-repeater-field' ),
                'default' 	 => '0',
                'type' 		 => 'pick',
                'data' 		 => $bln_arr,	
                'description'=> __( 'Display labels based on the Admin Table Columns. Only strings and numbers will be displayed.', 'panda-pods-repeater-field' ),			
            ),      
            self::$type . '_allow_reassign' => array(
                'label' 	 => __( 'Allow Reassign', 'panda-pods-repeater-field' ),
                'default' 	 => '0',
                'type' 		 => 'pick',
                'data' 		 => $bln_arr,	
                'description'=> __( 'Allow reassigning an item to another parent', 'panda-pods-repeater-field' ),			
            ),    
            self::$type . '_public_access' => array(
                'label' 	 => __( 'Allow Public Access', 'panda-pods-repeater-field' ),
                'default' 	 => '0',
                'type' 		 => 'pick',
                'data' 		 => $bln_arr,	
                'dependency' => true,
                'description'=> __( 'Allow not logged in users to access the field. Not recommended. A user role with edit_posts capability can always access the field.', 'panda-pods-repeater-field' ),			
            ),   
            self::$type . '_role_access' => array( // this is saved into _posts
                'label' 	 => __( 'Access Allowed To User Roles', 'panda-pods-repeater-field' ),                
                'depends-on' => array( self::$type . '_public_access' => 0 ),
                'group'		 => $roles_arr,	                
                'description'=> __( 'Only a user role with edit_posts capability can access the field. Grand the access right to more roles here.', 'panda-pods-repeater-field' ),	                
            ),                                          
/*            self::$type . '_delete_family_tree' => array(
                'label' 	 => __( 'Delete family tree', 'panda-pods-repeater-field' ),
                'default' 	 => '0',
                'type' 		 => 'pick',
                'data' 		 => $bln_arr,				
                'description'=> __( 'When a parent item is deleted, delete all its decendents.', 'panda-pods-repeater-field' ),
            ), */                            
		);

		return $options;
	}

	/**
	 * Options for the Admin area, defaults to $this->options()
	 *
	 * @return array $options
	 *
	 * @since 1.0
	 * @see PodsField::options
	 */
	public function ui_options () {
		return $this->options();
	}

	/**
	 * Define the current field's schema for DB table storage
	 *
	 * @param array $options
	 *
	 * @return string
	 * @since 1.0
	 */
	public function schema ( $options = null ) {
		$schema = 'VARCHAR(255)';

		return $schema;
	}

	/**
	 * Define the current field's preparation for sprintf
	 *
	 * @param array $options
	 *
	 * @return array
	 * @since 1.0
	 */
	public function prepare ( $options = null ) {
		$format = self::$prepare;

		return $format;
	}

	/**
	 * Change the value of the field
	 *
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param array $pod
	 * @param int $id
	 *
	 * @return mixed|null|string
	 * @since 1.0.0
	 */
	public function value ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
	
		return $value;
	}

	/**
	 * Change the way the value of the field is displayed with Pods::get
	 *
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param array $pod
	 * @param int $id
	 *
	 * @return mixed|null|string
	 * @since 1.0
	 */
	public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		
		return $value;
	}

	/**
	 * Customize output of the form field
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param array $options
	 * @param array $pod
	 * @param int $id
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
		global $wpdb, $current_user;
	
		$allow_bln 		= 	true;
		$inAdmin_bln	=	true;	
/*		if( !defined( 'PANDA_PODS_REPEATER_URL' ) || !is_user_logged_in() || !current_user_can('edit_posts') ){
			// action before the iframe
			$allow_bln = false;
			
		}*/

		/*if( !is_admin() ){ // Nested fields are treated as frontend even loaded in the admin			
			$inAdmin_bln	=	false;
		} */
		if( isset( $options['pandarepeaterfield_public_access'] ) && $options['pandarepeaterfield_public_access'] == 1 ){ 
			$allow_bln = true;			
		}		
		//$allow_bln = true;
		
		$allow_bln = apply_filters( 'pprf_load_panda_repeater_allow_input', $allow_bln, $inAdmin_bln, $name, $value, $options, $pod, $id );

		if( !$allow_bln ){
			echo apply_filters( 'pprf_load_panda_repeater_allow_input_msg', __('You do not have permission to edit this item.', 'panda-pods-repeater-field' ) ) ;
		} else {		
		
			$db_cla 	     = new panda_pods_repeater_field_db();
		
			$options 		 = (array) $options;
			
			$form_field_type = PodsForm::$field_type;
			
			$savedtb_str = trim( $options[ self::$typeTb_str ] );
			
			$cPod_arr	=	explode( '_', $savedtb_str );
			
			if( count( $cPod_arr ) == 2 && $cPod_arr[0] == 'pod' && is_numeric( $cPod_arr[1] ) ){
				// table saved as before 1.2.0
				$savedtb_int = substr( $savedtb_str, 4 );
				
				$tb_str 	 = '';
				if( is_numeric( $savedtb_int ) ){			
					$post_arr	 = get_post( absint( $savedtb_int ), ARRAY_A );
					
					if( is_array( $post_arr ) && $post_arr['post_type'] == '_pods_pod' ){
						$tb_str 	 = $post_arr['post_name'];

					} else {
						return;
					}
				} else {
					return;
				} 

			} else {
				// table saved as since 1.2.0
				$query_str = $wpdb->prepare( 'SELECT * FROM `' . $wpdb->posts . '` WHERE `post_name` = %s AND `post_type` = "_pods_pod" LIMIT 0, 1', array( $savedtb_str ) ) ;
							
				$items_arr = $wpdb->get_results( $query_str, ARRAY_A );		

				if( count( $items_arr ) ){
					$post_arr 		= $items_arr[0];
					$tb_str 		= $savedtb_str;
					$savedtb_int	= $items_arr[0]['ID'];	
				} else {
					return;
				}
			}

			if( !is_numeric( $id ) ){
				echo apply_filters( 'pprf_load_panda_repeater_reminder_msg', __('Please save the parent first to add ' . strtolower( $post_arr['post_title'] ) . '. ', 'panda-pods-repeater-field' ) ) ;
				return;
			}			
			if( $tb_str != ''  ){
				$tbInfo_arr	 = $db_cla->get_pods_tb_info_fn( 'pods_' . $tb_str );
				
				// load items for the current post only using regular expression
				$where_str   =    '   `pandarf_parent_pod_id`  = %d
							   	  AND `pandarf_parent_post_id` = %d
							   	  AND `pandarf_pod_field_id`   = %d '; 		
				$search_arr  = 	array( $options['pod_id'], $id, $options['id'] );			  	

				$limit_str	=	'';
				$limit_bln	=	false;
				if( isset( $options['pandarepeaterfield_entry_limit'] ) && is_numeric( $options['pandarepeaterfield_entry_limit'] ) &&  $options['pandarepeaterfield_entry_limit'] != 0 ){
					$limit_str	=	'LIMIT 0, ' . intval( $options['pandarepeaterfield_entry_limit'] );	
					$limit_bln	=	true;
				} else {
					if( isset( $options['pandarepeaterfield_enable_load_more'] ) && $options['pandarepeaterfield_enable_load_more'] == 1 ){
						if( isset( $options['pandarepeaterfield_initial_amount'] ) && is_numeric( $options['pandarepeaterfield_initial_amount'] ) ){
							$options['pandarepeaterfield_initial_amount']	=	abs( intval( $options['pandarepeaterfield_initial_amount'] ) );
							$limit_str	=	'LIMIT 0, ' . $options['pandarepeaterfield_initial_amount'];	
						}
					}
				}
				
				// if it is a wordpress post type, join wp_posts table
				$join_str  = '';

				if( self::$tbs_arr['pod_' . $savedtb_int ]['type'] == 'post_type' ){
					$join_str = 'INNER JOIN  `' . $wpdb->posts . '` AS post_tb ON post_tb.ID = main_tb.id';
				}

				// order
				$order_str		=	'CAST( `pandarf_order` AS UNSIGNED ) ';
				$orderInfo_str	=	__('Ordered by: ', 'panda-pods-repeater-field' );
				if( isset( $options['pandarepeaterfield_order_by'] ) && !empty( $options['pandarepeaterfield_order_by'] ) && version_compare( phpversion(), '5.5', '>=' ) && $options['pandarepeaterfield_order_by'] != 'pandarf_order' ){
					
					$tbFields_arr 	=	$db_cla->get_fields_fn( 'pods_' . $tb_str );

					$fields_arr 	=	array_column( $tbFields_arr, 'Field');
					$options['pandarepeaterfield_order_by']	=	esc_sql( $options['pandarepeaterfield_order_by'] );
					if( in_array( $options['pandarepeaterfield_order_by'], $fields_arr )  ){
						$order_str		=	'`' . $options['pandarepeaterfield_order_by'] . '` ' ;
						$orderInfo_str	.=	$options['pandarepeaterfield_order_by'] . ' ' ;
					}

				} else {
					$orderInfo_str	.=	'pandarf_order ';
				}	

				if( isset( $options['pandarepeaterfield_order'] ) && $options['pandarepeaterfield_order'] == 'DESC' ){
					$order_str		.=	'DESC';
					$orderInfo_str	.=	esc_html__( '- descending', 'panda-pods-repeater-field');
				} else {
					$order_str		.=	'ASC';
					$orderInfo_str	.=	esc_html__( '- ascending', 'panda-pods-repeater-field');
				}


				if( count( $search_arr ) > 0 ) {
					
					$query_str  	= $wpdb->prepare( 'SELECT 
														main_tb.*, 
														`' . self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] . '` 														
													   FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` AS main_tb
													   ' . $join_str  . '
													   WHERE ' . $where_str . ' 
													   ORDER BY ' . $order_str . ' 
													   ' . $limit_str . '; ' , 
													   $search_arr );	
					if( !$limit_bln ){
						$countQ_str		= $wpdb->prepare( 'SELECT 
														   COUNT( main_tb.`id` ) AS "count"														
														   FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` AS main_tb
														   ' . $join_str  . '
														   WHERE ' . $where_str . ' 
														   ORDER BY ' . $order_str . '
														   ; ' , 
														   $search_arr );		
					}
				} else {
					$query_str  	= 'SELECT 
										main_tb.*, 
										`' . self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] . '`
									   	FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` 
									   	' . $join_str  . ' 
									   	WHERE ' . $where_str . ' 
									   	ORDER BY ' . $order_str . '
									   	' . $limit_str . '; '; 
					if( !$limit_bln ){									   	
						$countQ_str  	= 'SELECT 
											COUNT( main_tb.`id` ) AS "count"	
										   	FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` 
										   	' . $join_str  . ' 
										   	WHERE ' . $where_str . ' 
										   	ORDER BY ' . $order_str . '
										   	; '; 									   	
					}
				}
			
				//$db_cla->get_admin_columns_fn( 'comic', 'comic_item', 261, 133 );
				//echo $query_str;
				$rows_arr   	= $wpdb->get_results( $query_str, ARRAY_A );	
				$count_int		= 0;	
				if( !$limit_bln ){	
					$rowsCount_arr 	= $wpdb->get_results( $countQ_str, ARRAY_A );	
				
					if( $rowsCount_arr && !empty( $rowsCount_arr ) ){
						$count_int	=  $rowsCount_arr[0]['count'];	
					}
				}
				//parent iframe $options['id'] pods field id
				
				$pIframeID_str = '';
				if( isset( $_GET ) && isset( $_GET['iframe_id'] ) ){
					$pIframeID_str = $_GET['iframe_id'];
				}
				$query_str = '&podid=' . esc_attr( $options['pod_id'] ) . '&tb=' . esc_attr( $savedtb_int ) . '&poditemid=' . esc_attr( $options['id'] ) ;
				$prfID_str = 'panda-repeater-fields-' . esc_attr( $savedtb_int ) . '-' . esc_attr( $options['id'] ) ;
				// if trash is enabled
				if( isset( $options['pandarepeaterfield_enable_trash'] ) && $options['pandarepeaterfield_enable_trash'] == 1 ){
					echo '<div  id="panda-repeater-fields-tabs-' . esc_attr( $savedtb_int ) . '-' . esc_attr( $options['id'] ) . '" class="pprf-left w100">
							<div class="pprf-tab active" data-target="' . esc_attr( $savedtb_int ) . '-' . esc_attr( $options['id'] ) . '"><span class="dashicons dashicons-portfolio"></span></div>	
							<div class="pprf-tab" data-target="' . esc_attr( $savedtb_int ) . '-' . esc_attr( $options['id'] ) . '"><span class="dashicons dashicons-trash"></span></span></div>	
						  </div>
						 ';
				}
				echo '<div id="' . esc_attr( $prfID_str ) . '" class="pprf-redorder-list-wrap">';
				
				// remove anything after /wp-admin/, otherwise, it will load a missing page				
				//$adminUrl_str 	=  substr( admin_url(), 0, strrpos( admin_url(), '/wp-admin/' ) + 10 );

				$adminUrl_str 	= PANDA_PODS_REPEATER_URL .	'fields/pandarepeaterfield.php';	
				if( is_admin( ) ){
					// remove anything after /wp-admin/, otherwise, it will load a missing page				
					$adminUrl_str 	=  substr( admin_url(), 0, strrpos( admin_url(), '/wp-admin/' ) + 10 );
				} else {
					$adminUrl_str 	= PANDA_PODS_REPEATER_URL .	'fields/pandarepeaterfield.php';			
				}		
				$src_str 	  	= $adminUrl_str . '?page=panda-pods-repeater-field&';
				
				//$src_str   = PANDA_PODS_REPEATER_URL . 'fields/pandarepeaterfield.php?';
				$bg_str		  	= 'pprf-purple-bg';

				$trash_int  	= 0;
				$notTrash_int  	= 0;
				$traBtn_str  	= 'pprf-btn-not-trashed';	
				if( isset( $options['pandarepeaterfield_enable_trash'] ) && $options['pandarepeaterfield_enable_trash'] == 1 ){
					if( isset( $row_obj['pandarf_trash'] ) && $row_obj['pandarf_trash'] == 1 ){
						$traBtn_str  	= 	'pprf-btn-trashed';
					} 
				}
				if( isset( $options['pandarepeaterfield_enable_trash'] ) && $options['pandarepeaterfield_enable_trash'] == 0 ){
					$traBtn_str  	= 	'pprf-btn-delete';
				}				
				//echo 	'<div class="pprf-redorder-list-wrap">';
				echo 		'<ul class="pprf-redorder-list ' . esc_attr( $options['pandarepeaterfield_order_by'] ) . '">';
				//$loaded_str	=	'';
				$options['id']		=	esc_attr( $options['id'] );
				$options['pod_id']	=	esc_attr( $options['pod_id'] );			

				$child_pod 		= new pods( $options['pandarepeaterfield_table'] );

				$adminCols_arr		=	array(); // if apply admin columns is picked, use admin columns instead of name
				if( isset(  $options['pandarepeaterfield_apply_admin_columns'] ) && $options['pandarepeaterfield_apply_admin_columns'] ){					
					$adminCols_arr 	= (array) pods_v( 'ui_fields_manage', $child_pod->pod_data['options'] );
				}				
									
				if ( is_array( $rows_arr ) ) {
					foreach( $rows_arr as $i => $row_obj ) { 	
						$bg_str 	 	= $i % 2 == 0 ? 'pprf-purple-bg' : 'pprf-white-bg';
						$trashed_str 	= 'pprf-not-trashed';
						$traBtn_str  	= 'pprf-btn-not-trashed';
						$css_str		=	'';
						$edit_str		=	'dashicons-edit';
						if( isset( $options['pandarepeaterfield_enable_trash'] ) && $options['pandarepeaterfield_enable_trash'] == 1 ){
							if( isset( $row_obj['pandarf_trash'] ) && $row_obj['pandarf_trash'] == 1 ){
								$trashed_str 	=  	'pprf-trashed' ;
								$traBtn_str  	= 	'pprf-btn-trashed';
								$css_str		=	'display:none';
								$edit_str		=	'dashicons-update';
								$bg_str 	 	= 	$trash_int % 2 == 0 ? 'pprf-purple-bg' : 'pprf-white-bg';
								
							} else{
								$notTrash_int ++;
								$bg_str 	 	= 	$notTrash_int % 2 == 0 ? 'pprf-purple-bg' : 'pprf-white-bg';
							}
						}
						if( isset( $options['pandarepeaterfield_enable_trash'] ) && $options['pandarepeaterfield_enable_trash'] == 0 ){
							$trashed_str 	= 	'';
							$traBtn_str  	= 	'pprf-btn-delete';
							$css_str		=	'display:block';
						}

						//print_r( self::$tbs_arr['pod_' . $savedtb_int ]  );
						$savedtb_int	=	esc_attr( $savedtb_int );
						$row_obj['id']	=	esc_attr( $row_obj['id'] );
										
						$ids_str     	= esc_attr( $savedtb_int . '-' . $row_obj['id'] . '-' . $options['id'] );
						$fullUrl_str 	= esc_attr( $src_str . 'piframe_id=' . $pIframeID_str . '&iframe_id=panda-repeater-edit-' . $ids_str . '' . $query_str . '&postid=' . $id . '&itemid=' . $row_obj['id'] );	

						$title_str		= $row_obj[ self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] ];	
						// integration with Simpods MVC Area Field
						if( isset( $child_pod->fields[ self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] ] ) ){
							$title_str		= $this->simpods_area_field_value( $child_pod->fields[ self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] ], $title_str );
						}	
						$title_str   	= apply_filters( 'pprf_item_title', $title_str, $savedtb_int, $row_obj['id'], $id, $options['id'] );
						$title_str	 	= substr( preg_replace( '/\[.*?\]/is', '',  wp_strip_all_tags( $title_str ) ), 0, 80 ) . pprf_check_media_in_content( $title_str ) ;

						$label_str		= ''; 
						if( isset(  $options['pandarepeaterfield_apply_admin_columns'] ) && $options['pandarepeaterfield_apply_admin_columns'] ){
							//echo '<pre>';
							$id_bln	=	false;
							foreach( $adminCols_arr as $adminCol_str ){
								if( strtolower( $adminCol_str ) == 'id' ){
									$id_bln	=	true;
									continue;
								}
								$colVal_ukn	=	pods_field( $options['pandarepeaterfield_table'], $row_obj['id'], $adminCol_str ); 
								// integration with Simpods MVC Area Field
								if( isset( $child_pod->fields[ $adminCol_str ] ) ){ 
									if( $child_pod->fields[ $adminCol_str ]['type'] == 'pick' &&  $child_pod->fields[ $adminCol_str ]['pick_object'] == 'user' ){
										$colVal_ukn = $colVal_ukn['display_name'];
									}
									if( $child_pod->fields[ $adminCol_str ]['type'] == 'simpodsareafield' ){
										$colVal_ukn		= $this->simpods_area_field_value( $child_pod->fields[ $adminCol_str ], $colVal_ukn );
									}
								}									
								if( is_string( $colVal_ukn ) || is_numeric( $colVal_ukn ) ){
									$label_str .= '<strong>' . esc_html( $child_pod->fields[ $adminCol_str ]['label'] ) . ':</strong> ' . substr( preg_replace( '/\[.*?\]/is', '',  wp_strip_all_tags( $colVal_ukn ) ), 0, 80 ) . pprf_check_media_in_content( $colVal_ukn )  ;
								}							
							}

							if( $id_bln ){
								$label_str = '<strong>ID:</strong> ' . esc_html( $row_obj['id'] ) . ' ' . $label_str;
							}
							//echo '<pre>';	
						}
						if( $label_str	== '' ){ 
							$label_str		= '<strong>ID:</strong> ' . esc_html( $row_obj['id'] ) . '<strong> ' . self::$tbs_arr['pod_' . $savedtb_int ]['name_label'] . ':</strong> ' .  $title_str;
						}

						// remove javascript
						//$label_str = preg_replace( '/<script\\b[^>]*>(.*?)<\\/script>/is', '', $label_str );
						echo '<li data-id="' . $row_obj['id'] . '" class="' . $trashed_str . '" id="li-' . $ids_str . '" style="' . $css_str . '">';						
						echo 	'<div class="pprf-row pprf-left ">
									<div class="w100 pprf-left" id="pprf-row-brief-' . $ids_str . '">
										<div class="pprf-left pd8 pprf-left-col ' . esc_attr( $bg_str ) . '">' . $label_str . '</div>
										<div class="button pprf-right-col center pprf-trash-btn ' . $traBtn_str . '" data-podid="' . $options['pod_id'] . '"  data-postid="' . $id . '"  data-tb="' . $savedtb_int . '"  data-itemid="' . $row_obj['id'] . '"  data-userid="' . $current_user->ID . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-target="' . $ids_str . '" >
											<span class="dashicons dashicons-trash pdt6 mgb0 "></span>
											<div id="panda-repeater-trash-' . $ids_str . '-loader" class="pprf-left hidden mgl5">
												<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 loading pprf-left"/>
											</div>
										</div>		
										<div class="button pprf-right-col center pprf-save-btn" data-podid="' . $options['pod_id'] . '"  data-postid="' . $id . '"  data-tb="' . $savedtb_int . '"  data-itemid="' . $row_obj['id'] . '"  data-userid="' . $current_user->ID . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-target="' . $ids_str . '" >
											<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/save-icon-tran.png' ) . '" class="pprf-save-icon  mgt8 mgb2"/>	
											<div id="panda-repeater-save-' . $ids_str . '-loader" class="pprf-left hidden mgl5">
												<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left"/>										
											</div>
										</div>																	
										<div class="button pprf-edit pprf-row-load-iframe alignright pprf-right-col center pprf-edit-btn" data-target="' . $ids_str . '" data-url="' . $fullUrl_str . '">
											<span class="dashicons ' . $edit_str . ' pdt8 mgb0 pprf-edit-span"></span>
											<div id="panda-repeater-edit-' . $ids_str . '-loader" class="pprf-left hidden mgl5">
												<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left"/>
											</div>	
										</div>
									</div>
									<div>
										<iframe id="panda-repeater-edit-' . $ids_str . '" name="panda-repeater-edit-' . $ids_str . '" frameborder="0" src="" scrolling="no" style="display:none;" class="panda-repeater-iframe w100">
										</iframe>
										<div id="panda-repeater-edit-expand-' . $ids_str . '" class="w100 pprf-left center pdt3 pdb3 pprf-expand-bar pprf-edit-expand" data-target="' . $ids_str . '"  style="display:none;">' . esc_html__('Content missing? Click here to expand', 'panda-pods-repeater-field' ). '</div>
									</div>
								 </div>';
						 echo '</li>';
						// $loaded_str	.=	$row_obj['id'] . ',';
					}
					//$loaded_str	=	rtrim( $loaded_str, ',');
				}
				echo		'</ul>';
				//echo '</div>';
				$bg_str 	 = $bg_str == 'pprf-white-bg' ? 'pprf-purple-bg' : 'pprf-white-bg';
				echo '</div>';
				echo '<div id="next-bg" data-bg="' . esc_attr( $bg_str ) . '"></div>';	
				echo '<div id="panda-repeater-fields-' . esc_attr( $savedtb_int ) . '-' . esc_attr( $options['id'] ) . '-loader" class="center hidden w100 mgb10 pprf-left">';
				echo 	'<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class=""/>';
				echo '</div>';
				// depreciated, don't show if the parent post is not created
				if( is_numeric( $id ) ){
					$token_str = $id;
				} else {
					// create a token if adding a new parent item, which will be used to identify which child item to update after saving the parent item
					$token_str = esc_attr( time() . '_' .  $savedtb_int . '_' .  $options['id'] . '_' .  $current_user->ID . '_pandarf' );		
				}
				$ids_str     	= esc_attr( $savedtb_int . '-' . $options['id'] ); // one less id compared to the added ones
				
				$fullUrl_str 	= esc_attr( $src_str . 'piframe_id=' . $pIframeID_str . '&iframe_id=panda-repeater-add-new-' . $ids_str . '' . $query_str . '&postid=' . $token_str );
				$hidden_str		= '';
				if( $limit_bln && count( $rows_arr ) == $options['pandarepeaterfield_entry_limit'] ){
					$hidden_str	=	'hidden';	
				}				
				$addNew_str		= 
				'<div class="pprf-row pprf-left mgb8 ' . $hidden_str . '" id="' . $prfID_str . '-add-new">
					<div class="w100 pprf-left">
						<div class="pprf-left pd8 pprf-left-col pprf-grey-bg "><strong>Add New ' . esc_html( get_the_title( $options['id'] ) ) . '</strong></div>
						<div class="button pprf-right-col center pprf-trash-btn" data-target="' . esc_attr( $traBtn_str ) . '" >
						</div>									

						<div class="button pprf-right-col center pprf-save-btn pprf-save-new-btn alignright " data-podid="' . $options['pod_id'] . '"  data-postid="' . $id . '"  data-tb="' . $savedtb_int . '" data-userid="' . $current_user->ID . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-target="' . $ids_str . '" >
							<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/save-icon-tran.png' ) . '" class="pprf-save-icon  mgt8 mgb2"/>	
							<div id="panda-repeater-save-' . $ids_str . '-loader" class="pprf-left hidden mgl5">
								<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left"/>										
							</div>
						</div>
						<div id="pprf-row-brief-' . $ids_str . '" class="alignright pprf-right-col button pprf-add pprf-row-load-iframe pprf-add " data-target="' . $ids_str . '" data-url="' . $fullUrl_str . '">
							<span class="dashicons dashicons-edit pdt8 mgb0 "></span>
							<div id="panda-repeater-add-new-' . $ids_str . '-loader" class="pprf-left hidden mgl5">
								<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left"/>
							</div>	
						</div>																		
						<iframe id="panda-repeater-add-new-' . $ids_str . '" name="panda-repeater-add-new-' . $ids_str . '" frameborder="0" src="" scrolling="no" style="display:none;" class="panda-repeater-iframe w100" >
						</iframe>
						<div id="panda-repeater-add-new-expand-' . $ids_str . '" class="w100 pprf-left center pdt3 pdb3 pprf-expand-bar pprf-add-expand" data-target="' . $ids_str . '"  style="display:none;">' . esc_html__('Content missing? Click here to expand', 'panda-pods-repeater-field' ). '</div>
					</div>
				 </div>';

				echo $addNew_str;
				echo '<div id="panda-repeater-trash-info-' . $ids_str . '" data-enable-trash="' . esc_attr( $options['pandarepeaterfield_enable_trash'] ) . '"  style="display:none;"/></div>';
				echo '<input type="hidden" name="' . esc_attr( $prfID_str ) . '-entry-limit" id="' . esc_attr( $prfID_str ) . '-entry-limit" value="' . esc_attr( $options['pandarepeaterfield_entry_limit'] ) . '">';
				echo '<input type="hidden" name="' . $name . '" value="' . $token_str . '">';
				if( is_numeric( $options['pandarepeaterfield_entry_limit'] ) && $options['pandarepeaterfield_entry_limit'] > 0 ){
					echo '<div class="pprf-left w100"><small>Max ' . esc_html( get_the_title( $options['id'] ) . ' - ' . $options['pandarepeaterfield_entry_limit'] ) . '</small></div>';	
				}
				if( isset( $options['pandarepeaterfield_enable_load_more'] ) && $options['pandarepeaterfield_enable_load_more'] && !$limit_bln ){
					echo '<div class="pprf-load-more-wrap w100 pprf-left"  id="pprf-load-more-wrap-' . $ids_str . '">
							<select class="pprf-left pprf-select mgr5 panda-repeater-to-load" name="panda-repeater-to-load" > 
								<option value="append_to">' . __('Append to', 'panda-pods-repeater-field' ) . '</option>
								<option value="replace">' . __('Replace', 'panda-pods-repeater-field' ) . '</option>								
							</select> 							
							<label class="pprf-left pdt2 mgr5" for="panda-repeater-amount"> ' . esc_html__('the list with', 'panda-pods-repeater-field' ) . '</label> 
							<input name="panda-repeater-amount" id="panda-repeater-amount-' . $ids_str . '" value="' . esc_attr( intval( $options['pandarepeaterfield_initial_amount'] ) ) . '" class="pprf-left pprf-input mgr5" type="number" step="1" min="1"  autocomplete="off"/> 
							<label class="pprf-left pdt2 mgr5" for="panda-repeater-start-from">' . esc_html__('new items from item', 'panda-pods-repeater-field' ) . '</label>
							<input name="panda-repeater-start-from" id="panda-repeater-start-from-' . $ids_str . '" value="' . esc_attr( intval( $options['pandarepeaterfield_initial_amount'] ) ) . '" class="pprf-left pprf-input mgr5"  type="number" step="1" min="0" autocomplete="off" title="' . esc_attr__('Start from 0', 'panda-pods-repeater-field' ) . '"/>  
							<div id="panda-repeater-load-more-button-' . $ids_str . '" class="pprf-left pprf-load-more-btn mgr5" data-target="' . $ids_str . '" data-podid="' . $options['pod_id'] . '"  data-postid="' . esc_attr( $id ) . '"  data-tb="' . esc_attr( $savedtb_int ) . '" data-userid="' . esc_attr( $current_user->ID ) . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-cptitle="' . esc_attr( get_the_title( $options['id'] ) ) . '" data-enable-trash="' . esc_attr( $options['pandarepeaterfield_enable_trash'] ) . '" data-order="' . esc_attr( $options['pandarepeaterfield_order'] ) . '" data-order-by="' . esc_attr( $options['pandarepeaterfield_order_by'] ) . '" />' . esc_html__('Load', 'panda-pods-repeater-field' ) . '</div>
							<label class="pprf-left pdt2 mgr5">' . esc_html__(' | Total items:', 'panda-pods-repeater-field' ) . ' ' . esc_html( $count_int ) . '</label>
							<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left pprf-ajax-img mgt13 mgr5" style="display:none;"/>
							<div class="pprf-load-more-report"></div>
							
						  </div> ';	
				}	
				if( isset( $options['pandarepeaterfield_order_by'] ) && !empty( $options['pandarepeaterfield_order_by'] )  && isset( $options['pandarepeaterfield_display_order_info'] ) && $options['pandarepeaterfield_display_order_info'] ){
					echo '<div class="pprf-order-info pdt5 w100 pprf-left" id="pprf-order-info-' . $ids_str . '">
						  ' . esc_html( $orderInfo_str ) . '	
						  </div>';
				}			
			} else {
				echo __( 'No Advanced Content Type Table Selected', 'panda-pods-repeater-field' );
			}
		} 
		//pods_view( PODS_DIR . 'ui/fields/text.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Get the data from the field, run when loading an area
	 *
	 * @param string $name The name of the field
	 * @param string|array $value The value of the field
	 * @param array $options
	 * @param array $pod
	 * @param int $id
	 * @param boolean $in_form
	 *
	 * @return array Array of possible field data
	 *
	 * @since 1.0
	 */
	public function data ( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {
		
		return (array) $value;
	}

	/**
	 * Build regex necessary for JS validation
	 *
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param string $pod
	 * @param int $id
	 *
	 * @return bool
	 * @since 1.0
	 */
	public function regex ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		return false;
	}

	/**
	 * Validate a value before it's saved
	 *
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param array $fields
	 * @param array $pod
	 * @param int $id
	 * @param array $params
	 *
	 * @return bool
	 * @since 1.0
	 */
	public function validate ( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		
		return true;
	}

	/**
	 * Change the value or perform actions after validation but before saving to the DB
	 *
	 * @param mixed $value
	 * @param int $id
	 * @param string $name
	 * @param array $options
	 * @param array $fields
	 * @param array $pod
	 * @param object $params
	 *
	 * @return mixed
	 * @since 1.0
	 */
	public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
	
		return $value;
	}

	/**
	 * Save the value to the DB
	 *
	 * @param mixed $value
	 * @param int $id
	 * @param string $name
	 * @param array $options
	 * @param array $fields
	 * @param array $pod
	 * @param object $params
	 *
	 * @return bool|void Whether the value was saved
	 *
	 * @since 1.0.0
	 */
	public function save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		
		return null;
	}

	/**
	 * Perform actions after saving to the DB
	 *
	 * @param mixed $value
	 * @param int $id
	 * @param string $name
	 * @param array $options
	 * @param array $fields
	 * @param array $pod
	 * @param object $params
	 *
	 *
	 * @since 1.0
	 */
	public function post_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		
	}

	/**
	 * Perform actions before deleting from the DB
	 *
	 * @param int $id
	 * @param string $name
	 * @param null $options
	 * @param string $pod
	 *
	 *
	 * @since 1.0
	 */
	public function pre_delete ( $id = null, $name = null, $options = null, $pod = null ) {

	}

	/**
	 * Delete the value from the DB
	 *
	 * @param int $id
	 * @param string $name
	 * @param array $options
	 * @param array $pod
	 *
	 *
	 * @since 1.0.0
	 */
	public function delete ( $id = null, $name = null, $options = null, $pod = null ) {
/*		global $wpdb;
		
		if( $options['type'] == 'pandarepeaterfield' && $options['pandarepeaterfield_delete_family_tree'] == 1 ){ // just to ensure
			
			$table_str 	= $wpdb->prefix . 'pods_' . $options['pandarepeaterfield_table'] ;			  
			// fetch the child item data and see if the item belong to the current post
			$where_str  = ' `pandarf_parent_pod_id`  = %d
						  	AND `pandarf_parent_post_id` = %d
						   	AND `pandarf_pod_field_id`   = %d '; 						
			
			$where_arr	= array( $options['pod_id'], $id,  $options['id'] );				   	  
			$query_str  = $wpdb->prepare( 'DELETE FROM `' . $table_str . '` WHERE ' . $where_str . '' , $where_arr );				
			$del_bln   	= $wpdb->query( $query_str );				

		}
		

		//$repeater_arr = is_pandarf_fn( $params_arr->name, $pods_obj->pod_id );	

		
		exit();		*/
	}

	/**
	 * Perform actions after deleting from the DB
	 *
	 * @param int $id
	 * @param string $name
	 * @param array $options
	 * @param array $pod
	 *
	 *
	 * @since 1.0
	 */
	public function post_delete ( $id = null, $name = null, $options = null, $pod = null ) {
/*		echo $id . ' ' . $name;
		print_r( $options );
		print_r( $pod );
		exit();*/
	}

	/**
	 * Customize the Pods UI manage table column output
	 *
	 * @param int $id
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param array $fields
	 * @param array $pod
	 *
	 * @since string Value to be shown in the UI
	 *
	 * @since 1.0
	 */
	public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		return $value;
	}
	/**
	 * pods_pre_save_fn, called by panda-pods-repeater-field.php, to update the relationship between the child and the parent
	 *
	 * @param array $pieces 
	 * @param boolean $is_new_item
	 *
	 * @since 29/01/2016
	 *
	 * @since 1.0.0
	 */	
	public function pods_post_save_fn( $pieces_arr, $isNew_bln, $id_int ) {
		
		global $wpdb, $current_user;

		$db_cla      = new panda_pods_repeater_field_db();
		$tables_arr  = $db_cla->get_tables_fn();
		
		/*if( isset( $pieces_arr['params'] ) ){
			// for pods 2.6
			$pieces_arr['params'] = $pieces_arr['params'];
			$loc_arr = explode( '?', $pieces_arr['params']->location );
		} else {
			// for pods 2.6.1
			$pieces_arr['params'] = $pieces_arr['params'];
			$loc_arr = explode( '?', $pieces_arr['params']->location );			
		}*/
		
		$cItemID_int  = $id_int;
		$pdTb_str     = '';
		$query_arr	  = array();
		if( isset( $_SERVER['HTTP_REFERER'] ) ){
			
			$loc_arr = explode( '?page=panda-pods-repeater-field&', $_SERVER['HTTP_REFERER'] );
			if( isset( $loc_arr[1] ) ){
			
				parse_str( $loc_arr[1], $query_arr );
				$postType_bln = false;
				if( isset( $tables_arr['pod_' . $query_arr['tb'] ] ) ){
					if( $tables_arr['pod_' . $query_arr['tb'] ]['type'] == 'post_type' ){
						
						$postType_bln = true;
					}
				}
				/**
				 * don't need to check array_key_exists( 'pod_' . $query_arr['podid'], $tables_arr )
				 * as, meta storage tabe is not in the list
				 * update panda keys after saving a child
				 */
				if( isset(  $query_arr['podid'] ) && is_numeric( $query_arr['podid'] )  ){

					$now_str		= date('Y-m-d H:i:s');
					// fetch the child table name
					$query_str  	= $wpdb->prepare( 'SELECT * FROM `' . $wpdb->posts . '' . '`  WHERE `ID` = %d LIMIT 0, 1' , array( $query_arr['tb'] ) );	
					$childTb_arr   	= $wpdb->get_results( $query_str, ARRAY_A );		
					$pdTb_str		= $childTb_arr[0]['post_name'];				
					$table_str 	 	= $wpdb->prefix . 'pods_' . $pdTb_str;		
					
	  
					// fetch the child item data
					/*if( strpos( $query_arr['iframe_id'], 'panda-repeater-edit' ) === 0 && isset( $query_arr['itemid'] ) ){
						$cItemID_int = $query_arr['itemid']; 	
					} else {
							
					}*/
					$query_str  	= $wpdb->prepare( 'SELECT * FROM `' . $table_str . '`  WHERE `id` = %d LIMIT 0, 1' , array( $id_int ) );	
				//	echo $query_str;
					//echo '--' . $cItemID_int . '--';						
					$item_arr   	= $wpdb->get_results( $query_str, ARRAY_A );		  
					
					if( is_array( $item_arr ) && count( $item_arr ) > 0 ){
						
							//	echo ' ' . $cItemID_int . ' ';	
						/*$panCats_arr	= maybe_unserialize( $item_arr[0]['pandarf_categories'] );
						
						if( !is_array( $panCats_arr ) ){
							$panCats_arr = array();	
						}
						array_push( $panCats_arr, intval( $query_arr['podid'] ) . '.' . $query_arr['postid'] . '.' . intval( $query_arr['poditemid'] ) ) ;
						$panCats_arr	= array_unique( $panCats_arr );*/
						$values_arr 	= array();			  
						//$update_str 	= '`pandarf_categories` = %s';
						//array_push( $values_arr, maybe_serialize( $panCats_arr ) );
						$update_str     = ' `pandarf_parent_pod_id` = %d';
						array_push( $values_arr, $query_arr['podid'] );
						$update_str    .= ', `pandarf_parent_post_id` = %s';
						array_push( $values_arr, $query_arr['postid'] );
						$update_str    .= ', `pandarf_pod_field_id` = %d';
						array_push( $values_arr, $query_arr['poditemid'] );																				
						$update_str    .= ', `pandarf_modified` = %s';
						array_push( $values_arr, $now_str );
						$update_str    .= ', `pandarf_modified_author` = %d';																
						array_push( $values_arr, $current_user->ID );
		
						//order
						if( $isNew_bln ){
							pprf_updated_tables( $table_str, 'remove' );
							if( pprf_updated_tables( $table_str ) == false ){
								$db_cla->update_columns_fn( $pdTb_str );
							}
							//$db_cla->update_columns_fn( $table_str );					
							$query_str  	= $wpdb->prepare( 'SELECT MAX(`pandarf_order`) AS last_order FROM `' . $table_str . '` WHERE `pandarf_parent_pod_id` = %d AND `pandarf_parent_post_id` = "%s" AND `pandarf_pod_field_id` = %d' , array( $query_arr['podid'], $query_arr['postid'], $query_arr['poditemid'] ) );	
							
							$order_arr   	= $wpdb->get_results( $query_str, ARRAY_A );	
							$update_str    .= ', `pandarf_order` = %d';
							array_push( $values_arr, ( $order_arr[0]['last_order'] + 1 ) );					
						}
						
										
						// if first time update
						if( $item_arr[0]['pandarf_created'] == '' || $item_arr[0]['pandarf_created'] == '0000-00-00 00:00:00' ){
							$update_str    .= ', `pandarf_created` = %s';
							array_push( $values_arr, $now_str );
						}
						if( $item_arr[0]['pandarf_author'] == '' || $item_arr[0]['pandarf_author'] == 0 ){				
							$update_str    .= ', `pandarf_author` = %d';																
							array_push( $values_arr, $current_user->ID );	
						}
						array_push( $values_arr, $id_int );				
						//if( count( $values_arr ) > 0 ){
							$query_str  	= $wpdb->prepare( 'UPDATE  `' . $table_str . '` SET ' . $update_str . ' WHERE id = %d' , $values_arr );
						//} else {
						//	$query_str  	= 'UPDATE  `' . $table_str . '` SET ' . $update_str . ' WHERE id = "' . $id_int . '";';
						//}
						//echo $query_str;
						$items_bln  	= $wpdb->query( $query_str, ARRAY_A );
					}
				} 
				
			} else {

			// saving a pod table, not a post type table, deprecated, now require saving parent post first
			if( isset( $_POST ) && is_array( $_POST ) ){	
			
				foreach( $_POST as $field_str => $v_str ){
						if( is_string( $v_str ) ){ 	
						
							$target_arr 	= explode( '_', $v_str );
						
							if( $target_arr[ count( $target_arr ) - 1 ] == 'pandarf' ){
								
								$childPodID_int = $target_arr[1];
								// get the child pod name
								$query_str  	= $wpdb->prepare( 'SELECT `post_name` FROM `' . $wpdb->posts . '` WHERE ID = %d LIMIT 0, 1' , array( $childPodID_int ) );	
					
								$item_arr  	= $wpdb->get_results( $query_str, ARRAY_A );	
		
								if( isset( $item_arr[0] ) ){
									// search the token in the child table and update
									$query_str  	= $wpdb->prepare( 'UPDATE `' . $wpdb->prefix . 'pods_' . $item_arr[0]['post_name'] . '` SET `pandarf_parent_post_id` = %d WHERE `pandarf_parent_post_id` = %s ' , array( $id_int, $v_str ) );	
								//			print_r( $query_str );	
								//exit($childPodID_int . ' ' . $v_str );
									$item_arr  	= $wpdb->query( $query_str );							
								}
							}	
	
						}
					}
				}
				
			} 
			
			
		}
		// find the panda field related tables
		$ppTbs_arr   = array();
		if( count( $query_arr ) > 0 ){
			$ppTbs_arr   = array(
								'parent' => $tables_arr['pod_' . $query_arr['podid'] ],
								'child'  => $tables_arr['pod_' . $query_arr['tb'] ],							
								);
		}
		
		$pieces_arr  = apply_filters( 'pprf_filter_pods_post_save_fn', $pieces_arr, $isNew_bln, $id_int, $query_arr, $ppTbs_arr );
		
		do_action( 'pprf_action_pods_post_save_fn', $pieces_arr, $isNew_bln, $id_int, $query_arr, $ppTbs_arr );		
		
		return $pieces_arr;
		
	} 
	/**
	 * pods_post_delete_fn, called by panda-pods-repeater-field.php
	 *
	 * @param array $item_obj 
	 * @param array $pods_arr
	 * @param array $podsAPI_obj	 
	 *
	 * @since 01/12/2016
	 *
	 * @since 1.0.0
	 */	
	public function pods_post_delete_fn( $item_obj, $pods_arr, $podsAPI_obj ) {
		
		global $wpdb, $current_user;
						
		$item_obj 	 = apply_filters( 'pprf_filter_pods_post_delete_fn', $item_obj, $pods_arr, $podsAPI_obj );	
			
		do_action( 'pprf_action_pods_post_delete_fn', $item_obj, $pods_arr, $podsAPI_obj );
		
		return $item_obj;
	}
	/**
	 * field_table_fields_fn: if a table is set as a field, check and update the table's fields
	 */		
	public function field_table_fields_fn( $pod_arr, $obj ) {
		
		foreach( $pod_arr['fields'] as $field_arr ){
			if( $field_arr['type'] == self::$type && isset( $field_arr['pandarepeaterfield_table'] ) ){ 				
				$db_cla      	= 	new panda_pods_repeater_field_db();
				$savedtb_str	=	$field_arr['pandarepeaterfield_table'];
				$cPod_arr		=	explode( '_', $savedtb_str );
				// if saved as pod_num, version < 1.2.0
				if( count( $cPod_arr ) == 2 && $cPod_arr[0] == 'pod' && is_numeric( $cPod_arr[1] ) ){				
					$podTbs_arr = $this->pods_tables_fn() ;

					// example $podTbs_arr[ $field_arr['pandarepeaterfield_table'] ] ->  $podTbs_arr['pod_16']
					if( isset( $podTbs_arr[ $savedtb_str ] ) ){					
						
						$tables_arr  = $db_cla->update_columns_fn( $podTbs_arr[ $savedtb_str ] );	
						
					}
				} else {
					$podTbs_arr = $this->pods_tables_fn( 2 ) ;

					if( in_array( $savedtb_str, $podTbs_arr ) ){						

						$tables_arr  = $db_cla->update_columns_fn( $savedtb_str );	
					}					
				}
			}
			
		}
		

	}

	/**
	 * save tables
	 * @param integer $type_int 0: table_num 1 : pod_table 2 : table
	 */
	 function pods_tables_fn( $type_int = 0 ){
		 
		global $wpdb, $current_user;

		if( ! defined( 'PPRF_ALL_TABLES' ) ){				
			$db_cla      = new panda_pods_repeater_field_db();
			$tables_arr  = $db_cla->get_tables_fn();
			define( 'PPRF_ALL_TABLES', serialize( $tables_arr ) );	
			
		} else {
			$tables_arr  = unserialize( PPRF_ALL_TABLES );			
		}
		$podsTbs_arr = array();
		if( is_array( $tables_arr ) ){
			foreach( $tables_arr as $tb_str => $tbv_arr ){

				if( $tbv_arr['type'] != 'wp' ){
					//$tb_str 				= substr( $tb_str, 5 );
					if( $type_int == 0 ){
						$podsTbs_arr[ $tb_str ] = $tbv_arr['pod'];						
					} 
					if( $type_int == 1 ){
						$podsTbs_arr[ 'pod_' . $tbv_arr['pod'] ] = $tbv_arr['pod'];						
					}
					if( $type_int == 2 ){
						$podsTbs_arr[ $tbv_arr['pod'] ] = $tbv_arr['pod'];						
					}					
				}				
			}
		}
		
		self::$tbs_arr = $tables_arr;
			
		if( ! defined( 'PPRF_PODS_TABLES' )   ){				
			define( 'PPRF_PODS_TABLES', serialize( $podsTbs_arr ) );	
		} 	
	//	print_fn( $podsTbs_arr);
		return $podsTbs_arr;			 
	 }

	/**
	 * update_child_pod_fn when a post is saved, update child pods.
	 *
	 * @deprecated now the parent item has to be available to before creating a child item	 
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 * @user string $post_arr[ self::$input_str ] example 1455276690_20_50_1 : time_childPod_parentPodField_authorID
	 */
	function update_child_pod_fn( $postID_int, $post_obj, $update_bln ) {	
		global $wpdb, $current_user;
		// if not an update, search the token and replace the child pod
		
		// avoid updating when it is a draft or revision
		if(  isset( $_POST['post_status'] ) && ( $_POST['post_status'] == 'publish' && $_POST['post_type'] == 'revision' ) ){
		
		//if( $update_bln && isset( $_POST[ self::$input_str ] ) ){
			foreach( $_POST as $k_str => $v_str ){
				if( strpos( $k_str, 'pods_meta_' ) === 0 ){
					$target_arr 	= explode( '_', $v_str );					
					
					if( $target_arr[ count( $target_arr ) - 1 ] == 'pandarf' ){
						
						$childPodID_int = $target_arr[1];
						// get the child pod name
						$query_str  	= $wpdb->prepare( 'SELECT `post_name` FROM `' . $wpdb->posts . '` WHERE ID = %d LIMIT 0, 1;' , array( $childPodID_int ) );	
			
						$item_arr  	= $wpdb->get_results( $query_str, ARRAY_A );	

						if( isset( $item_arr[0] ) ){
							// search the token in the child table and update
							$query_str  	= $wpdb->prepare( 'UPDATE `' . $wpdb->prefix . 'pods_' . $item_arr[0]['post_name'] . '` SET `pandarf_parent_post_id` = %d WHERE `pandarf_parent_post_id` = "%s" ' , array( $post_obj->ID, $v_str ) );	
							//print_r( $query_str );	
						//exit($childPodID_int . ' ' . $v_str );
							$item_arr  	= $wpdb->query( $query_str );							
						}
					}
				}
			}
		}
	}	 
	/**
	 * Fetch the first item in the simpodsareafield
	 * 
	 */
	public function simpods_area_field_value( $field_details, $item_value ){

		if( ! defined( 'SIMPODS_VERSION' ) || is_array( $item_value ) || ! isset( $field_details[ 'type' ] ) || $field_details[ 'type' ] != 'simpodsareafield' ){
			return $item_value;
		}

		$ids = explode( ',', $item_value );
		// simpods area field only store numbers
		if( ! is_numeric( $ids[ 0 ] ) ){
			return $item_value;
		}
		global $funs_cla;

		$fields_arr = array(
						'id' => $ids[ 0 ],
						);
		$atts_arr 	= array(
						'target_tb' => 'pods_' . $field_details[ 'options' ][ 'simpodsareafield_table' ],
						'limit'		=> 1,
						);
		$value_arr  = array();
		if( method_exists( $funs, 'simpods_select' ) ){ // after Simpods 3.0.0 variable names update
			$value_arr = $funs->simpods_select( $fields_arr, $atts_arr );		
		} else if( method_exists( $funs_cla, 'simpods_select' ) ){ // Since Simpods 3.0.0
			$value_arr = $funs_cla->simpods_select( $fields_arr, $atts_arr );		
		} else if( method_exists( $funs_cla, 'simpods_select_fn' ) ){ // before Simpods 3.0.0
			$value_arr = $funs_cla->simpods_select_fn( $fields_arr, $atts_arr );		
		} 

		if( ! empty( $value_arr ) && isset( $value_arr[ 0 ]['sp_title'] ) ){
			return $value_arr[ 0 ]['sp_title'];
		} else {
			return $item_value;
		}

	}
}
