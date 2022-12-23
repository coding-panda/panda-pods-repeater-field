<?php
/**
 * The class file for database
 *
 * @package panda_pods_repeater_field
 * @author  Dongjie Xu
 */

/**
 * Collection of functions can be used pods panda repeater field database
 *
 * @package panda_pods_repeater_field
 * @author Dongjie Xu
 * @since 1.0.0 20/05/2014
 */
class Panda_Pods_Repeater_Field_DB {
	/**
	 * Constructor of the class
	 */
	public function __construct() {

	}
	/**
	 * Fields to add to database table to establish the relationship.
	 *
	 * @var array $keys
	 */
	public static $keys = array(
		'pandarf_parent_pod_id'   => array(
			'type'     => 'int(11)',
			'settings' => 'NOT NULL',
			'default'  => '',
		),
		'pandarf_parent_post_id'  => array(
			'type'     => 'int(11)',
			'settings' => 'NOT NULL',
			'default'  => '',
		),
		'pandarf_pod_field_id'    => array(
			'type'     => 'int(11)',
			'settings' => 'NOT NULL',
			'default'  => '',
		),
		'pandarf_order'           => array(
			'type'     => 'text',
			'settings' => 'NOT NULL',
			'default'  => '',
		),
		'pandarf_created'         => array(
			'type'     => 'DATETIME',
			'settings' => 'NOT NULL',
			'default'  => 'DEFAULT "0000-00-00 00:00:00"',
		),
		'pandarf_modified'        => array(
			'type'     => 'DATETIME',
			'settings' => 'NOT NULL',
			'default'  => 'DEFAULT "0000-00-00 00:00:00"',
		),
		'pandarf_modified_author' => array(
			'type'     => 'int(11)',
			'settings' => 'NOT NULL',
			'default'  => '',
		),
		'pandarf_author'          => array(
			'type'     => 'int(11)',
			'settings' => 'NOT NULL',
			'default'  => '',
		),
		'pandarf_trash'           => array(
			'type'     => 'int(1)',
			'settings' => 'NOT NULL',
			'default'  => 'DEFAULT 0',
		),
	);
	/**
	 * Escape data for sql
	 *
	 * @param array $data_ukn An associated array.
	 * @return array $data_ukn The associated array with each item escaped for sql.
	 */
	public function escape_sqls( $data_ukn ) {
		if ( is_array( $data_ukn ) ) {
			foreach ( $data_ukn as $key_str => $val_ukn ) {
				$key_str = esc_sql( $key_str );
				if ( is_array( $val_ukn ) ) {
					$val_ukn = $this->escape_sqls( $val_ukn );
				} else {

					$val_ukn = strval( $val_ukn );

					$val_ukn = esc_sql( $val_ukn );

				}
				$data_ukn[ $key_str ] = $val_ukn;
			}
		} else {
			if ( is_string( $val_ukn ) ) {
				$data_ukn = esc_sql( $val_ukn );
			}
		}
		return $data_ukn;
	}
	/**
	 * Escape data using esc_attr
	 *
	 * @param array $data_ukn An associated array.
	 * @return array $data_ukn The associated array with each item escaped for html output.
	 */
	public function escape_attrs( $data_ukn ) {
		if ( is_array( $data_ukn ) ) {
			foreach ( $data_ukn as $key_str => $val_ukn ) {
				$key_str = esc_attr( $key_str );
				if ( is_array( $val_ukn ) ) {
					$val_ukn = $this->escape_attrs( $val_ukn );
				} else {
					$val_ukn = strval( $val_ukn );
					$val_ukn = esc_sql( $val_ukn );
				}
				$data_ukn[ $key_str ] = $val_ukn;
			}
		} else {
			if ( is_string( $val_ukn ) ) {
				$data_ukn = esc_attr( $val_ukn );
			}
		}
		return $data_ukn;
	}

	/**
	 * Get tables from database, by default, only return wp_posts, wp_users and pods tables
	 *
	 * @param boolean $return_all_tables Return all tables or not.
	 *
	 * @return array $pod_tables The Pod tables in an associated array.
	 */
	public function get_tables( $return_all_tables = false ) {
		global $wpdb;

		$pod_tables = array();
		$cache_name = 'simpods_all_tables';
		$tables     = wp_cache_get( $cache_name, 'simpods_tables' );

		if ( ! empty( $tables ) ) {
			if ( isset( $tables['act_tables'] ) ) {
				$act_tables = $tables['act_tables'];
				return $act_tables;
			}
		}

		$tables = get_option( 'simpods_all_tables', array() ); // Integrated with Simpods MVC.

		if ( empty( $tables ) || ! is_array( $tables ) ) {
			$query  = 'SHOW TABLES LIKE "%"';
			$tables = $wpdb->get_results(
				// phpcs:ignore
				$query 
			); // db call ok. no cache ok.
		}

		foreach ( $tables as $idx_int => $table_obj ) {
			foreach ( $table_obj as $table_name ) {
				$table = str_replace( $wpdb->prefix, '', $table_name );

				// Return all tables.
				if ( $return_all_tables ) {
					array_push( $pod_tables, $table );
				} else {

					if ( 0 === strpos( $table_name, $wpdb->prefix . 'pods_' ) ) {

						$table_info = $this->get_pods_tb_info( $table );
						$name_field = get_post_meta( $table_info['id'], 'pod_index', true );

						$name_label = '';
						if ( '' === $name_field ) {

							if ( 'post_type' === $table_info['type'] ) {
								$name_field = 'post_title';
							} elseif ( 'user' === $table_info['type'] ) {
								$name_field = 'display_name';
							} else {
								$name_field = 'sp_title';
							}
						} else {
							$query = $wpdb->prepare(
								'SELECT ps_tb.post_title
								 FROM `' . $wpdb->posts . '` AS ps_tb																		  
								 WHERE ps_tb.`post_name` = %s AND ps_tb.`post_parent` = %d AND ps_tb.`post_type` = "_pods_field" LIMIT 0, 1',
								array(
									$name_field,
									$table_info['id'],
								)
							);

							$items = $wpdb->get_results(
								// phpcs:ignore
								$query , ARRAY_A 
							);// db call ok. no-cache ok.

							if ( $items && count( $items ) > 0 ) {
								$name_label = $items[0]['post_title'];
							}
						}
						$pod_tables[ 'pod_' . $table_info['id'] ] = array(
							'name'       => $table,
							'pod'        => $table_info['name'],
							'type'       => $table_info['type'],
							'name_field' => $name_field,
							'name_label' => $name_label,
						);
						$pod_tables[ $table ]                     = $pod_tables[ 'pod_' . $table_info['id'] ];
					} else {
						$pod_tables[ $table ] = array(
							'name'       => $table,
							'pod'        => '',
							'type'       => 'wp',
							'name_field' => '',
							'name_label' => '',
						);
					}
				}
			}
		}

		return $pod_tables;

	}

	/**
	 * Get pods table info
	 *
	 * @param string $table The name for a pod table.
	 */
	public function get_pods_tb_info( $table ) {
		global $wpdb;

		// If prefix not found, add it to the target table.
		if ( 0 === strpos( $table, $wpdb->prefix ) ) {
			$table = substr( $table, strlen( $wpdb->prefix ) );
		}

		if ( 0 === strpos( $table, 'pods_' ) ) {
			$table = substr( $table, 5 );
		}

		$table_data = wp_cache_get( $table, 'pprf_table_data' );

		if ( false === $table_data ) {
			$table_info = wp_cache_get( $table, 'simpods_pods_tables_info' );   // Integrated with Simpods.
			if ( false !== $table_info ) {
				return $table_info;
			}
			$query = $wpdb->prepare(
				'SELECT ps_tb.*, pm_tb.`meta_value` AS type
											 FROM `' . $wpdb->posts . '` AS ps_tb
											 LEFT JOIN `' . $wpdb->postmeta . '` AS pm_tb ON ps_tb.`ID` = pm_tb.`post_id` AND pm_tb.`meta_key` = "type"					  
											 WHERE ps_tb.`post_name` = %s AND ps_tb.`post_type` = "_pods_pod" LIMIT 0, 1',
				array( $table )
			);
			$items = $wpdb->get_results(
				// phpcs:ignore
				$query , ARRAY_A 
			); // db call ok. no cache ok.

			$table_data = array(
				'id'   => 0,
				'name' => '',
				'type' => '',
			);
			if ( count( $items ) > 0 ) {
				$table_data['id']   = $items[0]['ID'];
				$table_data['name'] = $items[0]['post_name'];
				$table_data['type'] = '' === $items[0]['type'] ? 'pod' : $items[0]['type'];
			}

			wp_cache_add( $table, $table_data, 'pprf_table_data' );

		}
		return $table_data;
	}
	/**
	 * Check if a table column exists
	 *
	 * @param string $table Table name.
	 * @uses $this->check_column_existence() to check if a column exists.
	 */
	public function update_columns( $table ) {
		global $wpdb;

		$table = esc_sql( sanitize_text_field( wp_unslash( $table ) ) );

		self::$keys = $this->escape_sqls( self::$keys );
		foreach ( self::$keys as $key => $values ) {
			$key      = esc_sql( $key );
			$existing = $this->check_column_existence( 'pods_' . $table, $key );

			if ( ! $existing ) {
				$query = 'ALTER TABLE  `' . $wpdb->prefix . 'pods_' . $table . '` ADD `' . $key . '` ' . implode( ' ', $values );
				$wpdb->query(
					// phpcs:ignore
					$query 
				); // db call ok. no cache ok.
			}
		}

		pprf_updated_tables( $table, 'add' );

	}
	/**
	 * Backward compatibility
	 *
	 * @param string $table Table name.
	 */
	public function update_columns_fn( $table ) {
		$this->update_columns( $table );
	}
	/**
	 * Check if a table column exists
	 *
	 * @param string $table Table name.
	 * @param string $column Table name.
	 *
	 * @return array $result The table column details.
	 */
	public function check_column_existence( $table, $column ) {
		global $wpdb;
		$table  = esc_sql( sanitize_text_field( wp_unslash( $table ) ) );
		$result = $wpdb->query(
			// phpcs:ignore
			$wpdb->prepare( 'SHOW COLUMNS FROM `' . $wpdb->prefix . $table . '` LIKE %s', array( $column ) ) 
		); // db call ok. no cache ok.

		return $result;
	}
	/**
	 * Get field names of a table
	 *
	 * @param string  $table Targeted table.
	 * @param boolean $add_prefix Add the table prefix to $table. Default to true.
	 * @param boolean $shown_query For developers to debug the query.
	 *
	 * @return array $items Details of the table fields.
	 */
	public function get_fields( $table, $add_prefix = true, $shown_query = false ) {
		global $wpdb;

		$table = esc_sql( sanitize_text_field( wp_unslash( $table ) ) );

		if ( $add_prefix && stripos( $table, $wpdb->prefix ) !== 0 ) {
			$table = $wpdb->prefix . $table;
		}

		$cache_key = 'pprf_table_field_data';
		$items     = wp_cache_get( $table, $cache_key );

		if ( false === $items ) {

			$query = 'SHOW FIELDS FROM ' . $table;

			if ( $shown_query ) {
				echo esc_html( $query );
			}
			$items = $wpdb->get_results(
				// phpcs:ignore
				$query , ARRAY_A 
			); // db call ok. no cache ok.
			wp_cache_set( $cache_key, $items );
		}
		return $items;
	}

	/**
	 * If the field applys admin table columns, return the columns and label
	 *
	 * @param string $parent_table Parent table pod name.
	 * @param string $child_table Child table pod name.
	 * @param int    $field_id The repeater field id.
	 * @param int    $row_id The child table row id.
	 * @return array $return_data If it valid.
	 */
	public function get_admin_columns( $parent_table, $child_table, $field_id, $row_id = 0 ) {

		$return_data   = array(
			'valid'   => false,
			'columns' => array(),
			'label'   => '',
		);
		$admin_columns = array(); // If apply admin columns is picked, use admin columns instead of name.
		$parent_pod    = new pods( $parent_table );

		if ( $parent_pod ) {
			foreach ( $parent_pod->fields as $field_data ) {

				if ( $field_data['id'] === $field_id ) {

					if ( isset( $field_data['options']['pandarepeaterfield_apply_admin_columns'] ) && 1 === (int) $field_data['options']['pandarepeaterfield_apply_admin_columns'] ) {
						$child_pod = new pods( $child_table );

						if ( $child_pod ) {

							$admin_columns = (array) pods_v( 'ui_fields_manage', $child_pod->pod_data['options'] );

						}
					}
					break;
				}
			}
		}

		if ( count( $admin_columns ) > 0 ) {
			$return_data['valid']   = true;
			$return_data['columns'] = $admin_columns;
			$label_html             = '';
			if ( 0 !== $row_id && is_numeric( $row_id ) ) {
				$is_id = false;
				foreach ( $admin_columns as $admin_column_name ) {
					if ( 'id' === strtolower( $admin_column_name ) ) {
						$is_id = true;
						continue;
					}
					$column_value = pods_field( $child_table, $row_id, $admin_column_name );
					if ( is_string( $column_value ) || is_numeric( $column_value ) ) {
						$label_html .= '<strong>' . esc_html( $child_pod->fields[ $admin_column_name ]['label'] ) . ':</strong> ' . esc_html( $column_value ) . ' ';
					}
				}
				if ( $is_id ) {
					$label_html = '<strong>ID:</strong> ' . esc_html( $row_id ) . ' ' . $label_html;
				}
			}
			$return_data['label'] = $label_html;
		}

		return $return_data;

	}

	/**
	 * Duplicate the data of a repeater field
	 *
	 * @param array $params Parameters.
	 * @return array $return It contains the new id.
	 */
	public function duplicate( $params = array() ) {
		global $wpdb;
		$defaults = array(
			'pod_name'            => '',
			'parent_pod_id'       => 0,
			'parent_id'           => 0,
			'parent_pod_field_id' => 0,
			'new_parent_id'       => 0,
			'item_id'             => 0,
		);
		$args     = wp_parse_args( $params, $defaults );

		if ( empty( $args['pod_name'] ) ) {
			return false;
		}

		$now                         = gmdate( 'Y-m-d H:i:s' );
		$args['pod_name']            = sanitize_text_field( wp_unslash( $args['pod_name'] ) );
		$args['parent_pod_id']       = (int) $args['parent_pod_id'];
		$args['parent_id']           = (int) $args['parent_id'];
		$args['parent_pod_field_id'] = (int) $args['parent_pod_field_id'];
		$args['new_parent_id']       = (int) $args['new_parent_id'];
		$args['item_id']             = (int) $args['item_id'];

		$return = array(
			'new_id' => 0,
		);
		$pod    = pods( $args['pod_name'] );

		if ( $pod ) {

			$pod_fields = $pod->fields();

			$panda_fields = array();
			// Find out the repeater fields.
			if ( $pod_fields ) {
				foreach ( $pod_fields as $field_name => $field_data ) {
					if ( 'pandarepeaterfield' === $field_data->type ) {

						$panda_fields[ $field_name ] = array(
							'pod_name'            => $field_data->pandarepeaterfield_table,
							'parent_pod_id'       => $pod->pod_data->id,
							'parent_pod_field_id' => $field_data->id,
						);

					}
				}
			}

			$table = esc_sql( 'pods_' . $args['pod_name'] );

			if ( 0 !== $args['item_id'] ) { // Only duplicate one.

				$query = $wpdb->prepare(
					// phpcs:ignore
					'SELECT * FROM `' . $wpdb->prefix . $table . '`	WHERE `id` = %d',
					array(
						$args['item_id'],
					)
				);

			} else { // Duplicate all children.

				$query = $wpdb->prepare(
					// phpcs:ignore
					'SELECT * FROM `' . $wpdb->prefix . $table . '` WHERE `pandarf_parent_pod_id` = %d AND `pandarf_parent_post_id` = %d AND `pandarf_pod_field_id` = %d',
					array(
						$args['parent_pod_id'],
						$args['parent_id'],
						$args['parent_pod_field_id'],
					)
				);
			}

			$rows = $wpdb->get_results(
				// phpcs:ignore
				$query , ARRAY_A 
			); // db call ok. no cache ok.

			$date_fields = array(
				'pandarf_created',
				'pandarf_modified',
				'sp_created',
				'sp_modified',
			);
			$to_unset    = array(
				'id',
				'sp_start',
				'sp_end',
				'deadline',
			);

			foreach ( $rows as $i => $row ) {
				$old_id           = $row['id'];
				$new_id           = $pod->duplicate( $row['id'] );
				$return['new_id'] = $new_id;

				$row['pandarf_parent_post_id'] = $args['new_parent_id'];

				foreach ( $date_fields as $date_field ) {
					if ( isset( $row[ $date_field ] ) ) {
						$row[ $date_field ] = $now;
					}
				}
				foreach ( $to_unset as $unset_field ) {
					if ( isset( $row[ $unset_field ] ) ) {
						unset( $row[ $unset_field ] );
					}
				}
				$where = array(
					'id' => $new_id,
				);

				$updated = $this->update( $wpdb->prefix . $table, $row, $where );

				foreach ( $row as $key => $value ) {
					if ( isset( $panda_fields[ $key ] ) ) {

						$panda_fields[ $key ]['parent_id']     = $old_id;
						$panda_fields[ $key ]['new_parent_id'] = $new_id;

						$this->duplicate( $panda_fields[ $key ] );
					}
				}
			}
		}
		return $return;
	}
	/**
	 * Update the data of a repeater field
	 *
	 * @param string $table The pod table name.
	 * @param array  $data The data to update.
	 * @param array  $where Conditions for the update.
	 *
	 * @return boolean $updated Successfully updated or now.
	 */
	public function update( $table, $data, $where ) {
		global $wpdb;
		if ( ! is_string( $table ) || ! is_array( $data ) || ! is_array( $where ) || empty( $data ) || empty( $where ) ) {
			return false;
		}
		$table   = esc_sql( sanitize_text_field( wp_unslash( $table ) ) );
		$updates = array();
		$values  = array();

		foreach ( $data as $key => $value ) {
			if ( is_numeric( $value ) ) {
				$type = strpos( $value, '.' ) !== false ? '%f' : '%d';
			} else {
				$type = '%s';
			}
			array_push( $updates, '`' . $key . '` = ' . $type );
			array_push( $values, $value );
		}
		$update_sql = implode( ',', $updates );

		$searches = array();
		$count    = count( $where );
		foreach ( $where as $key => $value ) {
			if ( is_numeric( $value ) ) {
				strpos( $value, '.' ) !== false ? $type = '%d' : $value_type = '%f';
			} else {
				$type = '%s';
			}
			array_push( $searches, '`' . $key . '` = ' . $type );
			array_push( $values, $value );
		}

		$search_sql = implode( ' AND ', $searches );

		$query = $wpdb->prepare(
			// phpcs:ignore
			'UPDATE `' . $table . '` SET ' . $update_sql . ' WHERE ' . $search_sql, $values 
		);
		$updated = $wpdb->query(
			// phpcs:ignore
			$query 
		); // db call ok. no cache ok.

		return $updated;

	}

	/**
	 * Delete all data in all posterity if the "Delete item descendants" option is picked
	 *
	 * @param array $params Parameters.
	 */
	public function delete_item_descendants( $params = array() ) {
		// To emable deleting item descendants. Add it to the configure.php file. Only do it to if you have daily backup and backup before deleting an item. The plugin author is not responsible for any data loss.
		if ( ! defined( 'PANDA_PODS_REPEATER_DELETE_ITEM_DESCENDANTS' ) ) {
			return false;
		}
		global $wpdb;
		// A filter to decide if you want to carry on.
		$carry_on = true;
		$carry_on = apply_filters( 'pprf_carry_on_delete_item_descendants', $carry_on, $params );

		if ( true !== $carry_on ) {
			return false;
		}

		$defaults = array(
			'pod_name' => '',
			'item_id'  => 0,
		);
		$args     = wp_parse_args( $params, $defaults );
		if ( empty( $args['pod_name'] ) || empty( $args['item_id'] ) ) {
			return false;
		}
		$now              = gmdate( 'Y-m-d H:i:s' );
		$args['pod_name'] = esc_sql( $args['pod_name'] );
		$args['item_id']  = (int) $args['item_id'];

		$pod = pods( $args['pod_name'] );

		$pod_fields = $pod->fields();

		if ( $pod_fields ) {
			foreach ( $pod_fields as $field_name => $field_data ) {
				if ( 'pandarepeaterfield' === $field_data->type ) {

					if ( ! empty( $field_data->pandarepeaterfield_delete_data_tree ) ) {

						$for_child_data = array(
							'child_pod_name'      => $field_data->pandarepeaterfield_table,
							'parent_pod_id'       => $pod->pod_data->id,
							'parent_pod_post_id'  => $args['item_id'],
							'parent_pod_field_id' => $field_data->id,
						);

						$for_pandarf_items = array(
							'include_trashed' => true,
						);
						$child_data        = get_pandarf_items( $for_child_data, $for_pandarf_items );
						$for_repeater_pod  = array(
							'pod_name' => $field_data->pandarepeaterfield_table,
							'item_id'  => 0,
						);
						// Delete data.
						if ( ! empty( $child_data ) ) {
							foreach ( $child_data as $child ) {
								// Send the child pod into the same procedure. Do it before deleting the parent item so if something goes wrong, the parent item is still available.
								if ( ! empty( $child['id'] ) ) {
									$for_repeater_pod['item_id'] = $child['id'];
									$this->delete_item_descendants( $for_repeater_pod );
									$table = $wpdb->prefix . 'pods_' . $field_data->pandarepeaterfield_table;
									$table = esc_sql( sanitize_text_field( wp_unslash( $table ) ) );
									$query = $wpdb->prepare(
										// phpcs:ignore
										'DELETE FROM `' . $table . '` WHERE `id` = %d', array( $child['id'] ) 
									);
									$wpdb->query(
										// phpcs:ignore
										$query 
									); // db call ok. no cache ok.
								}
							}
						}
					}
				}
			}
		}
	}
}
