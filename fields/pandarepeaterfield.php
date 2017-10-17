<?php
/**
 * field by iframe
 *
 * @version: 1.0.0
 * @package panda-pods-repeater-field
 * @author Dongjie Xu
 * @since 09/02/2016 
 */

/** WordPress Administration Bootstrap */
//require_once( '../../../../wp-admin/admin.php' );
//include_once( ABSPATH . 'wp-admin/admin.php' );
show_admin_bar( false );
$parentPath_str = '../';
if ( is_multisite() ){
	$parentPath_str = '../../';
}
$allow_bln = true;

if( !defined( 'PANDA_PODS_REPEATER_URL' ) || !is_user_logged_in() || !current_user_can('edit_posts') ){
	// action before the iframe
	$allow_bln = false;
	
}
$allow_bln = apply_filters( 'pprf_load_panda_repeater_allow', $allow_bln, $_GET );
if( !$allow_bln ){
	die( apply_filters( 'pprf_load_panda_repeater_allow_msg', __('You do not have permission to edit this item.', 'panda-pods-repeater' ) ) );
}
//admin_enqueue_scripts
//add_action( 'admin_enqueue_scripts', 'embeded_fields_enqueue_fn' );
function embeded_fields_enqueue_fn(){
	//echo dirname( __FILE__ ) . '/admin.php';
	/*wp_enqueue_style( 'wp-mediaelement' );
	wp_enqueue_script( 'wp-mediaelement' );
	wp_localize_script( 'mediaelement', '_wpmejsSettings', array(
		'pluginPath' => includes_url( '../../../../wp-admin/js/mediaelement/', 'relative' ),
		'pauseOtherPlayers' => ''
	) );	*/

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
	$wid_int  = get_post_meta( $_GET['poditemid'], 'pandarepeaterfield_field_width' , true);
	
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

@media  (min-width: 992px) {
.pods-form-fields .pods-field {
	width: <?php echo $wid_int;?>%;
	margin-right: <?php echo $mgr_int; ?>%;
}

}
@media (max-width: 991px) and (min-width: 769px) { 
.pods-form-fields .pods-field {
	width: <?php echo $wid_int;?>%;
	margin-right: <?php echo $mgr_int; ?>%;	
}

}
</style>

<?php
echo '<div class="pprf-wid-' . $wid_str . '">';
$get_arr = isset( $_GET )? $_GET : array();
do_action('pandarf_item_top', $get_arr );

if( isset( $_GET['tb'] ) && is_numeric( $_GET['tb'] ) && array_key_exists( 'pod_' . $_GET['tb'], PodsField_Pandarepeaterfield::$actTbs_arr ) ) {
	
	$tb_str  = PodsField_Pandarepeaterfield::$actTbs_arr[ 'pod_' . $_GET['tb'] ];

	if( isset( $_GET['itemid'] ) && is_numeric( $_GET['itemid'] ) ){		
		$pod_cla = pods( $tb_str, $_GET['itemid'] );
		//echo '<span class="alignleft"><strong>' . get_the_title( $_GET['poditemid'] ) . ' ID:</strong> ' . $_GET['itemid'] . '</span>';
	} else {
		$pod_cla = pods( $tb_str );
	}
	//echo '<span class="alignright">Click left <img src="../../wp-content/plugins/panda-pods-repeater-field/fields/images/arrow-down.png" alt="expand"/> bar to expand. Click right <img src="../../wp-content/plugins/panda-pods-repeater-field/fields/images/arrow-up.png" alt="close"/> bar to close</span>';
	// Output a form with all fields
	
	echo $pod_cla->form( array(), 'Save ' . get_the_title( $_GET['poditemid'] ) ); 
	//if( isset( $_GET['itemid'] ) && is_numeric( $_GET['itemid'] ) ){		
		//echo '<button class="panda-repeater-field-delete-bn" data-cpodid="' . $_GET['tb'] . '" data-itemid="' . $_GET['itemid'] . '" >Delete</button>';
	//}
} else {
	exit();	
}
echo '</div>';
?>
<div id="pprf-on-page-data" data-saved="0"></div>
<!--
<div class="click-to-expand" ></div>
<div class="click-to-close" ></div>
<div class="click-to-expand-arrow" title="Click this bar to expand"></div>-->
<div class="click-to-close-arrow aligncenter" title="Click this bar to close" >Click here to contrast</div>
<!--
<div class="click-to-expand-arrow bottom" title="Click this bar to expand"></div>
<div class="click-to-close-arrow bottom" title="Click this bar to close" ></div>-->
<?php
include_once( ABSPATH . 'wp-admin/admin-footer.php' );
?>
<script type="text/javascript">
var pprf_loadedResized_bln = false;

// height before each click, 60 is for the padding top and bottom
var pprf_orgHei_int = jQuery('html #wpbody-content').height() + 60;
// height on load, 60 is for the padding top and bottom
var orgHei_int      = jQuery('html #wpbody-content').height() + 60;
function pprf_resize_fn( hei_int ) { 
	
	if( typeof hei_int == 'undefined' ){
		pprf_orgHei_int = jQuery('html #wpbody-content').height() + 60;
	} else {
		pprf_orgHei_int = hei_int;
	}
	pprf_update_parent_fn();
	//parent.parentHei_int;
}

function pprf_update_parent_fn() { 

	var hei_int = pprf_orgHei_int;
	if( jQuery('.media-modal').length != 0 ){
		hei_int = jQuery('.media-modal').height() + pprf_orgHei_int;	
	}
	
	if( jQuery('#ui-datepicker-div').length != 0 && jQuery('#ui-datepicker-div').css('display') == 'block' ){
		hei_int += jQuery('#ui-datepicker-div').height();
	} else {
		if( jQuery('#ui-colorpicker-div').length != 0 && jQuery('#ui-colorpicker-div').css('display') == 'block' ){
			hei_int += jQuery('#ui-colorpicker-div').height();
		}		
	}
	
	if( typeof updateIframeSize == 'function' ){
		parent.updateIframeSize('<?=$iframeID_int ?>', hei_int);	
	}
	<?php
	if( $piframeID_int != '' ){
	?>
	//call the resize function in the parent iframe, nested iframe only
	//parent.pprf_resize_fn();
	
	<?php
	}
	?>
	pprf_loadedResized_bln = false;
}
   
jQuery(document).ready( function($) {
	
	// append a div for click
	//$( '#wpcontent' ).prepend('<div style="height:100%; width:100%; background:#ccc; position: absolute; top: 0; left: 0; " id="expand-div"></div>');
/*	$('.toplevel_page_panda-pods-repeater-field').on('click', function(){
		//console.log('ddd');
		pprf_resize_fn();
	});*/
	
	//remove update messages
	$('.updated, .update-nag').remove();
	// remove admin outlook
	$('#adminmenuback, #adminmenuwrap, #wpadminbar, #wpfooter, #screen-meta-links, #screen-meta').remove();
	pprf_resize_fn();
	$('.pods-form-ui-field-type-pick').on('change', function(){				
		pprf_resize_fn( );		
	})
	//$( this ).on( 'click, mouseenter, mouseout, mouseleave', function(){
	$( '.click-to-expand, .click-to-expand-arrow' ).on( 'click', function(){
		
		// if the iframe has not been resized		
		//if( pprf_loadedResized_bln == false ){
			pprf_resize_fn();
			
			pprf_loadedResized_bln = true;
		//}
	})
	$( '.click-to-close, .click-to-close-arrow' ).on( 'click', function(){				
		// if the iframe has not been resized		
		//if( pprf_loadedResized_bln == true ){
			pprf_resize_fn( 150 );
			pprf_loadedResized_bln = false;
		//}
	})
/*	.children().click(function(e) {
		console.log( e );
	  //return false;
	});*/
	/*$('.wp-toolbar').on( 'click', function(){	
		 return false;
	}).children().click(function(e) {
	  return false;
	});*/
	//pprf_resize_fn();
	//var iframeWin = window.parent.document.getElementById('<?= $iframeID_int ?>-<?= $_GET['poditemid']; ?>').contentWindow;
	//console.log( iframeWin );
    parent.window.addEventListener('resize', function(){
       // console.log( jQuery('html').height() );
		pprf_resize_fn() ;
    });	
	
	<?php
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
	 $('.pods-submit-button').click( function (){		
		parent.pprfChanged_bln	=	false;		
	 })	
	$('.pods-field-input').on('click keyup change', function(){	 
		parent.pprfChanged_bln	=	true;		
	});	 
}).click( 
	//pprf_resize_fn

);   		 

//window.onclick = function() { pprf_resize_fn();}
        
</script>


