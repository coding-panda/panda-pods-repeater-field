<?php
/**
 * Field by iframe
 *
 * @package panda-pods-repeater-field
 * @author Dongjie Xu
 * @since 09/02/2016
 */

define( 'WP_USE_THEMES', false ); // Get pass the http_host problem.

global $current_user, $wpdb;

$is_admin = false;

if ( isset( $_SERVER['REQUEST_URI'] ) ) { // Is_admin doesn't work for nested fields.
	if ( false === function_exists( 'sanitize_text_field' ) ) {
		die( 'No frontend.' );
	}
	$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	if ( false !== strpos( $request_uri, 'wp-admin' ) ) {
		$is_admin = true;
	}
}
if ( false === $is_admin ) {
	die( 'No frontend.' );
}
$security_checked = false;
if ( isset( $_GET['pprf_nonce'] ) ) {
	$pprf_nonce = sanitize_text_field( wp_unslash( $_GET['pprf_nonce'] ) );
	if ( wp_verify_nonce( $pprf_nonce, 'load-pprf-page' ) ) {
		$security_checked = true;
	}
}
if ( false === $security_checked ) {
	die( 'Invalid nonce.' );
}
if ( ! isset( $_GET['page'] ) || 'panda-pods-repeater-field' !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
	die( 'Not a repeater page.' );
}

// Sanitize $_GET values.
$item_id         = isset( $_GET['itemid'] ) ? (int) $_GET['itemid'] : '';
$pod_id          = isset( $_GET['podid'] ) ? (int) $_GET['podid'] : '';
$success         = isset( $_GET['success'] ) ? (int) $_GET['success'] : '';
$current_post_id = isset( $_GET['postid'] ) ? (int) $_GET['postid'] : '';
$pod_item_id     = isset( $_GET['poditemid'] ) ? (int) $_GET['poditemid'] : '';
$pod_table_id    = isset( $_GET['tb'] ) ? (int) $_GET['tb'] : '';

$is_allowed = true;

if ( ! defined( 'PANDA_PODS_REPEATER_URL' ) || ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
	// Action before the iframe.
	$is_allowed = false;

}

$parent_pod = false;
if ( isset( $pod_id ) && is_numeric( $pod_id ) ) {

	// Check it is an Advanced Content Type or normal post type.
	$parent_details = pprf_pod_details( $pod_id );

	$parent_pages  = 1;
	$parrent_limit = 20;
	if ( $parent_details ) {
		$parent_table = $parent_details['post_name'];

		// Normal post type fetch all published and draft posts.
		if ( 'post_type' === $parent_details['type'] ) {

			$conditions['where'] = 't.post_status = "publish" OR t.post_status = "draft"';

		}
		$parent_details['pprf_parent'] = intval( $current_post_id );

		$conditions = pprf_parent_filter_conditions( $parent_details, $parrent_limit );
		if ( ! empty( $conditions['limit'] ) ) {
			$parrent_limit = $conditions['limit'];
		}

		$parent_pod = pods( $parent_table, $conditions );

		$parents_total = $parent_pod->total_found();

		$parent_pages = $parent_pod->total_pages();

	}
}

$get_data = array();
if ( isset( $_GET ) ) {
	$get_data = wp_unslash( $_GET );
	$get_data = array_map( 'sanitize_text_field', $get_data );
}

$is_allowed = apply_filters( 'pprf_load_panda_repeater_allow', $is_allowed, $get_data );
if ( false === $is_allowed ) {
	die( 'You do not have permission to load this item.' );
}
/**
 * Validate iframe id
 *
 * @param string $iframe_id A iframe id.
 *
 * @return boolean If the iframe id is validated.
 */
function pprf_validate_iframe_id( $iframe_id ) {
	if ( 'panda-repeater-add-new' === $iframe_id ) {
		return true;
	}
	if ( 0 !== strpos( $iframe_id, 'panda-repeater-add-new-' ) && 0 !== strpos( $iframe_id, 'panda-repeater-edit-' ) ) {
		return false;
	}
	if ( 0 === strpos( $iframe_id, 'panda-repeater-add-new-' ) ) {
		$parts = explode( '-', str_replace( 'panda-repeater-add-new-', '', $iframe_id ) );
		if ( 2 !== count( $parts ) ) {
			return false;
		}
		if ( ! is_numeric( $parts[0] ) || ! is_numeric( $parts[1] ) ) {
			return false;
		}
	}
	if ( 0 === strpos( $iframe_id, 'panda-repeater-edit-' ) ) {
		$parts = explode( '-', str_replace( 'panda-repeater-edit-', '', $iframe_id ) );
		if ( 3 !== count( $parts ) ) {
			return false;
		}
		if ( ! is_numeric( $parts[0] ) || ! is_numeric( $parts[1] ) || ! is_numeric( $parts[2] ) ) {
			return false;
		}
	}
	return true;
}

$iframe_id = isset( $_GET['iframe_id'] ) ? sanitize_text_field( wp_unslash( $_GET['iframe_id'] ) ) : '';

$iframe_id_valid = pprf_validate_iframe_id( $iframe_id );
if ( false === $iframe_id_valid ) {
	die( 'Invalid iframe ID.' );
}

$parent_iframe_id = isset( $_GET['piframe_id'] ) ? sanitize_text_field( wp_unslash( $_GET['piframe_id'] ) ) : '';
if ( '' !== $parent_iframe_id ) {
	$iframe_id_valid = pprf_validate_iframe_id( $parent_iframe_id );
	if ( false === $iframe_id_valid ) {
		die( 'Invalid parent iframe ID.' );
	}
}

$wid_int = 25;
$wid_str = 'quater';
if ( is_numeric( $pod_item_id ) ) {
	$wid_int = get_post_meta( absint( $pod_item_id ), 'pandarepeaterfield_field_width', true );
}
if ( ! is_numeric( $wid_int ) || 0 === $wid_int ) {
	$wid_int = 25;
}
$mgr_int = 0;
if ( 100 !== $wid_int ) {
	$wid_int = --$wid_int;
	$mgr_int = 1;
} else {
	$wid_str = 'full';
}
if ( 50 === $wid_int ) {
	$wid_str = 'half';
}
?>
<style>
html {
	margin-top: 0px !important; 
}	
@media  (min-width: 992px) {
.pods-form-fields .pods-field {
	width: <?php echo esc_html( $wid_int ); ?>%;
	margin-right: <?php echo esc_html( $mgr_int ); ?>%;
}

}
@media (max-width: 991px) and (min-width: 769px) { 
.pods-form-fields .pods-field {
	width: <?php echo esc_html( $wid_int ); ?>%;
	margin-right: <?php echo esc_html( $mgr_int ); ?>%;	
}

}
</style>

<?php
echo '<div id="pprf-form" class="pprf-wid-' . esc_attr( $wid_str ) . '">';

do_action( 'pandarf_item_top', $get_data );

if ( '' !== $pod_table_id ) {

	global $panda_pods_repeater_field;

	if ( array_key_exists( 'pod_' . $pod_table_id, PodsField_Pandarepeaterfield::$act_tables ) ) {
		$table_name = PodsField_Pandarepeaterfield::$act_tables[ 'pod_' . $pod_table_id ];

		if ( isset( $item_id ) && is_numeric( $item_id ) ) {
			$pod_cla = pods( $table_name, absint( $item_id ) );
		} else {
			$pod_cla = pods( $table_name );
		}

		// Output a form with all fields.
		// phpcs:ignore
		echo $pod_cla->form( array(), 'Save ' . get_the_title( absint( $pod_item_id ) ) );

		if ( isset( $item_id ) && is_numeric( $item_id ) && isset( $pod_id ) && is_numeric( $pod_id ) ) {
			if ( $parent_pod ) {

				$reassignable = false;
				$duplicable   = false;
				// Get current field.
				foreach ( $parent_pod->fields as $k => $child_fields ) {
					if ( $child_fields['id'] === $pod_item_id && 'pandarepeaterfield' === $child_fields['type'] ) {
						$child_table_name = $child_fields['options']['pandarepeaterfield_table'];
						if ( isset( $child_fields['options']['pandarepeaterfield_allow_reassign'] ) && 1 === (int) $child_fields['options']['pandarepeaterfield_allow_reassign'] ) {
							$reassignable = true;
						}
						if ( isset( $child_fields['options']['pandarepeaterfield_allow_duplicate'] ) && 1 === (int) $child_fields['options']['pandarepeaterfield_allow_duplicate'] ) {
							$duplicable = true;
						}
						break;
					}
				}

				// If reassigning or duplicating is allowed.
				if ( $reassignable || $duplicable ) {

					$same_child_fields = pprf_same_child_tb_fields( $parent_pod, $child_table_name );

					$parents_html = '';

					if ( 0 < $parent_pod->total() ) {
						$parents_html = '<div class="pprf-left mgt10 mgb15 w100" id="pprf-bottom-wrap">';
						if ( $parent_pages > 1 ) {
							$parents_html .= '<label class="pprf-left"><strong class="mgr10 mgt5">' . esc_html__( 'Load parents: ', 'panda-pods-repeater-field' ) . '</strong>';
							$parents_html .= '<select name="pprf_field pprf-left mgt5" id="pprf-field-parent-loader"  class="pprf-in-iframe-sel">';
							for ( $i = 1; $i <= $parent_pages; $i ++ ) {
								$max = $parrent_limit * $i;

								if ( $i === $parent_pages && $parents_total < $max ) {
									$max = $parents_total;
								}
								$parents_html .= '<option value="' . $i . '">' . ( $parrent_limit * ( $i - 1 ) + 1 ) . ' - ' . $max . '</option>';

							}
							$parents_html .= '</select>';
							$parents_html .= '</label>';
						}

						$parents_html .= '<label class="pprf-left"><strong class="mgr10 mgt5">' . esc_html__( 'Assign to parent: ', 'panda-pods-repeater-field' ) . '</strong>';
						$parents_html .= '<select name="pprf_parent_items" id="pprf-parent-items-sel" class="pprf-in-iframe-sel">';
						while ( $parent_pod->fetch() ) {
							$selected_html = '';
							if ( $parent_pod->display( 'id' ) === $current_post_id ) {
								$selected_html = 'selected = "selected"';
							}
							$draft = '';

							if ( 'Draft' === $parent_pod->display( 'post_status' ) ) {
								$draft = ' - draft';
							}
							$parents_html .= '<option ' . $selected_html . ' value="' . esc_attr( $parent_pod->display( 'id' ) ) . '">ID: ' . esc_attr( $parent_pod->display( 'id' ) ) . ' - ' . esc_attr( $parent_pod->display( 'name' ) . $draft ) . '</option>';

						}
						$parents_html .= '</select>';
						$parents_html .= '</label>';
						$parents_html .= '<label class="pprf-left"><strong class="mgr10 mgt5">' . esc_html__( 'field: ', 'panda-pods-repeater-field' ) . '</strong>';
						$parents_html .= '<select name="pprf_field pprf-left mgt5" id="pprf-field-sel"  class="pprf-in-iframe-sel">';
						foreach ( $same_child_fields as $k => $child_fields ) {
							$selected_html = '';
							if ( $child_fields['id'] === $pod_item_id && 'pandarepeaterfield' === $child_fields['type'] ) {
								$selected_html = 'selected = "selected"';
							}
							$parents_html .= '<option ' . $selected_html . ' value="' . esc_attr( $child_fields['id'] ) . '">' . esc_attr( $child_fields['label'] ) . '</option>';

						}
						$parents_html .= '</select>';
						$parents_html .= '</label>';

						$parents_html .= '<label class="pprf-left">';
						if ( $reassignable ) {
							$parents_html .= '<button id="pprf-reassign-btn" class="pprf-btn pprf-left mgr10 mgt5">' . esc_html__( 'Assign', 'panda-pods-repeater-field' ) . '</button>';
						}
						if ( $duplicable ) {
							$parents_html .= '<button id="pprf-duplicate-btn" class="pprf-btn pprf-left mgr10 mgt5">' . esc_html__( 'Duplicate', 'panda-pods-repeater-field' ) . '</button>';
						}
						$parents_html .= '<div id="pprf-reassign-loader" class="hidden pprf-left">	
												<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="pdt10"/>
											 </div>	';
						$parents_html .= '</label>';

						$parents_html .= '</div>';
					}

					echo wp_kses(
						$parents_html,
						$panda_pods_repeater_field->allowed_html_tags
					);
				}
			}
		}
	}
} else {
	echo esc_html__( 'Invalid table', 'panda-pods-repeater-field' );
}
echo '<div id="pprf-reassign-ajax-message"></div>';
echo '</div>';
?>
<div id="pprf-on-page-data" data-saved="0"></div>
<br/>
<br/>
<div class="click-to-close-arrow aligncenter" title="Click this bar to close" ><?php esc_html_e( 'Click here to collapse', 'panda-pods-repeater-field' ); ?></div>

<script type="text/javascript">

var pprf_loaded_resized 	= false;
var pprf_bottom_wrap_height = jQuery('html body #pprf-bottom-wrap').height() || 0;
// height before each click, 60 is for the padding top and bottom
var pprf_original_height = jQuery('html body #pprf-form').height() + pprf_bottom_wrap_height + 60;
// height on load, 40 is for the padding top and bottom
var pprf_test_orgHei_int      = jQuery('html body #pprf-form').height() + pprf_bottom_wrap_height + 60;
function pprf_resize_window( new_height ) {

	pprf_bottom_wrap_height = jQuery('html body #pprf-bottom-wrap').height() || 0;
	if( 'undefined' === typeof new_height ){
		pprf_original_height = jQuery('html body #pprf-form').height() +  pprf_bottom_wrap_height + 60;
	} else {
		pprf_original_height = new_height;
	}

	pprf_update_parent();
}

function pprf_update_parent() {

	var height = pprf_original_height;
	if( 0 !== jQuery('.media-modal').length ){
		height = jQuery('.media-modal').height() + pprf_original_height;	
	}

	if( 0 !== jQuery('#ui-datepicker-div').length && 'block' === jQuery('#ui-datepicker-div').css('display') ){
		height += jQuery('#ui-datepicker-div').height();
	} else {
		if( 0 !== jQuery('#ui-colorpicker-div').length && 'block' === jQuery('#ui-colorpicker-div').css('display') ){
			height += jQuery('#ui-colorpicker-div').height();
		}		
	}

	if( typeof parent.pprf_update_iframe_size == 'function' ){ 
		parent.pprf_update_iframe_size('<?php echo esc_js( $iframe_id ); ?>', height);	
	}

	pprf_loaded_resized = false;
}
   
jQuery(document).ready( function($) {

	<?php
	if ( isset( $item_id ) ) {
		?>
	$('#pprf-reassign-btn').on('click', function(){
		var data_obj = {
			action 		: 	'admin_pprf_reassign',		
			security 	: 	ajax_script.nonce,
			podid 		:  	'<?php echo intval( $pod_id ); ?>',			
			cpodid		:   '<?php echo intval( $pod_table_id ); ?>',			
			postid		: 	$('#pprf-parent-items-sel').val(),
			poditemid	: 	$('#pprf-field-sel').val(),
			curPItemid	: 	'<?php echo intval( $pod_item_id ); ?>',
			itemid		: 	'<?php echo intval( $item_id ); ?>',
		};
		$('#pprf-reassign-loader').removeClass('hidden');
		$.post(
			ajax_script.ajaxurl, 
			data_obj, 
			function( response_obj ){				
				$('#pprf-reassign-loader').addClass('hidden');
				if( response_obj['success'] == true && response_obj['data']['updated'] ){					
					parent.pprf_reassign( data_obj['cpodid'], data_obj['curPItemid'], data_obj['itemid'] );
				}
			}
		);	
	})
	$('#pprf-duplicate-btn').on('click', function(){
		var data_obj = {
			action 		: 	'admin_pprf_duplicate',		
			security 	: 	ajax_script.nonce,
			podid 		:  	'<?php echo intval( $pod_id ); ?>',			
			cpodid		:   '<?php echo intval( $pod_table_id ); ?>',		
			postid		: 	'<?php echo intval( $current_post_id ); ?>',		
			new_post_id	: 	$('#pprf-parent-items-sel').val(),
			poditemid	: 	$('#pprf-field-sel').val(),
			curPItemid	: 	'<?php echo intval( $pod_item_id ); ?>',
			item_id		: 	'<?php echo intval( $item_id ); ?>',
		};
		$('#pprf-reassign-loader').removeClass('hidden');
		$('#pprf-reassign-ajax-message').html( '' );
		$.post(
			ajax_script.ajaxurl, 
			data_obj, 
			function( response_obj ){				
				$('#pprf-reassign-loader').addClass('hidden');
				$('#pprf-reassign-ajax-message').html( response_obj['data']['message'] );
				pprf_resize_window();
			}
		);	
	})

	$('#pprf-field-parent-loader').on('change', function(){
		var data_obj = {
			action 		: 	'admin_pprf_load_parent_items',		
			security 	: 	ajax_script.nonce,
			podid 		:  	'<?php echo intval( $pod_id ); ?>',			
			cpodid		:   '<?php echo intval( $pod_table_id ); ?>',			
			page		: 	$('#pprf-field-parent-loader').val(),			
			curPItemid	: 	'<?php echo intval( $pod_item_id ); ?>',
			itemid		: 	'<?php echo intval( $item_id ); ?>',
			postid		: 	'<?php echo intval( $current_post_id ); ?>',
			limit		: 	'<?php echo (int) $parrent_limit; ?>',
		};
		$('#pprf-reassign-loader').removeClass('hidden');
		$.post(
			ajax_script.ajaxurl, 
			data_obj, 
			function( response_obj ){				
				$('#pprf-reassign-loader').addClass('hidden');
				if( response_obj['success'] == true && response_obj['data']['items'].trim() !== '' ){					
					$('#pprf-parent-items-sel').html( response_obj['data']['items'] );
				}
			}
		);	
	})	
		<?php
	}
	?>
	// Remove update messages.
	$('.updated, .update-nag').remove();
	// Remove admin outlook.
	$('#adminmenuback, #adminmenuwrap, #wpadminbar, #wpfooter, #screen-meta-links, #screen-meta').remove();

	$('.pods-form-ui-field-type-pick').on('change', function(){				
		pprf_resize_window( );		
	})

	$( '.click-to-expand, .click-to-expand-arrow' ).on( 'click', function(){
		// If the iframe has not been resized.
		pprf_resize_window();
		pprf_loaded_resized = true;		
	})

	$( '.click-to-close, .click-to-close-arrow' ).on( 'click', function(){				
		pprf_resize_window( 150 );
		pprf_loaded_resized = false;
	})

	<?php
	// If successfully added a new one.
	if ( 1 === $success && '' !== $iframe_id ) {

		if ( 0 === strpos( $iframe_id, 'panda-repeater-add-new' ) ) {
			global $wpdb;

			$item_title = wp_strip_all_tags( str_replace( array( '(', ')' ), '', get_the_title( $pod_item_id ) ) );
			?>
			parent.pprf_new( <?php echo (int) $pod_id; ?>, <?php echo (int) $current_post_id; ?>, <?php echo (int) $pod_table_id; ?>, <?php echo (int) $current_user->ID; ?>, '<?php echo esc_js( $iframe_id ); ?>', <?php echo (int) $pod_item_id; ?>, '<?php echo esc_js( $item_title ); ?>' );
			<?php
		}
	}

	?>

	// Saved, so don't popup the confirm box to ask if to ignore changes.	
	$( document ).on('click', '.pods-submit-button', function (){			
		parent.pprf_is_changed	= false;
	})
	$('.pods-field-input').on('click keyup change', function(){	 
		parent.pprf_is_changed	= true;
	});

	/**
	 * After running all javascript, resize the window.
	 */
	pprf_resize_window();

})  		 
</script>
<script type="text/javascript">
if ( window == window.top ) {
	document.body.innerHTML = 'Access denied!';
}
</script>
<?php
if ( ! $is_admin ) {
	wp_footer();
}
