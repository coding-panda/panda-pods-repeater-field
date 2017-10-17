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
		$tables_arr = $this->pods_tables_fn();
		if( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) && isset( $tables_arr['pod_' . $_GET['id'] ] ) ){
			unset( $tables_arr['pod_' . $_GET['id'] ] )	;
		}
				
		$wids_arr   = array(
							'100' => '100%',	
							'50'  => '50%',							
							'25'  => '25%',													
							); 
		
		$options = array( 
           
            self::$type . '_table' => array(
                'label' 	 => __( 'Pods Table', self::$input_str ),
                'default' 	 => 0,
                'type' 		 => 'pick',
                'data' 		 => $tables_arr,
				'dependency' => true
            ),		
            self::$type . '_field_width' => array(
                'label' 	 => __( 'Field Width', self::$input_str ),
                'default' 	 => 100,
                'type' 		 => 'pick',
                'data' 		 => $wids_arr,
				'dependency' => true
            ),		
            self::$type . '_entry_limit' => array(
                'label' 	 => __( 'Entry Limit', self::$input_str ),
                'default' 	 => '0',
                'type' 		 => 'number',
                'data' 		 => '',
				'dependency' => true
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
		global $wpdb, $table_prefix, $current_user;
				
		$allow_bln = true;
		
		if( !defined( 'PANDA_PODS_REPEATER_URL' ) || !is_user_logged_in() || !current_user_can('edit_posts') ){
			// action before the iframe
			$allow_bln = false;
			
		}
		
		$allow_bln = apply_filters( 'pprf_load_panda_repeater_allow', $allow_bln, $_GET );
		if( !$allow_bln ){
			echo apply_filters( 'pprf_load_panda_repeater_allow_msg', __('You do not have permission to edit this item.', 'panda-pods-repeater' ) ) ;
		} else {		
		
			$db_cla 	     = new panda_pods_repeater_field_db();
		
			$options 		 = (array) $options;
			
			$form_field_type = PodsForm::$field_type;
			//echo $name . ' ' . $id ;
			
			if ( is_array( $value ) )
				$value = implode( ' ', $value );
			
			$savedtb_str = trim( $options[ self::$typeTb_str ] );
			
			$savedtb_int = substr( $savedtb_str, 4 );
			
			$tb_str 	 = '';
			if( is_numeric( $savedtb_int ) ){			
				$post_arr	 = get_post( $savedtb_int, ARRAY_A );
				
				if( is_array( $post_arr ) && $post_arr['post_type'] == '_pods_pod' ){
					$tb_str 	 = $post_arr['post_name'];

				}
			} else {
				return;	
			}		
			
			if( !is_numeric( $id ) ){
				echo apply_filters( 'pprf_load_panda_repeater_allow_msg', __('Please save the parent first to add ' . strtolower( $post_arr['post_title'] ) . '. ', 'panda-pods-repeater' ) ) ;
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
				if( isset( $options['pandarepeaterfield_entry_limit'] ) && is_numeric( $options['pandarepeaterfield_entry_limit'] ) &&  $options['pandarepeaterfield_entry_limit'] != 0 ){
					$limit_str	=	'LIMIT 0, ' . intval( $options['pandarepeaterfield_entry_limit'] );	
				}
				// if it is a wordpress post type, join wp_posts table
				$join_str  = '';
				//print_r (self::$tbs_arr['pod_' . $savedtb_int ]);
				if( self::$tbs_arr['pod_' . $savedtb_int ]['type'] == 'post_type' ){
					$join_str = 'INNER JOIN  `' . $table_prefix . 'posts` AS post_tb ON post_tb.ID = main_tb.id';
				}
				if( count( $search_arr ) > 0 ) {
					
					$query_str  	= $wpdb->prepare( 'SELECT main_tb.`id` AS id, `' . self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] . '` 
													   FROM `' . $table_prefix . 'pods_' . $tb_str . '` AS main_tb
													   ' . $join_str  . '
													   WHERE ' . $where_str . ' 
													   ORDER BY CAST( `pandarf_order` AS UNSIGNED ) ASC 
													   ' . $limit_str . '; ' , 
													   $search_arr );	
				} else {
					$query_str  	= 'SELECT main_tb.`id` AS id, `' . self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] . '` 
									   FROM `' . $table_prefix . 'pods_' . $tb_str . '` 
									   ' . $join_str  . ' 
									   WHERE ' . $where_str . ' 
									   ORDER BY CAST( `pandarf_order` AS UNSIGNED ) ASC
									   ' . $limit_str . '; '; 
				}
				//echo $query_str;
				$rows_arr   	= $wpdb->get_results( $query_str, ARRAY_A );	
				
				//parent iframe $options['id'] pods field id
				
				$pIframeID_str = '';
				if( isset( $_GET ) && isset( $_GET['iframe_id'] ) ){
					$pIframeID_str = $_GET['iframe_id'];
				}
				$query_str = '&podid=' . $options['pod_id'] . '&tb=' . $savedtb_int . '&poditemid=' . $options['id'];
				$prfID_str = 'panda-repeater-fields-' . $savedtb_int . '-' . $options['id'];
				
				echo '<div id="' . $prfID_str . '" class="pprf-redorder-list-wrap">';
				
				//echo 	'<div role="button" class="alignright button pprf-redorder-btn mgb8" data-id="' . $prfID_str . '">Re-order</div>';
				//echo 	'<div role="button" class="alignright button pprf-save-redorder-btn hidden mgb8" data-id="' . $prfID_str . '">Back</div>';
				//echo 	'<div class="pprf-redorder-list-wrap hidden mgb8">';
				//echo 		'<p>You will have to refresh the page to see the effect after reordering.</p>';
				//echo 		'<ul class="pprf-redorder-list">';
			/*	if ( is_array( $rows_arr ) ) {
					$order_int = 1;
					
					foreach( $rows_arr as $row_obj ) { 	
						$ids_str   = $row_obj['id'] . '-' . $options['id'];
						$title_str   = apply_filters( 'pprf_item_title', $row_obj[ self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] ], $savedtb_int, $row_obj['id'], $id, $options['id'] );
						$title_str	 = esc_attr( $title_str );
						echo '<li data-id="' . $row_obj['id'] . '" class="" id="li-' . $savedtb_int . '-' . $ids_str . '">' . $title_str . '</li>';
						$order_int ++;
					}
				}*/
				//echo		'</ul>';
				//echo	'</div>';
				// remove anything after /wp-admin/, otherwise, it will load a missing page				
				$adminUrl_str =  substr( admin_url(), 0, strrpos( admin_url(), '/wp-admin/' ) + 10 );
				
				$src_str 	  = $adminUrl_str . '?page=panda-pods-repeater-field&';
				//$src_str   = PANDA_PODS_REPEATER_URL . 'fields/pandarepeaterfield.php?';
				$bg_str		  = 'pprf-purple-bg';
				//echo 	'<div class="pprf-redorder-list-wrap">';
				echo 		'<ul class="pprf-redorder-list">';
				if ( is_array( $rows_arr ) ) {
					foreach( $rows_arr as $i => $row_obj ) { 	
						$bg_str 	 = $i % 2 == 0 ? 'pprf-purple-bg' : 'pprf-white-bg';
						
						//print_r( self::$tbs_arr['pod_' . $savedtb_int ]  );
						$ids_str     = $savedtb_int . '-' . $row_obj['id'] . '-' . $options['id'];
						$fullUrl_str = $src_str . 'piframe_id=' . $pIframeID_str . '&iframe_id=panda-repeater-edit-' . $ids_str . '' . $query_str . '&postid=' . $id . '&itemid=' . $row_obj['id'];	
						
						$title_str   = apply_filters( 'pprf_item_title', $row_obj[ self::$tbs_arr['pod_' . $savedtb_int ]['name_field'] ], $savedtb_int, $row_obj['id'], $id, $options['id'] );
						$title_str	 = esc_attr( $title_str );
						echo '<li data-id="' . $row_obj['id'] . '" class="" id="li-' . $ids_str . '">';						
						echo 	'<div class="row pprf-row alignleft ">
									<div class="w100 alignleft" id="pprf-row-brief-' . $ids_str . '">
										<div class="alignleft pd8 pprf-left-col ' . $bg_str . '"><strong>' . get_the_title( $options['id'] ) . ' ID:</strong> ' . $row_obj['id'] . ' - ' . $title_str . '</div>
										<div class="button pprf-right-col center pprf-trash-btn" data-podid="' . $options['pod_id'] . '"  data-postid="' . $id . '"  data-tb="' . $savedtb_int . '"  data-itemid="' . $row_obj['id'] . '"  data-userid="' . $current_user->ID . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-target="' . $ids_str . '" >
											<span class="dashicons dashicons-trash pdt5 pdl5 pdr5 mgb0 "></span>
											<div id="panda-repeater-trash-' . $ids_str . '-loader" class="alignleft hidden mgl5">
												<img src = "' . PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif" alt="loading" class="mgl8 loading alignleft"/>
											</div>
										</div>		
										<div class="button pprf-right-col center pprf-save-btn" data-podid="' . $options['pod_id'] . '"  data-postid="' . $id . '"  data-tb="' . $savedtb_int . '"  data-itemid="' . $row_obj['id'] . '"  data-userid="' . $current_user->ID . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-target="' . $ids_str . '" >
											<img src = "' . PANDA_PODS_REPEATER_URL . 'images/save-icon-tran.png" class="pprf-save-icon alignleft mgl12 mgt7 mgb2"/>	
											<div id="panda-repeater-save-' . $ids_str . '-loader" class="alignleft hidden mgl5">
												<img src = "' . PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif" alt="loading" class="mgl8 alignleft"/>										
											</div>
										</div>																	
										<div class="button pprf-edit pprf-row-load-iframe alignright pprf-right-col center pprf-edit-btn" data-target="' . $ids_str . '" data-url="' . $fullUrl_str . '">
											<span class="dashicons dashicons-edit pdt8 pdl8 pdr8 mgb0 pprf-edit-span"></span>
											<div id="panda-repeater-edit-' . $ids_str . '-loader" class="alignleft hidden mgl5">
												<img src = "' . PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif" alt="loading" class="mgl8 alignleft"/>
											</div>	
										</div>
									</div>
									<div>
										<iframe id="panda-repeater-edit-' . $ids_str . '" name="panda-repeater-edit-' . $ids_str . '" frameborder="0" src="" scrolling="no" style="display:none;" class="panda-repeater-iframe w100">
										</iframe>
										<div id="panda-repeater-edit-expand-' . $ids_str . '" class="w100 alignleft center pdt3 pdb3 pprf-expand-bar pprf-edit-expand" data-target="' . $ids_str . '"  style="display:none;">' . __('Content missing? Click here to expand', 'panda-pods-repeater'). '</div>
									</div>
								 </div>';
						 echo '</li>';
					}
				}
				echo		'</ul>';
				//echo '</div>';
				$bg_str 	 = $bg_str == 'pprf-white-bg' ? 'pprf-purple-bg' : 'pprf-white-bg';
				echo '</div>';
				echo '<div id="next-bg" data-bg="' . $bg_str . '"></div>';	
				echo '<div id="panda-repeater-fields-' . $savedtb_int . '-' . $options['id'] . '-loader" class="center hidden w100 mgb10">';
				echo 	'<img src = "' . PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif" alt="loading" class=""/>';
				echo '</div>';
				// depreciated, don't show if the parent post is not created
				if( is_numeric( $id ) ){
					$token_str = $id;
				} else {
					// create a token if adding a new parent item, which will be used to identify which child item to update after saving the parent item
					$token_str = time() . '_' .  $savedtb_int . '_' .  $options['id'] . '_' .  $current_user->ID . '_pandarf' ;		
				}
				$ids_str     	= $savedtb_int . '-' . $options['id']; // one less id compared to the added ones
				$fullUrl_str 	= $src_str . 'piframe_id=' . $pIframeID_str . '&iframe_id=panda-repeater-add-new-' . $ids_str . '' . $query_str . '&postid=' . $token_str;
				$hidden_str		= '';
				if( $limit_str != '' && count( $rows_arr ) == $options['pandarepeaterfield_entry_limit'] ){
					$hidden_str	=	'hidden';	
				}				
				$addNew_str		= 
				'<div class="row pprf-row alignleft mgb8 ' . $hidden_str . '" id="' . $prfID_str . '-add-new">
					<div class="w100 alignleft">
						<div class="alignleft pd8 pprf-left-col pprf-grey-bg "><strong>Add New ' . get_the_title( $options['id'] ) . '</strong></div>
						<div class="button pprf-right-col center pprf-trash-btn" >
						</div>									

						<div class="button pprf-right-col center pprf-save-btn pprf-save-new-btn alignright " data-podid="' . $options['pod_id'] . '"  data-postid="' . $id . '"  data-tb="' . $savedtb_int . '" data-userid="' . $current_user->ID . '"  data-iframe_id="panda-repeater-edit-' . $ids_str . '"  data-poditemid="' . $options['id'] . '" data-target="' . $ids_str . '" >
							<img src = "' . PANDA_PODS_REPEATER_URL . 'images/save-icon-tran.png" class="pprf-save-icon alignleft mgl12 mgt7 mgb2"/>	
							<div id="panda-repeater-save-' . $ids_str . '-loader" class="alignleft hidden mgl5">
								<img src = "' . PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif" alt="loading" class="mgl8 alignleft"/>										
							</div>
						</div>
						<div id="pprf-row-brief-' . $ids_str . '" class="alignright pprf-right-col button pprf-add pprf-row-load-iframe pprf-add " data-target="' . $ids_str . '" data-url="' . $fullUrl_str . '">
							<span class="dashicons dashicons-edit pdt8 pdl8 pdr8 mgb0 "></span>
							<div id="panda-repeater-add-new-' . $ids_str . '-loader" class="alignleft hidden mgl5">
								<img src = "' . PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif" alt="loading" class="mgl8 alignleft"/>
							</div>	
						</div>																		
						<iframe id="panda-repeater-add-new-' . $ids_str . '" name="panda-repeater-add-new-' . $ids_str . '" frameborder="0" src="" scrolling="no" style="display:none;" class="panda-repeater-iframe w100" >
						</iframe>
						<div id="panda-repeater-add-new-expand-' . $ids_str . '" class="w100 alignleft center pd3 pprf-expand-bar pprf-add-expand" data-target="' . $ids_str . '"  style="display:none;">' . __('Content missing? Click here to expand', 'panda-pods-repeater'). '</div>
					</div>
				 </div>';

				echo $addNew_str;
				echo '<input type="hidden" name="' . $prfID_str . '-entry-limit" id="' . $prfID_str . '-entry-limit" value="' . esc_attr( $options['pandarepeaterfield_entry_limit'] ) . '">';
				echo '<input type="hidden" name="' . $name . '" value="' . $token_str . '">';
				if( is_numeric( $options['pandarepeaterfield_entry_limit'] ) && $options['pandarepeaterfield_entry_limit'] > 0 ){
					echo '<div class=""><small>Max ' . get_the_title( $options['id'] ) . ' - ' . esc_attr( $options['pandarepeaterfield_entry_limit'] ) . '</small></div>';	
				}
			} else {
				echo __( 'No Advanced Content Type Table Selected', self::$input_str );
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
		echo $id . ' ' . $name;
		print_r( $options );
		print_r( $pod );
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
		
		global $wpdb, $table_prefix, $current_user;

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
					$query_str  	= $wpdb->prepare( 'SELECT * FROM `' . $table_prefix . 'posts' . '`  WHERE `ID` = %d LIMIT 0, 1; ' , array( $query_arr['tb'] ) );	
					$childTb_arr   	= $wpdb->get_results( $query_str, ARRAY_A );		
					$pdTb_str		= $childTb_arr[0]['post_name'];				
					$table_str 	 	= $table_prefix . 'pods_' . $pdTb_str;		
					
	  
					// fetch the child item data
					/*if( strpos( $query_arr['iframe_id'], 'panda-repeater-edit' ) === 0 && isset( $query_arr['itemid'] ) ){
						$cItemID_int = $query_arr['itemid']; 	
					} else {
							
					}*/
					$query_str  	= $wpdb->prepare( 'SELECT * FROM `' . $table_str . '`  WHERE `id` = %d LIMIT 0, 1; ' , array( $id_int ) );	
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
						if( count( $values_arr ) > 0 ){
							$query_str  	= $wpdb->prepare( 'UPDATE  `' . $table_str . '` SET ' . $update_str . ' WHERE id = "' . $id_int . '";' , $values_arr );
						} else {
							$query_str  	= 'UPDATE  `' . $table_str . '` SET ' . $update_str . ' WHERE id = "' . $id_int . '";';
						}
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
								$query_str  	= $wpdb->prepare( 'SELECT `post_name` FROM `' . $table_prefix . 'posts` WHERE ID = %d LIMIT 0, 1;' , array( $childPodID_int ) );	
					
								$item_arr  	= $wpdb->get_results( $query_str, ARRAY_A );	
		
								if( isset( $item_arr[0] ) ){
									// search the token in the child table and update
									$query_str  	= $wpdb->prepare( 'UPDATE `' . $table_prefix . 'pods_' . $item_arr[0]['post_name'] . '` SET `pandarf_parent_post_id` = %d WHERE `pandarf_parent_post_id` = "%s" ' , array( $id_int, $v_str ) );	
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
		
		global $wpdb, $table_prefix, $current_user;
						
		$item_obj 	 = apply_filters( 'pprf_filter_pods_post_delete_fn', $item_obj, $pods_arr, $podsAPI_obj );	
			
		do_action( 'pprf_action_pods_post_delete_fn', $item_obj, $pods_arr, $podsAPI_obj );
		
		return $item_obj;
	}
	/**
	 * field_table_fields_fn: if a table is set as a field, check and update the table's fields
	 */		
	public function field_table_fields_fn( $pod_arr, $obj ) {
		
		foreach( $pod_arr['fields'] as $field_arr ){
			if( $field_arr['type'] == self::$type ){ 				
				
				$podTbs_arr = $this->pods_tables_fn() ;
				
				// example $podTbs_arr[ $field_arr['pandarepeaterfield_table'] ] ->  $podTbs_arr['pod_16']
				if( isset( $field_arr['pandarepeaterfield_table'] ) && isset( $podTbs_arr[ $field_arr['pandarepeaterfield_table'] ] ) ){
					$db_cla      = new panda_pods_repeater_field_db();
					$tables_arr  = $db_cla->update_columns_fn( $podTbs_arr[ $field_arr['pandarepeaterfield_table'] ] );	
				}
			}
			
		}
		
		//echo '---------------------';
		//print_r( $obj  );
		// check if the post type is panda repeater field
		/*$type_str = get_post_meta( $postID_int, 'type', true );
		print_r( $type_str );

		
		if( $type_str == self::$type ){
			// find out the value of the field
			$field_str   = get_post_meta( $postID_int, self::$typeTb_str, true );
			$field_arr   = explode( '_', $field_str );
			// get the table name						
			$post_arr 	 = get_post( $field_arr[ 1 ], ARRAY_A );
			$tb_str		 = $post_arr['post_name'];

			$db_cla      = new panda_pods_repeater_field_db();
			$tables_arr  = $db_cla->update_columns_fn( $tb_str );			

		} else {
			// if the field is no longer a Pods Table As Repeater Field, delete its panda repeater field postmeta
			//delete_post_meta( $postID_int, self::$typeTb_str );			
		}*/
	}

	/**
	 * save tables
	 */
	 function pods_tables_fn(){
		 
		global $wpdb, $table_prefix, $current_user;
		
		$db_cla      = new panda_pods_repeater_field_db();
		$tables_arr  = $db_cla->get_tables_fn();
		
		$podsTbs_arr = array();
		if( is_array( $tables_arr ) ){
			foreach( $tables_arr as $tb_str => $tbv_arr ){

				if( $tbv_arr['type'] != 'wp' ){
					//$tb_str 				= substr( $tb_str, 5 );
					$podsTbs_arr[ $tb_str ] = $tbv_arr['pod'];						
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
		global $wpdb, $table_prefix, $current_user;
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
						$query_str  	= $wpdb->prepare( 'SELECT `post_name` FROM `' . $table_prefix . 'posts` WHERE ID = %d LIMIT 0, 1;' , array( $childPodID_int ) );	
			
						$item_arr  	= $wpdb->get_results( $query_str, ARRAY_A );	

						if( isset( $item_arr[0] ) ){
							// search the token in the child table and update
							$query_str  	= $wpdb->prepare( 'UPDATE `' . $table_prefix . 'pods_' . $item_arr[0]['post_name'] . '` SET `pandarf_parent_post_id` = %d WHERE `pandarf_parent_post_id` = "%s" ' , array( $post_obj->ID, $v_str ) );	
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
