<?php
/**
* Class to create a new Pods field type: PodsField_Pandarepeaterfield
*
* @version: 1.1.5
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
		self::$actTbs_arr = $this->pods_tables_fn();		
		
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

		global $wpdb;
		$tables_arr = $this->pods_tables_fn( 2 );

		
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
							'0' 	=> 'No',	
							'1'  	=> 'Yes',																							
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
				'dependency' => true
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
                'label' 	 => __( 'Intial Amount', 'panda-pods-repeater-field' ),
                'depends-on' => array( self::$type . '_enable_load_more' => 1 ),
                'type' 		 => 'number',
                'default' 	 => '10',
                'data' 		 => '',	
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
				'description'=> __( 'Enter a field of the table. Default to pandarf_order. If not pandarf_order, re-order will be disabled. Min PHP version 5.5', 'panda-pods-repeater-field' ),
            ),	  
            self::$type . '_order' => array(
                'label' 	 => __( 'Order', 'panda-pods-repeater-field' ),
                'default' 	 => '0',
                'type' 		 => 'pick',
                'data' 		 => array('ASC' => 'Ascending', 'DESC' => 'Descending' ),
				'description'=> __( 'Default to Ascending', 'panda-pods-repeater-field' ),
            ),	
            self::$type . '_display_order_info' => array(
                'label' 	 => __( 'Display Order Info', 'panda-pods-repeater-field' ),
                'default' 	 => '0',
                'type' 		 => 'pick',
                'data' 		 => $bln_arr,				
            ),                   
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
				
		$allow_bln = true;
		
		if( !defined( 'PANDA_PODS_REPEATER_URL' ) || !is_user_logged_in() || !current_user_can('edit_posts') ){
			// action before the iframe
			$allow_bln = false;
			
		}
		
		$allow_bln = apply_filters( 'pprf_load_panda_repeater_allow', $allow_bln, $_GET );
		if( !$allow_bln ){
			echo apply_filters( 'pprf_load_panda_repeater_allow_msg', __('You do not have permission to edit this item.', 'panda-pods-repeater-field' ) ) ;
		} else {		
		
			$db_cla 	     = new panda_pods_repeater_field_db();
		
			$options 		 = (array) $options;
			
			$form_field_type = PodsForm::$field_type;
			//echo $name . ' ' . $id ;
			
			/*if ( is_array( $value ) ){					
				$value = implode( ' ', $value );
			}*/
			
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
				echo apply_filters( 'pprf_load_panda_repeater_allow_msg', __('Please save the parent first to add ' . strtolower( $post_arr['post_title'] ) . '. ', 'panda-pods-repeater-field' ) ) ;
				return;
			}			
			if( $tb_str != ''  ){
				$tbInfo_arr	 = $db_cla->get_pods_tb_info_fn( 'pods_' . $tb_str );
				//$tbabbr_str  = $tbInfo_arr['type'] == 'pod'? 't' : 'd';
				
				// load items for the current post only using regular expression
				$where_str   =    '   `pandarf_parent_pod_id`  = %d
							   	  AND `pandarf_parent_post_id` = %d
							   	  AND `pandarf_pod_field_id`   = %d '; 		
				$search_arr  = 	array( $options['pod_id'], $id, $options['id'] );			  	

				//'where'   => '`' . $tbabbr_str . '`.`pandarf_categories` REGEXP "(:\"' . $options['pod_id'] . '.' . $id . '.' . $options['id'] . '\";{1,})"', 
				
				/*
				only load published, couldn't find a way ar
				$params = array(
					'where'   => $where_str
				); 			
				$pod_cla   = pods( $tb_str, $params );
			
				$rows_obj  = $pod_cla->data();*/
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
				//print_r (self::$tbs_arr['pod_' . $savedtb_int ]);
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
					//$options['pandarepeaterfield_order']	=	abs( intval( $options['pandarepeaterfield_initial_amount'] ) );						
				} else {
					$orderInfo_str	.=	'pandarf_order ';
				}	

				if( isset( $options['pandarepeaterfield_order'] ) && $options['pandarepeaterfield_order'] == 'DESC' ){
					$order_str		.=	'DESC';
					$orderInfo_str	.=	'descending';
				} else {
					$order_str		.=	'ASC';
					$orderInfo_str	.=	'- ascending';
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
					echo '<div  id="panda-repeater-fields-tabs-' . esc_attr( $savedtb_int ) . '-' . esc_attr( $options['id'] ) . '" class="alignleft w100">
							<div class="pprf-tab active" data-target="' . esc_attr( $savedtb_int ) . '-' . esc_attr( $options['id'] ) . '"><span class="dashicons dashicons-portfolio"></span></div>	
							<div class="pprf-tab" data-target="' . esc_attr( $savedtb_int ) . '-' . esc_attr( $options['id'] ) . '"><span class="dashicons dashicons-trash"></span></span></div>	
						  </div>
						 ';
				}
				echo '<div id="' . esc_attr( $prfID_str ) . '" class="pprf-redorder-list-wrap">';
				
				// remove anything after /wp-admin/, otherwise, it will load a missing page				
				$adminUrl_str 	=  substr( admin_url(), 0, strrpos( admin_url(), '/wp-admin/' ) + 10 );
				
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
										
						$ids_str     = esc_attr( $savedtb_int . '-' . $row_obj['id'] . '-' . $options['id'] );
						$fullUrl_str = esc_attr( $src_str . 'piframe_id=' . $pIframeID_str . '&iframe_id=panda-repeater-edit-' . $ids_str . '' . $query_str . '&postid=' . $id . '&itemid=' . $row_obj['id'] );	
						
						$title_str   = apply_filters( 'pprf_item_title', $row_obj[ self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] ], $savedtb_int, $row_obj['id'], $id, $options['id'] );
						$title_str	 	= 	esc_attr( $title_str );

						
						echo '<li data-id="' . $row_obj['id'] . '" class="' . $trashed_str . '" id="li-' . $ids_str . '" style="' . $css_str . '">';						
						echo 	'<div class="pprf-row alignleft ">
									<div class="w100 alignleft" id="pprf-row-brief-' . $ids_str . '">
										<div class="alignleft pd8 pprf-left-col ' . esc_attr( $bg_str ) . '"><strong>' . esc_html( get_the_title( $options['id'] ) ) . ' ID:</strong> ' . esc_html( $row_obj['id'] . ' - ' . $title_str ) . '</div>
										<div class="button pprf-right-col center pprf-trash-btn ' . $traBtn_str . '" data-podid="' . $options['pod_id'] . '"  data-postid="' . $id . '"  data-tb="' . $savedtb_int . '"  data-itemid="' . $row_obj['id'] . '"  data-userid="' . $current_user->ID . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-target="' . $ids_str . '" >
											<span class="dashicons dashicons-trash pdt8 pdl5 pdr5 mgb0 "></span>
											<div id="panda-repeater-trash-' . $ids_str . '-loader" class="alignleft hidden mgl5">
												<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 loading alignleft"/>
											</div>
										</div>		
										<div class="button pprf-right-col center pprf-save-btn" data-podid="' . $options['pod_id'] . '"  data-postid="' . $id . '"  data-tb="' . $savedtb_int . '"  data-itemid="' . $row_obj['id'] . '"  data-userid="' . $current_user->ID . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-target="' . $ids_str . '" >
											<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/save-icon-tran.png' ) . '" class="pprf-save-icon alignleft mgl12 mgt10 mgb2"/>	
											<div id="panda-repeater-save-' . $ids_str . '-loader" class="alignleft hidden mgl5">
												<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 alignleft"/>										
											</div>
										</div>																	
										<div class="button pprf-edit pprf-row-load-iframe alignright pprf-right-col center pprf-edit-btn" data-target="' . $ids_str . '" data-url="' . $fullUrl_str . '">
											<span class="dashicons ' . $edit_str . ' pdt8 pdl8 pdr8 mgb0 pprf-edit-span"></span>
											<div id="panda-repeater-edit-' . $ids_str . '-loader" class="alignleft hidden mgl5">
												<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 alignleft"/>
											</div>	
										</div>
									</div>
									<div>
										<iframe id="panda-repeater-edit-' . $ids_str . '" name="panda-repeater-edit-' . $ids_str . '" frameborder="0" src="" scrolling="no" style="display:none;" class="panda-repeater-iframe w100">
										</iframe>
										<div id="panda-repeater-edit-expand-' . $ids_str . '" class="w100 alignleft center pdt3 pdb3 pprf-expand-bar pprf-edit-expand" data-target="' . $ids_str . '"  style="display:none;">' . esc_html__('Content missing? Click here to expand', 'panda-pods-repeater-field' ). '</div>
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
				echo '<div id="panda-repeater-fields-' . esc_attr( $savedtb_int ) . '-' . esc_attr( $options['id'] ) . '-loader" class="center hidden w100 mgb10 alignleft">';
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
				'<div class="pprf-row alignleft mgb8 ' . $hidden_str . '" id="' . $prfID_str . '-add-new">
					<div class="w100 alignleft">
						<div class="alignleft pd8 pprf-left-col pprf-grey-bg "><strong>Add New ' . esc_html( get_the_title( $options['id'] ) ) . '</strong></div>
						<div class="button pprf-right-col center pprf-trash-btn" data-target="' . esc_attr( $traBtn_str ) . '" >
						</div>									

						<div class="button pprf-right-col center pprf-save-btn pprf-save-new-btn alignright " data-podid="' . $options['pod_id'] . '"  data-postid="' . $id . '"  data-tb="' . $savedtb_int . '" data-userid="' . $current_user->ID . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-target="' . $ids_str . '" >
							<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/save-icon-tran.png' ) . '" class="pprf-save-icon alignleft mgl12 mgt10 mgb2"/>	
							<div id="panda-repeater-save-' . $ids_str . '-loader" class="alignleft hidden mgl5">
								<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 alignleft"/>										
							</div>
						</div>
						<div id="pprf-row-brief-' . $ids_str . '" class="alignright pprf-right-col button pprf-add pprf-row-load-iframe pprf-add " data-target="' . $ids_str . '" data-url="' . $fullUrl_str . '">
							<span class="dashicons dashicons-edit pdt8 pdl8 pdr8 mgb0 "></span>
							<div id="panda-repeater-add-new-' . $ids_str . '-loader" class="alignleft hidden mgl5">
								<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 alignleft"/>
							</div>	
						</div>																		
						<iframe id="panda-repeater-add-new-' . $ids_str . '" name="panda-repeater-add-new-' . $ids_str . '" frameborder="0" src="" scrolling="no" style="display:none;" class="panda-repeater-iframe w100" >
						</iframe>
						<div id="panda-repeater-add-new-expand-' . $ids_str . '" class="w100 alignleft center pd3 pprf-expand-bar pprf-add-expand" data-target="' . $ids_str . '"  style="display:none;">' . esc_html__('Content missing? Click here to expand', 'panda-pods-repeater-field' ). '</div>
					</div>
				 </div>';

				echo $addNew_str;
				echo '<div id="panda-repeater-trash-info-' . $ids_str . '" data-enable-trash="' . esc_attr( $options['pandarepeaterfield_enable_trash'] ) . '"  style="display:none;"/></div>';
				echo '<input type="hidden" name="' . esc_attr( $prfID_str ) . '-entry-limit" id="' . esc_attr( $prfID_str ) . '-entry-limit" value="' . esc_attr( $options['pandarepeaterfield_entry_limit'] ) . '">';
				echo '<input type="hidden" name="' . $name . '" value="' . $token_str . '">';
				if( is_numeric( $options['pandarepeaterfield_entry_limit'] ) && $options['pandarepeaterfield_entry_limit'] > 0 ){
					echo '<div class="alignleft w100"><small>Max ' . esc_html( get_the_title( $options['id'] ) . ' - ' . $options['pandarepeaterfield_entry_limit'] ) . '</small></div>';	
				}
				if( isset( $options['pandarepeaterfield_enable_load_more'] ) && $options['pandarepeaterfield_enable_load_more'] && !$limit_bln ){
					echo '<div class="pprf-load-more-wrap w100 alignleft"  id="pprf-load-more-wrap-' . $ids_str . '">
							<select class="alignleft pprf-select mgr5 panda-repeater-to-load" name="panda-repeater-to-load" > 
								<option value="append_to">' . __('Append to', 'panda-pods-repeater-field' ) . '</option>
								<option value="replace">' . __('Replace', 'panda-pods-repeater-field' ) . '</option>								
							</select> 							
							<label class="alignleft pdt2 mgr5" for="panda-repeater-amount"> ' . esc_html__('the list with', 'panda-pods-repeater-field' ) . '</label> 
							<input name="panda-repeater-amount" id="panda-repeater-amount-' . $ids_str . '" value="' . esc_attr( intval( $options['pandarepeaterfield_initial_amount'] ) ) . '" class="alignleft pprf-input mgr5" type="number" step="1" min="1"  autocomplete="off"/> 
							<label class="alignleft pdt2 mgr5" for="panda-repeater-start-from">' . esc_html__('new items from item', 'panda-pods-repeater-field' ) . '</label>
							<input name="panda-repeater-start-from" id="panda-repeater-start-from-' . $ids_str . '" value="' . esc_attr( intval( $options['pandarepeaterfield_initial_amount'] ) ) . '" class="alignleft pprf-input mgr5"  type="number" step="1" min="0" autocomplete="off" title="' . esc_attr__('Start from 0', 'panda-pods-repeater-field' ) . '"/>  
							<div id="panda-repeater-load-more-button-' . $ids_str . '" class="alignleft pprf-load-more-btn mgr5" data-target="' . $ids_str . '" data-podid="' . $options['pod_id'] . '"  data-postid="' . esc_attr( $id ) . '"  data-tb="' . esc_attr( $savedtb_int ) . '" data-userid="' . esc_attr( $current_user->ID ) . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-cptitle="' . esc_attr( get_the_title( $options['id'] ) ) . '" data-enable-trash="' . esc_attr( $options['pandarepeaterfield_enable_trash'] ) . '" data-order="' . esc_attr( $options['pandarepeaterfield_order'] ) . '" data-order-by="' . esc_attr( $options['pandarepeaterfield_order_by'] ) . '" />' . esc_html__('Load', 'panda-pods-repeater-field' ) . '</div>
							<label class="alignleft pdt2 mgr5">' . esc_html__(' | Total items:', 'panda-pods-repeater-field' ) . ' ' . esc_html( $count_int ) . '</label>
							<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 alignleft pprf-ajax-img mgt13 mgr5" style="display:none;"/>
							<div class="pprf-load-more-report"></div>
							
						  </div> ';	
				}	
				if( isset( $options['pandarepeaterfield_order_by'] ) && !empty( $options['pandarepeaterfield_order_by'] )  && isset( $options['pandarepeaterfield_display_order_info'] ) && $options['pandarepeaterfield_display_order_info'] ){
					echo '<div class="pprf-order-info pdt5 w100 alignleft" id="pprf-order-info-' . $ids_str . '">
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
		//echo $id . ' ' . $name;
		//print_r( $options );
		//print_r( $pod );
		exit();
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
		
		$db_cla      = new panda_pods_repeater_field_db();
		$tables_arr  = $db_cla->get_tables_fn();
			
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
}
