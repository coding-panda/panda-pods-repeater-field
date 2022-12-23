<?php
/**
 * The main class file
 *
 * @package Panda Pods Repeater Field.
 * @author  Dongjie Xu
 */

/**
 * The class that holds the entire Panda_Pods_Repeater_Field plugin
 *
 * @package Panda Pods Repeater Field.
 */
class Panda_Pods_Repeater_Field {
	/**
	 * The allowed html tags for html outputs
	 *
	 * @var array
	 * @since 1.5.6
	 */
	public $allowed_html_tags = array(
		'strong' => array(),
		'span'   => array(
			'class' => true,
			'title' => true,
		),
		'div'    => array(
			'class'  => true,
			'title'  => true,
			'id'     => true,
			'data-*' => true,
			'style'  => true,
		),
		'iframe' => array(
			'src'             => true,
			'height'          => true,
			'width'           => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'name'            => true,
			'id'              => true,
			'style'           => true,
			'class'           => true,
		),
		'img'    => array(
			'class' => true,
			'title' => true,
			'id'    => true,
			'src'   => true,
			'alt'   => true,
			'style' => true,
		),
		'label'    => array(
			'class' => true,
			'id'    => true,
			'style' => true,
		),	
		'select'    => array(
			'class' => true,
			'id'    => true,
			'style' => true,
			'name' 	=> true,	
			'disabled' => true,			
		),			
		'option' 	=> array(
			'class' => true,
			'value' => true,
			'style' => true,
			'selected' => true,
			'disabled' => true,
		),	
		'button' 	=> array(
			'class' => true,
			'id'    => true,
			'style' => true,
			'name' 	=> true,	
			'disabled' => true,	
			'data-*' => true,			
		),			
	);
	/**
	 * Title to use in the menu.
	 *
	 * @var string $menu_title
	 */
	public $menu_title = 'Panda Pods Repeater Field';
	/**
	 * The name of the field.
	 *
	 * @var string TYPE_NAME
	 */
	const TYPE_NAME = 'pandarepeaterfield';
	/**
	 * Constructor for the Panda_Pods_Repeater_Field class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Return false if Pods Framework is not available.
		if ( ! class_exists( 'PodsField' ) ) {
			return false;
		}
		$files = array(
			'class-panda-pods-repeater-field-db',
			'class-podsfield-pandarepeaterfield',
			'class-panda-pods-repeater-field-ajax',
		);

		$active_plugins = get_option( 'active_plugins' );
		$has_classes    = true;
		$files_count    = count( $files );

		for ( $i = 0; $i < $files_count; $i ++ ) {
			$file = dirname( __FILE__ ) . '/classes/' . $files[ $i ] . '.php';

			if ( file_exists( $file ) ) {
				$class_name = str_replace( '-', '_', $files[ $i ] );
				$class_name = substr( $class_name, 6 );

				include_once $file;

				if ( ! class_exists( $class_name ) ) {

					$has_classes = false;
				}
			} else {
				$has_classes = false;
			}
		}

		if ( true === $has_classes ) {
			// Create an instance to store pods adavance custom tables.
			$panda_repeater_field = new podsfield_pandarepeaterfield();
			// Ajax.
			$repeater_field_ajax = new Panda_Pods_Repeater_Field_Ajax();

			foreach ( PodsField_Pandarepeaterfield::$act_tables as $pod_table_id => $pod_table_name ) {
				// After pod saved.
				add_action( 'pods_api_post_save_pod_item_' . $pod_table_name, array( $panda_repeater_field, 'pods_post_save' ), 10, 3 );
				add_action( 'pods_api_post_delete_pod_item_' . $pod_table_name, array( $panda_repeater_field, 'pods_post_delete' ), 10, 3 );
			}
			add_action( 'pods_admin_ui_setup_edit_fields', array( $panda_repeater_field, 'field_table_fields' ), 10, 2 );

		}

		/**
		 * Plugin Setup
		 */
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		/**
		 * Scripts/ Styles
		 */
		// Loads admin scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Example: Add a submenu item to Pods Admin Menu.
		add_filter( 'pods_admin_menu', array( $this, 'add_menu' ) );

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
			$prf_cla = new Panda_Pods_Repeater_Field();
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

	}



	/**
	 * Enqueue admin scripts
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {
		global $pprf_l10n, $wp_version;
		/**
		 * All admin styles goes here.
		 */
		wp_register_style( 'panda-pods-repeater-general-styles', plugins_url( 'css/general.min.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_style( 'panda-pods-repeater-general-styles' );
		wp_register_style( 'panda-pods-repeater-admin-styles', plugins_url( 'css/admin.min.css', __FILE__ ), array( 'panda-pods-repeater-general-styles' ), '1.0.0' );
		wp_enqueue_style( 'panda-pods-repeater-admin-styles' );

		/**
		 * All admin scripts goes here
		 */
		if ( isset( $_SERVER['REQUEST_URI'] ) && isset( $_GET ) && isset( $_GET['page'] ) && isset( $_GET['pprf_nonce'] ) ) {
			$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$page        = sanitize_title( wp_unslash( $_GET['page'] ) );
			$pprf_nonce  = sanitize_text_field( wp_unslash( $_GET['pprf_nonce'] ) );
			if ( wp_verify_nonce( $pprf_nonce, 'load-pprf-page' ) ) {
				if ( false !== strpos( $request_uri, 'wp-admin' ) && 'panda-pods-repeater-field' === $page ) {
					wp_register_style( 'pprf_fields', plugins_url( 'fields/css/pprf.min.css', __FILE__ ), array( 'panda-pods-repeater-general-styles', 'panda-pods-repeater-admin-styles' ), '1.0.0' );
					wp_enqueue_style( 'pprf_fields' );
				}
			}
		}

		if ( version_compare( $wp_version, '5.9', '=' ) ) {

			wp_register_script( 'panda-pods-repeater-admin-scripts', plugins_url( 'js/admin.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'panda-pods-repeater-jquery-ui' ), '1.0.1', true );
		} else {
			wp_register_script( 'panda-pods-repeater-admin-scripts', plugins_url( 'js/admin.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable' ), '1.0.0', true );

		}

		wp_enqueue_script( 'panda-pods-repeater-admin-scripts' );
		// prepare ajax.
		wp_localize_script(
			'panda-pods-repeater-admin-scripts',
			'ajax_script',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'panda-pods-repeater-field-nonce' ),
			)
		);

		wp_localize_script(
			'panda-pods-repeater-admin-scripts',
			'strs_obj',
			$pprf_l10n
		);
		$admin_url = substr( admin_url(), 0, strrpos( admin_url(), '/wp-admin/' ) + 10 );
		wp_localize_script(
			'panda-pods-repeater-admin-scripts',
			'PANDA_PODS_REPEATER_PAGE_URL',
			array( $admin_url . '?page=panda-pods-repeater-field&' )
		);
		wp_localize_script(
			'panda-pods-repeater-admin-scripts',
			'PANDA_PODS_REPEATER_CONSTANTS',
			array(
				'url'   => PANDA_PODS_REPEATER_URL,
				'nonce' => PANDA_PODS_REPEATER_NONCE,
			)
		);

	}

	/**
	 * Adds an admin tab to Pods editor for all post types.
	 *
	 * @param array  $tabs The admin tabs.
	 * @param object $pod Current Pods Object.
	 * @param array  $additional_args additional arguments.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function pt_tab( $tabs, $pod, $additional_args ) {
		$tabs['panda-pods-repeater'] = __( 'Panda Repeater Options', 'panda-pods-repeater-field' );

		return $tabs;

	}

	/**
	 * Adds options to Pods editor for post types.
	 *
	 * @param array  $options All the options.
	 * @param object $pod Current Pods object.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function pt_options( $options, $pod ) {

		$options['panda-pods-repeater'] = array(
			'example_boolean'    => array(
				'label'             => __( 'Enable something?', 'panda-pods-repeater-field' ),
				'help'              => __( 'Helpful info about this option that will appear in its help bubble', 'panda-pods-repeater-field' ),
				'type'              => 'boolean',
				'default'           => true,
				'boolean_yes_label' => 'Yes',
			),
			'example_text'       => array(
				'label'   => __( 'Enter some text', 'panda-pods-repeater-field' ),
				'help'    => __( 'Helpful info about this option that will appear in its help bubble', 'panda-pods-repeater-field' ),
				'type'    => 'text',
				'default' => 'Default text',
			),
			'dependency_example' => array(
				'label'             => __( 'Dependency Example', 'panda-pods-repeater-field' ),
				'help'              => __( 'When set to true, this field reveals the field "dependent_example".', 'pods' ),
				'type'              => 'boolean',
				'default'           => false,
				'dependency'        => true,
				'boolean_yes_label' => '',
			),
			'dependent_example'  => array(
				'label'      => __( 'Dependent Option', 'panda-pods-repeater-field' ),
				'help'       => __( 'This field is hidden unless the field "dependency_example" is set to true.', 'pods' ),
				'type'       => 'text',
				'depends-on' => array( 'dependency_example' => true ),
			),

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
	public function add_menu( $admin_menus ) {
		$admin_menus['panda_repeater'] = array(
			'label'    => __( 'Panda Repeater', 'panda-pods-repeater-field' ),
			'function' => array( $this, 'menu_page' ),
			'access'   => 'manage_options',

		);

		return $admin_menus;

	}

	/**
	 * This is the callback for the menu page. Be sure to create some actual functionality!
	 *
	 * @since 1.0.0
	 */
	public function menu_page() {
		echo '<h3>' . esc_html__( 'Panda Repeater Field', 'panda-pods-repeater-field' ) . '</h3>';

	}
	/**
	 * Not needed, now use pods_register_field_type
	 *
	 * @param array $field_types field types.
	 *
	 * @return array $field_types an array of the field types
	 *
	 * @deprecated
	 */
	public function filter_pods_api_field_types( $field_types ) {

		if ( ! in_array( 'pandarepeaterfield', $field_types, true ) ) {
			array_push( $field_types, 'pandarepeaterfield' );
		}
		return $field_types;
	}
	/**
	 * Not needed, now use pods_register_field_type
	 *
	 * @param string $pods_dir the path to the pod field.
	 * @param string $field_type the type of the pod field.
	 *
	 * @return string $pods_dir the path to the pod field.
	 *
	 * @deprecated
	 */
	public function filter_pods_form_field_include( $pods_dir, $field_type ) {

		if ( 'pandarepeaterfield' === $field_type ) {
			$pods_dir = dirname( __FILE__ ) . '/classes/class-podsfield-pandarepeaterfield.php';
		}

		return $pods_dir;
	}
	/**
	 * To filter the field output
	 *
	 * @param string $output the output of the field.
	 * @param string $name the name of the field.
	 * @param string $value the value of the field.
	 * @param string $options the options of the field.
	 * @param string $pod the pod of the field.
	 * @param string $id the id of the field.
	 *
	 * @return string $output the output of the field.
	 *
	 * @deprecated
	 */
	public function filter_pods_form_ui_field_panda_repeater( $output, $name, $value, $options, $pod, $id ) {
		return $output;
	}

}
