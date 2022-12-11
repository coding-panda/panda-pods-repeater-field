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

global $current_user, $wpdb;

$is_admin	=	false;

if( strpos( $_SERVER['REQUEST_URI'], 'wp-admin') && isset( $_GET['page'] ) && $_GET['page'] == 'panda-pods-repeater-field' ){ // is_admin doesn't work for nested fields
	$is_admin	=	true;	
} else {
	require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';
	wp_head();
}
if( isset( $_GET ) && count( $_GET ) > 0 ){
	foreach( $_GET as $key => $value ){
		$_GET[ $key ]	= str_replace(array( '(', ')' ), '', esc_attr( $value ) );
	}
}
$_GET = array_map('wp_strip_all_tags', $_GET);

$int_fields = array( 'itemid', 'podid', 'success', 'postid', 'poditemid' );
foreach( $int_fields as $int_field ){
	if( isset( $_GET[ $int_field ] ) ){
		$_GET[ $int_field ] = intval( $_GET[ $int_field ] );
	}
}

$is_allowed = true;

if( !defined( 'PANDA_PODS_REPEATER_URL' ) || !is_user_logged_in() || !current_user_can('edit_posts')  ){
	// action before the iframe
	$is_allowed = false;
	
}

$parent_pod = false; 
if( isset( $_GET['podid'] ) && is_numeric( $_GET['podid'] ) ){
	//$pprf_db  = new panda_pods_repeater_field_db();                

    //check it is an Advanced Content Type or normal post type
    $parent_details	=	pprf_pod_details( $_GET['podid'] );
                    
    $parent_pages	= 	1;  
    $parrent_limit	= 	20;              
    if( $parent_details ){
        $parent_table   =	$parent_details['post_name'] ;
   //      $conditions    	=	array(
   //      		'limit' 	=> $parrent_limit,
   //      	);
	    //normal post type fetch all published and draft posts
	    if( $parent_details['type'] == 'post_type' ){

	        $conditions['where'] 	= 't.post_status = "publish" OR t.post_status = "draft"';	        					  	

	    }
        $parent_details['pprf_parent']  = intval( $_GET['postid'] );

	    $conditions 	= pprf_parent_filter_conditions( $parent_details, $parrent_limit );
	    if( ! empty( $conditions['limit'] ) ){
	    	$parrent_limit 	= $conditions['limit'];
	    }
	 	
		$parent_pod 	= pods( $parent_table, $conditions ); 

		$parents_total 	= $parent_pod->total_found();

		$parent_pages	= $parent_pod->total_pages(); //ceil( $parent_total / $parrent_limit );

		if( ! $is_allowed ){
			//get current field 
			foreach( $parent_pod->fields as $k => $child_fields ){
				if( $child_fields['id'] == $_GET['poditemid']	&& $child_fields['type'] == 'pandarepeaterfield' ){
			
					if( isset( $child_fields['options']['pandarepeaterfield_public_access'] ) && $child_fields['options']['pandarepeaterfield_public_access'] == 1 ){ // allowed for public access
						$is_allowed = true;
					} else {
						// $child_fields['options']['pandarepeaterfield_role_access'] has no value. It is saved into the _postmeta
						if( is_user_logged_in() ){
							foreach( $current_user->roles as $role ){ // the user role can access
								$ok	=	get_post_meta( $child_fields['id'], $role, true );
								if( $ok ){
									$is_allowed = true;
									break;
								}
							}						
						}
						
					}

					break;
				}
			}	
		}	
	}

}


$is_allowed = apply_filters( 'pprf_load_panda_repeater_allow', $is_allowed, $_GET );
if( !$is_allowed ){
	echo '<div class="mg10">';
	die( apply_filters( 'pprf_load_panda_repeater_allow_msg', esc_html__('You do not have permission to load this item.', 'panda-pods-repeater-field' ) ) );
	echo '</div>';
}

//include_once( ABSPATH . 'wp-admin/admin-header.php' );


//
//print_r( $_SERVER );
?>
<?php 
$iframe_id  		= isset( $_GET['iframe_id'] ) ? esc_attr( $_GET['iframe_id'] ) : ''; 
$parent_iframe_id 	= isset( $_GET['piframe_id'] ) ?  esc_attr( $_GET['piframe_id'] ) : ''; 
$wid_int	   		= 25;
$wid_str			= 'quater';
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

if( isset( $_GET['tb'] ) && is_numeric( $_GET['tb'] ) && array_key_exists( 'pod_' . $_GET['tb'], PodsField_Pandarepeaterfield::$act_tables ) ) {
	
	$table_name  = PodsField_Pandarepeaterfield::$act_tables[ 'pod_' . $_GET['tb'] ];

	if( isset( $_GET['itemid'] ) && is_numeric( $_GET['itemid'] ) ){		
		$pod_cla = pods( $table_name, absint( $_GET['itemid'] ) );
	} else {
		$pod_cla = pods( $table_name );
	}
	
	// Output a form with all fields
	echo $pod_cla->form( array(), 'Save ' . get_the_title( absint( $_GET['poditemid'] ) ) ); 

	if( isset( $_GET['itemid'] ) && is_numeric( $_GET['itemid'] ) && isset( $_GET['podid'] ) && is_numeric( $_GET['podid'] ) ) { //&& array_key_exists( 'pod_' . $_GET['podid'], PodsField_Pandarepeaterfield::$act_tables ) 


		if( $parent_pod ){
			
			$reassignable	= false;
			$duplicable		= false;
			//get current field 
			foreach( $parent_pod->fields as $k => $child_fields ){
				if( $child_fields['id'] == $_GET['poditemid']	&& $child_fields['type'] == 'pandarepeaterfield' ){
					$child_table_name	=	$child_fields['options']['pandarepeaterfield_table'];		
					if( isset( $child_fields['options']['pandarepeaterfield_allow_reassign'] ) && $child_fields['options']['pandarepeaterfield_allow_reassign'] == 1 ){
						$reassignable	= true;
					}
					if( isset( $child_fields['options']['pandarepeaterfield_allow_duplicate'] ) && $child_fields['options']['pandarepeaterfield_allow_duplicate'] == 1 ){
						$duplicable		= true;
					}					
					break;
				}
			}

			//If reassigning or duplicating is allowed
			if( $reassignable || $duplicable ){
				
				$same_child_fields	=	pprf_same_child_tb_fields( $parent_pod, $child_table_name );				
				//$all_rows = $parent_pod->data(); 
				$parents_html		= '';				

			    if ( 0 < $parent_pod->total() ) { 
			    	$parents_html	=	'<div class="pprf-left mgt10 mgb15 w100" id="pprf-bottom-wrap">';
					if( $parent_pages > 1 )	{
				    	$parents_html	.=	'<label class="pprf-left"><strong class="mgr10 mgt5">' . esc_html__('Load parents: ', 'panda-pods-repeater-field' ) . '</strong>';
				    	$parents_html	.=	'<select name="pprf_field pprf-left mgt5" id="pprf-field-parent-loader"  class="pprf-in-iframe-sel">';		    	
				        for( $i = 1; $i <= $parent_pages; $i ++ ){
				        	$max = $parrent_limit * $i;

				        	if( $i === $parent_pages && $parents_total < $max ){
				        		$max = $parents_total;
				        	}
				        	$parents_html	.=	'<option value="' . $i . '">' . ( $parrent_limit * ( $i - 1 ) + 1 ) . ' - ' . $max . '</option>'; 
				        	
						}
						$parents_html	.=	'</select>';
						$parents_html	.=	'</label>';							
					}

			    	$parents_html	.=	'<label class="pprf-left"><strong class="mgr10 mgt5">' . esc_html__('Assign to parent: ', 'panda-pods-repeater-field' ) . '</strong>';
			    	$parents_html	.=	'<select name="pprf_parent_items" id="pprf-parent-items-sel" class="pprf-in-iframe-sel">';		    	
			        while ( $parent_pod->fetch() ) { 		
			        	$selected_html	=	'';
			        	if( $parent_pod->display( 'id' ) == $_GET['postid'] ){
			        		$selected_html	=	'selected = "selected"';
			        	}
			        	$draft = '';
			        	
			        	if( $parent_pod->display( 'post_status' ) == 'Draft' ){
			        		$draft = esc_attr( ' - draft', 'panda-pods-repeater-field');
			        	}
			        	$parents_html	.=	'<option ' . $selected_html . ' value="' . esc_attr( $parent_pod->display( 'id' ) ) . '">ID: ' . esc_attr( $parent_pod->display( 'id' ) ) . ' - ' . esc_attr( $parent_pod->display( 'name' ) ) . $draft . '</option>'; 
			        	
					}
					$parents_html	.=	'</select>';
					$parents_html	.=	'</label>';
			    	$parents_html	.=	'<label class="pprf-left"><strong class="mgr10 mgt5">' . esc_html__('field: ', 'panda-pods-repeater-field' ) . '</strong>';
			    	$parents_html	.=	'<select name="pprf_field pprf-left mgt5" id="pprf-field-sel"  class="pprf-in-iframe-sel">';		    	
			        foreach( $same_child_fields as $k => $child_fields ){
			        	$selected_html	=	'';
			        	if( $child_fields['id'] == $_GET['poditemid']	&& $child_fields['type'] == 'pandarepeaterfield' ){
			        		$selected_html	=	'selected = "selected"';
			        	}
			        	$parents_html	.=	'<option ' . $selected_html . ' value="' . esc_attr( $child_fields['id'] ) . '">' . esc_attr( $child_fields['label'] ) . '</option>'; 
			        	
					}
					$parents_html	.=	'</select>';
					$parents_html	.=	'</label>';			

					$parents_html	.=	'<label class="pprf-left">';
					if( $reassignable ){
						$parents_html	.=	'<button id="pprf-reassign-btn" class="pprf-btn pprf-left mgr10 mgt5">' . esc_html__('Assign', 'panda-pods-repeater-field' ) . '</button>';
					}
					if( $duplicable ){
						$parents_html	.=	'<button id="pprf-duplicate-btn" class="pprf-btn pprf-left mgr10 mgt5">' . esc_html__('Duplicate', 'panda-pods-repeater-field' ) . '</button>';
					}
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
echo '<div id="pprf-reassign-ajax-message"></div>';
echo '</div>';
?>
<div id="pprf-on-page-data" data-saved="0"></div>
<br/>
<br/>
<div class="click-to-close-arrow aligncenter" title="Click this bar to close" ><?php esc_html_e('Click here to collapse', 'panda-pods-repeater-field' ); ?></div>

<script type="text/javascript">

var pprf_loaded_resized 	= false;
var pprf_bottom_wrap_height = jQuery('html body #pprf-bottom-wrap').height() || 0;
// height before each click, 60 is for the padding top and bottom
var pprf_original_height = jQuery('html body #pprf-form').height() + pprf_bottom_wrap_height + 60;
// height on load, 40 is for the padding top and bottom
var pprf_test_orgHei_int      = jQuery('html body #pprf-form').height() + pprf_bottom_wrap_height + 60;
function pprf_resize_window( new_height ) { 
	
	pprf_bottom_wrap_height = jQuery('html body #pprf-bottom-wrap').height() || 0;
	if( typeof new_height == 'undefined' ){
		pprf_original_height = jQuery('html body #pprf-form').height() +  pprf_bottom_wrap_height + 60;
		
	} else {
		pprf_original_height = new_height;
		
	}	

	pprf_update_parent();
	//parent.pprfParentheight;
}

function pprf_update_parent() { 

	var height = pprf_original_height;
	if( jQuery('.media-modal').length != 0 ){
		height = jQuery('.media-modal').height() + pprf_original_height;	
	}
	
	if( jQuery('#ui-datepicker-div').length != 0 && jQuery('#ui-datepicker-div').css('display') == 'block' ){
		height += jQuery('#ui-datepicker-div').height();
	} else {
		if( jQuery('#ui-colorpicker-div').length != 0 && jQuery('#ui-colorpicker-div').css('display') == 'block' ){
			height += jQuery('#ui-colorpicker-div').height();
		}		
	}
	
	if( typeof parent.pprf_update_iframe_size == 'function' ){ 
		parent.pprf_update_iframe_size('<?php echo esc_attr( $iframe_id ); ?>', height);	
	}
	<?php
	if( $parent_iframe_id != '' ){
	?>
	//call the resize function in the parent iframe, nested iframe only
	//parent.pprf_resize_window();
	
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
			action 		: 	'admin_pprf_reassign',		
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
					parent.pprf_reassign( data_obj['cpodid'], data_obj['curPItemid'], data_obj['itemid'] );
				}
			}
		);	
	})
	$('#pprf-duplicate-btn').on('click', function(){
		var data_obj = {
			action 		: 	'admin_pprf_duplicate',		
			security 	: 	ajax_script.nonce,
			podid 		:  	'<?php echo esc_js( $_GET['podid'] ); ?>',			
			cpodid		:   '<?php echo esc_js( $_GET['tb'] ); ?>',		
			postid		: 	'<?php echo esc_js( $_GET['postid'] ); ?>',		
			new_post_id	: 	$('#pprf-parent-items-sel').val(),
			poditemid	: 	$('#pprf-field-sel').val(),
			curPItemid	: 	'<?php echo esc_js( $_GET['poditemid'] );?>',
			item_id		: 	'<?php echo esc_js( $_GET['itemid'] );?>',
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
			podid 		:  	'<?php echo esc_js( $_GET['podid'] ); ?>',			
			cpodid		:   '<?php echo esc_js( $_GET['tb'] ); ?>',			
			page		: 	$('#pprf-field-parent-loader').val(),			
			curPItemid	: 	'<?php echo esc_js( $_GET['poditemid'] );?>',
			itemid		: 	'<?php echo esc_js( $_GET['itemid'] );?>',
			postid		: 	'<?php echo esc_js( $_GET['postid'] );?>',
			limit		: 	'<?php echo $parrent_limit;?>',
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
	//remove update messages
	$('.updated, .update-nag').remove();
	// remove admin outlook
	$('#adminmenuback, #adminmenuwrap, #wpadminbar, #wpfooter, #screen-meta-links, #screen-meta').remove();

	$('.pods-form-ui-field-type-pick').on('change', function(){				
		pprf_resize_window( );		
	})
	//$( this ).on( 'click, mouseenter, mouseout, mouseleave', function(){
	$( '.click-to-expand, .click-to-expand-arrow' ).on( 'click', function(){
		
		// if the iframe has not been resized		
		//if( pprf_loaded_resized == false ){
			pprf_resize_window();
			
			pprf_loaded_resized = true;
		//}
	})
	$( '.click-to-close, .click-to-close-arrow' ).on( 'click', function(){				
		pprf_resize_window( 150 );
		pprf_loaded_resized = false;
	})

	
	<?php

	// if successfully added a new one
	if( isset( $_GET['success'] ) && $_GET['success'] == 1 && isset( $_GET['iframe_id'] ) && strpos( $_GET['iframe_id'], 'panda-repeater-add-new' ) === 0 ){
		global $wpdb;
		$lastid = $wpdb->insert_id;
	?>
	parent.pprf_new( <?php echo $_GET['podid'];?>, "<?php echo $_GET['postid'];?>", <?php echo $_GET['tb'];?>, <?php echo $current_user->ID;?>, '<?php echo $_GET['iframe_id']; ?>', <?php echo $_GET['poditemid']; ?>, '<?php echo esc_attr( get_the_title( $_GET['poditemid'] ) ); ?>' );
	<?php	
	}
	// disable it until I am sure it is not needed
	if( 1 == 0 && isset( $_GET['itemid'] ) && is_numeric( $_GET['itemid'] ) ){	
	?>
	// tell the parent window to delete this item
	$('.panda-repeater-field-delete-bn').on('click', function(){
		parent.pprf_delete_item( <?php echo $_GET['podid'];?>, "<?php echo $_GET['postid'];?>", <?php echo $_GET['tb'];?>, <?php echo $_GET['itemid'];?>, <?php echo $current_user->ID;?>, '<?php echo $_GET['iframe_id']; ?>', <?php echo $_GET['poditemid']; ?> );
		pprf_resize_window();
	});
	<?php	
	}
	
	?>	
	// saved, so don't popup the confirm box to ask if to ignore changes
	// $('.pods-submit-button').click( function (){		
	$( document ).on('click', '.pods-submit-button', function (){			
		parent.pprf_is_changed	=	false;		
	 })	
	$('.pods-field-input').on('click keyup change', function(){	 
		parent.pprf_is_changed	=	true;		
	});	 

	/**
	 * after running all javascript, resize the window 
	 */
	//$(window).load(function(){
		pprf_resize_window();
	//} );
})  		 
</script>
<script type="text/javascript">
	
if ( window == window.top ) {
  // document.body.innerHTML = 'Access denied!';
}
</script>
<?php
if( !$is_admin ){
	wp_footer();
}