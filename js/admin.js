

function resizeIframe(obj) {
	obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';

}

function updateIframeSize( x, y ){
	
	if ( x != '') {
		// 4px is the small gap at the bottom
		//if( jQuery('#' + x).parent().height() < y + 4 ){
			jQuery('#' + x).height( jQuery('#' + x).height() ).animate({height: y}, 500);
			//jQuery('#' + x).closest('iframe').height( jQuery('#' + x).closest('iframe').height() ).animate({height: y}, 500);
		//} else {

			//set it to the parent div size if the iframe is smaller
		//	jQuery('#' + x).height( jQuery('#' + x).height() ).animate({height: jQuery('#' + x).parent().height() - 4 }, 500);;
		//}
	}
}
function pprf_updateSize_fn( x ){
	
	if ( x != '' ) {
		var pprf_orgHei_int = jQuery( x + ' html #wpbody-content').height() + 60;
	
		jQuery('#' + x).height( jQuery('#' + x).height() ).animate({height: pprf_orgHei_int }, 500);;

	}
}
function updateParentIframe_fn( x ){
	
	if ( x != '') {
		//console.log( x );
		//console.log(  jQuery('#' + x + ' html #wpbody-content').height );
		var y = jQuery('#' + x + ' html #wpbody-content').height();
		// 4px is the small gap at the bottom
		//if( jQuery('#' + x).parent().height() < y + 4 ){
			jQuery('#' + x).height( jQuery('#' + x).height() ).animate({height: y }, 500);
			//jQuery('#' + x).closest('iframe').height( jQuery('#' + x).closest('iframe').height() ).animate({height: y}, 500);
		//} else {

			//set it to the parent div size if the iframe is smaller
		//	jQuery('#' + x).height( jQuery('#' + x).height() ).animate({height: jQuery('#' + x).parent().height() - 4 }, 500);;
		//}
	}
}
var parentHei_int = jQuery('html').height();
/**
 * insert a new row to the page after adding a new item
 */
function pprf_new_fn( podid, postid, cpodid, authorid , iframeid, poditemid, parentName ){
	if( jQuery.isNumeric( podid ) && jQuery.isNumeric( cpodid ) && jQuery.isNumeric( authorid ) && jQuery.isNumeric( poditemid ) ) {
		
		var para_obj  = { 'podid': podid, 'postid': postid, 'cpodid': cpodid, 'authorid': authorid, 'poditemid' : poditemid, 'action' : 'admin_load_newly_added_fn' };
	
		var data_obj  = para_obj;
		
		jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-' + 'loader' ).removeClass('hidden');			
		jQuery.post(
			ajax_script.ajaxurl, 
			data_obj, 
			function( return_str ){	
				jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-' + 'loader' ).addClass('hidden');	
				//jQuery('#panda-repeater-save-' + cpodid + '-' + return_arr['id'] + '-' + poditemid + '-' + 'loader' ).parent().children('.pprf-save-icon').attr('src', PANDA_PODS_REPEATER_URL + '/images/save-icon.png');
				
				
				var return_arr = jQuery.parseJSON( return_str );
				//console.log( return_arr );
				if( typeof return_arr['id'] != 'undefined' && jQuery.isNumeric( return_arr['id'] ) ){
					var ids_str	 		= cpodid + '-' + return_arr['id'] + '-' + poditemid;
					var response_str 	= return_arr['id'];
					var title_str	 	= return_arr['title'];
					var nextBg_str	 	= jQuery('#next-bg').data('bg');
					var fullUrl_str	 	= PANDA_PODS_REPEATER_PAGE_URL + 'iframe_id=panda-repeater-edit-' + ids_str + '&podid=' + podid + '&tb=' + cpodid + '&postid=' + postid + '&itemid=' + response_str + '&poditemid=' + poditemid;
					var iframe_str   	= '<li data-id="' + response_str + '" class="pprf-not-trashed" id="li-' + ids_str + '">' +
											'<div class="row pprf-row  w100 alignleft">' + 
												'<div class="w100 alignleft" id="pprf-row-brief-' + ids_str + '">' +
													'<div class="alignleft pd8 pprf-left-col ' + nextBg_str + ' "><strong>' + parentName + ' ID:</strong> ' + response_str + ' - ' + title_str + '</div>' +
													'<div class="button pprf-right-col center pprf-trash-btn pprf-btn-not-trashed" data-podid="' + podid + '"  data-postid="' + postid + '"  data-tb="' + cpodid + '"  data-itemid="' + response_str + '"  data-userid="' + authorid + '"  data-iframe_id="panda-repeater-edit-' + ids_str + '"  data-poditemid="' + poditemid + '" data-target="' + ids_str + '" >' + 
														'<span class="dashicons dashicons-trash pdt5 pdl5 pdr5 mgb0 "></span>' +
														'<div id="panda-repeater-trash-' + ids_str + '-loader" class="alignleft hidden mgl5">' +
															'<img src = "' + PANDA_PODS_REPEATER_URL + '/images/dots-loading.gif" alt="loading" class="mgl8 loading alignleft"/>' +
														'</div>' +															
													'</div>' +	
													'<div class="button pprf-right-col center pprf-save-btn" data-podid="' + podid + '"  data-postid="' + postid + '"  data-tb="' + cpodid + '"  data-itemid="' + response_str + '"  data-userid="' + authorid + '"  data-iframe_id="panda-repeater-edit-' + ids_str + '" data-poditemid="' + poditemid + '" data-target="' + ids_str + '" >' +
														'<img src = "' + PANDA_PODS_REPEATER_URL + 'images/save-icon-tran.png" class="pprf-save-icon alignleft mgl12 mgt7 mgb2"/>' + 	
														'<div id="panda-repeater-save-' + ids_str + '-loader" class="alignleft hidden mgl5">' +
															'<img src = "' + PANDA_PODS_REPEATER_URL + 'images/dots-loading.gif" alt="loading" class="mgl8 alignleft"/>' +										
														'</div>' +
													'</div>' +													
													'<div class="button pprf-edit pprf-row-load-iframe alignright pprf-right-col center pprf-edit-btn" data-target="' + ids_str + '" data-url="' + fullUrl_str + '">' +
														'<span class="dashicons dashicons-edit pdt8 pdl8 pdr8 mgb0 "></span>' +
														'<div id="panda-repeater-edit-' + ids_str + '-loader" class="alignleft hidden mgl5">' +
															'<img src = "' + PANDA_PODS_REPEATER_URL + '/images/dots-loading.gif" alt="loading" class="mgl9 alignleft"/>' +
														'</div>	' +
													'</div>' +
												'</div>' +										   
												'<div>' + 
													'<iframe id="panda-repeater-edit-' + ids_str + '" frameborder="0" scrolling="no" src="" style="display:none; " class="panda-repeater-iframe w100"></iframe>' + 
													'<div id="panda-repeater-edit-expand-' + ids_str + '" class="w100 alignleft center pd3 pprf-expand-bar pprf-edit-expand" data-target="' + ids_str + '"  style="display:none;">Content missing? Click here to expand</div>' + 
												'</div>' +
										   	  '</div>' +
											'</li>'
										   ;
					
					jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list' ).append( iframe_str ); 
					// if entries limit, toggle the add new 
					var itemsLeft_int	= jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' > .pprf-redorder-list > li').length;
					var limit_int	=	parseInt( jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + '-entry-limit' ).val() );
					if( limit_int != 0 && itemsLeft_int >= limit_int ){
						jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + '-add-new' ).addClass('hidden');	
						
					}						
					//var li_str	   = '<li id="li-' + ids_str + '" class="ui-sortable-handle" data-id="' + response_str + '">' + title_str + '</li>'
					//jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list' ).append( li_str ); 
					//pprf_updateSize_fn( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' #' + iframeid );
					//prepare bg colour for the next row
					if( nextBg_str == 'pprf-purple-bg' ){
						jQuery('#next-bg').data('bg', 'pprf-white-bg');
					} else {
						jQuery('#next-bg').data('bg', 'pprf-purple-bg');						
					}
					
				}
				// if add a new one, activeate the live items tab
				jQuery( '#panda-repeater-fields-tabs-' + cpodid + '-' + poditemid + ' .pprf-tab .dashicons-portfolio').click();
				odd_even_color_fn( cpodid + '-' + poditemid );
			}
		);	
	}
}


/**
 * delete an item
 */
function pprf_delete_item_fn( podid, postid, cpodid, itemid, authorid , iframeid, poditemid, trashed ){
	
	if( jQuery.isNumeric( podid ) && jQuery.isNumeric( cpodid ) && jQuery.isNumeric( authorid ) && jQuery.isNumeric( itemid ) && jQuery.isNumeric( poditemid )  ) {
		
		var para_obj  	= { 'podid': podid, 'postid': postid, 'cpodid': cpodid, 'itemid' : itemid, 'authorid': authorid, 'poditemid' : poditemid, 'action' : 'admin_delete_item_fn', 'trash' : trashed };
		var info_str	=	'';
		if( trashed == 0 ){
			info_str	=	' It will be restored.';
		}		
		if( trashed == 1 ){
			info_str	=	' You can recover it from trash.';
		}
		if( trashed == 2 ){
			info_str	=	' It will be deleted permanently.';
		}
		//panda-repeater-edit-13-506 236
		var data_obj  = para_obj;
		var passt_bln = confirm( 'Are you sure? ' + info_str );
		//$('#overlord').removeClass('hidden');		
		
		if( passt_bln == true  ){

			if( trashed == 0 ){
				jQuery( '#panda-repeater-edit-' + cpodid + '-' + itemid + '-' + poditemid + '-loader' ).removeClass('hidden');
			} else {				 
				jQuery( '#panda-repeater-trash-' + cpodid + '-' + itemid + '-' + poditemid + '-loader' ).removeClass('hidden');
			}
			//jQuery( '#pprf-row-brief-' + cpodid + '-' + itemid + '-' + poditemid + ' .pprf-trash-btn .dashicons-trash' ).remove( );
			jQuery.post(
				ajax_script.ajaxurl, 
				data_obj, 
				function( response_str ){	
					var rsp_arr = jQuery.parseJSON( response_str );
					if( rsp_arr.length != 0 ){
						var ids_str	=	cpodid + '-' + itemid + '-' + poditemid;
						var exp_str		= 'panda-repeater-edit-expand-' + ids_str;
						var iframe_str 	= 'panda-repeater-edit-' + ids_str;		

						if( trashed == 0 ){
							jQuery( '#panda-repeater-edit-' + cpodid + '-' + itemid + '-' + poditemid + '-loader' ).addClass('hidden');
						} else {
							jQuery( '#panda-repeater-trash-' + cpodid + '-' + itemid + '-' + poditemid + '-loader' ).addClass('hidden');
						}
						//jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' #' + iframeid ).remove( );
						if( trashed == 0 ){
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').removeClass('dashicons-update');
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').addClass('dashicons-edit')
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]' ).removeClass('pprf-trashed');
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]' ).addClass('pprf-not-trashed');
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]' ).css('display', 'none');	
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').addClass('pprf-btn-not-trashed');	
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').removeClass('pprf-btn-trashed');
							
							if( jQuery.trim( jQuery('#' + iframe_str  ).contents().find("body").html() ) != '' ) {
								jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-save-btn .pprf-save-icon').attr('src', PANDA_PODS_REPEATER_URL + 'images/save-icon.png');
							}
						}
						if( trashed == 1 ){


							if( jQuery('#' + iframe_str  ) != 'undefined' ){
								jQuery('#' + iframe_str ).hide();	
							}
							if( jQuery('#' + exp_str  ) != 'undefined' ){
								jQuery('#' + exp_str ).hide();	
							}							
													
							jQuery('#pprf-row-brief-' + ids_str + ' .dashicons' ).removeClass('dashicons-arrow-up');
							jQuery('#pprf-row-brief-' + ids_str + ' .dashicons' ).addClass('dashicons-edit');							

							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]' ).removeClass('pprf-not-trashed');
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]' ).addClass('pprf-trashed');
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]' ).css('display', 'none');
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span' ).addClass('dashicons-update');
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span' ).removeClass('dashicons-edit');
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').addClass('pprf-btn-trashed');	
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').removeClass('pprf-btn-not-trashed');	
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-save-btn .pprf-save-icon').attr('src', PANDA_PODS_REPEATER_URL + 'images/save-icon-tran.png');
							

						}
						if( trashed == 2 ){	
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' #' + iframeid ).parent().parent().remove( );
							jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]' ).remove( );
							// if entries limit, toggle the add new 
							var itemsLeft_int	= jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' > .pprf-redorder-list > li').length;
							var limit_int	=	parseInt( jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + '-entry-limit' ).val() );
							if( limit_int != 0 && itemsLeft_int < limit_int ){
								jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + '-add-new' ).removeClass('hidden');	
								
							}						
							// integrate with simpods js
							if( typeof call_simpods_fn !== 'undefined' && jQuery.isFunction( call_simpods_fn ) ) {
								call_simpods_fn( response_str );
							}
						}
						//document.getElementById( iframeid ).contentWindow.pprf_resize_fn() ;
						 odd_even_color_fn( cpodid + '-' + poditemid )
					}
					
				}
			);	
			
		}
	}
}
jQuery('.pprf-redorder-btn').click( function(){
	var id = jQuery( this ).data('id');
	jQuery( this ).addClass('hidden');	
	jQuery( this ).parent().children('.pprf-save-redorder-btn').removeClass('hidden');	
	jQuery( this ).parent().children('.pprf-redorder-list-wrap').removeClass('hidden');
	jQuery( this ).parent().children('.pprf-row').addClass('hidden');	
	jQuery( '#' + id + '-add-new' ).addClass('hidden');	
});
jQuery('.pprf-save-redorder-btn').click( function(){
	var id = jQuery( this ).data('id');
	jQuery( this ).addClass('hidden');	
	jQuery( this ).parent().children('.pprf-redorder-list-wrap').addClass('hidden');	
	jQuery( this ).parent().children('.pprf-save-redorder-btn').addClass('hidden');
	jQuery( this ).parent().children('.pprf-redorder-btn').removeClass('hidden');
	jQuery( this ).parent().children('.pprf-row').removeClass('hidden');	
	jQuery( '#' + id + '-add-new' ).removeClass('hidden');	
});
/**
 * reset colours for each row
 */

function odd_even_color_fn( ids_str ){

	jQuery( '#panda-repeater-fields-' + ids_str + ' .pprf-left-col').removeClass('pprf-purple-bg');
	jQuery( '#panda-repeater-fields-' + ids_str + ' .pprf-left-col').removeClass('pprf-white-bg');
	
	if( jQuery( '#panda-repeater-fields-tabs-' + ids_str ).length == 0 ){
		
		jQuery( '#panda-repeater-fields-' + ids_str + ' .pprf-left-col').each( function( idx_int ) {
			if( idx_int % 2 == 0 ){
				jQuery( this ).addClass('pprf-white-bg');
			} else {
				jQuery( this ).addClass('pprf-purple-bg');
			}
		})
	}
	jQuery( '#panda-repeater-fields-' + ids_str + ' .pprf-not-trashed').each( function( idx_int ) {
		if( idx_int % 2 == 0 ){
			jQuery( this ).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-white-bg');
		} else {
			jQuery( this ).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-purple-bg');
		}
	});
	jQuery( '#panda-repeater-fields-' + ids_str + ' .pprf-trashed').each( function( idx_int ) {
		if( idx_int % 2 == 0 ){
			jQuery( this ).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-white-bg');
		} else {
			jQuery( this ).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-purple-bg');
		}
	});	
}
jQuery(document).ready( function($) {
		
	/**
	 * fixHelperModified_fn for drag and drop
	 */
	var fixHelperModified_fn = function(e, tr) {
		var $originals = tr.children();
		var $helper = tr.clone();
		$helper.children().each(function(index) {
			$(this).width($originals.eq(index).width());					
		});
		
		return $helper;
	},
	updateIndex_fn = function(e, ui) {
		var theOrder_arr = $(this).sortable('toArray');
		//console.log(theOrder_arr);
		var data = {
			action:   'admin_pprf_update_order_fn',
			order: 	  theOrder_arr
		};

		$.post(
			ajaxurl, 
			data, 
			function(response){
				
			}
		)
		
		
	};	
	if( $('.pprf-redorder-list').length != 0 ){
		$('.pprf-redorder-list').sortable({
			helper: fixHelperModified_fn,
			cursor:         'move',
			opacity:        0.7,
			tolerance:      'pointer',		
			update: updateIndex_fn		
		});		
	}
	
/*	if( $('.pprf-row').length != 0 ){
		$('.pprf-row').sortable({
			helper: fixHelperModified_fn,
			cursor:         'move',
			opacity:        0.7,
			tolerance:      'pointer',		
			update: updateIndex_fn		
		});		
	}	*/
	
	$('.pprf-row-load-iframe').live( 'click', function(){
		var url_str    	= $( this ).data('url');
		var ids_str	   	= $( this ).data('target');
		var exp_str		= 'panda-repeater-edit-expand-' + ids_str;
		var iframe_str 	= 'panda-repeater-edit-' + ids_str;		
		var trash_ele	= $( this ).parent().children('.pprf-trash-btn');
		if( $( this ).children('.pprf-edit-span').hasClass('dashicons-update') ){ 
			// restore this item		
			pprf_delete_item_fn( trash_ele.data('podid'), trash_ele.data('postid'), trash_ele.data('tb'), trash_ele.data('itemid'), trash_ele.data('userid'), trash_ele.data('iframe_id'), trash_ele.data('poditemid'), 0 );
		} else { 

			if( $( this ).hasClass('pprf-add') ){
				iframe_str 	= 'panda-repeater-add-new-' + ids_str;
				exp_str		= 'panda-repeater-add-new-expand-' + ids_str;
			}	
			
			if( $('#pprf-row-brief-' + ids_str + ' .dashicons' ).hasClass('dashicons-edit') ){		

				//if iframe not loaded
				
				if( $('#' + iframe_str ).attr('src') == '' ){
					$('#' + iframe_str ).attr('src', url_str ); 
					$('#' + iframe_str + '-' + 'loader' ).removeClass('hidden');		
				}
				
				$('#' + iframe_str ).show('slow');
				$('#' + exp_str ).show('slow');	
				$('#' + iframe_str ).on('load', function(){
					
					$('#' + iframe_str + '-' + 'loader' ).addClass('hidden');	
					//change icon	
					$('#panda-repeater-save-' + ids_str + '-' + 'loader' ).parent().children('.pprf-save-icon').attr('src', PANDA_PODS_REPEATER_URL + '/images/save-icon.png');
					$('#panda-repeater-save-' + ids_str + '-' + 'loader' ).parent().addClass('pprf-btn-ready');
					$('#panda-repeater-save-' + ids_str + '-' + 'loader' ).addClass('hidden');
					//$('#pprf-row-brief-' + ids_str + '' ).addClass('hidden');	
					//$('#' + iframe_str )[0].contentWindow.pprf_resize_fn();
					//console.log( $(this).parent().height() );
				});	
			//	if( $('#pprf-row-brief-' + ids_str + ' .dashicons' ).hasClass('dashicons') ){	
					$('#pprf-row-brief-' + ids_str + ' .dashicons' ).addClass('dashicons-arrow-up');
					$('#pprf-row-brief-' + ids_str + ' .dashicons' ).removeClass('dashicons-edit');		
				//}
			} else {
				
				$('#' + iframe_str ).hide('slow');	
				$('#' + exp_str ).hide('slow');	
			//	if( $('#pprf-row-brief-' + ids_str + ' .dashicons' ).hasClass('dashicons') ){	
					$('#pprf-row-brief-' + ids_str + ' .dashicons' ).removeClass('dashicons-arrow-up');
					$('#pprf-row-brief-' + ids_str + ' .dashicons' ).addClass('dashicons-edit');		
			//	}
			}
			$('#pprf-row-brief-' + ids_str + ' .dashicons-trash' ).removeClass('dashicons-arrow-up');
		}
		
	});	
	/**
	 * click to explan its iframe
	 */
	$(".pprf-expand-bar").live( 'click', function(){
		var ids_str	   	= $( this ).data('target');
		var iframe_str 	= 'panda-repeater-edit-' + ids_str;
		if( $( this ).hasClass('pprf-add-expand') ){
			iframe_str 	= 'panda-repeater-add-new-' + ids_str;			
		}	
		if( typeof document.getElementById( iframe_str ) != 'undefined' ){
			if( typeof document.getElementById( iframe_str ).contentWindow.pprf_resize_fn() != 'undefined' ){
				document.getElementById( iframe_str ).contentWindow.pprf_resize_fn();
			}
		}
	});
	/**
	 * click to delete
	 */
	 $('.pprf-trash-btn').live( 'click', function(){
		var ids_str	   	= $( this ).data('target');
		var iframe_str 	= 'panda-repeater-edit-' + ids_str;
		if( $( this ).hasClass('pprf-add-expand') ){
			iframe_str 	= 'panda-repeater-add-new-' + ids_str;			
		}	
		var trash_int	= 0;
		if( $( this ).hasClass('pprf-btn-not-trashed') ){
			trash_int	= 1;
		}
		if( $( this ).hasClass('pprf-btn-trashed') ){
			trash_int	= 2;
		}	
		if( $( this ).hasClass('pprf-btn-delete') ){
			trash_int	= 2;
		}				
		//document.getElementById( iframe_str ).contentWindow.pprf_delete_item_fn();
		pprf_delete_item_fn( $( this ).data('podid'), $( this ).data('postid'), $( this ).data('tb'), $( this ).data('itemid'), $( this ).data('userid'), $( this ).data('iframe_id'), $( this ).data('poditemid'), trash_int );
	 })
	 
	 $('.pprf-save-btn').live( 'click', function(){
		 if( $( this ).hasClass( 'pprf-btn-ready' ) ){
			var ids_str		= $( this ).data('target');
			var iframe_str 	= 'panda-repeater-edit-' + ids_str;
			if( $( this ).hasClass('pprf-save-new-btn') ){
				iframe_str 	= 'panda-repeater-add-new-' + ids_str;
			}			 
			$('#panda-repeater-save-' + ids_str + '-loader' ).removeClass('hidden');
			
			$('#' + iframe_str ).contents().find('.pods-submit-button').trigger( "click" );	
			pprfChanged_bln	=	false;		

		 }
	 });
	 

	 
	 /**
	  * if a pods is is clicked, flag it as saved
	  */
	 $('.toplevel_page_panda-pods-repeater-field .pods-field-input').on('click keyup change', function(){
		// if( typeof $('#pprf-on-page-data').data('saved') != 'undefined' ){
//			$('#pprf-on-page-data').data('saved', '1');			
//		 }
		 pprfChanged_bln	=	true;
	 });
	 $('#publishing-action .button, #save-action .button').click( function( evt ){
		
		  if( pprfChanged_bln ){
			evt.preventDefault();
			var leave_bln = confirm('It seems like you have made some changes in a repeater field. Ignore the changes?');
			if ( leave_bln == true){
				pprfChanged_bln	=	false;
				$( this ).click();
			} 
			if ( leave_bln == false){
				return false;
			}
		  }
	 });

	 /**
	  * toggle trashed and current
	  */
	 $('.pprf-tab .dashicons-trash').parent().click( function(){
	 	
	 	$( '#panda-repeater-fields-' + $( this).data('target') + ' .pprf-trashed').css('display', 'block');
	 	$( '#panda-repeater-fields-' + $( this).data('target') + ' .pprf-not-trashed').css('display', 'none');
	 	$( this ).parent().children('.active').removeClass('active');
	 	$( this ).addClass('active');
	 	odd_even_color_fn( $( this).data('target') );

	 })
	 $('.pprf-tab .dashicons-portfolio').parent().click( function(){
	 	$( '#panda-repeater-fields-' + $( this).data('target') + ' .pprf-trashed').css('display', 'none');
	 	$( '#panda-repeater-fields-' + $( this).data('target') + ' .pprf-not-trashed').css('display', 'block');
	 	$( this ).parent().children('.active').removeClass('active');
	 	$( this ).addClass('active');	 	
	 	odd_even_color_fn( $( this).data('target') );
	 })	 
});

var pprfChanged_bln	=	false;	