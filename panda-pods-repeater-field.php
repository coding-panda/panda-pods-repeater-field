<?php
/*
Plugin Name: Panda Pods Repeater Field
Plugin URI: https://wordpress.org/plugins/panda-pods-repeater-field/
Description: Panda Pods Repeater Field is a plugin for Pods Framework. The beauty of it is that it is not just a repeater field. It is a quick way to set up a relational database and present the data on the same page. It takes the advantage of Pods table storage, so you don’t need to worry that the posts and postmeta data table may expand dramatically and slow down the page loading. This plugin is compatible with Pods Framework 2.6.1 or later. To download Pods Framework, please visit http://pods.io/. After each update, please clear the cache to make sure the CSS and JS are updated. Usually, Ctrl + F5 will do the trick.
Version: 1.4.10
Author: Dongjie Xu
Author URI: http://www.multimediapanda.co.uk/
Text Domain: panda-pods-repeater-field
Domain Path: /languages
*/


// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Define constants
 *
 * @since 1.0.0
 */
define( 'PANDA_PODS_REPEATER_SLUG', plugin_basename( __FILE__ ) );
define( 'PANDA_PODS_REPEATER_URL', plugin_dir_url( __FILE__ ) );
define( 'PANDA_PODS_REPEATER_DIR', plugin_dir_path( __FILE__ ) );
define( 'PANDA_PODS_REPEATER_VERSION', '1.4.10' );

 
 
/**
 * Panda_Pods_Repeater_Field class
 *
 * @class Panda_Pods_Repeater_Field The class that holds the entire Panda_Pods_Repeater_Field plugin
 *
 * @since 1.0.0
 */
class Panda_Pods_Repeater_Field {

	var $menuTitle_str 		= 'Panda Pods Repeater Field';
	//public $can_elementor	= false;
	const type_str	   		= 'pandarepeaterfield';
	/**
	 * Constructor for the Panda_Pods_Repeater_Field class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {		
		// Return false if Pods Framework is not available
		if( ! class_exists('PodsField' ) ){
			return false;
		}		
		$files   = array(
						'panda_pods_repeater_field_db',  
						'podsfield_pandarepeaterfield', 
						'panda_pods_repeater_field_ajax',
						
						);
	
		$active_plugins = get_option('active_plugins');
	
		// if( in_array('elementor/elementor.php', $active_plugins ) && ! wp_doing_ajax() ){ 
		//  	$this->can_elementor = true;
		//  	array_push( $files_arr, 'pprf_elementor_accordion_widget' );
		// }

		$class_bln   = true;
		
		for( $i = 0; $i < count( $files ); $i ++ ){
			$file_str = dirname(__FILE__) . '/classes/' . $files[ $i ] . '.php';			
			
			if( file_exists( $file_str ) ) {
				$claName_str = str_replace( ' ', '_', ucwords( strtolower( str_replace( '_', ' ', $files[ $i ] ) ) ) ) ;			
				include_once $file_str;		
				
				if( !class_exists( $claName_str ) )	{
					$class_bln = false;
				}
			} else {
				$class_bln = false;
			}
		}
		//print_r( PodsForm::field_types() );
		if( $class_bln ) {	
			// create an instance to store pods adavance custom tables
			$panda_repeater_field   = new podsfield_pandarepeaterfield();	
			// ajax
			$repeater_field_ajax 	= new Panda_Pods_Repeater_Field_Ajax();
			//add_action('admin_menu',  array( $ssefProfile_cla, 'add_admin_menu_fn' ), 15);	
							
			foreach( PodsField_Pandarepeaterfield::$actTbs_arr as $tb_str => $tbn_str ){
				// after pod saved
				add_action('pods_api_post_save_pod_item_' . $tbn_str , array( $panda_repeater_field, 'pods_post_save_fn' ), 10, 3);
				add_action('pods_api_post_delete_pod_item_' . $tbn_str , array( $panda_repeater_field, 'pods_post_delete_fn' ), 10, 3);
			}
			add_action( 'pods_admin_ui_setup_edit_fields', array( $panda_repeater_field, 'field_table_fields_fn' ), 10, 2 );
			// check table fields when update pod editor
			//add_action( 'save_post', array( $panda_repeater_field, 'update_child_pod_fn' ), 10, 3 );			
				
		}
		//$this->instances();
		/**
		 * Plugin Setup
		 */
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Localize our plugin, doesn't work
		//add_action( 'init', array( $this, 'localization_setup' ) );

		/**
		 * Scripts/ Styles
		 */
		// Loads frontend scripts and styles
		//

		// Loads admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		

		/**
		 * Hooks that extend Pods
		 *
		 * NOTE: These are some example hooks that are useful for extending Pods, uncomment as needed.
		 */

		//Example: Add a tab to the pods editor for a CPT Pod called 'jedi
		//add_filter( 'pods_admin_setup_edit_tabs_post_type_jedi', array( $this, 'jedi_tabs' ), 11, 3 );

		//Example: Add fields to the Pods editor for all Advanced Content Types
		//add_filter( 'pods_admin_setup_edit_options_advanced', array( $this, 'act_options' ), 11, 2 );
		//add_filter( 'pods_admin_setup_edit_options_advanced', array( $this, 'act_options' ), 11, 2 );	
		//
		//Example: Add a submenu item to Pods Admin Menu
		add_filter( 'pods_admin_menu', array( $this, 'add_menu' ) );

		/**
		//Complete Example: Add a tab for all post types and some options inside of it.
		//See example callbacks below
		add_filter( 'pods_admin_setup_edit_tabs_post_type', array( $this, 'pt_tab' ), 11, 3 );
		add_filter( 'pods_admin_setup_edit_options_post_type', array( $this, 'pt_options' ), 12, 2 );
		*/
		//add_screen_option( 'per_page',  array( 'default' => 0,
			//'option' => 'pprf-auto-load' , 'label' => _x( 'Panda Pods Repeater Field auto load', 'panda-pods-repeater-fields' )) );
		//	add filter to migrate package

		// Elementor widget
		// if( $this->can_elementor ){ 
  //  			add_action( 'elementor/widgets/widgets_registered',  array( $this, 'register_widgets' ) );		
		// }
						
					
	}

	/**
	 * Initializes the Panda_Pods_Repeater_Field() class
	 *
	 * Checks for an existing Panda_Pods_Repeater_Field() instance
	 * and if it doesn't find one, creates it.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		static $prf_cla = false;
		
		if ( ! $prf_cla ) {
			$prf_cla 	 = new Panda_Pods_Repeater_Field();

			// add to pod editor
			//add_filter( 'pods_api_field_types', array( $prf_cla, 'filter_pods_api_field_types') );
			//add_filter( 'pods_form_field_include', array( $prf_cla, 'filter_pods_form_field_include'), 10, 2 );		
			
			//add_filter( 'pods_form_ui_field_' . PodsField_Pandarepeaterfield::$type, array( $prf_cla, 'filter_pods_form_ui_field_panda_repeater' ), 10, 6 );

			
		}


		return $prf_cla;
		
	}

	/**
	 * Placeholder for activation function
	 *
	 * @since 1.0.0
	 */
	public function activate() {

	}

	/**
	 * Placeholder for deactivation function
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {

	}

	/**
	 * Initialize plugin for localization
	 *
	 * @since 1.0.0
	 */
	public function localization_setup() {
		//load_plugin_textdomain( 'panda-pods-repeater-field', false, basename( dirname( __FILE__ ) ) . '/languages' );
		
	}



	/**
	 * Enqueue admin scripts
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {
		global $pprf_l10n;
		/**
		 * All admin styles goes here
		 */
		wp_register_style(  'panda-pods-repeater-general-styles', plugins_url( 'css/general.min.css', __FILE__ ) );
		wp_enqueue_style( 'panda-pods-repeater-general-styles' );		
		wp_register_style(  'panda-pods-repeater-admin-styles', plugins_url( 'css/admin.min.css', __FILE__ ), array( 'panda-pods-repeater-general-styles') );
		wp_enqueue_style( 'panda-pods-repeater-admin-styles' );

		/**
		 * All admin scripts goes here
		 */
		if( strpos( $_SERVER['REQUEST_URI'], 'wp-admin') && isset( $_GET ) && isset( $_GET['page'] ) && $_GET['page'] == 'panda-pods-repeater-field' ){ 
			wp_register_style('pprf_fields', plugins_url( 'fields/css/pprf.min.css', __FILE__ ), array( 'panda-pods-repeater-general-styles', 'panda-pods-repeater-admin-styles') );
			wp_enqueue_style('pprf_fields');		 			

		}
		
		//wp_enqueue_script( 'panda-pods-repeater-resize-iframe', plugins_url( 'js/resize-iframe/iframeResizer.min.js', __FILE__ ), array( 'jquery' ), false, true );
		wp_register_script(  'panda-pods-repeater-admin-scripts', plugins_url( 'js/admin.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable' ), false, true  );
		wp_enqueue_script( 'panda-pods-repeater-admin-scripts' );
		// prepare ajax
		wp_localize_script( 
			'panda-pods-repeater-admin-scripts', 
			'ajax_script', 
			array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			 	'nonce' 	=> wp_create_nonce( 'panda-pods-repeater-field-nonce' ),
			)
		);		

		wp_localize_script( 
			'panda-pods-repeater-admin-scripts', 
			'strs_obj', 
			$pprf_l10n
		);			
		$admin_url =  substr( admin_url(), 0, strrpos( admin_url(), '/wp-admin/' ) + 10 );
		wp_localize_script( 
			'panda-pods-repeater-admin-scripts', 
			'PANDA_PODS_REPEATER_PAGE_URL', 
			array( $admin_url . '?page=panda-pods-repeater-field&' )
		);		
		wp_localize_script( 
			'panda-pods-repeater-admin-scripts', 
			'PANDA_PODS_REPEATER_URL', 
			array( PANDA_PODS_REPEATER_URL )
		);			
	/*	wp_localize_script( 
			'panda-pods-repeater-admin-scripts', 
			'$_GETS', 
			$_GET
		);	*/		
		//PANDA_PODS_REPEATER_URL
	}

	/**
	 * Adds an admin tab to Pods editor for all post types
	 *
	 * @param array $tabs The admin tabs
	 * @param object $pod Current Pods Object
	 * @param $addtl_args
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	function pt_tab( $tabs, $pod, $addtl_args ) {
		$tabs[ 'panda-pods-repeater' ] = __( 'Panda Repeater Options', 'panda-pods-repeater-field' );
		
		return $tabs;
		
	}

	/**
	 * Adds options to Pods editor for post types
	 *
	 * @param array $options All the options
	 * @param object $pod Current Pods object.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	function pt_options( $options, $pod  ) {

		$options[ 'panda-pods-repeater' ] = array(
			'example_boolean' => array(
				'label' => __( 'Enable something?', 'panda-pods-repeater-field' ),
				'help' => __( 'Helpful info about this option that will appear in its help bubble', 'panda-pods-repeater-field' ),
				'type' => 'boolean',
				'default' => true,
				'boolean_yes_label' => 'Yes'
			),
			'example_text' => array(
				'label' => __( 'Enter some text', 'panda-pods-repeater-field' ),
				'help' => __( 'Helpful info about this option that will appear in its help bubble', 'panda-pods-repeater-field' ),
				'type' => 'text',
				'default' => 'Default text',
			),
			'dependency_example' => array(
				'label' => __( 'Dependency Example', 'panda-pods-repeater-field' ),
				'help' => __( 'When set to true, this field reveals the field "dependent_example".', 'pods' ),
				'type' => 'boolean',
				'default' => false,
				'dependency' => true,
				'boolean_yes_label' => ''
			),
				'dependent_example' => array(
				'label' => __( 'Dependent Option', 'panda-pods-repeater-field' ),
				'help' => __( 'This field is hidden unless the field "dependency_example" is set to true.', 'pods' ),
				'type' => 'text',
				'depends-on' => array( 'dependency_example' => true )
			)

		);
		
		return $options;
		
	}

	/**
	 * Adds a sub menu page to the Pods admin
	 *
	 * @param array $admin_menus The submenu items in Pods Admin menu.
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	function add_menu( $admin_menus ) {
		$admin_menus[ 'panda_repeater'] = array(
			'label' => __( 'Panda Repeater', 'panda-pods-repeater-field' ),
			'function' => array( $this, 'menu_page' ),
			'access' => 'manage_options'

		);
		
		return $admin_menus;
		
	}

	/**
	 * This is the callback for the menu page. Be sure to create some actual functionality!
	 *
	 * @since 1.0.0
	 */
	function menu_page() {
		echo '<h3>' . __( 'Panda Repeater', 'panda-pods-repeater-field' ) . '</h3>';

	}
	/**
	 * not needed, now use pods_register_field_type
	 */
	function filter_pods_api_field_types( $field_types  ){
	//	print_r( $field_types  );
		if( !in_array( 'pandarepeaterfield', $field_types ) ){
			array_push( $field_types, 'pandarepeaterfield' );
			
		}
		return $field_types ;
	}
	/**
	 * not needed, now use pods_register_field_type
	 */
	function filter_pods_form_field_include( $pods_dir, $field_type ){
		//echo $pods_dir . ' ' . $field_type . '<br/>';
		if( 'pandarepeaterfield' == $field_type ){
			$pods_dir = dirname(__FILE__) . '/classes/podsfield_pandarepeaterfield.php';
		}
		 //$pods_dir = dirname(__FILE__) . '/classes/pods_repeater_table_as_field.php';	
		 return $pods_dir; 
	}

	function filter_pods_form_ui_field_panda_repeater( $output, $name, $value, $options, $pod, $id ){
		//print_r( $output );
		 return $output; 	
	}
	/**
	 * @deprecated
	 */ 
	private function instances(){
		global $wpdb, $current_user;
		
		$query = $wpdb->prepare( 'SELECT COUNT(`post_id`) AS count FROM `' . $wpdb->postmeta . '`  WHERE `meta_key` LIKE "type" AND  `meta_value` LIKE  "%s";', array( self::type_str ) );		
		
		$items = $wpdb->get_results( $query, ARRAY_A );
		
		return md5( $items[0]['count'] ) ;
		
	}
	/**
	 * register widgets
	 */ 
	// public function register_widgets() {
	// 	\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\PPRF_Elementor_Accordion_Widget() );
		
	// }
} // Panda_Pods_Repeater_Field

/**
 * Initialize class, if Pods is active.
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'panda_repeater_safe_activate');
function panda_repeater_safe_activate() {

	//echo PODS_VERSION;
	if ( defined( 'PANDA_PODS_REPEATER_VERSION' ) ) {
		//$GLOBALS[ 'Panda_Pods_Repeater_Field' ] = Panda_Pods_Repeater_Field::init();
		
	}
	if( function_exists( 'pods_register_field_type' ) ){
		pods_register_field_type( 'pandarepeaterfield', PANDA_PODS_REPEATER_DIR . 'classes/podsfield_pandarepeaterfield.php' );
	}
	  //plugin is activated
	  
	add_action( 'admin_menu',  'pprf_add_admin_menu_fn' );	
}

/**
 * Adds a menu page to the WP admin, then hide it for the iframe
 *
 * @return mixed
 *
 * @since 1.0.0
 */
function pprf_add_admin_menu_fn(  ) {

	$page = add_menu_page( __('Panda Pods Repeater Field', 'panda-pods-repeater-field' ), 'Panda Pods Repeater Field', 'edit_posts', 'panda-pods-repeater-field', 'pprf_main_page_fn'  );		

	//add_action('load-' . $page_str, 'pprf_load_fn' );		

}

function pprf_main_page_fn(){
	/* don't need Emoji and smiley js */
	//remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	//remove_action( 'wp_print_styles', 'print_emoji_styles' );		
	//add_action('admin_menu', 'pprf_remove_admin_menu_items_fn');

	include_once( PANDA_PODS_REPEATER_DIR . 'fields/pandarepeaterfield.php');
	//die( );
}

/**
 * Remove Unwanted Admin Menu Items 
 * @link https://managewp.com/wordpress-admin-sidebar-remove-unwanted-items
 **/
function pprf_remove_admin_menu_items_fn() {
	$remove_menu_items = array(__('Links'));
	global $menu;
	end ($menu);
	while (prev($menu)){
		$item = explode(' ',$menu[key($menu)][0]);
		if( in_array($item[0] != NULL?$item[0]:"" , $remove_menu_items)){
		unset($menu[key($menu)]);}
	}
}


/*
*  load_fn
*
*  @description: 
*  @since 3.5.2
*  @created: 27/05/16
*/

function pprf_load_fn(){

	// include export action
	//if( isset( $_GET ) && isset( $_GET['csv'] ) ){
		include_once( PANDA_PODS_REPEATER_DIR . 'fields/pandarepeaterfield.php');
		die( );
	//}		
}
/**
 * Initialize class, if Pods is active.
 *
 * @since 1.0.0
 */
if( is_admin() ){ 
    add_action( 'admin_init', 'check_some_other_plugin', 20 );
} else {
    add_action( 'init', 'check_some_other_plugin', 20 );
}

function check_some_other_plugin() {
	
  //if ( is_plugin_active( 'pods/init.php' ) ) {
	if ( defined( 'PODS_VERSION' ) ) {
		$GLOBALS[ 'Panda_Pods_Repeater_Field' ] = Panda_Pods_Repeater_Field::init();
		 
	}
  //}
 
}


/**
 * Throw admin nag if Pods isn't activated.
 *
 * Will only show on the plugins page.
 *
 * @since 1.0.0
 */
add_action( 'admin_notices', 'panda_repeater_admin_notice_pods_not_active' );
function panda_repeater_admin_notice_pods_not_active() {

	if ( ! defined( 'PODS_VERSION' ) ) {

		//use the global pagenow so we can tell if we are on plugins admin page
		global $pagenow;
		if ( $pagenow == 'plugins.php' ) {
			?>
			<div class="error">
				<p><?php esc_html_e( 'You have activated Panda Pods Repeater Field. Pods Framework plugin required.', 'panda-pods-repeater-field' ); ?></p>
			</div>
		<?php

		} //endif on the right page
	} //endif Pods is not active

}

/**
 * Throw admin nag if Pods minimum version is not met
 *
 * Will only show on the Pods admin page
 *
 * @since 1.0.0
 */
add_action( 'admin_notices', 'panda_repeater_admin_notice_pods_min_version_fail' );
function panda_repeater_admin_notice_pods_min_version_fail() {

	if ( defined( 'PODS_VERSION' ) ) {

		//set minimum supported version of Pods.
		$minimum_version = '2.3.18';

		//check if Pods version is greater than or equal to minimum supported version for this plugin
		if ( version_compare(  $minimum_version, PODS_VERSION ) > 0) {

			//create $page variable to check if we are on pods admin page
			$page = pods_v('page','get', false, true );

			//check if we are on Pods Admin page
			if ( $page === 'pods' ) {
				?>
				<div class="updated">
					<p><?php esc_html_e( 'Panda Pods Repeater Field requires Pods version 2.3.18 or later.', 'panda-pods-repeater-field' ); ?></p>
				</div>
			<?php

			} //endif on the right page
		} //endif version compare
	} //endif Pods is not active


}

add_action( 'wp_loaded', 'pprf_translate' );

function pprf_translate(){
	// translation 
	$strings = array(
		'be_restored' 		=> esc_html__( 'It will be restored.', 'panda-pods-repeater-field' ),
		'can_recover' 		=> esc_html__( 'You can recover it from trash.', 'panda-pods-repeater-field' ),
		'be_deleted' 		=> esc_html__( 'It will be deleted permanently.', 'panda-pods-repeater-field' ),
		'you_sure' 			=> esc_html__( 'Are you sure?', 'panda-pods-repeater-field' ),
		'Ignore_changes' 	=> esc_html__( 'It seems like you have made some changes in a repeater field. Ignore the changes?', 'panda-pods-repeater-field' ),
	);
	$GLOBALS['pprf_l10n'] = $strings;
}
/**
 * pandarf_pods_fn extension of pods( $table, $params )
 *
 * @param string $tb_str repeater field table
 * @param array  $search_arr search repeater field table array( 
 																'pod_id': parent pod id
 																'post_id: parent post id
																'pod_field_id' pod field id
															  )	
 * @param array $params_arr an array to pass into pods( $table, $params )														  	
 * @user pods( $table, $param )
 */
function pandarf_pods_fn( $tb_str, $search_arr = array( 'pod_id' => '', 'post_id' => '', 'pod_field_id' => '' ), $params_arr = array() ){
	if( !is_numeric( $search_arr['pod_id'] ) || !is_numeric( $search_arr['post_id'] ) || !is_numeric( $search_arr['pod_field_id'] ) ){
		return array();	
	}
	$files_arr   = array('panda_pods_repeater_field_db');
		
	$class_bln   = true;	
	$file_str = dirname(__FILE__) . '/classes/' . $files_arr[ 0 ] . '.php';			

	if( file_exists( $file_str ) ) {
		$claName_str = str_replace( ' ', '_', ucwords( strtolower( str_replace( '_', ' ', $files_arr[ 0 ] ) ) ) ) ;			
		include_once $file_str;		
		
		$db_cla 	 = new panda_pods_repeater_field_db();	
		$tbInfo_arr	 = $db_cla->get_pods_tb_info_fn( 'pods_' . $tb_str );
		$tbabbr_str  = $tbInfo_arr['type'] == 'pod'? 't' : 'd';	

		$where_str   = '   `' . $tbabbr_str . '`.`pandarf_parent_pod_id`  = ' . intval( $search_arr['pod_id'] ) . '
					   AND `' . $tbabbr_str . '`.`pandarf_parent_post_id` = "' . intval( $search_arr['post_id'] ) . '"
					   AND `' . $tbabbr_str . '`.`pandarf_pod_field_id`   = ' . intval( $search_arr['pod_field_id'] ) . ' '; 
		if( isset( $params_arr['where'] ) && $params_arr['where'] != '' ){
			$params_arr['where'] .= ' AND ' . $where_str;
		} else {
			$params_arr['where']  = $where_str;
		}
			
		$pod_cla   = pods( $tb_str, $params_arr );
	
		$rows_obj  = $pod_cla->data();	
		
		
		return $rows_obj;
	}

}
/**
 * Alias of pandarf_items_fn
 */ 
function get_pandarf_items( $fields_arr = array(), $atts_arr = array(), $showQuery_bln = false ){
	pandarf_items_fn( $fields_arr = array(), $atts_arr = array(), $showQuery_bln = false );
}
/**
 * pandarf_items_fn fetch child pod data
 *
 * @param array  $fields_arr search repeater field table array( 
																'id'              			  => '',		
																'name'               		  => '', //the common name field used by pods						 
																'child_pod_name'              => '', //repeater table name		
																'parent_pod_id'               => '', //main table pod id		
																'parent_pod_post_id'          => '', //main table post id		
																'parent_pod_field_id'         => '', //main table pod Panda Pod Repeater Field id	
															  )	
 * @param array  $atts_arr search repeater field table array( 
															  'where'				=> '',	//exter where, expected to be escaped
															  'order' 				=> 'ASC', 
															  'order_by'			=> 'pandarf_order',
															  'group_by'			=> '',		
															  'start'           	=> 0,		
															  'limit'           	=> 0,	
															  'count_only'			=> false,
															  'full_child_pod_name'	=> false, //if child_pod_name is a full table name, $wpdb->prefix and pods_ won't be added to the table name
															  )																  
 * @return array $items_arr;
 */
function pandarf_items_fn( $fields_arr = array(), $atts_arr = array(), $showQuery_bln = false ){

	global $wpdb;

	$filter_arr 	=  array(
		'id'              			  => '',		
		'name'               		  => '',	
		'child_pod_name'              => '',		
		'parent_pod_id'               => '',		
		'parent_pod_post_id'          => '',		
		'parent_pod_field_id'         => '',
	);		
	$filter_arr = wp_parse_args( $fields_arr, $filter_arr );		
		
	$_atts_arr  = array(
 		  'where'				=> '',	
		  'order' 				=> 'ASC',
		  'order_by'			=> 'pandarf_order',
		  'group_by'			=> '',		
		  'start'           	=> 0,		
		  'limit'           	=> 0,	
		  'count_only'			=> false,
		  'full_child_pod_name'	=> false,
	);		
	$atts_arr  = wp_parse_args( $atts_arr, $_atts_arr );						

	$para_arr  = array();
	$where_str = '';
	if( is_numeric( $filter_arr['id'] ) ){
		$where_str .= ' AND `id` = %d';
		array_push( $para_arr, $filter_arr['id'] );					
	}																
	if( $filter_arr['name'] != '' ){
		if( is_numeric( $filter_arr['name'] ) ){		
			// if putting a dot at the end of an number, like 24., strpos will return false so it is treated as an integer
			$dsf_str 	= strpos( $filter_arr['name'], '.' ) !== false ? '%f' : '%d';		
		} else {
			$dsf_str	= '%s';	
		}
		$where_str .= ' AND `name` = ' . $dsf_str . '';
		array_push( $para_arr, $filter_arr['name'] );					
	}		
	if( is_numeric( $filter_arr['parent_pod_id'] ) ){
		$where_str .= ' AND `pandarf_parent_pod_id` = %d';
		array_push( $para_arr, $filter_arr['parent_pod_id'] );					
	}																
	if( is_numeric( $filter_arr['parent_pod_post_id'] ) ){
		$where_str .= ' AND `pandarf_parent_post_id` = %d';
		array_push( $para_arr, $filter_arr['parent_pod_post_id'] );					
	}	
	if( is_numeric( $filter_arr['parent_pod_field_id'] ) ){
		$where_str .= ' AND `pandarf_pod_field_id` = %d';
		array_push( $para_arr, $filter_arr['parent_pod_field_id'] );					
	}		

	//exit( $where_str  );		
	$groupby_str = '';
	if( $atts_arr['group_by'] != '' ){
		$groupby_str  =	'GROUP BY( ' . esc_sql( $atts_arr['group_by'] ) . ' )';	
	}	
			
	$limit_str   = '';
	if( ! empty( $atts_arr['limit'] ) ){
		$limit_str = 'LIMIT ' . esc_sql( intval( $atts_arr['start'] ) ) . ', ' . esc_sql( intval( $atts_arr['limit'] ) ) . '';
	}

		
	if( $atts_arr['count_only'] === false ){
		$fields_str = ' * ' ;			   						   
	} else {
		$fields_str = ' COUNT( `id` ) AS "count"'; 
	}

	$where_str	.= ' ' . $atts_arr['where'] . ' ';		
	$table_str 	 = esc_sql( $filter_arr['child_pod_name'] );		
	if( $atts_arr['full_child_pod_name'] == false ){				
		$table_str 	 	= $wpdb->prefix . 'pods_' . $table_str;		
	} 

	$pPost_obj	=	get_post( $filter_arr['parent_pod_id'] );
	if( $pPost_obj ){
		$parent_pod =	pods( $pPost_obj->post_name );
		foreach( $parent_pod->fields as $k_str => $v_arr ){
			if( is_array( $v_arr ) ){
				if( isset( $v_arr['type'] ) && $v_arr['type'] == 'pandarepeaterfield' && $filter_arr['parent_pod_field_id'] == $v_arr['id'] ) {
					
					if( isset( $v_arr['options']['pandarepeaterfield_enable_trash'] ) && $v_arr['options']['pandarepeaterfield_enable_trash'] == 1 ){ // if trash enabled, only load those not trashed 
						$where_str .= ' AND `pandarf_trash` != 1';
					
					}
					if( isset( $v_arr['options']['pandarepeaterfield_order_by'] ) && !empty( $v_arr['options']['pandarepeaterfield_order_by'] ) ){ // different order field
						if( $atts_arr['order_by'] == 'pandarf_order' && !empty( $v_arr['options']['pandarepeaterfield_order_by'] ) ){ // if not changed by the filter, load the saved one
							$atts_arr['order_by'] = $v_arr['options']['pandarepeaterfield_order_by'] ;					
						}
					}		
					if( isset( $v_arr['options']['pandarepeaterfield_order'] )  && !empty( $v_arr['options']['pandarepeaterfield_order'] ) ){ // different order field
						if( $atts_arr['order'] == 'ASC' ){ // if not changed by the filter, load the saved one
							$atts_arr['order'] = $v_arr['options']['pandarepeaterfield_order'];		
						}
					}						
					break;											
				}

			}
		}		
	}

	$order_str   = '';
	if( $atts_arr['order_by'] != '' ){
		if( $atts_arr['order_by'] == 'random' ){
			$order_str = 'ORDER BY RAND()';
		} else {
		
			if( $atts_arr['order'] != 'ASC' ){
				$atts_arr['order'] = 'DESC';	
			}
			if( $atts_arr['order_by'] == 'pandarf_order' ){
				$order_str = 'ORDER BY CAST( ' . esc_sql( $atts_arr['order_by'] ) . ' AS UNSIGNED ) ' . $atts_arr['order'] . '' ;
			} else {
				$order_str = 'ORDER BY ' . esc_sql( $atts_arr['order_by'] ) . ' ' . $atts_arr['order'] . '';
			}
		}
	}	
	// find out the file type
	$join_str	=	'';
	$child_pod	= 	pods( $filter_arr['child_pod_name'] );
/*echo '<pre>';
	print_r( $child_pod );
	//echo $child_pod;

echo '</pre>';	*/

	if( pprf_updated_tables(  $filter_arr['child_pod_name'] ) == false ){
		$file_str = dirname(__FILE__) . '/classes/panda_pods_repeater_field_db.php';					
		if( file_exists( $file_str ) ) {		
			include_once $file_str;	
			$db_cla 	 = new panda_pods_repeater_field_db();	
			$db_cla->update_columns_fn(  $filter_arr['child_pod_name'] );
		}	
	}

	if( is_object( $child_pod ) && $atts_arr['count_only'] == false ){
		$i 	= 	0;
		foreach( $child_pod->fields as $k_str => $v_arr ){
			if( is_array( $v_arr ) ){
/*				if( ( isset( $v_arr['type'] ) && $v_arr['type'] == 'pandarepeaterfield' ) ) {
					//echo '<pre>';
					if( $v_arr['options']['pandarepeaterfield_enable_trash'] == 1 ){ // if trash enabled, only load those not trashed 
						$where_str .= ' AND `pandarf_trash` != 1';
					//	echo $where_str;
					}
					//echo '</pre>';					
				}*/
				$relatePick_arr	 = array('user', 'post_type', 'pod', 'media');
				if( ( isset( $v_arr['type'] ) && $v_arr['type'] == 'file' ) || ( isset( $v_arr['type'] ) && $v_arr['type'] == 'pick' && in_array( $v_arr['pick_object'], $relatePick_arr ) ) ){

					$fields_str .= ',(
									SELECT GROUP_CONCAT( psl' . $i .  '_tb.related_item_id ORDER BY psl' . $i .  '_tb.weight ASC SEPARATOR "," )
									FROM `' . $wpdb->prefix . 'podsrel` AS psl' . $i .  '_tb
									WHERE psl' . $i .  '_tb.pod_id = "' . $child_pod->pod_id . '" 
									AND psl' . $i .  '_tb.field_id = "' . $v_arr['id'] . '" 
									AND psl' . $i .  '_tb.item_id = pod_tb.id
									GROUP BY psl' . $i .  '_tb.item_id									
									) AS ' . $k_str;

					$i++;				
				}			
			}
		}
	}	
	if( count( $para_arr ) > 0 ){
		$query_str = $wpdb->prepare( 'SELECT ' . $fields_str . ' FROM `' . $table_str . '` AS pod_tb ' . $join_str . ' WHERE 1=1 ' . $where_str . ' ' . $groupby_str . ' ' . $order_str . ' ' . $limit_str , $para_arr );
	} else {
		$query_str = 'SELECT ' . $fields_str . ' FROM `' . $table_str . '` AS pod_tb ' . $join_str . '  WHERE 1=1 ' . $where_str . ' ' . $groupby_str . ' ' . $order_str . ' ' . $limit_str;
	}
	//echo $query_str;
	if( $showQuery_bln ){
		echo $query_str;
	}
	
	$items_arr = $wpdb->get_results( $query_str , ARRAY_A );
	//echo '<pre>';
	//print_r( $items_arr );
	//echo '</pre>';	
		
	return 	$items_arr;
}

/**
 * pandarf_insert_fn insert data to panda repeater field table
 * 
 * @param array  $fields_arr extra fields other than panda repeater fields to insert array( 'field_name' => '', 'field_name' => '' ... )
 * @param array  $atts_arr search repeater field table array( 
																'child_pod_name'              => '', repeater table name		
																'parent_pod_id'               => '', main table pod id		
																'parent_pod_post_id'          => '', main table post id		
																'parent_pod_field_id'         => '', main table pod Panda Pod Repeater Field id	
																'user_id' 					  => 0, The author id
																'full_child_pod_name'		  => false, //if child_pod_name is a full table name, $wpdb->prefix and pods_ won't be added to the table name
															  )	
 * @return boolean $done_bln;
 */
function pandarf_insert_fn( $fields_arr = array(), $atts_arr = array(), $show_bln = false ){

	global $wpdb, $current_user;		

	$_atts_arr 	= array(
		'child_pod_name'              => '',		
		'parent_pod_id'               => '',		
		'parent_pod_post_id'          => '',		
		'parent_pod_field_id'         => '',
		'user_id'					  => $current_user->ID,
		'full_child_pod_name'		  => false,
	);		

	$atts_arr  		= wp_parse_args( $atts_arr, $_atts_arr );				

	$now_str		= date('Y-m-d H:i:s');	
	$table_str 	 	= esc_sql( $atts_arr['child_pod_name'] );		
	if( $atts_arr['full_child_pod_name'] == false ){				
		$table_str 	 	= $wpdb->prefix . 'pods_' . $table_str;		
	} 
	$para_arr  		= array();
	$where_str 		= '';
	// get the last order
	$query_str  	= $wpdb->prepare( 'SELECT MAX( CAST(`pandarf_order` AS UNSIGNED) ) AS last_order FROM `' . $table_str . '` WHERE `pandarf_parent_pod_id` = %d AND `pandarf_parent_post_id` = "%s" AND `pandarf_pod_field_id` = %d' , array( $atts_arr['parent_pod_id'], $atts_arr['parent_pod_post_id'], $atts_arr['parent_pod_field_id'] ) );	

	$order_arr   	= $wpdb->get_results( $query_str, ARRAY_A );	

	$order_int		= count( $order_arr ) > 0 ? $order_arr[0]['last_order'] + 1 : 1;

	$pafields_arr	= array( 
							'pandarf_parent_pod_id'   => $atts_arr['parent_pod_id'], 
							'pandarf_parent_post_id'  => $atts_arr['parent_pod_post_id'], 
							'pandarf_pod_field_id' 	  => $atts_arr['parent_pod_field_id'], 
							'pandarf_created' 		  => $now_str, 
							'pandarf_modified' 		  => $now_str, 
							'pandarf_modified_author' => $atts_arr['user_id'],
							'pandarf_author'		  => $atts_arr['user_id'],
							'pandarf_order'			  => $order_int,
							);
		
	// insert
	$values_arr   	= array();
	$keys_arr	  	= array();
	$fields_arr   	= array_merge( $fields_arr, $pafields_arr );
	$vals_arr 		= array();	 
	foreach( $fields_arr as $k_str => $v_str ){
		array_push( $keys_arr, '`' . esc_sql( $k_str ) . '`' );	
		if( is_numeric( $v_str ) ){
			// if putting a dot at the end of an number, like 24., strpos will return false so it is treated as an integer
			$dsf_str 	= strpos( $v_str, '.' ) !== false ? '%f' : '%d';		
			
			array_push( $vals_arr, $dsf_str );	

		} else if( is_array( $v_str ) ){
			array_push( maybe_serialize( $vals_arr ), '%s' );	
		} else {
			array_push( $vals_arr, '%s' );	
		}
		array_push( $values_arr, $v_str );	
	}	

	$fields_str   	= join( ',', $keys_arr ); 
	$vals_str 		= join( ',', $vals_arr ); 	
	//if( count(  $values_arr ) > 0 ){
		$query_str 	= $wpdb->prepare( 'INSERT INTO `' . $table_str . '` ( ' . $fields_str . ' ) VALUES ( ' . $vals_str . ' );' , $values_arr );
	//} else {
		//$query_str 	= 'INSERT INTO `' . $table_str . '` ( ' . $fields_str . ' ) VALUES ( ' . $vals_str . ' );';
	//}

	if( $show_bln ){
		echo $query_str ;
	}	
	$done_bln 	    = $wpdb->query( $query_str );	
	if( $done_bln ){
		return $wpdb->insert_id; 
	} 
	return false; 
}
/**
 * pandarf_pods_field_fn filter for pods_field
 * 
 * @param string $value_ukn value of the field
 * @param array  $row_arr 
 * @param array $params_arr
 * @param object  $pods_obj 
 * @return string|number|array 
 */					
add_filter( 'pods_pods_field', 'pandarf_pods_field_fn', 10, 4 );					

function pandarf_pods_field_fn( $value_ukn, $row_arr, $params_arr, $pods_obj ){
	global $wpdb;
/*	echo '<pre>';
	print_r( $pods_obj->pod_id );
	echo '</pre>';*/
	$repeater_arr = is_pandarf_fn( $params_arr->name, $pods_obj->pod_id );	
	//echo $pods_obj->id() .  ' ' . $pods_obj->id;
	if( $repeater_arr ){
		$savedtb_str	=	$pods_obj->fields[ $params_arr->name ]['options']['pandarepeaterfield_table'];
		$items_arr		=	array();
		$cPod_arr		=	explode( '_',  $savedtb_str );
		if( count( $cPod_arr ) == 2 && $cPod_arr[0] == 'pod' && is_numeric( $cPod_arr[1] ) ){
			// find the repeater table pod name
			$query_str = $wpdb->prepare( 'SELECT `post_name` FROM `' . $wpdb->posts . '` WHERE `ID` = %d LIMIT 0, 1', array( $cPod_arr[ 1 ] ) ) ;
			
			$items_arr = $wpdb->get_results( $query_str, ARRAY_A );
		} else {
			$query_str = $wpdb->prepare( 'SELECT `ID`, `post_name` FROM `' . $wpdb->posts . '` WHERE `post_name` = "%s" AND `post_type` = "_pods_pod" LIMIT 0, 1', array( $savedtb_str ) ) ;
							
			$items_arr = $wpdb->get_results( $query_str, ARRAY_A );		

		}		

		if( count( $items_arr ) == 1 ){
			

			$attrs_arr	= apply_filters( 'pandarf_pods_field_attrs', array(), $value_ukn, $row_arr, $params_arr, $pods_obj  );
			$fields_arr = array( 
								'child_pod_name' 		=> $items_arr[0]['post_name'] , 
								'parent_pod_id' 		=> $repeater_arr['post_parent'], 
								'parent_pod_post_id' 	=> $pods_obj->id(),
								'parent_pod_field_id' 	=> $repeater_arr['ID']
								);
			$fields_arr	= apply_filters( 'pandarf_pods_field_fields', $fields_arr, $value_ukn, $row_arr, $params_arr, $pods_obj  );
			$data_arr	= pandarf_items_fn( 
											$fields_arr ,
											$attrs_arr,
											0
										  );	
			// check if it is a repeater field, if yes, return data							  			
			$data_arr 	= pandarf_data_fn( $data_arr, $items_arr[0]['post_name'] );
			
			return 	$data_arr;								  			
		}

		
	}
	return $value_ukn;								  
}
/**
 * check if it is a repeater field, if yes, return data
 * @param array $data_arr data from the table row e.g. Array
														(
															[0] => Array
																(
																	[id] => 26
																	[name] => hi
																	[repeater] =>
																)
														)
 * @param string $parentPod_str parent pod's name											
 */
function pandarf_data_fn( $data_arr, $parentPod_str ){
	global $wpdb;
	
	$pods_obj = pods( $parentPod_str ) ;
/*	echo '<pre>';
	print_r( $pods_obj->pod_id );
	echo '</pre>';	*/
	$pandarf_arr = array();
	if( count( $data_arr ) > 0 ){
		foreach( $data_arr[0] as $k_str => $v_ukn ){
			$repeater_arr = is_pandarf_fn( $k_str,  $pods_obj->pod_id );
			if( $repeater_arr ) {
				$pandarf_arr[ $k_str ] = $repeater_arr;
			}
		}
	}	
	
	if( count( $pandarf_arr ) > 0 ){
		
		// go through each repeater field and attach data
		foreach( $pandarf_arr as $k_str => $v_ukn ){
			if( $pods_obj && isset( $pods_obj->fields[ $k_str ]['options']['pandarepeaterfield_table'] )){
				$savedtb_str	=	$pods_obj->fields[ $k_str ]['options']['pandarepeaterfield_table'];
				$items_arr		=	array();
				$cPod_arr		=	explode( '_',  $savedtb_str );
				// if saved as pod_num, version < 1.2.0
				if( count( $cPod_arr ) == 2 && $cPod_arr[0] == 'pod' && is_numeric( $cPod_arr[1] ) ){
					// find the repeater table pod name
					$query_str = $wpdb->prepare( 'SELECT `post_name` FROM `' . $wpdb->posts . '` WHERE `ID` = %d LIMIT 0, 1', array( $cPod_arr[ 1 ] ) ) ;
					
					$items_arr = $wpdb->get_results( $query_str, ARRAY_A );
				} else {
					$query_str = $wpdb->prepare( 'SELECT `ID`, `post_name` FROM `' . $wpdb->posts . '` WHERE `post_name` = "%s" AND `post_type` = "_pods_pod" LIMIT 0, 1', array( $savedtb_str ) ) ;
									
					$items_arr = $wpdb->get_results( $query_str, ARRAY_A );		

				}		
				if( count( $items_arr ) == 1 ){
					for( $i = 0; $i < count( $data_arr ); $i ++ ){
						$attrs_arr	= apply_filters( 'pandarf_data_attrs', array(), $data_arr, $parentPod_str  );
						$fields_arr	= array( 
											'child_pod_name' 		=> $items_arr[0]['post_name'] , 
											'parent_pod_id' 		=> $pandarf_arr[ $k_str ]['post_parent'], 
											'parent_pod_post_id' 	=> $data_arr[ $i ]['id'], 
											'parent_pod_field_id' 	=> $pandarf_arr[ $k_str ]['ID']
											) ;
						$cData_arr	= pandarf_items_fn( 
														$fields_arr,
														$attrs_arr,
														0
													  );	
										  
						// check if it is a repeater field, if yes, return data							  			
						$cData_arr 	= pandarf_data_fn( $cData_arr, $items_arr[0]['post_name'] );
						
						$data_arr[ $i ][ $k_str ]	=	$cData_arr;			
														  
					}
				}

			}
		}
	}
	return $data_arr;
}
/**
 * Is a panda pods repeater field?
 * @param string $fieldName_str pods field name	 
 * @param integer $parentID_int parent post id	 
 */
function is_pandarf_fn( $fieldName_str, $parentID_int = 0 ){
	global $wpdb;

	$para_arr 	=	array( $fieldName_str );
	$where_str	=	'';
	if( is_numeric( $parentID_int ) && $parentID_int != 0 ){
		$where_str	=	' AND ps_tb.`post_parent` =  %d';
		array_push( $para_arr, $parentID_int );
	}
	
	$query_str 	= $wpdb->prepare( 'SELECT ps_tb.ID, ps_tb.post_name, ps_tb.post_title, ps_tb.post_author, ps_tb.post_parent 

									 FROM `' . $wpdb->posts . '` AS ps_tb

									 INNER JOIN `' . $wpdb->postmeta . '` AS pm_tb ON ps_tb.`ID` = pm_tb.`post_id` AND pm_tb.`meta_key` = "type" AND pm_tb.`meta_value` = "pandarepeaterfield"				  
									 WHERE ps_tb.`post_type` = "_pods_field" AND ps_tb.`post_name` = "%s" ' . $where_str . ' LIMIT 0, 1' , $para_arr );		
	//if( 'simpods_normal_contents' == $fieldName_str ){
	//	echo $query_str;
	//}
	
	$items_arr = $wpdb->get_results( $query_str, ARRAY_A );
	if( count( $items_arr ) ){
		return $items_arr[0];
	} 
	return false;
	
}				
if( !is_admin() ){
	add_action( 'after_setup_theme', 'load_pprf_frontend_scripts' );
	/**
	 * load the PPRF scripts and style
	 */ 
	function load_pprf_frontend_scripts(){
		$can_load_pprf_scripts = true;
		$can_load_pprf_scripts = apply_filters( 'load_pprf_scripts_frontend', $can_load_pprf_scripts ); 
		if( true == $can_load_pprf_scripts ){
			add_action( 'wp_enqueue_scripts', 'pprf_enqueue_scripts' ) ;	
		}
	}
}
//add_action( 'wp_enqueue_scripts', 'pprf_enqueue_scripts' ) ;	
/**
 * Enqueue front-end scripts
 *
 * Allows plugin assets to be loaded.
 *
 * @since 1.0.0
 */
function pprf_enqueue_scripts() {
	global $pprf_l10n;
	/**
	 * All styles goes here
	 */
	wp_register_style(  'panda-pods-repeater-general-styles', plugins_url( 'css/general.min.css', __FILE__ ) );
	wp_enqueue_style( 'panda-pods-repeater-general-styles' );		
	wp_register_style( 'panda-pods-repeater-styles', plugins_url( 'css/front-end.min.css', __FILE__ ), array('panda-pods-repeater-general-styles'), 1.2 );
	wp_enqueue_style( 'panda-pods-repeater-styles');

	if( isset( $_GET ) && isset( $_GET['page'] ) && $_GET['page'] == 'panda-pods-repeater-field' ){ 
		wp_enqueue_style( 'dashicons' );
		wp_register_style('pprf_fields', plugins_url( 'fields/css/pprf.min.css', __FILE__ ), array( 'panda-pods-repeater-general-styles', 'panda-pods-repeater-styles') );
		wp_enqueue_style('pprf_fields');		 

	}
	/**
	 * All scripts goes here
	 */
	
	wp_register_script( 'panda-pods-repeater-scripts', plugins_url( 'js/admin.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable' ), false, true );

	wp_enqueue_script( 'panda-pods-repeater-scripts' );
	//translation
	wp_localize_script( 
		'panda-pods-repeater-scripts', 
		'strs_obj', 
		$pprf_l10n
	);

	// prepare ajax
	wp_localize_script( 
		'panda-pods-repeater-scripts', 
		'ajax_script', 
		array(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
		 	'nonce' 	=> wp_create_nonce( 'panda-pods-repeater-field-nonce' ),
		)
	);	
	//$adminUrl_str =  substr( admin_url(), 0, strrpos( admin_url(), '/wp-admin/' ) + 10 );
	//$adminUrl_str 	= PANDA_PODS_REPEATER_URL .	'fields/pandarepeaterfield.php';	
	$admin_url 	= PANDA_PODS_REPEATER_URL .	'fields/'; // since 1.4.9, we have index.php to avoid being stopped by <FilesMatch "\.(?i:php)$">				
	wp_localize_script( 
		'panda-pods-repeater-scripts', 
		'PANDA_PODS_REPEATER_PAGE_URL', 
		array( $admin_url . '?page=panda-pods-repeater-field&' )
	);		
	wp_localize_script( 
		'panda-pods-repeater-scripts', 
		'PANDA_PODS_REPEATER_URL', 
		array( PANDA_PODS_REPEATER_URL )
	);	

	/**
	 * Example for setting up text strings from Javascript files for localization
	 *
	 * Uncomment line below and replace with proper localization variables.
	 */
	// $translation_array = array( 'some_string' => __( 'Some string to translate', 'panda-pods-repeater-field' ), 'a_value' => '10' );
	// wp_localize_script( 'panda-pods-repeater-scripts', 'podsExtend', $translation_array ) );
	
}


/**
 * check pod type
 */

function pprf_pod_details_fn( $pod_int ){
	global $wpdb;	
	$query_str	=	$wpdb->prepare(
								'SELECT *, pm_tb.`meta_value` AS type FROM `' . $wpdb->prefix . 'posts` AS ps_tb 
								INNER JOIN 	`' . $wpdb->prefix . 'postmeta` AS pm_tb ON ps_tb.`ID` = pm_tb.`post_id` AND pm_tb.`meta_key` = "type"
								WHERE `ID` = %d LIMIT 0, 1 ', array( $pod_int ) 
							); 
	
	$parent_arr	=	$wpdb->get_results( $query_str, ARRAY_A );
	if( $parent_arr ){
		$parent_arr	=	$parent_arr[0];
	}
	return $parent_arr;
}
/*function pprf_family_tree_fn( $atts_arr ){

	global $wpdb;		

	$_atts_arr 	= array(		
		'parent_pod_id'               => '',		
		'parent_pod_post_id'          => '',		
		'parent_pod_field_id'         => '',		
	);		

	$atts_arr  		= wp_parse_args( $atts_arr, $_atts_arr );	
}*/
/**
 * get the repeater fields using the same same table in the pod
 *
 * @param string $pod_cla a pod, generated by pods( pod_slug );	 
 * @param string $ctb_str the table pod slug for the repeater field
 * @return array the repeater fields using the same same table in the pod
 */
function pprf_same_child_tb_fields_fn( $pod_cla, $ctb_str = '' ){

	$return_arr	=	array();

	foreach( $pod_cla->fields as $ck_str => $cField_arr ){
		if( $cField_arr['type'] == 'pandarepeaterfield' && $ctb_str	==	$cField_arr['options']['pandarepeaterfield_table'] ){			
			$return_arr[ $ck_str ]	=	$cField_arr;
		}
	}	

	return $return_arr;
}
// load language
add_action( 'plugins_loaded', 'pprf_localization_setup_fn' );
function pprf_localization_setup_fn() {
	load_plugin_textdomain( 'panda-pods-repeater-field', false, basename( dirname( __FILE__ ) ) . '/languages' );	
}
/**
 * load the tables that been updated with pprf columns. 
 * @since 1.4.5
 * @param $table string the table name to search. If empty, return the saved record for the updated tables.
 * @param $operate string Works if $table is not empty. If $operate is empty, return ture or false respectively if the table is found or not. Return null.
 * @param $operate string Works if $table is not empty. If $operate = 'add', add the table to the record. Return null.
 * @param $operate string Works if $table is not empty. If $operate = 'remove', remove the table from the record. Return null.
 * @return array|boolean|null See the descriptions of the parameters above.
 */
function pprf_updated_tables( $table = '', $operate = '' ){
	$updated_tables = get_option('pprf_updated_tables', array() );

	if( ! is_array( $updated_tables ) ){
		$updated_tables = array();
	}

	if( $table == '' ){
		return $updated_tables;
	} else {
		if( isset( $updated_tables[ $table ] ) ){
			if( '' == $operate ){
				return true;
			}
			if( 'remove' == $operate ){
				unset( $updated_tables[ $table ] );
				return update_option( 'pprf_updated_tables', $updated_tables );
				
			}

		} else {
			if( 'add' == $operate ){
				$updated_tables[ $table ] = array();// set it as an array for futurn use
				return update_option( 'pprf_updated_tables', $updated_tables );
				
			}
			return false;
		}

	}

	return false;
}
/**
 * Check if a string contains images, videos, audio medias or relevant shortcode start with them.
 * @since 1.4.5
 * @param $content string the string
 * @return return relevant icons if it contains a media .
 */
function pprf_check_media_in_content( $content ){
	$html = ' ';
	preg_match_all('/(<img .*?>|\[img.*?\]|\[image.*?\])/is', $content, $tags );
	
	if( ! empty( $tags[0] ) ){
		$html 	.= ' <span class="dashicons dashicons-format-image" title ="' . esc_attr__( 'Contains images', 'panda-pods-repeater-field' ). '"></span>';
	}
	preg_match_all('/(<video .*?>|\[video.*?\])/is', $content, $tags );
	
	if( ! empty( $tags[0] ) ){
		$html 	.= ' <span class="dashicons dashicons-format-video" title ="' . esc_attr__( 'Contains videos', 'panda-pods-repeater-field' ). '"></span>';
	}

	preg_match_all('/(<audio .*?>|\[audio.*?\])/is', $content, $tags );
	
	if( ! empty( $tags[0] ) ){
		$html 	.= ' <span class="dashicons dashicons-format-audio"  title ="' . esc_attr__( 'Contains audio', 'panda-pods-repeater-field' ). '"></span>';
	}
	preg_match_all('/(\[.*?\])/is', $content, $tags );
	
	if( ! empty( $tags[0] ) ){
		$html 	.= ' <span class="dashicons dashicons-wordpress"  title ="' . esc_attr__( 'Maybe contain shortcode', 'panda-pods-repeater-field' ). '"></span>';
	}	

	return 	$html;
}