<?php
/**
 * field by iframe
 *
 * @package panda-pods-repeater-field
 * @author Dongjie Xu
 * @since 09/02/2016 
 */

/** WordPress Administration Bootstrap */
//require_once( '../../../../wp-admin/admin.php' );
//include_once( ABSPATH . 'wp-admin/admin.php' );
define( 'WP_USE_THEMES', false ); // get pass the http_host problem


$isAdmin_bln	=	false;

if( strpos( $_SERVER['REQUEST_URI'], 'wp-admin') && isset( $_GET['page'] ) && $_GET['page'] == 'panda-pods-repeater-field' ){ // is_admin doesn't work for nested fields
	$isAdmin_bln	=	true;	
} else {
	require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';
	wp_head();
}

$_GET = array_map('wp_strip_all_tags', $_GET);
?>

<?php
//show_admin_bar( false );
/*$parentPath_str = '../';
if ( is_multisite() ){
	$parentPath_str = '../../';
}*/
$allow_bln = true;

if( !defined( 'PANDA_PODS_REPEATER_URL' ) || !is_user_logged_in() || !current_user_can('edit_posts')  ){
	// action before the iframe
	$allow_bln = false;
	
}
global $current_user;
$parent_pod = false; 
if( isset( $_GET['podid'] ) && is_numeric( $_GET['podid'] ) ){
                
    //check it is an Advanced Content Type or normal post type
    $parent_arr	=	pprf_pod_details_fn( $_GET['podid'] );
                    
    if( $parent_arr ){
        $parentTb_str   =	$parent_arr['post_name'] ;
        $condit_arr    	=	array();
	    //normal post type fetch all published and draft posts
	    if( $parent_arr['type'] == 'post_type' ){
	        $condit_arr =     array( 'where' => 't.post_status = "publish" OR t.post_status = "draft"');
	    }

		$parent_pod 	= pods( $parentTb_str, $condit_arr ); 
		if( ! $allow_bln ){
			//get current field 
			foreach( $parent_pod->fields as $k => $child_fields ){
				if( $child_fields['id'] == $_GET['poditemid']	&& $child_fields['type'] == 'pandarepeaterfield' ){
			
					if( isset( $child_fields['options']['pandarepeaterfield_public_access'] ) && $child_fields['options']['pandarepeaterfield_public_access'] == 1 ){ // allowed for public access
						$allow_bln = true;
					} else {
						// $child_fields['options']['pandarepeaterfield_role_access'] has no value. It is saved into the _postmeta
						if( is_user_logged_in() ){
							foreach( $current_user->roles as $role_str ){ // the user role can access
								$ok_ukn	=	get_post_meta( $child_fields['id'], $role_str, true );
								if( $ok_ukn ){
									$allow_bln = true;
									break;
								}
							}						
						}
						//if( get_post_meta()
						
					}

					break;
				}
			}	
		}	
	}

}


$allow_bln = apply_filters( 'pprf_load_panda_repeater_allow', $allow_bln, $_GET );
if( !$allow_bln ){
	echo '<div class="mg10">';
	die( apply_filters( 'pprf_load_panda_repeater_allow_msg', esc_html__('You do not have permission to load this item.', 'panda-pods-repeater-field' ) ) );
	echo '</div>';
}

//include_once( ABSPATH . 'wp-admin/admin-header.php' );

global $current_user;
//
//print_r( $_SERVER );
?>
<?php 
$iframeID_int  = isset( $_GET['iframe_id'] ) ? esc_attr( $_GET['iframe_id'] ) : ''; 
$piframeID_int = isset( $_GET['piframe_id'] ) ?  esc_attr( $_GET['piframe_id'] ) : ''; 
$wid_int	   = 25;
$wid_str		= 'quater';
if( isset( $_GET['poditemid'] ) && is_numeric( $_GET['poditemid'] ) ){
	$wid_int  = get_post_meta( absint( $_GET['poditemid'] ), 'pandarepeaterfield_field_width' , true);
	
}
if( !is_numeric( $wid_int ) || $wid_int == 0 ){
	$wid_int  = 25;	
}
$mgr_int	=	0;
if( $wid_int != 100 ){
	$wid_int	=	$wid_int - 1;
	$mgr_int	=	1;
} else {
	$wid_str	= 'full';
}
if( $wid_int == 50 ){
	$wid_str	= 'half';
}
?>
<style>
html {
    margin-top: 0px !important; 
}	
@media  (min-width: 992px) {
.pods-form-fields .pods-field {
	width: <?php echo esc_html( $wid_int );?>%;
	margin-right: <?php echo esc_html( $mgr_int ); ?>%;
}

}
@media (max-width: 991px) and (min-width: 769px) { 
.pods-form-fields .pods-field {
	width: <?php echo esc_html( $wid_int );?>%;
	margin-right: <?php echo esc_html( $mgr_int ); ?>%;	
}

}
</style>

<?php
echo '<div id="pprf-form" class="pprf-wid-' . esc_attr( $wid_str ) . '">';
$get_data = isset( $_GET )? $_GET : array();
do_action('pandarf_item_top', $get_data );

if( isset( $_GET['tb'] ) && is_numeric( $_GET['tb'] ) && array_key_exists( 'pod_' . $_GET['tb'], PodsField_Pandarepeaterfield::$actTbs_arr ) ) {
	
	$table_name  = PodsField_Pandarepeaterfield::$actTbs_arr[ 'pod_' . $_GET['tb'] ];

	if( isset( $_GET['itemid'] ) && is_numeric( $_GET['itemid'] ) ){		
		$pod_cla = pods( $table_name, absint( $_GET['itemid'] ) );
	} else {
		$pod_cla = pods( $table_name );
	}
	
	// Output a form with all fields
	echo $pod_cla->form( array(), 'Save ' . get_the_title( absint( $_GET['poditemid'] ) ) ); 

	if( isset( $_GET['itemid'] ) && is_numeric( $_GET['itemid'] ) && isset( $_GET['podid'] ) && is_numeric( $_GET['podid'] ) ) { //&& array_key_exists( 'pod_' . $_GET['podid'], PodsField_Pandarepeaterfield::$actTbs_arr ) 


		if( $parent_pod ){
		   /* $condit_arr	=	array();
			//normal post type fetch all published and draft posts
			if( $parent_arr['type'] == 'post_type' ){
				$condit_arr =	array( 'where' => 't.post_status = "publish" OR t.post_status = "draft"');
			}

			$parent_pod 	= pods( $parentTb_str, $condit_arr ); */
			
			$reassignable	= false;
			//get current field 
			foreach( $parent_pod->fields as $k => $child_fields ){
				if( $child_fields['id'] == $_GET['poditemid']	&& $child_fields['type'] == 'pandarepeaterfield' ){
					$child_table_name	=	$child_fields['options']['pandarepeaterfield_table'];		
					if( isset( $child_fields['options']['pandarepeaterfield_allow_reassign'] ) && $child_fields['options']['pandarepeaterfield_allow_reassign'] == 1 ){
						$reassignable	= true;
					}
					break;
				}
			}

			//If reassigning allowed
			if( $reassignable ){
				$same_child_fields	=	pprf_same_child_tb_fields_fn( $parent_pod, $child_table_name );				
				//$all_rows = $parent_pod->data(); 
				$parents_html	= '';
			    if ( 0 < $parent_pod->total() ) { 
			    	$parents_html	=	'<div class="pprf-left mgt10 mgb15 w100" id="pprf-bottom-wrap">';
			    	$parents_html	.=	'<label class="pprf-left"><strong class="mgr10 mgt5">' . esc_html__('Assign to parent: ', 'panda-pods-repeater-field' ) . '</strong>';
			    	$parents_html	.=	'<select name="pprf_parent_items pprf-left mgt5" id="pprf-parent-items-sel" class="pprf-in-iframe-sel">';		    	
			        while ( $parent_pod->fetch() ) { 		
			        	$selected_str	=	'';
			        	if( $parent_pod->display( 'id' ) == $_GET['postid'] ){
			        		$selected_str	=	'selected = "selected"';
			        	}
			        	$parents_html	.=	'<option ' . $selected_str . ' value="' . esc_attr( $parent_pod->display( 'id' ) ) . '">' . esc_attr( $parent_pod->display( 'name' ) ) . '</option>'; 
			        	
					}
					$parents_html	.=	'</select>';
					$parents_html	.=	'</label>';
			    	$parents_html	.=	'<label class="pprf-left"><strong class="mgr10 mgt5">' . esc_html__('field: ', 'panda-pods-repeater-field' ) . '</strong>';
			    	$parents_html	.=	'<select name="pprf_field pprf-left mgt5" id="pprf-field-sel"  class="pprf-in-iframe-sel">';		    	
			        foreach( $same_child_fields as $k => $child_fields ){
			        	$selected_str	=	'';
			        	if( $child_fields['id'] == $_GET['poditemid']	&& $child_fields['type'] == 'pandarepeaterfield' ){
			        		$selected_str	=	'selected = "selected"';
			        	}
			        	$parents_html	.=	'<option ' . $selected_str . ' value="' . esc_attr( $child_fields['id'] ) . '">' . esc_attr( $child_fields['label'] ) . '</option>'; 
			        	
					}
					$parents_html	.=	'</select>';
					$parents_html	.=	'</label>';				
					$parents_html	.=	'<label class="pprf-left">';
					$parents_html	.=	'<button id="pprf-reassign-btn" class="pprf-btn pprf-left mgr10">' . esc_html__('Assign', 'panda-pods-repeater-field' ) . '</button>';

					$parents_html	.=	'<div id="pprf-reassign-loader" class="hidden pprf-left">	
											<img src = "' . esc_url( PANDA_PODS_REPEATER_URL . 'images/dots-loading.gif' ) . '" alt="loading" class="pdt10"/>
										 </div>	';		
					$parents_html	.=	'</label>';									 	
					$parents_html	.=	'</div>';
				}
				echo $parents_html;

			}
		}
	}	
} else {
	echo esc_html__('Invalid table', 'panda-pods-repeater-field' );
}
echo '</div>';
?>
<div id="pprf-on-page-data" data-saved="0"></div>
<br/>
<br/>
<div class="click-to-close-arrow aligncenter" title="Click this bar to close" ><?php esc_html_e('Click here to collapse', 'panda-pods-repeater-field' ); ?></div>

<?php
//include_once( ABSPATH . 'wp-admin/admin-footer.php' );
?>
<script type="text/javascript">

var pprf_loaded_resized 	= false;
var pprf_bottom_wrap_height = jQuery('html body #pprf-bottom-wrap').height() || 0;
// height before each click, 60 is for the padding top and bottom
var pprf_orgHei_int = jQuery('html body #pprf-form').height() + pprf_bottom_wrap_height + 60;
// height on load, 40 is for the padding top and bottom
var pprf_test_orgHei_int      = jQuery('html body #pprf-form').height() + pprf_bottom_wrap_height + 60;
function pprf_resize_fn( new_height ) { 
	
	pprf_bottom_wrap_height = jQuery('html body #pprf-bottom-wrap').height() || 0;
	if( typeof new_height == 'undefined' ){
		pprf_orgHei_int = jQuery('html body #pprf-form').height() +  pprf_bottom_wrap_height + 60;
		
	} else {
		pprf_orgHei_int = new_height;
		
	}	

	pprf_update_parent_fn();
	//parent.pprfParentheight;
}

function pprf_update_parent_fn() { 

	var height = pprf_orgHei_int;
	if( jQuery('.media-modal').length != 0 ){
		height = jQuery('.media-modal').height() + pprf_orgHei_int;	
	}
	
	if( jQuery('#ui-datepicker-div').length != 0 && jQuery('#ui-datepicker-div').css('display') == 'block' ){
		height += jQuery('#ui-datepicker-div').height();
	} else {
		if( jQuery('#ui-colorpicker-div').length != 0 && jQuery('#ui-colorpicker-div').css('display') == 'block' ){
			height += jQuery('#ui-colorpicker-div').height();
		}		
	}
	
	if( typeof parent.pprf_updateIframeSize_fn == 'function' ){ 
		parent.pprf_updateIframeSize_fn('<?php echo esc_attr( $iframeID_int ); ?>', height);	
	}
	<?php
	if( $piframeID_int != '' ){
	?>
	//call the resize function in the parent iframe, nested iframe only
	//parent.pprf_resize_fn();
	
	<?php
	}
	?>
	pprf_loaded_resized = false;
}
   
jQuery(document).ready( function($) {
	
	<?php
	if( isset( $_GET['itemid'] ) ){
	?>
	$('#pprf-reassign-btn').on('click', function(){
		var data_obj = {
			action 		: 	'admin_pprf_reassign_fn',		
			security 	: 	ajax_script.nonce,
			podid 		:  	'<?php echo esc_js( $_GET['podid'] ); ?>',			
			cpodid		:   '<?php echo esc_js( $_GET['tb'] ); ?>',			
			postid		: 	$('#pprf-parent-items-sel').val(),
			poditemid	: 	$('#pprf-field-sel').val(),
			curPItemid	: 	'<?php echo esc_js( $_GET['poditemid'] );?>',
			itemid		: 	'<?php echo esc_js( $_GET['itemid'] );?>',
		};
		$('#pprf-reassign-loader').removeClass('hidden');
		$.post(
			ajax_script.ajaxurl, 
			data_obj, 
			function( response_obj ){				
				$('#pprf-reassign-loader').addClass('hidden');
				if( response_obj['success'] == true && response_obj['data']['updated'] ){					
					parent.pprf_reassign_fn( data_obj['cpodid'], data_obj['curPItemid'], data_obj['itemid'] );
				}
			}
		);	
	})
	<?php
	}
	?>
	
	//remove update messages
	$('.updated, .update-nag').remove();
	// remove admin outlook
	$('#adminmenuback, #adminmenuwrap, #wpadminbar, #wpfooter, #screen-meta-links, #screen-meta').remove();

	$('.pods-form-ui-field-type-pick').on('change', function(){				
		pprf_resize_fn( );		
	})
	//$( this ).on( 'click, mouseenter, mouseout, mouseleave', function(){
	$( '.click-to-expand, .click-to-expand-arrow' ).on( 'click', function(){
		
		// if the iframe has not been resized		
		//if( pprf_loaded_resized == false ){
			pprf_resize_fn();
			
			pprf_loaded_resized = true;
		//}
	})
	$( '.click-to-close, .click-to-close-arrow' ).on( 'click', function(){				
		pprf_resize_fn( 150 );
		pprf_loaded_resized = false;
	})

	
	<?php
	if( isset( $_GET ) && count( $_GET ) > 0 ){
		foreach( $_GET as $key => $value ){
			$_GET[ $key ]	= esc_attr( $value );
		}
	}
	// if successfully added a new one
	if( isset( $_GET['success'] ) && $_GET['success'] == 1 && isset( $_GET['iframe_id'] ) && strpos( $_GET['iframe_id'], 'panda-repeater-add-new' ) === 0 ){
		global $wpdb;
		$lastid = $wpdb->insert_id;
	?>
	parent.pprf_new_fn( <?php echo $_GET['podid'];?>, "<?php echo $_GET['postid'];?>", <?php echo $_GET['tb'];?>, <?php echo $current_user->ID;?>, '<?php echo $_GET['iframe_id']; ?>', <?php echo $_GET['poditemid']; ?>, '<?php echo esc_attr( get_the_title( $_GET['poditemid'] ) ); ?>' );
	<?php	
	}
	// disable it until I am sure it is not needed
	if( 1 == 0 && isset( $_GET['itemid'] ) && is_numeric( $_GET['itemid'] ) ){	
	?>
	// tell the parent window to delete this item
	$('.panda-repeater-field-delete-bn').on('click', function(){
		parent.pprf_delete_item_fn( <?php echo $_GET['podid'];?>, "<?php echo $_GET['postid'];?>", <?php echo $_GET['tb'];?>, <?php echo $_GET['itemid'];?>, <?php echo $current_user->ID;?>, '<?php echo $_GET['iframe_id']; ?>', <?php echo $_GET['poditemid']; ?> );
		pprf_resize_fn();
	});
	<?php	
	}
	
	?>	
	// saved, so don't popup the confirm box to ask if to ignore changes
	// $('.pods-submit-button').click( function (){		
	$( document ).on('click', '.pods-submit-button', function (){			
		parent.pprfChanged_bln	=	false;		
	 })	
	$('.pods-field-input').on('click keyup change', function(){	 
		parent.pprfChanged_bln	=	true;		
	});	 

	/**
	 * after running all javascript, resize the window 
	 */
	//$(window).load(function(){
		pprf_resize_fn();
	//} );
})  		 
</script>
<script type="text/javascript">
	
if ( window == window.top ) {
   document.body.innerHTML = 'Access denied!';
}
</script>
<?php
if( !$isAdmin_bln ){
	wp_footer();
}