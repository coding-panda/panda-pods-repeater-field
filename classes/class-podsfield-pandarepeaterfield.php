<?php
/**
 * The class file for the repeater field
 *
 * @package panda_pods_repeater_field
 * @author  Dongjie Xu
 */

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
	public static $type = 'pandarepeaterfield';
	/**
	 * Option Name to save to postmeta table
	 *
	 * @var string
	 * @since 1.0
	 */
	public static $type_table = 'pandarepeaterfield_table';
	/**
	 * Input name
	 *
	 * @var string
	 * @since 1.0
	 */

	public static $input = 'panda-pods-repeater-field';
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
	public static $act_tables = array();
	/**
	 * Tables caching for all tables
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public static $tables = array();
	/**
	 * Constructor of the class
	 */
	public function __construct() {
		if ( ! class_exists( 'Panda_Pods_Repeater_Field_DB' ) ) {
			include_once PANDA_PODS_REPEATER_DIR . 'classes/class-panda-pods-repeater-field-db.php';
		}

		if ( ! defined( 'PPRF_PODS_TABLES' ) ) {
			self::$act_tables = $this->get_pods_tables();
		} else {
			self::$act_tables = (array) json_decode( PPRF_PODS_TABLES );
		}

	}


	/**
	 * Add options and set defaults for field type, shows in admin area
	 *
	 * @return array $options.
	 *
	 * @since 1.0
	 * @see PodsField::ui_options.
	 * @uses get_pods_tables() has to call the function rather than using the static one. The static one doesn't include all tables after saving.
	 */
	public function options() {

		global $wpdb, $wp_roles;
		$tables_arr = $this->get_pods_tables( 2 );

		$roles_arr = array();
		foreach ( $wp_roles->roles as $role_str => $details_arr ) { // Only a user role with edit_posts capability can access the field. Grand the access right to more roles here.
			if ( ! isset( $details_arr['capabilities']['edit_posts'] ) || 0 === (int) $details_arr['capabilities']['edit_posts'] ) {

				$roles_arr[ $role_str ] = array(
					'label'   => $details_arr['name'],
					'default' => 0,
					'type'    => 'boolean',
				);
			}
		}
		// phpcs:ignore
		if ( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) { // This is a Pod page, I can't attach a nonce to the url.
			// phpcs:ignore
			$id = (int) $_GET['id'];
			if ( isset( $tables_arr[ 'pod_' . $id ] ) ) {
				unset( $tables_arr[ 'pod_' . $id ] );
			}

			$query = $wpdb->prepare(
				// phpcs:ignore
				'SELECT `post_name` FROM `' . $wpdb->posts . '` WHERE `ID` = %d LIMIT 0, 1', array( $id ) 
			);

			$items_arr = $wpdb->get_results(
				// phpcs:ignore	
				$query, ARRAY_A 
			); // db call ok. no cache ok.
			if ( count( $items_arr ) && isset( $tables_arr[ $items_arr[0]['post_name'] ] ) ) {
				unset( $tables_arr[ $items_arr[0]['post_name'] ] );
			}
		}

		$wids_arr = array(
			'100' => '100%',
			'50'  => '50%',
			'25'  => '25%',
		);
		$bln_arr  = array(
			'0' => __( 'No', 'panda-pods-repeater-field' ),
			'1' => __( 'Yes', 'panda-pods-repeater-field' ),
		);
		$options  = array(

			self::$type . '_table'               => array(
				'label'      => __( 'Pods Table', 'panda-pods-repeater-field' ),
				'default'    => 0,
				'type'       => 'pick',
				'data'       => $tables_arr,
				'dependency' => true,
			),
			self::$type . '_field_width'         => array(
				'label'      => __( 'Field Width', 'panda-pods-repeater-field' ),
				'default'    => 100,
				'type'       => 'pick',
				'data'       => $wids_arr,
				'dependency' => true,
			),
			self::$type . '_entry_limit'         => array(
				'label'       => __( 'Entry Limit', 'panda-pods-repeater-field' ),
				'default'     => 0,
				'type'        => 'number',
				'data'        => '',
				'dependency'  => true,
				'description' => __( 'Leave it to 0 if you want to Enable Load More', 'panda-pods-repeater-field' ),
			),
			self::$type . '_enable_load_more'    => array(
				'label'      => __( 'Enable Load More', 'panda-pods-repeater-field' ),
				'depends-on' => array( self::$type . '_entry_limit' => '0' ),
				'default'    => '0',
				'type'       => 'pick',
				'data'       => $bln_arr,
				'dependency' => true,
			),
			self::$type . '_initial_amount'      => array(
				'label'       => __( 'Initial Amount', 'panda-pods-repeater-field' ),
				'depends-on'  => array( self::$type . '_enable_load_more' => 1 ),
				'type'        => 'number',
				'default'     => '10',
				'data'        => '',
				'description' => __( 'Default amount to load, no negative number.', 'panda-pods-repeater-field' ),
			),
			self::$type . '_enable_trash'        => array(
				'label'      => __( 'Enable Trash', 'panda-pods-repeater-field' ),
				'default'    => '0',
				'type'       => 'pick',
				'data'       => $bln_arr,
				'dependency' => true,
			),
			self::$type . '_order_by'            => array(
				'label'       => __( 'Order By', 'panda-pods-repeater-field' ),
				'default'     => 'pandarf_order',
				'type'        => 'text',
				'data'        => '',
				'description' => __( 'Enter a field of the table. Default to pandarf_order. If not pandarf_order, re-order will be disabled. Min PHP version 5.5.', 'panda-pods-repeater-field' ),
			),
			self::$type . '_order'               => array(
				'label'       => __( 'Order', 'panda-pods-repeater-field' ),
				'default'     => 'ASC',
				'type'        => 'pick',
				'data'        => array(
					'ASC'  => __( 'Ascending', 'panda-pods-repeater-field' ),
					'DESC' => __( 'Descending', 'panda-pods-repeater-field' ),
				),
				'description' => __( 'Default to Ascending', 'panda-pods-repeater-field' ),
			),
			self::$type . '_display_order_info'  => array(
				'label'   => __( 'Display Order Info', 'panda-pods-repeater-field' ),
				'default' => '0',
				'type'    => 'pick',
				'data'    => $bln_arr,
			),
			self::$type . '_apply_admin_columns' => array(
				'label'       => __( 'Apply Admin Table Columns', 'panda-pods-repeater-field' ),
				'default'     => '0',
				'type'        => 'pick',
				'data'        => $bln_arr,
				'description' => __( 'Display labels based on the Admin Table Columns. Only strings and numbers will be displayed.', 'panda-pods-repeater-field' ),
			),
			self::$type . '_allow_reassign'      => array(
				'label'       => __( 'Allow Reassignment', 'panda-pods-repeater-field' ),
				'default'     => '0',
				'type'        => 'pick',
				'data'        => $bln_arr,
				'description' => __( 'Allow reassigning an item to another parent', 'panda-pods-repeater-field' ),
			),
			self::$type . '_allow_duplicate'     => array(
				'label'       => __( 'Allow Duplication', 'panda-pods-repeater-field' ),
				'default'     => '0',
				'type'        => 'pick',
				'data'        => $bln_arr,
				'description' => __( 'Allow duplicating an item to another parent', 'panda-pods-repeater-field' ),
			),

		);
		// To emable deleting item descendants. Add it to the configure.php file. Only do it to if you have daily backup and backup before deleting an item. The plugin author is not responsible for any data loss.
		if ( defined( 'PANDA_PODS_REPEATER_DELETE_ITEM_DESCENDANTS' ) ) {
			$options[ self::$type . '_delete_data_tree' ] = array(
				'label'       => __( 'Delete item descendants', 'panda-pods-repeater-field' ),
				'default'     => '0',
				'type'        => 'pick',
				'data'        => $bln_arr,
				'description' => __( 'When a parent item is deleted, delete all its descendants. Please make sure you have backups to cover any data loss.', 'panda-pods-repeater-field' ),
			);
		}
		return $options;
	}

	/**
	 * Options for the Admin area, defaults to $this->options()
	 *
	 * @return array $options Options of the field.
	 *
	 * @since 1.0
	 * @see PodsField::options
	 */
	public function ui_options() {
		return $this->options();
	}

	/**
	 * Define the current field's schema for DB table storage
	 *
	 * @param array $options Options of the field.
	 *
	 * @return string
	 * @since 1.0
	 */
	public function schema( $options = null ) {
		$schema = 'VARCHAR(255)';

		return $schema;
	}

	/**
	 * Define the current field's preparation for sprintf
	 *
	 * @param array $options Options of the field.
	 *
	 * @return array
	 * @since 1.0
	 */
	public function prepare( $options = null ) {
		$format = self::$prepare;

		return $format;
	}

	/**
	 * Change the value of the field
	 *
	 * @param mixed  $value Value of the field.
	 * @param string $name Name of the field.
	 * @param array  $options Options of the field.
	 * @param array  $pod Pod of the field.
	 * @param int    $id ID of the field.
	 *
	 * @return mixed|null|string
	 * @since 1.0.0
	 */
	public function value( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return $value;
	}

	/**
	 * Change the way the value of the field is displayed with Pods::get
	 *
	 * @param mixed  $value Value of the field.
	 * @param string $name Name of the field.
	 * @param array  $options Options of the field.
	 * @param array  $pod Pod of the field.
	 * @param int    $id ID of the field.
	 *
	 * @return mixed|null|string
	 * @since 1.0
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return $value;
	}

	/**
	 * Customize output of the form field
	 *
	 * @param string $name Name of the field.
	 * @param mixed  $value Value of the field.
	 * @param array  $options Options of the field.
	 * @param array  $pod Pod of the field.
	 * @param int    $id ID of the field.
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {
		global $wpdb, $current_user, $panda_pods_repeater_field;

		$is_allowed = true;
		$in_admin   = true;

		$nonce_query = '&pprf_nonce=' . PANDA_PODS_REPEATER_NONCE;

		if ( isset( $options['pandarepeaterfield_public_access'] ) && 1 === (int) $options['pandarepeaterfield_public_access'] ) {
			$is_allowed = true;
		}

		$is_allowed = apply_filters( 'pprf_load_panda_repeater_allow_input', $is_allowed, $in_admin, $name, $value, $options, $pod, $id );

		if ( ! $is_allowed ) {
			echo esc_html( apply_filters( 'pprf_load_panda_repeater_allow_input_msg', __( 'You do not have permission to edit this item.', 'panda-pods-repeater-field' ) ) );
		} else {

			$db_cla = new Panda_Pods_Repeater_Field_DB();

			$options       = (array) $options;
			$parent_pod_id = 0;

			if ( version_compare( PODS_VERSION, '2.8.0' ) >= 0 || 2.8 <= floatval( substr( PODS_VERSION, 0, 3 ) ) ) { // From 2.8. pod_id doesn't exist anymore.
				$parent_pod_id = (int) $options['parent'];
			} else {
				$parent_pod_id = (int) $options['pod_id'];
			}

			$form_field_type = PodsForm::$field_type;

			$saved_table = trim( $options[ self::$type_table ] );

			$child_pods = explode( '_', $saved_table );

			if ( 2 === count( $child_pods ) && 'pod' === $child_pods[0] && is_numeric( $child_pods[1] ) ) {
				// Table saved as before 1.2.0.
				$saved_table_id = substr( $saved_table, 4 );

				$tb_str = '';
				if ( is_numeric( $saved_table_id ) ) {
					$post_arr = get_post( absint( $saved_table_id ), ARRAY_A );

					if ( is_array( $post_arr ) && '_pods_pod' === $post_arr['post_type'] ) {
						$tb_str = $post_arr['post_name'];

					} else {
						return;
					}
				} else {
					return;
				}
			} else {
				// Table saved as since 1.2.0.
				$query = $wpdb->prepare(
					'SELECT * FROM `' . $wpdb->posts . '` WHERE `post_name` = %s AND `post_type` = "_pods_pod" LIMIT 0, 1',
					array( $saved_table )
				);

				$items_arr = $wpdb->get_results(
					// phpcs:ignore
					$query,
					ARRAY_A
				); // db call ok. no cache ok.

				if ( ! empty( $items_arr ) ) {
					$post_arr       = $items_arr[0];
					$tb_str         = $saved_table;
					$saved_table_id = (int) $items_arr[0]['ID'];
				} else {
					return;
				}
			}

			if ( ! is_numeric( $id ) || empty( $id ) ) {
				// Translators: %s: new id.
				echo '<p class="pprf-reminder">' . esc_html( apply_filters( 'pprf_load_panda_repeater_reminder_msg', sprintf( __( 'Please save the parent first to add %s. ', 'panda-pods-repeater-field' ), strtolower( $post_arr['post_title'] ) ) ) ) . '</p>';
			}
			if ( '' !== $tb_str ) {
				$table_info = $db_cla->get_pods_tb_info( 'pods_' . $tb_str );

				// Load items for the current post only using regular expression.
				$where_sql = '   `pandarf_parent_pod_id`  = %d
							   	  AND `pandarf_parent_post_id` = %d
							   	  AND `pandarf_pod_field_id`   = %d ';
				$searches  = array( $parent_pod_id, $id, $options['id'] );

				$limit_sql = '';
				$limited   = false;
				if ( isset( $options['pandarepeaterfield_entry_limit'] ) && is_numeric( $options['pandarepeaterfield_entry_limit'] ) && 0 !== (int) $options['pandarepeaterfield_entry_limit'] ) {
					$limit_sql = 'LIMIT 0, ' . absint( $options['pandarepeaterfield_entry_limit'] );
					$limited   = true;
				} else {
					if ( isset( $options['pandarepeaterfield_enable_load_more'] ) && 1 === (int) $options['pandarepeaterfield_enable_load_more'] ) {
						if ( isset( $options['pandarepeaterfield_initial_amount'] ) && is_numeric( $options['pandarepeaterfield_initial_amount'] ) ) {
							$options['pandarepeaterfield_initial_amount'] = absint( $options['pandarepeaterfield_initial_amount'] );
							$limit_sql                                    = 'LIMIT 0, ' . $options['pandarepeaterfield_initial_amount'];
						}
					}
				}

				// If it is a WordPress post type, join wp_posts table.
				$join_sql = '';

				if ( 'post_type' === self::$tables[ 'pod_' . $saved_table_id ]['type'] ) {
					$join_sql = 'INNER JOIN  `' . $wpdb->posts . '` AS post_tb ON post_tb.ID = main_tb.id';
				}

				// Order.
				$order_sql  = 'CAST( `pandarf_order` AS UNSIGNED ) ';
				$order_info = __( 'Ordered by: ', 'panda-pods-repeater-field' );
				if ( isset( $options['pandarepeaterfield_order_by'] ) && ! empty( $options['pandarepeaterfield_order_by'] ) && version_compare( phpversion(), '5.5', '>=' ) && 'pandarf_order' !== $options['pandarepeaterfield_order_by'] ) {

					$table_fields = $db_cla->get_fields( 'pods_' . $tb_str );
					$fields       = array_column( $table_fields, 'Field' );

					$options['pandarepeaterfield_order_by'] = esc_sql( sanitize_text_field( wp_unslash( $options['pandarepeaterfield_order_by'] ) ) );

					if ( in_array( $options['pandarepeaterfield_order_by'], $fields, true ) ) {
						$order_sql   = '`' . $options['pandarepeaterfield_order_by'] . '` ';
						$order_info .= $options['pandarepeaterfield_order_by'] . ' ';
					}
				} else {
					$order_info .= 'pandarf_order ';
				}

				if ( isset( $options['pandarepeaterfield_order'] ) && 'DESC' === $options['pandarepeaterfield_order'] ) {
					$order_sql  .= 'DESC';
					$order_info .= esc_html__( '- descending', 'panda-pods-repeater-field' );
				} else {
					$order_sql  .= 'ASC';
					$order_info .= esc_html__( '- ascending', 'panda-pods-repeater-field' );
				}

				// Name field may saved by not exists in the table, if not exist.
				$name_field_sql = '';
				if ( ! empty( self::$tables[ 'pod_' . $saved_table_id ]['name_field'] ) && ! empty( self::$tables[ 'pod_' . $saved_table_id ]['name_label'] ) ) {
					$name_field_sql = ', `' . self::$tables[ 'pod_' . $saved_table_id ]['name_field'] . '` ';
				}

				if ( count( $searches ) > 0 ) {

					$query = $wpdb->prepare(
						// phpcs:ignore
						'SELECT main_tb.* ' . $name_field_sql . ' FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` AS main_tb ' . $join_sql . ' WHERE ' . $where_sql . ' ORDER BY ' . $order_sql . ' ' . $limit_sql,
						$searches
					);
					if ( ! $limited ) {
						$count_query = $wpdb->prepare(
							// phpcs:ignore
							'SELECT COUNT( main_tb.`id` ) AS "count" FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` AS main_tb ' . $join_sql . ' WHERE ' . $where_sql . ' ORDER BY ' . $order_sql,
							$searches
						);
					}
				} else {
					$query = 'SELECT 
										main_tb.*, 
										 ' . $name_field_sql . '	
									   	FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` 
									   	' . $join_sql . ' 
									   	WHERE ' . $where_sql . ' 
									   	ORDER BY ' . $order_sql . '
									   	' . $limit_sql . '; ';
					if ( ! $limited ) {
						$count_query = 'SELECT 
											COUNT( main_tb.`id` ) AS "count"	
										   	FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` 
										   	' . $join_sql . ' 
										   	WHERE ' . $where_sql . ' 
										   	ORDER BY ' . $order_sql . '
										   	; ';
					}
				}

				$entries = $wpdb->get_results(
					// phpcs:ignore
					$query, ARRAY_A 
				); // db call ok. no cache ok.

				$count_int = 0;
				if ( ! $limited ) {
					$rows_for_count = $wpdb->get_results(
						// phpcs:ignore
						$count_query, ARRAY_A 
					);// db call ok. no cache ok.

					if ( $rows_for_count && ! empty( $rows_for_count ) ) {
						$count_int = $rows_for_count[0]['count'];
					}
				}

				$parent_iframe_id = '';
				if ( isset( $_GET ) && isset( $_GET['pprf_nonce'] ) ) {
					$pprf_nonce = sanitize_text_field( wp_unslash( $_GET['pprf_nonce'] ) );
					if ( wp_verify_nonce( $pprf_nonce, 'load-pprf-page' ) ) {
						if ( isset( $_GET['iframe_id'] ) ) {
							$parent_iframe_id = esc_attr( sanitize_text_field( wp_unslash( $_GET['iframe_id'] ) ) );
						}
					}
				}
				$query_str         = '&podid=' . esc_attr( $parent_pod_id ) . '&tb=' . esc_attr( $saved_table_id ) . '&poditemid=' . esc_attr( $options['id'] );
				$repeater_field_id = 'panda-repeater-fields-' . esc_attr( $saved_table_id ) . '-' . esc_attr( $options['id'] );
				// If trash is enabled.

				if ( isset( $options['pandarepeaterfield_enable_trash'] ) && 1 === (int) $options['pandarepeaterfield_enable_trash'] && is_numeric( $id ) && ! empty( $id ) ) {
					echo '<div  id="panda-repeater-fields-tabs-' . esc_attr( $saved_table_id ) . '-' . esc_attr( $options['id'] ) . '" class="pprf-left w100">
							<div class="pprf-tab active" data-target="' . esc_attr( $saved_table_id ) . '-' . esc_attr( $options['id'] ) . '"><span class="dashicons dashicons-portfolio"></span></div>	
							<div class="pprf-tab" data-target="' . esc_attr( $saved_table_id ) . '-' . esc_attr( $options['id'] ) . '"><span class="dashicons dashicons-trash"></span></span></div>	
						  </div>
						 ';
				}
				echo '<div id="' . esc_attr( $repeater_field_id ) . '" class="pprf-redorder-list-wrap">';

				// Remove anything after /wp-admin/, otherwise, it will load a missing page.
				$admin_url = PANDA_PODS_REPEATER_URL . 'fields/'; // since 1.4.9, we have index.php to avoid being stopped by <FilesMatch "\.(?i:php)$">.
				if ( is_admin() ) {
					// Remove anything after /wp-admin/, otherwise, it will load a missing page.
					$admin_url = substr( admin_url(), 0, strrpos( admin_url(), '/wp-admin/' ) + 10 );
				} else {
					$admin_url = PANDA_PODS_REPEATER_URL . 'fields/'; // Since 1.4.9, we have index.php to avoid being stopped by <FilesMatch "\.(?i:php)$">.
				}
				$src_str = $admin_url . '?page=panda-pods-repeater-field&';

				$bg_css = 'pprf-purple-bg';

				$trash_int         = 0;
				$not_trashed_count = 0;
				$trash_btn_css     = 'pprf-btn-not-trashed';
				$bin_action        = '';
				if ( isset( $options['pandarepeaterfield_enable_trash'] ) ) {
					$options['pandarepeaterfield_enable_trash'] = intval( $options['pandarepeaterfield_enable_trash'] );
					if ( 1 === $options['pandarepeaterfield_enable_trash'] ) {
						$bin_action = 'trash';
						if ( isset( $row_obj['pandarf_trash'] ) && 1 === (int) $row_obj['pandarf_trash'] ) {
							$trash_btn_css = 'pprf-btn-trashed';
						}
					}

					if ( 0 === $options['pandarepeaterfield_enable_trash'] ) {
						$bin_action    = 'delete';
						$trash_btn_css = 'pprf-btn-delete';
					}
				}

				echo '<ul class="pprf-redorder-list ' . esc_attr( $options['pandarepeaterfield_order_by'] ) . '">';

				$options['id'] = esc_attr( $options['id'] );
				$parent_pod_id = esc_attr( $parent_pod_id );

				$child_pod = pods( $options['pandarepeaterfield_table'] );

				$admin_columns = array(); // If apply admin columns is picked, use admin columns instead of name.
				if ( isset( $options['pandarepeaterfield_apply_admin_columns'] ) && $options['pandarepeaterfield_apply_admin_columns'] ) {
					$admin_columns = (array) pods_v( 'ui_fields_manage', $child_pod->pod_data['options'] );
				}

				if ( is_array( $entries ) ) {
					foreach ( $entries as $i => $row_obj ) {
						$bg_css        = 0 === $i % 2 ? 'pprf-purple-bg' : 'pprf-white-bg';
						$trashed_css   = 'pprf-not-trashed';
						$trash_btn_css = 'pprf-btn-not-trashed';
						$css_style     = '';
						$edit_css      = 'dashicons-edit';
						if ( 'trash' === $bin_action ) {
							if ( isset( $row_obj['pandarf_trash'] ) && 1 === (int) $row_obj['pandarf_trash'] ) {
								$trashed_css   = 'pprf-trashed';
								$trash_btn_css = 'pprf-btn-trashed';
								$css_style     = 'display:none';
								$edit_css      = 'dashicons-update';
								$bg_css        = 0 === $trash_int % 2 ? 'pprf-purple-bg' : 'pprf-white-bg';

							} else {
								$not_trashed_count ++;
								$bg_css = 0 === $not_trashed_count % 2 ? 'pprf-purple-bg' : 'pprf-white-bg';
							}
						}
						if ( 'delete' === $bin_action ) {
							$trashed_css   = '';
							$trash_btn_css = 'pprf-btn-delete';
							$css_style     = 'display:block';
						}

						$ids_in_css = $saved_table_id . '-' . $row_obj['id'] . '-' . $options['id'];
						$full_url   = $src_str . 'piframe_id=' . $parent_iframe_id . '&iframe_id=panda-repeater-edit-' . $ids_in_css . '' . $query_str . '&postid=' . $id . '&itemid=' . $row_obj['id'] . $nonce_query;

						$label_html = '';
						if ( isset( $options['pandarepeaterfield_apply_admin_columns'] ) && 1 === (int) $options['pandarepeaterfield_apply_admin_columns'] ) {

							$label_html = $this->create_label_with_admin_columns( $options['pandarepeaterfield_table'], $row_obj['id'], $child_pod );

						}
						if ( '' === $label_html ) {
							$title = '';

							if ( ! empty( self::$tables[ 'pod_' . $saved_table_id ]['name_label'] ) ) { // ID doesn't have a label, sometimes, the index field is deleted by still registered in the database, so it return an empty label.
								$title = $row_obj[ self::$tables[ 'pod_' . $saved_table_id ]['name_field'] ];
								// Integration with Simpods MVC Area Field.
								if ( isset( $child_pod->fields[ self::$tables[ 'pod_' . $saved_table_id ]['name_field'] ] ) ) {
									$title = $this->simpods_area_field_value( $child_pod->fields[ self::$tables[ 'pod_' . $saved_table_id ]['name_field'] ], $title );
								}
							}
							$title = apply_filters( 'pprf_item_title', $title, $saved_table_id, $row_obj['id'], $id, $options['id'] );
							$title = substr( preg_replace( '/\[.*?\]/is', '', wp_strip_all_tags( $title ) ), 0, 80 ) . pprf_check_media_in_content( $title );

							$label_html = '<strong>ID:</strong> ' . esc_html( $row_obj['id'] ) . '<strong> ' . self::$tables[ 'pod_' . $saved_table_id ]['name_label'] . ':</strong> ' . $title;
						}

						echo '
						<li data-id="' . esc_attr( $row_obj['id'] ) . '" class="' . esc_attr( $trashed_css ) . '" id="li-' . esc_attr( $ids_in_css ) . '" style="' . esc_attr( $css_style ) . '">
						';

						echo '
						<div class="pprf-row pprf-left ">
							<div class="w100 pprf-left" id="pprf-row-brief-' . esc_attr( $ids_in_css ) . '">
								<div class="pprf-left pd8 pprf-left-col ' . esc_attr( $bg_css ) . '">' . wp_kses( $label_html, $panda_pods_repeater_field->allowed_html_tags ) . '</div>
								<div class="button pprf-right-col center pprf-trash-btn ' . esc_attr( $trash_btn_css ) . '" data-podid="' . esc_attr( $parent_pod_id ) . '"  data-postid="' . esc_attr( $id ) . '"  data-tb="' . esc_attr( $saved_table_id ) . '"  data-itemid="' . esc_attr( $row_obj['id'] ) . '"  data-userid="' . esc_attr( $current_user->ID ) . '"  data-iframe_id="panda-repeater-edit-' . esc_attr( $ids_in_css ) . '"  data-poditemid="' . esc_attr( $options['id'] ) . '" data-target="' . esc_attr( $ids_in_css ) . '" >
									<span class="dashicons dashicons-trash pdt6 mgb0 "></span>
									<div id="panda-repeater-trash-' . esc_attr( $ids_in_css ) . '-loader" class="pprf-left hidden mgl5">
										<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 loading pprf-left"/>
									</div>
								</div>
								<div class="button pprf-right-col center pprf-save-btn" data-podid="' . esc_attr( $parent_pod_id ) . '"  data-postid="' . esc_attr( $id ) . '"  data-tb="' . esc_attr( $saved_table_id ) . '"  data-itemid="' . esc_attr( $row_obj['id'] ) . '"  data-userid="' . esc_attr( $current_user->ID ) . '"  data-iframe_id="panda-repeater-edit-' . esc_attr( $ids_in_css ) . '"  data-poditemid="' . esc_attr( $options['id'] ) . '" data-target="' . esc_attr( $ids_in_css ) . '" >
									<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/save-icon-tran.png' ) . '" class="pprf-save-icon  mgt8 mgb2"/>	
									<div id="panda-repeater-save-' . esc_attr( $ids_in_css ) . '-loader" class="pprf-left hidden mgl5">
										<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left"/>		
									</div>
								</div>													
								<div class="button pprf-edit pprf-row-load-iframe alignright pprf-right-col center pprf-edit-btn" data-target="' . esc_attr( $ids_in_css ) . '" data-url="' . esc_url( $full_url ) . '">
									<span class="dashicons ' . esc_attr( $edit_css ) . ' pdt8 mgb0 pprf-edit-span"></span>
									<div id="panda-repeater-edit-' . esc_attr( $ids_in_css ) . '-loader" class="pprf-left hidden mgl5">
										<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left"/>
									</div>	
								</div>
							</div>
							<div>
								<iframe id="panda-repeater-edit-' . esc_attr( $ids_in_css ) . '" name="panda-repeater-edit-' . esc_attr( $ids_in_css ) . '" frameborder="0" src="" scrolling="no" style="display:none;" class="panda-repeater-iframe w100">
								</iframe>
								<div id="panda-repeater-edit-expand-' . esc_attr( $ids_in_css ) . '" class="w100 pprf-left center pdt3 pdb3 pprf-expand-bar pprf-edit-expand" data-target="' . esc_attr( $ids_in_css ) . '" style="display:none;">' . esc_html__( 'Content missing? Click here to expand', 'panda-pods-repeater-field' ) . '</div>
							</div>
						</div>';
						echo '
						</li>
						';

					}
				}
				echo '</ul>';

				$bg_css = 'pprf-white-bg' === $bg_css ? 'pprf-purple-bg' : 'pprf-white-bg';
				echo '</div>';
				echo '<div id="next-bg" data-bg="' . esc_attr( $bg_css ) . '"></div>';
				echo '<div id="panda-repeater-fields-' . esc_attr( $saved_table_id ) . '-' . esc_attr( $options['id'] ) . '-loader" class="center hidden w100 mgb10 pprf-left">';
				echo '<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class=""/>';
				echo '</div>';
				if ( is_numeric( $id ) ) {
					$token = $id;
				} else {
					// Depreciated: don't show if the parent post is not created. Create a token if adding a new parent item, which will be used to identify which child item to update after saving the parent item. For very early versions only.
					$token = esc_attr( time() . '_' . $saved_table_id . '_' . $options['id'] . '_' . $current_user->ID . '_pandarf' );
				}
				$ids_in_css = esc_attr( $saved_table_id . '-' . $options['id'] ); // One less id compared to the added ones.

				$full_url   = esc_url( $src_str . 'piframe_id=' . $parent_iframe_id . '&iframe_id=panda-repeater-add-new-' . $ids_in_css . '' . $query_str . '&postid=' . $token . $nonce_query );
				$hidden_css = '';
				if ( $limited && count( $entries ) === (int) $options['pandarepeaterfield_entry_limit'] ) {
					$hidden_css = 'hidden';
				}
				$add_new_html =
				'<div class="pprf-row pprf-left mgb8 ' . esc_attr( $hidden_css ) . '" id="' . esc_attr( $repeater_field_id ) . '-add-new">
					<div class="w100 pprf-left">
						<div class="pprf-left pd8 pprf-left-col pprf-grey-bg "><strong>Add New ' . esc_html( get_the_title( $options['id'] ) ) . '</strong></div>
						<div class="button pprf-right-col center pprf-trash-btn" data-target="' . esc_attr( $trash_btn_css ) . '" >
						</div>									

						<div class="button pprf-right-col center pprf-save-btn pprf-save-new-btn alignright " data-podid="' . esc_attr( $parent_pod_id ) . '"  data-postid="' . esc_attr( $id ) . '"  data-tb="' . esc_attr( $saved_table_id ) . '" data-userid="' . esc_attr( $current_user->ID ) . '"  data-iframe_id="panda-repeater-edit-' . esc_attr( $ids_in_css ) . '"  data-poditemid="' . esc_attr( $options['id'] ) . '" data-target="' . esc_attr( $ids_in_css ) . '" >
							<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/save-icon-tran.png' ) . '" class="pprf-save-icon  mgt8 mgb2"/>	
							<div id="panda-repeater-save-' . esc_attr( $ids_in_css ) . '-loader" class="pprf-left hidden mgl5">
								<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left"/>			
							</div>
						</div>
						<div id="pprf-row-brief-' . esc_attr( $ids_in_css ) . '" class="alignright pprf-right-col button pprf-add pprf-row-load-iframe pprf-add " data-target="' . esc_attr( $ids_in_css ) . '" data-url="' . esc_attr( $full_url ) . '">
							<span class="dashicons dashicons-edit pdt8 mgb0 "></span>
							<div id="panda-repeater-add-new-' . esc_attr( $ids_in_css ) . '-loader" class="pprf-left hidden mgl5">
								<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left"/>
							</div>	
						</div>																		
						<iframe id="panda-repeater-add-new-' . esc_attr( $ids_in_css ) . '" name="panda-repeater-add-new-' . esc_attr( $ids_in_css ) . '" frameborder="0" src="" scrolling="no" style="display:none;" class="panda-repeater-iframe w100" >
						</iframe>
						<div id="panda-repeater-add-new-expand-' . esc_attr( $ids_in_css ) . '" class="w100 pprf-left center pdt3 pdb3 pprf-expand-bar pprf-add-expand" data-target="' . esc_attr( $ids_in_css ) . '"  style="display:none;">' . esc_html__( 'Content missing? Click here to expand', 'panda-pods-repeater-field' ) . '</div>
					</div>
				 </div>';

				if ( is_numeric( $id ) && ! empty( $id ) ) {

					echo wp_kses(
						$add_new_html,
						$panda_pods_repeater_field->allowed_html_tags
					);
				}
				echo '<div id="panda-repeater-trash-info-' . esc_attr( $ids_in_css ) . '" data-enable-trash="' . esc_attr( $options['pandarepeaterfield_enable_trash'] ) . '"  style="display:none;"/></div>';
				echo '<input type="hidden" name="' . esc_attr( $repeater_field_id ) . '-entry-limit" id="' . esc_attr( $repeater_field_id ) . '-entry-limit" value="' . esc_attr( $options['pandarepeaterfield_entry_limit'] ) . '">';
				echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $token ) . '">';
				if ( is_numeric( $options['pandarepeaterfield_entry_limit'] ) && $options['pandarepeaterfield_entry_limit'] > 0 ) {
					echo '<div class="pprf-left w100"><small>Max ' . esc_html( get_the_title( $options['id'] ) . ' - ' . $options['pandarepeaterfield_entry_limit'] ) . '</small></div>';
				}
				if ( isset( $options['pandarepeaterfield_enable_load_more'] ) && $options['pandarepeaterfield_enable_load_more'] && ! $limited ) {
					echo '<div class="pprf-load-more-wrap w100 pprf-left"  id="pprf-load-more-wrap-' . esc_attr( $ids_in_css ) . '">
							<select class="pprf-left pprf-select mgr5 panda-repeater-to-load" name="panda-repeater-to-load" > 
								<option value="append_to">' . esc_html__( 'Append to', 'panda-pods-repeater-field' ) . '</option>
								<option value="replace">' . esc_html__( 'Replace', 'panda-pods-repeater-field' ) . '</option>								
							</select> 							
							<label class="pprf-left pdt2 mgr5" for="panda-repeater-amount"> ' . esc_html__( 'the list with', 'panda-pods-repeater-field' ) . '</label> 
							<input name="panda-repeater-amount" id="panda-repeater-amount-' . esc_attr( $ids_in_css ) . '" value="' . esc_attr( intval( $options['pandarepeaterfield_initial_amount'] ) ) . '" class="pprf-left pprf-input mgr5" type="number" step="1" min="1"  autocomplete="off"/> 
							<label class="pprf-left pdt2 mgr5" for="panda-repeater-start-from">' . esc_html__( 'new items from item', 'panda-pods-repeater-field' ) . '</label>
							<input name="panda-repeater-start-from" id="panda-repeater-start-from-' . esc_attr( $ids_in_css ) . '" value="' . esc_attr( intval( $options['pandarepeaterfield_initial_amount'] ) ) . '" class="pprf-left pprf-input mgr5"  type="number" step="1" min="0" autocomplete="off" title="' . esc_attr__( 'Start from 0', 'panda-pods-repeater-field' ) . '"/>  
							<div id="panda-repeater-load-more-button-' . esc_attr( $ids_in_css ) . '" class="pprf-left pprf-load-more-btn mgr5" data-target="' . esc_attr( $ids_in_css ) . '" data-podid="' . esc_attr( $parent_pod_id ) . '"  data-postid="' . esc_attr( $id ) . '"  data-tb="' . esc_attr( $saved_table_id ) . '" data-userid="' . esc_attr( $current_user->ID ) . '"  data-iframe_id="panda-repeater-edit-' . esc_attr( $ids_in_css ) . '"  data-poditemid="' . esc_attr( $options['id'] ) . '" data-cptitle="' . esc_attr( get_the_title( $options['id'] ) ) . '" data-enable-trash="' . esc_attr( $options['pandarepeaterfield_enable_trash'] ) . '" data-order="' . esc_attr( $options['pandarepeaterfield_order'] ) . '" data-order-by="' . esc_attr( $options['pandarepeaterfield_order_by'] ) . '" />' . esc_html__( 'Load', 'panda-pods-repeater-field' ) . '</div>
							<label class="pprf-left pdt2 mgr5">' . esc_html__( ' | Total items:', 'panda-pods-repeater-field' ) . ' ' . esc_html( $count_int ) . '</label>
							<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="mgl8 pprf-left pprf-ajax-img mgt13 mgr5" style="display:none;"/>
							<div class="pprf-load-more-report"></div>
							
						  </div> ';
				}
				if ( isset( $options['pandarepeaterfield_order_by'] ) && ! empty( $options['pandarepeaterfield_order_by'] ) && isset( $options['pandarepeaterfield_display_order_info'] ) && $options['pandarepeaterfield_display_order_info'] ) {
					echo '<div class="pprf-order-info pdt5 w100 pprf-left" id="pprf-order-info-' . esc_attr( $ids_in_css ) . '">
						  ' . esc_html( $order_info ) . '	
						  </div>';
				}
			} else {
				echo esc_html__( 'No Advanced Content Type Table Selected', 'panda-pods-repeater-field' );
			}
		}

	}

	/**
	 * Create item labels
	 *
	 * @param string         $pod_name The pod name.
	 * @param int            $item_id The item id.
	 * @param boolean|object $child_pod The child pod.
	 *
	 * @return string $label_html The label with some HTML code.
	 */
	public function create_label_with_admin_columns( $pod_name, $item_id, $child_pod = false ) {
		$is_id      = false;
		$label_html = '';
		if ( ! $child_pod ) {
			$child_pod = pods( $pod_name );
		}
		$admin_columns = (array) pods_v( 'ui_fields_manage', $child_pod->pod_data['options'] );

		foreach ( $admin_columns as $admin_column_name ) {
			if ( 'id' === strtolower( $admin_column_name ) ) {
				$is_id = true;
				continue;
			}

			$column_value = pods_field( $pod_name, $item_id, $admin_column_name );

			// Integration with Simpods MVC Area Field.
			if ( isset( $child_pod->fields[ $admin_column_name ] ) ) {
				if ( 'pick' === $child_pod->fields[ $admin_column_name ]['type'] ) {
					if ( 'user' === $child_pod->fields[ $admin_column_name ]['pick_object'] ) {
						$column_value = $column_value['display_name'];
					} else {
						// If it is custom relationship, display the labels.
						if ( 'custom-simple' === $child_pod->fields[ $admin_column_name ]['pick_object'] && '' !== trim( $child_pod->fields[ $admin_column_name ]['options']['pick_custom'] ) ) {
							$pick_customs = explode( PHP_EOL, $child_pod->fields[ $admin_column_name ]['options']['pick_custom'] );

							if ( 'single' === $child_pod->fields[ $admin_column_name ]['options']['pick_format_type'] ) {
								foreach ( $pick_customs as $pick_custom ) {
									if ( 0 === strpos( $pick_custom, $column_value . '|' ) ) {
										$pick_custom_details = explode( '|', $pick_custom );
										$column_value        = $pick_custom_details[1];
										break;
									}
								}
							} else {

								$first_column_value = $column_value;
								if ( is_array( $column_value ) ) {
									foreach ( $column_value as $column_value_item ) {
										$column_value_item_found = false;
										foreach ( $pick_customs as $pick_custom ) {
											if ( 0 === strpos( $pick_custom, $column_value_item . '|' ) ) {
												$pick_custom_details     = explode( '|', $pick_custom );
												$first_column_value      = $pick_custom_details[1];
												$column_value_item_found = true;
												break;
											}
										}
										if ( $column_value_item_found ) {
											break;
										}
									}

									if ( count( $column_value ) > 1 ) { // More than one, add three dots.
										$column_value = $first_column_value . '...';
									} else {
										$column_value = $first_column_value;
									}
								}
							}
						}
					}
				}
				if ( 'simpodsareafield' === $child_pod->fields[ $admin_column_name ]['type'] ) {
					$column_value = $this->simpods_area_field_value( $child_pod->fields[ $admin_column_name ], $column_value );
				}
			}
			if ( is_string( $column_value ) || is_numeric( $column_value ) ) {
				$label_html .= '<strong>' . esc_html( $child_pod->fields[ $admin_column_name ]['label'] ) . ':</strong> ' . esc_html( substr( preg_replace( '/\[.*?\]/is', '', wp_strip_all_tags( $column_value ) ), 0, 80 ) ) . pprf_check_media_in_content( $column_value );
			}
		}

		if ( $is_id ) {
			$label_html = '<strong>ID:</strong> ' . esc_html( $item_id ) . ' ' . $label_html;
		}
		return $label_html;
	}
	/**
	 * Get the data from the field, run when loading an area
	 *
	 * @param string       $name The name of the field.
	 * @param string|array $value The value of the field.
	 * @param array        $options The options of the field.
	 * @param array        $pod The pod of the field.
	 * @param int          $id The id of the field.
	 * @param boolean      $in_form Is it in a form.
	 *
	 * @return array Array of possible field data
	 *
	 * @since 1.0
	 */
	public function data( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {

		return (array) $value;
	}

	/**
	 * Build regex necessary for JS validation
	 *
	 * @param mixed  $value The value of the field.
	 * @param string $name The name of the field.
	 * @param array  $options The options of the field.
	 * @param string $pod The pod of the field.
	 * @param int    $id The id of the field.
	 *
	 * @return bool
	 * @since 1.0
	 */
	public function regex( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		return false;
	}

	/**
	 * Validate a value before it's saved
	 *
	 * @param mixed  $value The value of the field.
	 * @param string $name The name of the field.
	 * @param array  $options The options of the field.
	 * @param array  $fields Fields.
	 * @param array  $pod The pod of the field.
	 * @param int    $id The id of the field.
	 * @param array  $params Other parameters.
	 *
	 * @return bool
	 * @since 1.0
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		return true;
	}

	/**
	 * Change the value or perform actions after validation but before saving to the DB
	 *
	 * @param mixed  $value The value of the field.
	 * @param int    $id The id of the field.
	 * @param string $name The name of the field.
	 * @param array  $options The options of the field.
	 * @param array  $fields Fields.
	 * @param array  $pod The pod of the field.
	 * @param array  $params Other parameters.
	 *
	 * @return mixed
	 * @since 1.0
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		return $value;
	}

	/**
	 * Save the value to the DB
	 *
	 * @param mixed  $value The value of the field.
	 * @param int    $id The id of the field.
	 * @param string $name The name of the field.
	 * @param array  $options The options of the field.
	 * @param array  $fields Fields.
	 * @param array  $pod The pod of the field.
	 * @param array  $params Other parameters.
	 *
	 * @return bool|void Whether the value was saved
	 *
	 * @since 1.0.0
	 */
	public function save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		return null;
	}

	/**
	 * Perform actions after saving to the DB
	 *
	 * @param mixed  $value The value of the field.
	 * @param int    $id The id of the field.
	 * @param string $name The name of the field.
	 * @param array  $options The options of the field.
	 * @param array  $fields Fields.
	 * @param array  $pod The pod of the field.
	 * @param array  $params Other parameters.
	 *
	 * @since 1.0
	 */
	public function post_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

	}

	/**
	 * Perform actions before deleting from the DB
	 *
	 * @param int    $id The id of the field.
	 * @param string $name The name of the field.
	 * @param null   $options The options of the field.
	 * @param string $pod The pod of the field.
	 *
	 * @since 1.0
	 */
	public function pre_delete( $id = null, $name = null, $options = null, $pod = null ) {

	}

	/**
	 * Delete the value from the DB
	 *
	 * @param int    $id The id of the field.
	 * @param string $name The name of the field.
	 * @param null   $options The options of the field.
	 * @param string $pod The pod of the field.
	 *
	 * @since 1.0.0
	 */
	public function delete( $id = null, $name = null, $options = null, $pod = null ) {

	}

	/**
	 * Perform actions after deleting from the DB
	 *
	 * @param int    $id The id of the field.
	 * @param string $name The name of the field.
	 * @param null   $options The options of the field.
	 * @param string $pod The pod of the field.
	 *
	 * @since 1.0
	 */
	public function post_delete( $id = null, $name = null, $options = null, $pod = null ) {

	}

	/**
	 * Customize the Pods UI manage table column output
	 *
	 * @param int    $id The id of the field.
	 * @param mixed  $value The value of the field.
	 * @param string $name The name of the field.
	 * @param array  $options The options of the field.
	 * @param array  $fields Fields.
	 * @param array  $pod The pod of the field.
	 *
	 * @since 1.0
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		return $value;
	}
	/**
	 * Called by panda-pods-repeater-field.php, to update the relationship between the child and the parent
	 *
	 * @param array   $pieces The data for saving.
	 * @param boolean $is_new_item Is it a new item.
	 * @param int     $id The id of the field.
	 *
	 * @since 29/01/2016
	 *
	 * @since 1.0.0
	 */
	public function pods_post_save( $pieces, $is_new_item, $id ) {

		global $wpdb, $current_user;

		$db_cla     = new Panda_Pods_Repeater_Field_DB();
		$tables_arr = $db_cla->get_tables();

		$parent_table_name = '';
		$query_arr         = array();

		// Admin_pprf_duplicate uses Pods duplicate method which trigger the post save as well, so it  messes some pandarf_parent_post_ids. It must be stopped if it is from admin_pprf_duplicate.
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$http_referer = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );

			$referer_variables = wp_parse_url( $http_referer );

			parse_str( $referer_variables['query'], $query_arr );
			if ( ! isset( $query_arr['podid'] ) || ! isset( $query_arr['postid'] ) || ! isset( $query_arr['poditemid'] ) || ! isset( $query_arr['tb'] ) ) {
				return $pieces;
			}
			if ( ! is_numeric( $query_arr['podid'] ) || ! is_numeric( $query_arr['postid'] ) || ! is_numeric( $query_arr['poditemid'] ) || ! is_numeric( $query_arr['tb'] ) ) {
				return $pieces;
			}
			$security_checked = false;
			if ( isset( $query_arr['pprf_nonce'] ) ) {
				$security = sanitize_text_field( wp_unslash( $query_arr['pprf_nonce'] ) );
				if ( wp_verify_nonce( $security, 'load-pprf-page' ) ) {
					$security_checked = true;
				}
			}

			if ( true === $security_checked ) {
				if ( ! isset( $_POST['action'] ) || ( isset( $_POST['action'] ) && 'admin_pprf_duplicate' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) ) {

					if ( ! empty( $query_arr ) ) {
						/**
						 * Don't need to check array_key_exists( 'pod_' . $query_arr['podid'], $tables_arr ) as meta storage tabe is not in the list.
						 * update panda keys after saving a child.
						 */
						$now = gmdate( 'Y-m-d H:i:s' );

						$query        = $wpdb->prepare( 'SELECT * FROM `' . $wpdb->posts . '`  WHERE `ID` = %d LIMIT 0, 1', array( $query_arr['tb'] ) );
						$child_tables = $wpdb->get_results(
							// phpcs:ignore
							$query, ARRAY_A 
						); // db call ok. no cache ok.
						$parent_table_name = $child_tables[0]['post_name'];
						$table_full_name   = $wpdb->prefix . 'pods_' . $parent_table_name;
						$table_full_name   = esc_sql( sanitize_text_field( wp_unslash( $table_full_name ) ) );
						$query             = $wpdb->prepare(
							// phpcs:ignore
							'SELECT * FROM `' . $table_full_name . '` WHERE `id` = %d LIMIT 0, 1', array( $id ) 
						);
						$item_arr = $wpdb->get_results(
							// phpcs:ignore
							$query, ARRAY_A 
						); // db call ok. no cache ok.

						if ( is_array( $item_arr ) && count( $item_arr ) > 0 ) {

							$values_arr = array();

							$update_query = ' `pandarf_parent_pod_id` = %d';
							array_push( $values_arr, $query_arr['podid'] );
							$update_query .= ', `pandarf_parent_post_id` = %d';
							array_push( $values_arr, $query_arr['postid'] );
							$update_query .= ', `pandarf_pod_field_id` = %d';
							array_push( $values_arr, $query_arr['poditemid'] );
							$update_query .= ', `pandarf_modified` = %s';
							array_push( $values_arr, $now );
							$update_query .= ', `pandarf_modified_author` = %d';
							array_push( $values_arr, $current_user->ID );

							// Order.
							if ( $is_new_item ) {
								pprf_updated_tables( $table_full_name, 'remove' );
								if ( false === pprf_updated_tables( $table_full_name ) ) {
									$db_cla->update_columns( $parent_table_name );
								}

								$query = $wpdb->prepare( 'SELECT MAX(`pandarf_order`) AS last_order FROM %s WHERE `pandarf_parent_pod_id` = %d AND `pandarf_parent_post_id` = %d AND `pandarf_pod_field_id` = %d', array( $table_full_name, $query_arr['podid'], $query_arr['postid'], $query_arr['poditemid'] ) );

								$order_arr = $wpdb->get_results(
									// phpcs:ignore
									$query, ARRAY_A 
								); // db call ok. no cache ok.
								$update_query .= ', `pandarf_order` = %d';
								array_push( $values_arr, ( $order_arr[0]['last_order'] + 1 ) );
							}

							// If first time update.
							if ( '' === $item_arr[0]['pandarf_created'] || '0000-00-00 00:00:00' === $item_arr[0]['pandarf_created'] ) {
								$update_query .= ', `pandarf_created` = %s';
								array_push( $values_arr, $now );
							}
							if ( '' === $item_arr[0]['pandarf_author'] || 0 === (int) $item_arr[0]['pandarf_author'] ) {
								$update_query .= ', `pandarf_author` = %d';
								array_push( $values_arr, $current_user->ID );
							}
							array_push( $values_arr, $id );

							$query = $wpdb->prepare(
								// phpcs:ignore
								'UPDATE  `' . $table_full_name . '` SET ' . $update_query . ' WHERE id = %d', $values_arr 
							);

							$items_bln = $wpdb->query(
								// phpcs:ignore
								$query, ARRAY_A 
							); // db call ok. no cache ok.
						}
					}
				}
			}
		}
		// Find the panda field related tables.
		$related_tables = array();
		if ( count( $query_arr ) > 0 ) {
			$related_tables = array(
				'parent' => '',
				'child'  => '',
			);

			if ( isset( $tables_arr[ 'pod_' . $query_arr['podid'] ] ) ) {
				$related_tables['parent'] = $tables_arr[ 'pod_' . $query_arr['podid'] ];
			}
			if ( isset( $tables_arr[ 'pod_' . $query_arr['tb'] ] ) ) {
				$related_tables['child'] = $tables_arr[ 'pod_' . $query_arr['tb'] ];
			}
		}

		$pieces = apply_filters( 'pprf_filter_pods_post_save', $pieces, $is_new_item, $id, $query_arr, $related_tables );

		do_action( 'pprf_action_pods_post_save', $pieces, $is_new_item, $id, $query_arr, $related_tables );

		return $pieces;

	}
	/**
	 * Called by class-panda-pods-repeater-field.php
	 *
	 * @param object $item The item to delete.
	 * @param array  $pod The pod.
	 * @param array  $pods_api The pods API.
	 *
	 * @since 01/12/2016
	 *
	 * @since 1.0.0
	 */
	public function pods_post_delete( $item, $pod, $pods_api ) {

		global $wpdb, $current_user;

		$db_cla           = new Panda_Pods_Repeater_Field_DB();
		$for_repeater_pod = array(
			'pod_name' => $item->pod,
			'item_id'  => $item->id,
		);
		$db_cla->delete_item_descendants( $for_repeater_pod );

		$item = apply_filters( 'pprf_filter_pods_post_delete', $item, $pod, $pods_api );

		do_action( 'pprf_action_pods_post_delete', $item, $pod, $pods_api );

		return $item;
	}
	/**
	 * If a table is set as a field, check and update the table's fields
	 *
	 * @param array  $pod_data Pod data.
	 * @param object $obj From Pod.
	 */
	public function field_table_fields( $pod_data, $obj ) {

		foreach ( $pod_data['fields'] as $field_data ) {
			if ( $field_data['type'] === self::$type && isset( $field_data['pandarepeaterfield_table'] ) ) {
				$db_cla      = new Panda_Pods_Repeater_Field_DB();
				$saved_table = $field_data['pandarepeaterfield_table'];
				$child_pods  = explode( '_', $saved_table );
				// If saved as pod_num, version < 1.2.0.
				if ( 2 === count( $child_pods ) && 'pod' === $child_pods[0] && is_numeric( $child_pods[1] ) ) {
					$pods_tables = $this->get_pods_tables();

					/**
					 * Add the columns to the table.
					 *
					 * @example $pods_tables[ $field_data['pandarepeaterfield_table'] ] ->  $pods_tables['pod_16']
					 */
					if ( isset( $pods_tables[ $saved_table ] ) ) {

						$tables = $db_cla->update_columns( $pods_tables[ $saved_table ] );

					}
				} else {
					$pods_tables = $this->get_pods_tables( 2 );

					if ( in_array( $saved_table, $pods_tables, true ) ) {

						$tables = $db_cla->update_columns( $saved_table );
					}
				}
			}
		}

	}

	/**
	 * Save tables
	 *
	 * @param int $type_index 0: table_num 1 : pod_table 2 : table.
	 */
	public function get_pods_tables( $type_index = 0 ) {

		global $wpdb, $current_user;

		if ( ! defined( 'PPRF_ALL_TABLES' ) ) {
			$pprf_db = new Panda_Pods_Repeater_Field_DB();
			$tables  = $pprf_db->get_tables();
			define( 'PPRF_ALL_TABLES', wp_json_encode( $tables ) );

		} else {

			$tables = (array) json_decode( PPRF_ALL_TABLES );

		}
		$pod_tables = array();
		if ( is_array( $tables ) ) {
			foreach ( $tables as $table_key => $table_data ) {
				$table_data           = (array) $table_data;
				$tables[ $table_key ] = $table_data;
				if ( 'wp' !== $table_data['type'] ) {

					if ( 0 === $type_index ) {
						$pod_tables[ $table_key ] = $table_data['pod'];
					}
					if ( 1 === $type_index ) {
						$pod_tables[ 'pod_' . $table_data['pod'] ] = $table_data['pod'];
					}
					if ( 2 === $type_index ) {
						$pod_tables[ $table_data['pod'] ] = $table_data['pod'];
					}
				}
			}
		}

		self::$tables = $tables;

		if ( ! defined( 'PPRF_PODS_TABLES' ) ) {
			define( 'PPRF_PODS_TABLES', wp_json_encode( $pod_tables ) );
		}

		return $pod_tables;
	}

	/**
	 * Fetch the first item in the simpodsareafield
	 *
	 * @param array  $field_details The field details.
	 * @param string $item_value The item value.
	 */
	public function simpods_area_field_value( $field_details, $item_value ) {

		if ( ! defined( 'SIMPODS_VERSION' ) || is_array( $item_value ) || ! isset( $field_details['type'] ) || 'simpodsareafield' !== $field_details['type'] ) {
			return $item_value;
		}

		$ids = explode( ',', $item_value );
		// Simpods area field only store numbers.
		if ( ! is_numeric( $ids[0] ) ) {
			return $item_value;
		}
		global $funs_cla, $funs;

		$fields = array(
			'id' => $ids[0],
		);
		$attrs  = array(
			'target_tb' => 'pods_' . $field_details['options']['simpodsareafield_table'],
			'limit'     => 1,
		);
		$values = array();
		if ( method_exists( $funs, 'simpods_select' ) ) { // After Simpods 3.0.0 variable names update.
			$values = $funs->simpods_select( $fields, $attrs );
		} elseif ( method_exists( $funs_cla, 'simpods_select' ) ) { // Since Simpods 3.0.0.
			$values = $funs_cla->simpods_select( $fields, $attrs );
		} elseif ( method_exists( $funs_cla, 'simpods_select_fn' ) ) { // before Simpods 3.0.0.
			$values = $funs_cla->simpods_select_fn( $fields, $attrs );
		}

		if ( ! empty( $values ) && isset( $values[0]['sp_title'] ) ) {
			return $values[0]['sp_title'];
		} else {
			return $item_value;
		}

	}
}
