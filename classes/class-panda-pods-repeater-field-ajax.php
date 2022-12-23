<?php
/**
 * The class file for ajax
 *
 * @package panda_pods_repeater_field
 * @author  Dongjie Xu
 */

/**
 * AJax class, collection for AJax functions
 *
 * @package panda_pods_repeater_field
 * @author Dongjie Xu
 * @since 09/02/2016
 */
class Panda_Pods_Repeater_Field_Ajax {
	/**
	 * Constructor for the class
	 */
	public function __construct() {
		$this->define_pprf_all_tables();
		$this->actions();

	}
	/**
	 * All action hooks come here
	 */
	protected function actions() {
		// Login user only, for everyone, use wp_ajax_nopriv_.
		add_action( 'wp_ajax_admin_pprf_load_newly_added', array( $this, 'admin_pprf_load_newly_added' ) );
		add_action( 'wp_ajax_admin_pprf_delete_item', array( $this, 'admin_pprf_delete_item' ) );
		add_action( 'wp_ajax_admin_pprf_update_order', array( $this, 'admin_pprf_update_order' ) );
		add_action( 'wp_ajax_admin_pprf_load_more', array( $this, 'admin_pprf_load_more' ) );
		add_action( 'wp_ajax_admin_pprf_reassign', array( $this, 'admin_pprf_reassign' ) );
		add_action( 'wp_ajax_admin_pprf_load_parent_items', array( $this, 'admin_pprf_load_parent_items' ) );
		add_action( 'wp_ajax_admin_pprf_duplicate', array( $this, 'admin_pprf_duplicate' ) );
		// Frontend.
		add_action( 'wp_ajax_nopriv_admin_pprf_load_newly_added', array( $this, 'admin_pprf_load_newly_added' ) );
		add_action( 'wp_ajax_nopriv_admin_pprf_delete_item', array( $this, 'admin_pprf_delete_item' ) );
		add_action( 'wp_ajax_nopriv_admin_pprf_update_order', array( $this, 'admin_pprf_update_order' ) );
		add_action( 'wp_ajax_nopriv_admin_pprf_load_more', array( $this, 'admin_pprf_load_more' ) );
		add_action( 'wp_ajax_nopriv_admin_pprf_reassign', array( $this, 'admin_pprf_reassign' ) );
		add_action( 'wp_ajax_nopriv_pprf_load_parent_items', array( $this, 'admin_pprf_load_parent_items' ) );
		add_action( 'wp_ajax_nopriv_admin_pprf_duplicate', array( $this, 'admin_pprf_duplicate' ) );

	}

	/**
	 * Create a constance to store all the table information.
	 */
	public function define_pprf_all_tables() {
		if ( ! defined( 'PPRF_ALL_TABLES' ) ) {
			$db_cla = new Panda_Pods_Repeater_Field_DB();
			$tables = $db_cla->get_tables();
			define( 'PPRF_ALL_TABLES', maybe_serialize( $tables ) );
		}
	}
	/**
	 * Find out the last inserted id
	 */
	public function pprf_load_newly_added() {
		$security_checked = false;
		if ( isset( $_POST['security'] ) ) {
			$security = sanitize_text_field( wp_unslash( $_POST['security'] ) );
			if ( wp_verify_nonce( $security, 'panda-pods-repeater-field-nonce' ) ) {
				$security_checked = true;
			}
		}
		if ( false === $security_checked ) {
			$data = array( 'security' => false );
			wp_send_json_error( $data );
		}
		global $wpdb, $current_user;

		$tables = (array) json_decode( PPRF_ALL_TABLES );

		if ( isset( $_POST['podid'] ) && is_numeric( $_POST['podid'] ) && isset( $_POST['cpodid'] ) && is_numeric( $_POST['cpodid'] ) && isset( $_POST['postid'] ) && isset( $_POST['authorid'] ) && is_numeric( $_POST['authorid'] ) && isset( $_POST['poditemid'] ) && is_numeric( $_POST['poditemid'] ) ) {

			$pod_id       = (int) $_POST['podid'];
			$child_pod_id = (int) $_POST['cpodid'];
			$post_id      = (int) $_POST['postid'];
			$author_id    = (int) $_POST['authorid'];
			$pod_item_id  = (int) $_POST['poditemid'];

			// Update panda keys.
			if ( array_key_exists( 'pod_' . $child_pod_id, $tables ) ) {
				$tables[ 'pod_' . $child_pod_id ] = (array) $tables[ 'pod_' . $child_pod_id ];
				$field_options                    = array();
				$parent_pod_name                  = '';
				// Get the parent field.
				if ( array_key_exists( 'pod_' . $pod_id, $tables ) ) { // Custom settings don't have a parent table.
					$tables[ 'pod_' . $pod_id ] = (array) $tables[ 'pod_' . $pod_id ];
					$parent_pod_name            = $tables[ 'pod_' . $pod_id ]['pod'];
				} else {
					$parent_post = get_post( $pod_id, ARRAY_A );
					if ( ! empty( $parent_post ) && '_pods_pod' === $parent_post['post_type'] ) {
						$parent_pod_name = $parent_post['post_name'];
					}
				}
				if ( '' !== $parent_pod_name ) {
					$parent_pod = pods( $parent_pod_name );

					foreach ( $parent_pod->fields as $key => $value_data ) {

						if ( $value_data['id'] === $pod_item_id ) {
							// Get the conditions.
							$field_options = $value_data['options'];
							break;
						}
					}
				}

				$apply_admin_columns = false;
				if ( isset( $field_options['pandarepeaterfield_apply_admin_columns'] ) && $field_options['pandarepeaterfield_apply_admin_columns'] ) {
					$apply_admin_columns = true;
				}

				$panda_repeater_field = new PodsField_Pandarepeaterfield();
				$admin_columns        = array(); // If apply admin columns is picked, use admin columns instead of name.

				$child_pod_name = $tables[ 'pod_' . $child_pod_id ]['pod'];

				$table = esc_sql( $wpdb->prefix . $tables[ 'pod_' . $child_pod_id ]['name'] );

				$title = '';

				if ( '' !== $tables[ 'pod_' . $child_pod_id ]['name_field'] && '' !== $tables[ 'pod_' . $child_pod_id ]['name_label'] ) {
					$title = '' . $tables[ 'pod_' . $child_pod_id ]['name_field'];

				}

				// If it is a WordPress post type, join wp_posts table.
				$join_sql = '';

				if ( 'post_type' === $tables[ 'pod_' . $child_pod_id ]['type'] ) {
					$join_sql = 'INNER JOIN  `' . $wpdb->prefix . 'posts` AS post_tb ON post_tb.ID = t.id';
				}

				// Fetch the child item data.
				$where_sql = '   `t`.`pandarf_parent_pod_id`  = %d
							   	  AND `t`.`pandarf_parent_post_id` = %d
							   	  AND `t`.`pandarf_pod_field_id`   = %d ';

				$wheres    = array( $pod_id, $post_id, $pod_item_id, $author_id );
				$title_sql = '' !== $title ? ', `' . esc_sql( $title ) . '`' : '';
				$query     = $wpdb->prepare(
					// phpcs:ignore
					'SELECT t.`id` ' . $title_sql . ' FROM `' . $table . '` AS t ' . $join_sql . ' WHERE ' . $where_sql . ' AND `t`.`pandarf_author` = %d ORDER BY `t`.`id` DESC LIMIT 0, 1;',
					$wheres
				);

				$items = $wpdb->get_results(
					// phpcs:ignore
					$query, 
					ARRAY_A
				); // db call ok. no-cache ok.
				if ( is_array( $items ) && isset( $items[0]['id'] ) ) {
					if ( ! isset( $items[0][ $title ] ) ) {
						$items[0][ $title ] = '';
					}
					$label_html = '';
					if ( $apply_admin_columns ) {
						$label_html = $panda_repeater_field->create_label_with_admin_columns( $child_pod_name, $items[0]['id'] );
					}

					if ( '' === $label_html ) {
						$name_field_html = '';
						if ( ! empty( $tables[ 'pod_' . $child_pod_id ]['name_label'] ) ) {

							$name_field_html = ' <strong>' . $tables[ 'pod_' . $child_pod_id ]['name_label'] . ': </strong>' . substr( preg_replace( '/\[.*?\]/is', '', wp_strip_all_tags( $items[0][ $title ] ) ), 0, 80 ) . pprf_check_media_in_content( $items[0][ $title ] );

						}
						$label_html = '<strong>ID:</strong> ' . $items[0]['id'] . $name_field_html;
					}
					$data_arr = array(
						'id'              => $items[0]['id'],
						'title'           => $label_html,
						'pprf_name_label' => $tables[ 'pod_' . $child_pod_id ]['name_label'],
						'label'           => '',
					);
					wp_send_json_success( $data_arr );
				} else {
					$data_arr = array(
						'id'         => '',
						'title'      => '',
						'name_label' => '',
						'label'      => '',
					);
					wp_send_json_error( $data_arr );
				}
			}
		}
		die();
	}
	/**
	 * Add a new item at the backend.
	 */
	public function admin_pprf_load_newly_added() {
		// phpcs:ignore
		if ( !isset( $_POST['action'] ) || 'admin_pprf_load_newly_added' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			wp_send_json_error();
		}
		// Nonce verification conducted in pprf_load_newly_added().
		$this->pprf_load_newly_added();
	}
	/**
	 * Add a new item at the frontend.
	 */
	public function front_pprf_load_newly_added() {
		// phpcs:ignore
		if ( !isset( $_POST['action'] ) || 'front_pprf_load_newly_added' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			wp_send_json_error();
		}
		// Nonce verification conducted in pprf_load_newly_added().
		$this->pprf_load_newly_added();
	}
	/**
	 * Delete item
	 */
	public function pprf_delete_item() {
		$security_checked = false;
		if ( isset( $_POST['security'] ) ) {
			$security = sanitize_text_field( wp_unslash( $_POST['security'] ) );
			if ( wp_verify_nonce( $security, 'panda-pods-repeater-field-nonce' ) ) {
				$security_checked = true;
			}
		}
		if ( false === $security_checked ) {
			$data = array( 'security' => false );
			wp_send_json_error( $data );
		}

		global $wpdb, $current_user;

		$tables = (array) json_decode( PPRF_ALL_TABLES );

		if ( isset( $_POST['podid'] ) && is_numeric( $_POST['podid'] ) && isset( $_POST['cpodid'] ) && is_numeric( $_POST['cpodid'] ) && isset( $_POST['postid'] ) && isset( $_POST['authorid'] ) && is_numeric( $_POST['authorid'] ) && isset( $_POST['itemid'] ) && is_numeric( $_POST['itemid'] ) && isset( $_POST['poditemid'] ) && is_numeric( $_POST['poditemid'] ) ) {
			$pod_id       = (int) $_POST['podid'];
			$child_pod_id = (int) $_POST['cpodid'];
			$post_id      = (int) $_POST['postid'];
			$author_id    = (int) $_POST['authorid'];
			$pod_item_id  = (int) $_POST['poditemid'];
			$item_id      = (int) $_POST['itemid'];

			// Update panda keys.
			if ( array_key_exists( 'pod_' . $child_pod_id, $tables ) ) {
				$tables[ 'pod_' . $child_pod_id ] = (array) $tables[ 'pod_' . $child_pod_id ];
				$table_str                        = esc_sql( sanitize_text_field( wp_unslash( $wpdb->prefix . $tables[ 'pod_' . $child_pod_id ]['name'] ) ) );

				$wheres   = array();
				$join_sql = '';
				// Check it is an Advanced Content Type or normal post type.
				$pod_details = pprf_pod_details( $child_pod_id );

				if ( $pod_details ) {
					// Normal post type fetch all published and draft posts.
					if ( 'post_type' === $pod_details['type'] ) {
						$join_sql = 'INNER JOIN `' . $wpdb->prefix . 'posts` AS ps_tb ON ps_tb.`ID` = t.`id`';
					}
				}
				// Fetch the child item data and see if the item belong to the current post.
				$query = $wpdb->prepare(
					// phpcs:ignore
					'SELECT * FROM `' . $table_str . '` AS t ' . $join_sql . ' WHERE `t`.`pandarf_parent_pod_id`  = %d AND `t`.`pandarf_parent_post_id` = %d AND `t`.`pandarf_pod_field_id`   = %d AND `t`.`id` = %d ORDER BY `t`.`id` DESC LIMIT 0, 1', 
					array( $pod_id, $post_id, $pod_item_id, $item_id )
				);
				// phpcs:ignore
				$item_arr = $wpdb->get_results(
					// phpcs:ignore
					$query, ARRAY_A 
				);

				if ( is_array( $item_arr ) && isset( $item_arr[0]['id'] ) && $item_id === (int) $item_arr[0]['id'] ) {

					$delete_action = 'delete';

					if ( isset( $_POST['trash'] ) ) {
						$to_trash = (int) $_POST['trash'];
					} else {
						$to_trash = 2;
					}
					if ( 0 === $to_trash ) {

						$query = $wpdb->prepare(
							// phpcs:ignore
							'UPDATE `' . $table_str . '` SET `pandarf_trash` = 0 WHERE `id` = %d;', 
							array( $item_id )
						);
						// phpcs:ignore
						$deleted = $wpdb->query(
							// phpcs:ignore
							$query 
						);
						$delete_action = 'restore';
					}
					if ( 1 === $to_trash ) { // If $to_trash == 1, the table should be already updated.

						$query = $wpdb->prepare(
							// phpcs:ignore
							'UPDATE `' . $table_str . '` SET `pandarf_trash` = 1 WHERE `id` = %d;', array( $item_id ) 
						);
						// phpcs:ignore
						$deleted = $wpdb->query(
							// phpcs:ignore
							$query 
						);
						$delete_action = 'trash';
					}
					if ( 2 === $to_trash ) {
						$pod_obj = pods( $tables[ 'pod_' . $child_pod_id ]['pod'], absint( $item_id ) );
						if ( $pod_obj->exists() ) {
							$deleted = $pod_obj->delete( $item_id );
						}
					}
					if ( $deleted ) {
						$data_arr = array(
							'id'          => $item_arr[0]['id'],
							'pod_idx'     => $tables[ 'pod_' . $child_pod_id ]['name_field'],
							'pod_idx_val' => '',
							'ppod_fie_id' => $pod_item_id,
							'action'      => $delete_action,
						);
						if ( ! empty( $item_arr[0][ $tables[ 'pod_' . $child_pod_id ]['name_label'] ] ) ) {
							$data_arr['pod_idx_val'] = $item_arr[0][ $tables[ 'pod_' . $child_pod_id ]['name_field'] ];
						}
						wp_send_json_success( $data_arr );
					}
				} else {
					$data_arr = array(
						'id'          => '',
						'pod_idx'     => '',
						'pod_idx_val' => '',
						'ppod_fie_id' => '',
						'action'      => '',
					);
					wp_send_json_error( $data_arr );
				}
			}
		}
		die();
	}
	/**
	 * Delete item at the backend
	 */
	public function admin_pprf_delete_item() {
		// phpcs:ignore
		if ( ! isset( $_POST['action'] ) || 'admin_pprf_delete_item' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			die();
		}
		// Nonce verification conducted in pprf_delete_item().
		$this->pprf_delete_item();

	}
	/**
	 * Delete item at the frontend
	 */
	public function front_pprf_delete_item() {
		// phpcs:ignore
		if ( ! isset( $_POST['action'] ) || 'front_pprf_delete_item' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			die();
		}
		// Nonce verification conducted in pprf_delete_item().
		$this->pprf_delete_item();

	}
	/**
	 * Update order
	 */
	public function pprf_update_order() {

		$security_checked = false;
		if ( isset( $_POST['security'] ) ) {
			$security = sanitize_text_field( wp_unslash( $_POST['security'] ) );
			if ( wp_verify_nonce( $security, 'panda-pods-repeater-field-nonce' ) ) {
				$security_checked = true;
			}
		}
		if ( false === $security_checked ) {
			$data = array( 'security' => false );
			wp_send_json_error( $data );
		}
		global $wpdb, $current_user;

		$tables      = (array) json_decode( PPRF_ALL_TABLES );
		$pprf_id     = '';
		$return_data = array( 'pprf_id' => '' );
		if ( isset( $_POST['order'] ) && is_array( $_POST['order'] ) ) {

			$items_to_order = array_map( 'sanitize_text_field', wp_unslash( $_POST['order'] ) );

			$order_count = count( $items_to_order );

			for ( $i = 0; $i < $order_count; $i ++ ) {
				$ids_arr = explode( '-', $items_to_order[ $i ] );
				if ( count( $ids_arr ) >= 3 ) {
					// If the pods table is listed.
					if ( isset( $tables[ 'pod_' . $ids_arr[1] ] ) ) {
						$tables[ 'pod_' . $ids_arr[1] ] = (array) $tables[ 'pod_' . $ids_arr[1] ];
						if ( '' === $pprf_id ) {
							$pprf_id = $ids_arr[1] . '-' . $ids_arr[3];
						}

						$query = $wpdb->prepare(
							'UPDATE `' . $wpdb->prefix . esc_sql( $tables[ 'pod_' . $ids_arr[1] ]['name'] ) . '`
														SET  `pandarf_order` =  %d 
													  WHERE  `id` = %d;',
							array( $i, $ids_arr[2] )
						);
						// phpcs:ignore
						$wpdb->query(
							// phpcs:ignore
							$query 
						);

						$return_data['pprf_id'] = $pprf_id;
					}
				}
			}
			if ( '' !== $pprf_id ) {
				wp_send_json_success( $return_data );
			} else {
				wp_send_json_error( $return_data );
			}
		} else {
			wp_send_json_error( $return_data );
		}
	}
	/**
	 * Update order
	 */
	public function admin_pprf_update_order() {
		// phpcs:ignore
		if ( ! isset( $_POST['action'] ) || 'admin_pprf_update_order' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			$data_arr = array( 'action' => false );
			wp_send_json_error( $data_arr );
		}
		// Nonce verification conducted in pprf_update_order().
		$this->pprf_update_order();
	}

	/**
	 * Load more
	 */
	public function pprf_load_more() {
		$security_checked = false;
		if ( isset( $_POST['security'] ) ) {
			$security = sanitize_text_field( wp_unslash( $_POST['security'] ) );
			if ( wp_verify_nonce( $security, 'panda-pods-repeater-field-nonce' ) ) {
				$security_checked = true;
			}
		}
		if ( false === $security_checked ) {
			$data = array( 'security' => false );
			wp_send_json_error( $data );
		}
		global $wpdb, $current_user;

		$tables = (array) json_decode( PPRF_ALL_TABLES );

		$tb_str = '';
		if ( isset( $_POST['saved_tb'] ) && is_numeric( $_POST['saved_tb'] ) ) {
			$saved_table_id = absint( $_POST['saved_tb'] );
			$post_arr       = get_post( $saved_table_id, ARRAY_A );

			if ( is_array( $post_arr ) && '_pods_pod' === $post_arr['post_type'] ) {
				$tb_str = esc_sql( $post_arr['post_name'] );

			} else {
				wp_send_json_error();
			}
		} else {
			wp_send_json_error();
		}

		if ( ! isset( $_POST['pod_id'] ) || ! isset( $_POST['post_id'] ) || ! isset( $_POST['pod_item_id'] ) ) {
			wp_send_json_error();
		}
		$pod_id      = sanitize_text_field( wp_unslash( $_POST['pod_id'] ) );
		$post_id     = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );
		$pod_item_id = sanitize_text_field( wp_unslash( $_POST['pod_item_id'] ) );

		$where_sql = '   `pandarf_parent_pod_id`  = %d
					   	  AND `pandarf_parent_post_id` = %d
					   	  AND `pandarf_pod_field_id`   = %d ';
		$searches  = array( $pod_id, $post_id, $pod_item_id );

		// Order.
		$order_by_sql = 'CAST( `pandarf_order` AS UNSIGNED )';
		if ( isset( $_POST['order_by'] ) ) {
			$order_by = sanitize_text_field( wp_unslash( $_POST['order_by'] ) );
			if ( 'pandarf_order' !== $order_by ) {
				$order_by_sql = '`' . esc_sql( $order_by ) . '`';
			}
		}

		$order_sql = 'ASC';
		if ( isset( $_POST['order'] ) ) {
			$order = sanitize_text_field( wp_unslash( $_POST['order'] ) );
			if ( 'DESC' === $order ) {
				$order_sql = 'DESC';
			}
		}
		// Limit.
		$limit_sql = '';

		if ( isset( $_POST['start'] ) && isset( $_POST['amount'] ) ) {
			$start     = sanitize_text_field( wp_unslash( $_POST['start'] ) );
			$amount    = sanitize_text_field( wp_unslash( $_POST['amount'] ) );
			$limit_sql = 'LIMIT %d, %d';
			array_push( $searches, $start, $amount );
		}

		// If it is a WordPress post type, join wp_posts table.
		$join_sql                           = '';
		$tables[ 'pod_' . $saved_table_id ] = (array) $tables[ 'pod_' . $saved_table_id ];
		if ( 'post_type' === $tables[ 'pod_' . $saved_table_id ]['type'] ) {
			$join_sql = 'INNER JOIN  `' . $wpdb->prefix . 'posts` AS post_tb ON post_tb.ID = main_tb.id';
		}
		// phpcs:ignore
		$query = $wpdb->prepare(
			// phpcs:ignore
			'SELECT main_tb.`id`, CONCAT( "' . $tables[ 'pod_' . $saved_table_id ]['name_label'] . '" ) AS pprf_name_label, `' . $tables[ 'pod_' . $saved_table_id ]['name_field'] . '` AS title, main_tb.`pandarf_trash` AS trashed FROM `' . $wpdb->prefix . 'pods_' . $tb_str . '` AS main_tb ' . $join_sql . ' WHERE ' . $where_sql . ' ORDER BY ' . $order_by_sql . ' ' . $order_sql . ' ' . $limit_sql,
			$searches
		);
		// phpcs:ignore	
		$entries = $wpdb->get_results(
			// phpcs:ignore
			$query, 
			ARRAY_A
		);

		wp_send_json_success( $entries );

	}
	/**
	 * Load more parent items
	 */
	public function admin_pprf_load_more() {
		// phpcs:ignore
		if ( ! isset( $_POST['action'] ) || 'admin_pprf_load_more' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			$data = array( 'action' => false );
			wp_send_json_error( $data );
		}
		// Nonce verification conducted in pprf_load_more().
		$this->pprf_load_more();
	}
	/**
	 * Reassign to another parent.
	 */
	public function admin_pprf_reassign() {

		$security_checked = false;
		if ( isset( $_POST['security'] ) ) {
			$security = sanitize_text_field( wp_unslash( $_POST['security'] ) );
			if ( wp_verify_nonce( $security, 'panda-pods-repeater-field-nonce' ) ) {
				$security_checked = true;
			}
		}
		if ( false === $security_checked ) {
			$data = array(
				'security' => false,
				'updated'  => false,
				'message'  => '',
			);
			wp_send_json_error( $data );
		}
		if ( ! isset( $_POST['action'] ) || 'admin_pprf_reassign' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			$data = array(
				'security' => true,
				'updated'  => false,
				'message'  => 'Wrong action.',
			);
			wp_send_json_error( $data );
		}
		global $wpdb, $current_user;

		$tables = (array) json_decode( PPRF_ALL_TABLES );
		if ( ! isset( $_POST['cpodid'] ) || ! isset( $_POST['postid'] ) || ! isset( $_POST['poditemid'] ) || ! isset( $_POST['itemid'] ) ) {
			$data = array(
				'security' => true,
				'updated'  => false,
				'message'  => 'Wrong params.',
			);
			wp_send_json_error( $data );
		}

		$child_pod_id = sanitize_text_field( wp_unslash( $_POST['cpodid'] ) );
		$post_id      = sanitize_text_field( wp_unslash( $_POST['postid'] ) );
		$pod_item_id  = sanitize_text_field( wp_unslash( $_POST['poditemid'] ) );
		$item_id      = sanitize_text_field( wp_unslash( $_POST['itemid'] ) );

		$tables[ 'pod_' . $child_pod_id ] = (array) $tables[ 'pod_' . $child_pod_id ];
		$table                            = esc_sql( sanitize_text_field( wp_unslash( $tables[ 'pod_' . $child_pod_id ]['name'] ) ) );
		$query                            = $wpdb->prepare(
			// phpcs:ignore
			'UPDATE `' . $wpdb->prefix . $table . '` SET `pandarf_parent_post_id` = %d, `pandarf_pod_field_id` = %d WHERE `id` = %d',
			array( $post_id, $pod_item_id, $item_id )
		);

		$done = $wpdb->query(
			// phpcs:ignore
			$query 
		);  // db call ok. no-cache ok.
		if ( $done ) {
			$data = array(
				'security' => true,
				'updated'  => $done,
				'message'  => 'Done.',
			);
			wp_send_json_success( $data );
		} else {
			$data = array(
				'security' => true,
				'updated'  => false,
				'message'  => 'Update failed.',
			);
			wp_send_json_error( $data );
		}
	}

	/**
	 * Load parent items
	 */
	public function admin_pprf_load_parent_items() {
		$security_checked = false;
		if ( isset( $_POST['security'] ) ) {
			$security = sanitize_text_field( wp_unslash( $_POST['security'] ) );
			if ( wp_verify_nonce( $security, 'panda-pods-repeater-field-nonce' ) ) {
				$security_checked = true;
			}
		}
		if ( false === $security_checked ) {
			$data = array(
				'security' => false,
				'updated'  => false,
			);
			wp_send_json_error( $data );
		}
		if ( ! isset( $_POST['action'] ) || 'admin_pprf_load_parent_items' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			$data = array(
				'security' => true,
				'updated'  => false,
			);
			wp_send_json_error( $data );
		}
		global $wpdb, $current_user;

		if ( ! isset( $_POST['podid'] ) || ! isset( $_POST['limit'] ) || ! isset( $_POST['page'] ) || ! isset( $_POST['postid'] ) ) {
			$data = array(
				'security' => true,
				'updated'  => false,
			);
			wp_send_json_error( $data );
		}
		// Check it is an Advanced Content Type or normal post type.
		$parent_details = pprf_pod_details( (int) $_POST['podid'] );

		$parrent_limit = (int) $_POST['limit'];
		$page          = (int) $_POST['page'];
		$html          = '';
		if ( $parent_details ) {
			$parent_table                  = $parent_details['post_name'];
			$parent_details['pprf_parent'] = (int) $_POST['postid'];
			$conditions                    = pprf_parent_filter_conditions( $parent_details, $parrent_limit, $page );

			$parent_pod = pods( $parent_table, $conditions );

			if ( 0 < $parent_pod->total() ) {

				while ( $parent_pod->fetch() ) {
					$draft = '';

					if ( 'Draft' === $parent_pod->display( 'post_status' ) ) {
						$draft = esc_attr( ' - draft' );
					}
					$html .= '<option value="' . esc_attr( $parent_pod->display( 'id' ) ) . '">ID: ' . esc_attr( $parent_pod->display( 'id' ) ) . ' - ' . esc_attr( $parent_pod->display( 'name' ) ) . $draft . '</option>';

				}
			}
		}

		if ( ! empty( $html ) ) {
			$data = array(
				'security' => true,
				'items'    => $html,
			);
			wp_send_json_success( $data );
		} else {
			$data = array(
				'security' => true,
				'items'    => '',
			);
			wp_send_json_error( $data );
		}
	}
	/**
	 * Duplicate to a parent item
	 */
	public function admin_pprf_duplicate() {
		$security_checked = false;
		if ( isset( $_POST['security'] ) ) {
			$security = sanitize_text_field( wp_unslash( $_POST['security'] ) );
			if ( wp_verify_nonce( $security, 'panda-pods-repeater-field-nonce' ) ) {
				$security_checked = true;
			}
		}
		if ( false === $security_checked ) {
			$data = array(
				'security' => false,
				'updated'  => false,
			);
			wp_send_json_error( $data );
		}
		if ( ! isset( $_POST['action'] ) || 'admin_pprf_duplicate' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			$data = array(
				'security' => true,
				'updated'  => false,
			);
			wp_send_json_error( $data );
		}
		if ( ! isset( $_POST['cpodid'] ) || ! isset( $_POST['podid'] ) || ! isset( $_POST['postid'] ) || ! isset( $_POST['poditemid'] ) || ! isset( $_POST['new_post_id'] ) || ! isset( $_POST['item_id'] ) ) {
			$data = array(
				'security' => true,
				'updated'  => false,
			);
			wp_send_json_error( $data );
		}
		global $wpdb, $current_user;

		$panda_pods_repeater_field_db = new Panda_Pods_Repeater_Field_DB();

		$tables       = (array) json_decode( PPRF_ALL_TABLES );
		$child_pod_id = (int) $_POST['cpodid'];

		$tables[ 'pod_' . $child_pod_id ] = (array) $tables[ 'pod_' . $child_pod_id ];

		$args = array(
			'pod_name'            => $tables[ 'pod_' . $child_pod_id ]['pod'],
			'parent_pod_id'       => (int) $_POST['podid'],
			'parent_id'           => (int) $_POST['postid'],
			'parent_pod_field_id' => (int) $_POST['poditemid'],
			'new_parent_id'       => (int) $_POST['new_post_id'],
			'item_id'             => (int) $_POST['item_id'],
		);

		$done = $panda_pods_repeater_field_db->duplicate( $args );

		if ( ! empty( $done['new_id'] ) ) {
			// Translators: %d: new id.
			$done['message'] = sprintf( esc_html__( 'Succeed. Refresh or go to the parent you assigned to to have a look. The ID is: %d. ', 'panda-pods-repeater-field' ), $done['new_id'] );
			wp_send_json_success( $done );
		} else {
			$done['message'] = esc_html__( 'Failed.', 'panda-pods-repeater-field' );
			wp_send_json_error( $done );
		}
	}
}
