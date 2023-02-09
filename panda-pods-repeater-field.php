<?php
/**
 * Panda Pods Repeater Field
 *
 * @package   panda_pods_repeater_field
 * @author    Dongjie Xu
 * @copyright Dongjie Xu
 * @license   GPL v2 or later
 *
 * Plugin Name: Panda Pods Repeater Field
 * Plugin URI: https://wordpress.org/plugins/panda-pods-repeater-field/
 * Description: Panda Pods Repeater Field is a plugin for Pods Framework. The beauty of it is that it is not just a repeater field. It is a quick way to set up a relational database and present the data on the same page. It takes the advantage of Pods table storage, so you don’t need to worry that the posts and postmeta data table may expand dramatically and slow down the page loading. This plugin is compatible with Pods Framework 2.6.1 or later. To download Pods Framework, please visit http://pods.io/. After each update, please clear the cache to make sure the CSS and JS are updated. Usually, Ctrl + F5 will do the trick.
 * Version: 1.5.9
 * Author: Dongjie Xu
 * Author URI: http://www.multimediapanda.co.uk/
 * Text Domain: panda-pods-repeater-field
 * Domain Path: /languages
 */

/**
 * The plugin initial file
 */

/**
 * Don't call the file directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Define constants
 *
 * @since 1.0.0
 */
define( 'PANDA_PODS_REPEATER_SLUG', plugin_basename( __FILE__ ) );
define( 'PANDA_PODS_REPEATER_URL', plugin_dir_url( __FILE__ ) );
define( 'PANDA_PODS_REPEATER_DIR', plugin_dir_path( __FILE__ ) );
define( 'PANDA_PODS_REPEATER_VERSION', '1.5.9' );

/**
 * To emable deleting item descendants. Add it to the configure.php file. Only do it to if you have daily backup and backup before deleting an item. The plugin author is not responsible for any data loss.
 *
 * @example define( 'PANDA_PODS_REPEATER_DELETE_ITEM_DESCENDANTS', true );
 */

require_once PANDA_PODS_REPEATER_DIR . '/class-panda-pods-repeater-field.php';

add_action( 'plugins_loaded', 'panda_repeater_safe_activate' );
/**
 * Initialize class, if Pods is active.
 */
function panda_repeater_safe_activate() {
	define( 'PANDA_PODS_REPEATER_NONCE', wp_create_nonce( 'load-pprf-page' ) );
	if ( function_exists( 'pods_register_field_type' ) ) {
		pods_register_field_type( 'pandarepeaterfield', PANDA_PODS_REPEATER_DIR . 'classes/class-podsfield-pandarepeaterfield.php' );
	}
	// plugin is activated.
	add_action( 'admin_menu', 'pprf_add_admin_menu' );
}

/**
 * Adds a menu page to the WP admin, then hide it for the iframe
 *
 * @return mixed
 */
function pprf_add_admin_menu() {

	$page = add_menu_page( __( 'Panda Pods Repeater Field', 'panda-pods-repeater-field' ), 'Panda Pods Repeater Field', 'edit_posts', 'panda-pods-repeater-field', 'pprf_main_page' );
}
/**
 * Include the field file
 */
function pprf_main_page() {
	include_once PANDA_PODS_REPEATER_DIR . 'fields/pandarepeaterfield.php';
}

/**
 * Remove Unwanted Admin Menu Items
 *
 * @link https://managewp.com/wordpress-admin-sidebar-remove-unwanted-items
 **/
function pprf_remove_admin_menu_items() {
	$remove_menu_items = array( __( 'Links' ) );
	global $menu;
	end( $menu );
	while ( prev( $menu ) ) {
		$item = explode( ' ', $menu[ key( $menu ) ][0] );
		if ( in_array( null !== $item[0] ? $item[0] : '', $remove_menu_items, true ) ) {
			unset( $menu[ key( $menu ) ] );}
	}
}

/**
 * Include the field file
 */
function pprf_load_field() {
	include_once PANDA_PODS_REPEATER_DIR . 'fields/pandarepeaterfield.php';
	die();
}
/**
 * Initialize class, if Pods is active.
 */
if ( is_admin() ) {
	add_action( 'admin_init', 'check_some_other_plugin', 20 );
} else {
	add_action( 'init', 'check_some_other_plugin', 20 );
}
/**
 * If Pods is active, create an instance of the repeater field.
 */
function check_some_other_plugin() {

	if ( defined( 'PODS_VERSION' ) ) {
		$GLOBALS['panda_pods_repeater_field'] = Panda_Pods_Repeater_Field::init();
	}
}

add_action( 'admin_notices', 'panda_repeater_admin_notice_pods_not_active' );
/**
 * Throw admin nag if Pods isn't activated.
 *
 * It will only show on the plugins page.
 */
function panda_repeater_admin_notice_pods_not_active() {

	if ( ! defined( 'PODS_VERSION' ) ) {

		// use the global pagenow so we can tell if we are on plugins admin page.
		global $pagenow;
		if ( 'plugins.php' === $pagenow ) {
			?>
			<div class="error">
				<p><?php esc_html_e( 'You have activated Panda Pods Repeater Field. Pods Framework plugin required.', 'panda-pods-repeater-field' ); ?></p>
			</div>
			<?php

		}
	}

}


add_action( 'admin_notices', 'panda_repeater_admin_notice_pods_min_version_fail' );
/**
 * Throw admin nag if Pods minimum version is not met.
 *
 * It will only show on the Pods admin page.
 */
function panda_repeater_admin_notice_pods_min_version_fail() {

	if ( defined( 'PODS_VERSION' ) ) {

		// set minimum supported version of Pods.
		$minimum_version = '2.3.18';

		// check if Pods version is greater than or equal to minimum supported version for this plugin.
		if ( version_compare( $minimum_version, PODS_VERSION ) > 0 ) {

			// create $page variable to check if we are on pods admin page.
			$page = pods_v( 'page', 'get', false, true );

			// check if we are on Pods Admin page.
			if ( 'pods' === $page ) {
				?>
				<div class="updated">
					<p><?php esc_html_e( 'Panda Pods Repeater Field requires Pods version 2.3.18 or later.', 'panda-pods-repeater-field' ); ?></p>
				</div>
				<?php

			} //endif on the right page.
		} //endif version compare.
	} //endif Pods is not active.

}

add_action( 'wp_loaded', 'pprf_translate' );
/**
 * Translations for localization
 */
function pprf_translate() {

	$strings              = array(
		'be_restored'    => esc_html__( 'It will be restored.', 'panda-pods-repeater-field' ),
		'can_recover'    => esc_html__( 'You can recover it from trash.', 'panda-pods-repeater-field' ),
		'be_deleted'     => esc_html__( 'It will be deleted permanently.', 'panda-pods-repeater-field' ),
		'you_sure'       => esc_html__( 'Are you sure?', 'panda-pods-repeater-field' ),
		'Ignore_changes' => esc_html__( 'It seems like you have made some changes in a repeater field. Ignore the changes?', 'panda-pods-repeater-field' ),
	);
	$GLOBALS['pprf_l10n'] = $strings;
}
/**
 * Extension of pods( $table, $params )
 *
 * @param string $table repeater field table.
 * @param array  $searches search repeater field table array( 'pod_id': parent pod id, 'post_id: parent post id, 'pod_field_id' pod field id ).
 * @param array  $params an array to pass into pods( $table, $params ).
 * @uses pods( $table, $param )
 */
function pandarf_pods_data( $table, $searches = array(
	'pod_id'       => '',
	'post_id'      => '',
	'pod_field_id' => '',
),
	$params = array()
) {
	if ( ! is_numeric( $searches['pod_id'] ) || ! is_numeric( $searches['post_id'] ) || ! is_numeric( $searches['pod_field_id'] ) ) {
		return array();
	}
	$files_arr = array( 'panda_pods_repeater_field_db' );

	$class_bln = true;
	$file_str  = dirname( __FILE__ ) . '/classes/' . $files_arr[0] . '.php';

	if ( file_exists( $file_str ) ) {
		$class_name = str_replace( ' ', '_', ucwords( strtolower( str_replace( '_', ' ', $files_arr[0] ) ) ) );
		include_once $file_str;

		$db_cla      = new Panda_Pods_Repeater_Field_DB();
		$table_info  = $db_cla->get_pods_tb_info( 'pods_' . $table );
		$table_short = 'pod' === $table_info['type'] ? 't' : 'd';

		$where_sql = '   `' . $table_short . '`.`pandarf_parent_pod_id`  = ' . intval( $searches['pod_id'] ) . '
					   AND `' . $table_short . '`.`pandarf_parent_post_id` = "' . intval( $searches['post_id'] ) . '"
					   AND `' . $table_short . '`.`pandarf_pod_field_id`   = ' . intval( $searches['pod_field_id'] ) . ' ';
		if ( isset( $params['where'] ) && '' !== $params['where'] ) {
			$params['where'] .= ' AND ' . $where_sql;
		} else {
			$params['where'] = $where_sql;
		}

		$pod = pods( $table, $params );

		$rows = $pod->data();

		return $rows;
	}

}

/**
 * Fetch child pod data
 *
 * @param array   $fields Search repeater field table. See the $filters in the function for the expected elements.
 * @param array   $attrs Search repeater field table. See the $defaults in the function for the expected elements.
 * @param boolean $show_query Fo developers to debug.
 *
 * @return array $items_arr;
 */
function get_pandarf_items( $fields = array(), $attrs = array(), $show_query = false ) {

	global $wpdb;

	$filters = array(
		'id'                  => '',
		'name'                => '', // the common name field used by pods.
		'child_pod_name'      => '', // repeater table name.
		'parent_pod_id'       => '', // main table pod id.
		'parent_pod_post_id'  => '', // main table post id.
		'parent_pod_field_id' => '', // main table pod Panda Pod Repeater Field id.
	);
	$filters = wp_parse_args( $fields, $filters );

	$defaults = array(
		'where'               => '', // extend where, expected to be escaped.
		'order'               => 'ASC',
		'order_by'            => 'pandarf_order',
		'group_by'            => '',
		'start'               => 0,
		'limit'               => 0,
		'count_only'          => false,
		'full_child_pod_name' => false, // if child_pod_name is a full table name, $wpdb->prefix and pods_ won't be added to the table name.
		'include_trashed'     => false,
	);
	$attrs    = wp_parse_args( $attrs, $defaults );

	$para_arr  = array();
	$where_sql = '';
	if ( is_numeric( $filters['id'] ) ) {
		$where_sql .= ' AND `id` = %d';
		array_push( $para_arr, $filters['id'] );
	}
	if ( '' !== $filters['name'] ) {
		if ( is_numeric( $filters['name'] ) ) {
			// if putting a dot at the end of an number, like 24., strpos will return false so it is treated as an integer.
			$value_type = false !== strpos( $filters['name'], '.' ) ? '%f' : '%d';
		} else {
			$value_type = '%s';
		}
		$where_sql .= ' AND `name` = ' . $value_type . '';
		array_push( $para_arr, $filters['name'] );
	}
	if ( is_numeric( $filters['parent_pod_id'] ) ) {
		$where_sql .= ' AND `pandarf_parent_pod_id` = %d';
		array_push( $para_arr, $filters['parent_pod_id'] );
	}
	if ( is_numeric( $filters['parent_pod_post_id'] ) ) {
		$where_sql .= ' AND `pandarf_parent_post_id` = %d';
		array_push( $para_arr, $filters['parent_pod_post_id'] );
	}
	if ( is_numeric( $filters['parent_pod_field_id'] ) ) {
		$where_sql .= ' AND `pandarf_pod_field_id` = %d';
		array_push( $para_arr, $filters['parent_pod_field_id'] );
	}

	$group_by = '';
	if ( '' !== $attrs['group_by'] ) {
		$group_by = 'GROUP BY( ' . esc_sql( $attrs['group_by'] ) . ' )';
	}

	$limit_sql = '';
	if ( ! empty( $attrs['limit'] ) ) {
		$limit_sql = 'LIMIT ' . esc_sql( intval( $attrs['start'] ) ) . ', ' . esc_sql( intval( $attrs['limit'] ) ) . '';
	}

	if ( false === $attrs['count_only'] ) {
		$fields_sql = ' * ';
	} else {
		$fields_sql = ' COUNT( `id` ) AS "count"';
	}

	$where_sql .= ' ' . $attrs['where'] . ' ';
	$table_str  = esc_sql( $filters['child_pod_name'] );
	if ( false === $attrs['full_child_pod_name'] ) {
		$table_str = $wpdb->prefix . 'pods_' . $table_str;
	}

	$parent_post = get_post( $filters['parent_pod_id'] );
	if ( $parent_post ) {
		$parent_pod = pods( $parent_post->post_name );
		foreach ( $parent_pod->fields as $k_str => $field ) {

			if ( is_array( $field ) ) {
				$field = (object) $field; // so it works in before and after pods 2.8.
			}
			if ( is_object( $field ) ) {
				if ( isset( $field->type ) && 'pandarepeaterfield' === $field->type && $filters['parent_pod_field_id'] === $field->id ) {

					if ( isset( $field->options['pandarepeaterfield_enable_trash'] ) && ! empty( $field->options['pandarepeaterfield_enable_trash'] ) && false === $attrs['include_trashed'] ) { // if trash enabled, also not forced to include trashed only load those not trashed.
						$where_sql .= ' AND `pandarf_trash` != 1';

					}
					if ( isset( $field->options['pandarepeaterfield_order_by'] ) && ! empty( $field->options['pandarepeaterfield_order_by'] ) ) { // different order field.
						if ( 'pandarf_order' === $attrs['order_by'] && ! empty( $field->options['pandarepeaterfield_order_by'] ) ) { // if not changed by the filter, load the saved one.
							$attrs['order_by'] = sanitize_text_field( wp_unslash( $field->options['pandarepeaterfield_order_by'] ) );
						}
					}
					if ( isset( $field->options['pandarepeaterfield_order'] ) && ! empty( $field->options['pandarepeaterfield_order'] ) ) { // different order field.
						if ( 'ASC' === $attrs['order'] ) { // if not changed by the filter, load the saved one.
							$attrs['order'] = $field->options['pandarepeaterfield_order'];
						}
					}
					break;
				}
			}
		}
	}

	$order_sql = '';
	if ( '' !== $attrs['order_by'] ) {
		if ( 'random' === $attrs['order_by'] ) {
			$order_sql = 'ORDER BY RAND()';
		} else {

			if ( 'ASC' !== $attrs['order'] ) {
				$attrs['order'] = 'DESC';
			}
			if ( 'pandarf_order' === $attrs['order_by'] ) {
				$order_sql = 'ORDER BY CAST( ' . esc_sql( $attrs['order_by'] ) . ' AS UNSIGNED ) ' . $attrs['order'] . '';
			} else {
				$order_sql = 'ORDER BY ' . esc_sql( $attrs['order_by'] ) . ' ' . $attrs['order'] . '';
			}
		}
	}
	// Find out the file type.
	$join_sql  = '';
	$child_pod = pods( $filters['child_pod_name'] );

	if ( false === pprf_updated_tables( $filters['child_pod_name'] ) ) {
		$file = dirname( __FILE__ ) . '/classes/class-panda-pods-repeater-field-db.php';
		if ( file_exists( $file ) ) {
			include_once $file;
			$db_cla = new panda_pods_repeater_field_db();
			$db_cla->update_columns( $filters['child_pod_name'] );
		}
	}

	if ( is_object( $child_pod ) && false === $attrs['count_only'] ) {
		$i = 0;
		foreach ( $child_pod->fields as $k_str => $field ) {
			if ( is_array( $field ) ) {
				$field = (object) $field;
			}
			if ( is_object( $field ) ) {

				$relate_types = array( 'user', 'post_type', 'pod', 'media' );
				if ( ( isset( $field->type ) && 'file' === $field->type ) || ( isset( $field->type ) && 'pick' === $field->type && in_array( $field->pick_object, $relate_types, true ) ) ) {

					$fields_sql .= ',(
						SELECT GROUP_CONCAT( psl' . $i . '_tb.related_item_id ORDER BY psl' . $i . '_tb.weight ASC SEPARATOR "," )
						FROM `' . $wpdb->prefix . 'podsrel` AS psl' . $i . '_tb
						WHERE psl' . $i . '_tb.pod_id = "' . $child_pod->pod_id . '" 
						AND psl' . $i . '_tb.field_id = "' . $field->id . '" 
						AND psl' . $i . '_tb.item_id = pod_tb.id
						GROUP BY psl' . $i . '_tb.item_id									
						) AS ' . $k_str;

					$i++;
				}
			}
		}
	}
	if ( count( $para_arr ) > 0 ) {
		$query = $wpdb->prepare(
			// phpcs:ignore
			'SELECT ' . $fields_sql . ' FROM `' . $table_str . '` AS pod_tb ' . $join_sql . ' WHERE 1=1 ' . $where_sql . ' ' . $group_by . ' ' . $order_sql . ' ' . $limit_sql,
			$para_arr
		);
	} else {
		$query = 'SELECT ' . $fields_sql . ' FROM `' . $table_str . '` AS pod_tb ' . $join_sql . '  WHERE 1=1 ' . $where_sql . ' ' . $group_by . ' ' . $order_sql . ' ' . $limit_sql;
	}

	if ( $show_query ) {
		echo esc_html( $query );
	}

	$items = $wpdb->get_results(
		// phpcs:ignore
		$query, 
		ARRAY_A
	); // db call ok. no-cache ok.

	return $items;
}
/**
 * Alias of get_pandarf_items
 *
 * @param array   $fields Search repeater field table. See the $filters in the function for the expected elements.
 * @param array   $attrs Search repeater field table. See the $defaults in the function for the expected elements.
 * @param boolean $show_query Fo developers to debug.
 *
 * @return array $items_arr;
 */
function pandarf_items_fn( $fields = array(), $attrs = array(), $show_query = false ) {

	return get_pandarf_items( $fields, $attrs, $show_query );
}
/**
 * Insert data to panda repeater field table
 *
 * @param array   $fields extra fields other than panda repeater fields to insert array( 'field_name' => '', 'field_name' => '' ... ).
 * @param array   $attrs search repeater field table. See $defaults in the function.
 * @param boolean $show_query Fo developers to debug.
 *
 * @return boolean $done;
 */
function pandarf_insert( $fields = array(), $attrs = array(), $show_query = false ) {

	global $wpdb, $current_user;

	$defaults = array(
		'child_pod_name'      => '', // Repeater table name.
		'parent_pod_id'       => '', // Main table pod id.
		'parent_pod_post_id'  => '', // Main table post id.
		'parent_pod_field_id' => '', // Main table pod Panda Pod Repeater Field id.
		'user_id'             => $current_user->ID, // 0, The author id.
		'full_child_pod_name' => false, // If child_pod_name is a full table name, $wpdb->prefix and pods_ won't be added to the table name.
	);

	$attrs = wp_parse_args( $attrs, $defaults );

	if ( empty( $attrs['child_pod_name'] ) || ! is_numeric( $attrs['parent_pod_id'] ) || ! is_numeric( $attrs['parent_pod_post_id'] ) || ! is_numeric( $attrs['parent_pod_field_id'] ) || ! is_numeric( $attrs['user_id'] ) ) {
		return false;
	}
	$now   = gmddate( 'Y-m-d H:i:s' );
	$table = esc_sql( $attrs['child_pod_name'] );
	if ( false === $attrs['full_child_pod_name'] ) {
		$table = $wpdb->prefix . 'pods_' . $table;
	}
	$para_arr  = array();
	$where_sql = '';
	// get the last order.
	$query = $wpdb->prepare( 'SELECT MAX( CAST(`pandarf_order` AS UNSIGNED) ) AS last_order FROM `%s` WHERE `pandarf_parent_pod_id` = %d AND `pandarf_parent_post_id` = %s AND `pandarf_pod_field_id` = %d', array( $table, $attrs['parent_pod_id'], $attrs['parent_pod_post_id'], $attrs['parent_pod_field_id'] ) );

	$order_arr = $wpdb->get_results(
		// phpcs:ignore
		$query, 
		ARRAY_A
	); // db call ok. no-cache ok.

	$order_int = count( $order_arr ) > 0 ? $order_arr[0]['last_order'] + 1 : 1;

	$pandarf_data = array(
		'pandarf_parent_pod_id'   => $attrs['parent_pod_id'],
		'pandarf_parent_post_id'  => $attrs['parent_pod_post_id'],
		'pandarf_pod_field_id'    => $attrs['parent_pod_field_id'],
		'pandarf_created'         => $now,
		'pandarf_modified'        => $now,
		'pandarf_modified_author' => $attrs['user_id'],
		'pandarf_author'          => $attrs['user_id'],
		'pandarf_order'           => $order_int,
	);

	// Insert.
	$values_arr   = array();
	$keys         = array();
	$placeholders = array();

	foreach ( $pandarf_data as $k_str => $v_str ) {
		array_push( $keys, '`' . esc_sql( $k_str ) . '`' );
		if ( is_numeric( $v_str ) ) {
			// If putting a dot at the end of an number, like 24., strpos will return false so it is treated as an integer.
			$value_type = strpos( $v_str, '.' ) !== false ? '%f' : '%d';

			array_push( $placeholders, $value_type );

		} elseif ( is_array( $v_str ) ) {
			array_push( maybe_serialize( $placeholders ), '%s' );
		} else {
			array_push( $placeholders, '%s' );
		}
		array_push( $values_arr, $v_str );
	}

	$fields_sql = join( ',', $keys );
	$vals_str   = join( ',', $placeholders );

	$query = $wpdb->prepare(
		'INSERT INTO `%s` ( 
			pandarf_parent_pod_id, 
			pandarf_parent_post_id, 
			pandarf_pod_field_id, 
			pandarf_created, 
			pandarf_modified, 
			pandarf_modified_author, 
			pandarf_author, 
			pandarf_order 
		) VALUES ( 
			%d,
			%d,
			%d,
			%s,
			%s,
			%d,
			%d,
			%d
		)',
		array(
			$table,
			$attrs['parent_pod_id'],
			$attrs['parent_pod_post_id'],
			$attrs['parent_pod_field_id'],
			$now,
			$now,
			$attrs['user_id'],
			$attrs['user_id'],
			$order_int,
		)
	);

	if ( $show_query ) {
		echo esc_html( $query );
	}
	$done = $wpdb->query(
		// phpcs:ignore
		$query 
	); // db call ok. no-cache ok.
	if ( $done ) {
		$insert_id = $wpdb->insert_id;
		// remove prefix to keep the pod table name.
		$table = ltrim( $table, $wpdb->prefix . 'pods_' );
		$pod   = pods( $table, $insert_id );

		$pod->save( $fields );
		return $insert_id;
	}
	return false;
}
/**
 * Backward compatibility
 *
 * @param array   $fields extra fields other than panda repeater fields to insert array( 'field_name' => '', 'field_name' => '' ... ).
 * @param array   $attrs search repeater field table. See $defaults in the function.
 * @param boolean $show_query Fo developers to debug.
 *
 * @return boolean $done;
 */
function pandarf_insert_fn( $fields = array(), $attrs = array(), $show_query = false ) {
	return pandarf_insert( $fields, $attrs, $show_query );
}

add_filter( 'pods_pods_field', 'pandarf_pods_field', 10, 4 );
/**
 * Filter for pods_field
 *
 * @param string $value value of the field.
 * @param array  $rows from Pods.
 * @param array  $params from Pods.
 * @param object $pods from Pods.
 *
 * @return string|number|array
 */
function pandarf_pods_field( $value, $rows, $params, $pods ) {
	global $wpdb;

	$repeaters = is_pandarf( $params->name, $pods->pod_id );

	if ( $repeaters ) {
		$saved_table = $pods->fields[ $params->name ]['options']['pandarepeaterfield_table'];
		$items_arr   = array();
		$child_pods  = explode( '_', $saved_table );
		if ( 2 === count( $child_pods ) && 'pod' === $child_pods[0] && is_numeric( $child_pods[1] ) ) {
			// Find the repeater table pod name.
			$query = $wpdb->prepare( 'SELECT `post_name` FROM `' . $wpdb->posts . '` WHERE `ID` = %d LIMIT 0, 1', array( $child_pods[1] ) );

			$items = $wpdb->get_results(
				// phpcs:ignore
				$query, 
				ARRAY_A
			); // db call ok. no-cache ok.
		} else {
			$query = $wpdb->prepare( 'SELECT `ID`, `post_name` FROM `' . $wpdb->posts . '` WHERE `post_name` = %s AND `post_type` = "_pods_pod" LIMIT 0, 1', array( $saved_table ) );

			$items = $wpdb->get_results(
				// phpcs:ignore
				$query, 
				ARRAY_A
			); // db call ok. no-cache ok.

		}

		if ( ! empty( $items ) ) {

			$attrs  = apply_filters( 'pandarf_pods_field_attrs', array(), $value, $rows, $params, $pods );
			$fields = array(
				'child_pod_name'      => $items[0]['post_name'],
				'parent_pod_id'       => $repeaters['post_parent'],
				'parent_pod_post_id'  => $pods->id(),
				'parent_pod_field_id' => $repeaters['ID'],
			);
			$fields = apply_filters( 'pandarf_pods_field_fields', $fields, $value, $rows, $params, $pods );
			$data   = get_pandarf_items(
				$fields,
				$attrs,
				false
			);
			// Check if it is a repeater field, if yes, return data.
			$data = pandarf_get_data( $data, $items[0]['post_name'] );

			return $data;
		}
	}
	return $value;
}
/**
 * Check if it is a repeater field, if yes, return data
 *
 * @param array  $data_arr data from the table row e.g. Array ( [0] => Array ( [id] => 26, [name] => hi, [repeater] => '' ) ).
 * @param string $parent_pod_name parent pod's name.
 */
function pandarf_get_data( $data_arr, $parent_pod_name ) {
	global $wpdb;

	$pods_obj = pods( $parent_pod_name );

	$pprf_data = array();
	if ( is_array( $data_arr ) && count( $data_arr ) > 0 ) {
		foreach ( $data_arr[0] as $k_str => $v_ukn ) {
			$repeaters = is_pandarf( $k_str, $pods_obj->pod_id );
			if ( $repeaters ) {
				$pprf_data[ $k_str ] = $repeaters;
			}
		}
	}

	if ( count( $pprf_data ) > 0 ) {

		// Go through each repeater field and attach data.
		foreach ( $pprf_data as $k_str => $v_ukn ) {
			if ( $pods_obj && isset( $pods_obj->fields[ $k_str ]['options']['pandarepeaterfield_table'] ) ) {
				$saved_table = $pods_obj->fields[ $k_str ]['options']['pandarepeaterfield_table'];
				$items_arr   = array();
				$child_pods  = explode( '_', $saved_table );
				// If saved as pod_num, version < 1.2.0.
				if ( 2 === count( $child_pods ) && 'pod' === $child_pods[0] && is_numeric( $child_pods[1] ) ) {
					// Find the repeater table pod name.
					$query = $wpdb->prepare( 'SELECT `post_name` FROM `' . $wpdb->posts . '` WHERE `ID` = %d LIMIT 0, 1', array( $child_pods[1] ) );

					$items_arr = $wpdb->get_results(
						// phpcs:ignore
						$query, 
						ARRAY_A
					); // db call ok. no-cache ok.
				} else {
					$query = $wpdb->prepare( 'SELECT `ID`, `post_name` FROM `' . $wpdb->posts . '` WHERE `post_name` = %s AND `post_type` = "_pods_pod" LIMIT 0, 1', array( $saved_table ) );

					$items_arr = $wpdb->get_results(
						// phpcs:ignore
						$query, 
						ARRAY_A
					); // db call ok. no-cache ok.

				}
				if ( ! empty( $items_arr ) ) {
					$data_count = count( $data_arr );
					for ( $i = 0; $i < $data_count; $i ++ ) {
						$attrs      = apply_filters( 'pandarf_data_attrs', array(), $data_arr, $parent_pod_name );
						$fields     = array(
							'child_pod_name'      => $items_arr[0]['post_name'],
							'parent_pod_id'       => $pprf_data[ $k_str ]['post_parent'],
							'parent_pod_post_id'  => $data_arr[ $i ]['id'],
							'parent_pod_field_id' => $pprf_data[ $k_str ]['ID'],
						);
						$child_data = get_pandarf_items(
							$fields,
							$attrs,
							false
						);

						// Check if it is a repeater field, if yes, return data.
						$child_data = pandarf_get_data( $child_data, $items_arr[0]['post_name'] );

						$data_arr[ $i ][ $k_str ] = $child_data;

					}
				}
			}
		}
	}
	return $data_arr;
}
/**
 * Is it a panda pods repeater field?
 *
 * @param string  $field_name pods field name.
 * @param integer $parent_id parent post id.
 *
 * @return false|array $pandarf_field;
 */
function is_pandarf( $field_name, $parent_id = 0 ) {
	global $wpdb;

	$field_name    = esc_sql( $field_name );
	$parent_id     = intval( $parent_id );
	$key           = $field_name . '_' . $parent_id;
	$pandarf_field = wp_cache_get( $key, 'pandarf_fields' );

	if ( false === $pandarf_field ) {

		$params = array( $field_name );
		$where  = '';
		if ( is_numeric( $parent_id ) && 0 !== $parent_id ) {
			$where = ' AND ps_tb.`post_parent` =  %d';
			array_push( $params, $parent_id );
		}

		$query = $wpdb->prepare(
			// phpcs:ignore
			'SELECT ps_tb.ID, ps_tb.post_name, ps_tb.post_title, ps_tb.post_author, ps_tb.post_parent FROM `' . $wpdb->posts . '` AS ps_tb INNER JOIN `' . $wpdb->postmeta . '` AS pm_tb ON ps_tb.`ID` = pm_tb.`post_id` AND pm_tb.`meta_key` = "type" AND pm_tb.`meta_value` = "pandarepeaterfield" WHERE ps_tb.`post_type` = "_pods_field" AND ps_tb.`post_name` = %s ' . $where . ' LIMIT 0, 1',
			$params
		);

		$items = $wpdb->get_results(
			// phpcs:ignore
			$query, 
			ARRAY_A
		); // db call ok. no-cache ok.
		$pandarf_field = 0; // use 0 so it won't conflict with wp_cache_get() when it returns false.
		if ( ! empty( $items ) ) {

			$pandarf_field = $items[0];
		}

		wp_cache_set( $key, $pandarf_field, 'pandarf_fields' );
	}
	return $pandarf_field;

}
/**
 * Backward compatibility
 *
 * @param string  $field_name Pods field name.
 * @param integer $parent_id Parent post id.
 *
 * @return false|array $pandarf_field;
 */
function is_pandarf_fn( $field_name, $parent_id = 0 ) {
	return is_pandarf( $field_name, $parent_id );
}

if ( ! is_admin() ) {
	add_action( 'after_setup_theme', 'load_pprf_frontend_scripts' );
	/**
	 * Load the PPRF scripts and style.
	 */
	function load_pprf_frontend_scripts() {
		$can_load_pprf_scripts = true;
		$can_load_pprf_scripts = apply_filters( 'load_pprf_scripts_frontend', $can_load_pprf_scripts );
		if ( true === $can_load_pprf_scripts ) {
			add_action( 'wp_enqueue_scripts', 'pprf_enqueue_scripts' );
		}
	}
}

/**
 * Enqueue front-end scripts
 *
 * Allows plugin assets to be loaded.
 *
 * @since 1.0.0
 */
function pprf_enqueue_scripts() {
	global $pprf_l10n, $wp_version;

	// All styles goes here.
	wp_register_style( 'panda-pods-repeater-general-styles', plugins_url( 'css/general.min.css', __FILE__ ), array(), '1.0.0' );
	wp_enqueue_style( 'panda-pods-repeater-general-styles' );
	wp_register_style( 'panda-pods-repeater-styles', plugins_url( 'css/front-end.min.css', __FILE__ ), array( 'panda-pods-repeater-general-styles' ), '1.2.0' );
	wp_enqueue_style( 'panda-pods-repeater-styles' );

	if ( isset( $_GET ) && isset( $_GET['page'] ) && isset( $_GET['pprf_nonce'] ) ) {
		$page       = sanitize_title( wp_unslash( $_GET['page'] ) );
		$pprf_nonce = sanitize_text_field( wp_unslash( $_GET['pprf_nonce'] ) );
		if ( wp_verify_nonce( $pprf_nonce, 'load-pprf-page' ) ) {
			if ( 'panda-pods-repeater-field' === $_GET['page'] ) {
				wp_enqueue_style( 'dashicons' );
				wp_register_style( 'pprf_fields', plugins_url( 'fields/css/pprf.min.css', __FILE__ ), array( 'panda-pods-repeater-general-styles', 'panda-pods-repeater-styles' ), '1.0.0' );
				wp_enqueue_style( 'pprf_fields' );
			}
		}
	}

	// All scripts goes here.
	if ( version_compare( $wp_version, '5.9', '=' ) ) {
		wp_register_script( 'panda-pods-repeater-scripts', plugins_url( 'js/admin.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'panda-pods-repeater-jquery-ui' ), '1.0.0', true );
	} else {
		wp_register_script( 'panda-pods-repeater-scripts', plugins_url( 'js/admin.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable' ), '1.0.0', true );
	}

	wp_enqueue_script( 'panda-pods-repeater-scripts' );
	// Translation.
	wp_localize_script(
		'panda-pods-repeater-scripts',
		'strs_obj',
		$pprf_l10n
	);

	// Prepare ajax.
	wp_localize_script(
		'panda-pods-repeater-scripts',
		'ajax_script',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'panda-pods-repeater-field-nonce' ),
		)
	);

	$admin_url = PANDA_PODS_REPEATER_URL . 'fields/'; // since 1.4.9, we have index.php to avoid being stopped by <FilesMatch "\.(?i:php)$">.
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

}


/**
 * Check pod type
 *
 * @param int $pod_id Pod id.
 */
function pprf_pod_details( $pod_id ) {
	global $wpdb;
	$query = $wpdb->prepare(
		'SELECT *, pm_tb.`meta_value` AS type FROM `' . $wpdb->prefix . 'posts` AS ps_tb 
								INNER JOIN 	`' . $wpdb->prefix . 'postmeta` AS pm_tb ON ps_tb.`ID` = pm_tb.`post_id` AND pm_tb.`meta_key` = "type"
								WHERE `ID` = %d LIMIT 0, 1 ',
		array( $pod_id )
	);

	$parent_details = $wpdb->get_results(
		// phpcs:ignore
		$query, 
		ARRAY_A
	); // db call ok. no-cache ok.
	if ( ! empty( $parent_details ) ) {
		$parent_details = $parent_details[0];
	}
	return $parent_details;
}
/**
 * Backward compatibility.
 *
 * @param int $pod_id Pod id.
 */
function pprf_pod_details_fn( $pod_id ) {
	return pprf_pod_details( $pod_id );
}

/**
 * Get the repeater fields using the same same table in the pod
 *
 * @param string $pod A pod, generated by pods( pod_slug ).
 * @param string $child_table The table pod slug for the repeater field.
 *
 * @return array $return_data The repeater fields using the same same table in the pod.
 */
function pprf_same_child_tb_fields( $pod, $child_table = '' ) {

	$return_data = array();

	foreach ( $pod->fields as $key => $child_field ) {
		if ( 'pandarepeaterfield' === $child_field['type'] && $child_table === $child_field['options']['pandarepeaterfield_table'] ) {
			$return_data[ $key ] = $child_field;
		}
	}

	return $return_data;
}

add_action( 'plugins_loaded', 'pprf_localization_setup' );
/**
 * Load language
 */
function pprf_localization_setup() {
	load_plugin_textdomain( 'panda-pods-repeater-field', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
/**
 * Load the tables that been updated with pprf columns.
 *
 * @since 1.4.5
 * @param string $table the table name to search. If empty, return the saved record for the updated tables.
 * @param string $operate Works if $table is not empty. If $operate is empty, return ture or false respectively. If the table is found or not, return null. If $operate = 'add', add the table to the record. If $operate = 'remove', remove the table from the record.
 * @return array|boolean|null See the descriptions of the parameters above.
 */
function pprf_updated_tables( $table = '', $operate = '' ) {
	$updated_tables = get_option( 'pprf_updated_tables', array() );

	if ( ! is_array( $updated_tables ) ) {
		$updated_tables = array();
	}

	if ( '' === $table ) {
		return $updated_tables;
	} else {
		if ( isset( $updated_tables[ $table ] ) ) {
			if ( '' === $operate ) {
				return true;
			}
			if ( 'remove' === $operate ) {
				unset( $updated_tables[ $table ] );
				return update_option( 'pprf_updated_tables', $updated_tables );

			}
		} else {
			if ( 'add' === $operate ) {
				$updated_tables[ $table ] = array();// set it as an array for futurn use.
				return update_option( 'pprf_updated_tables', $updated_tables );

			}
			return false;
		}
	}

	return false;
}
/**
 * Check if a string contains images, videos, audio medias or relevant shortcode start with them.
 *
 * @since 1.4.5
 * @param string $content The content string.
 * @return string $html Relevant icons if it contains a media.
 */
function pprf_check_media_in_content( $content ) {
	$html = ' ';
	preg_match_all( '/(<img .*?>|\[img.*?\]|\[image.*?\])/is', $content, $tags );

	if ( ! empty( $tags[0] ) ) {
		$html .= ' <span class="dashicons dashicons-format-image mgr5" title ="' . esc_attr__( 'Contains images', 'panda-pods-repeater-field' ) . '"></span>';
	}
	preg_match_all( '/(<video .*?>|\[video.*?\])/is', $content, $tags );

	if ( ! empty( $tags[0] ) ) {
		$html .= ' <span class="dashicons dashicons-format-video mgr5" title ="' . esc_attr__( 'Contains videos', 'panda-pods-repeater-field' ) . '"></span>';
	}

	preg_match_all( '/(<audio .*?>|\[audio.*?\])/is', $content, $tags );

	if ( ! empty( $tags[0] ) ) {
		$html .= ' <span class="dashicons dashicons-format-audio mgr5"  title ="' . esc_attr__( 'Contains audio', 'panda-pods-repeater-field' ) . '"></span>';
	}
	preg_match_all( '/(\[.*?\])/is', $content, $tags );

	if ( ! empty( $tags[0] ) ) {
		$html .= ' <span class="dashicons dashicons-wordpress mgr5"  title ="' . esc_attr__( 'Maybe contain shortcode', 'panda-pods-repeater-field' ) . '"></span>';
	}

	return $html;
}

/**
 * Parent items filter conditions for assigning child items
 *
 * @param array $parent_details Conditions to load the parent items.
 * @param int   $parent_limit How many to parent items load.
 * @param int   $page From which page.
 *
 * @return array $condtions Return the query conditions.
 */
function pprf_parent_filter_conditions( $parent_details = array(), $parent_limit = 20, $page = 1 ) {
	$conditions = array(
		'limit' => $parent_limit,
		'page'  => (int) $page,
	);
	// Normal post type fetch all published and draft posts.
	if ( isset( $parent_details['type'] ) && 'post_type' === $parent_details['type'] ) {

		$conditions['where']   = '( t.post_status = "publish" OR t.post_status = "draft" )';
		$conditions['orderby'] = 't.post_title';

	}

	$conditions = apply_filters( 'filter_pprf_parent_filter_conditions', $conditions, $parent_details );

	return $conditions;
}

/**
 * Add iFrame to allowed wp_kses_post tags
 *
 * @param array  $tags Allowed tags, attributes, and/or entities.
 * @param string $context Context to judge allowed tags by. Allowed values are 'post'.
 *
 * @link https://gist.github.com/bjorn2404/8afe35383a29d2dd1135ae0a39dc018c
 * @example add_filter( 'wp_kses_allowed_html', 'pprf_custom_wpkses_post_tags', 10, 2 );
 *
 * @return array
 */
function pprf_custom_wpkses_post_tags( $tags, $context ) {

	if ( 'post' === $context ) {
		$tags['iframe'] = array(
			'src'             => true,
			'height'          => true,
			'width'           => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'name'            => true,
			'id'              => true,
			'style'           => true,
			'class'           => true,
		);
		$tags['input']  = array(
			'type'   => true,
			'value'  => true,
			'name'   => true,
			'id'     => true,
			'style'  => true,
			'class'  => true,
			'data-*' => true,
		);
	}

	return $tags;
}
