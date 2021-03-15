"use strict";

function pprf_resizeIframe_fn(obj) {
  obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}

function pprf_updateIframeSize_fn(x, y) {
  if (x != '') {
    jQuery('#' + x).height(jQuery('#' + x).height()).animate({
      height: y
    }, 500);
  }
}

function pprf_updateSize_fn(x) {
  if (x != '') {
    var xpprf_orgHei_int = jQuery(x + ' html #wpbody-content').height() + 60;
    jQuery('#' + x).height(jQuery('#' + x).height()).animate({
      height: pprf_orgHei_int
    }, 500);
    ;
  }
}

function pprf_updateParentIframe_fn(x) {
  if (x != '') {
    var y = jQuery('#' + x + ' html #wpbody-content').height(); // 4px is the small gap at the bottom

    jQuery('#' + x).height(jQuery('#' + x).height()).animate({
      height: y
    }, 500);
  }
}

var pprfParentHei_int = jQuery('html').height();
/**
 * insert a new row to the page after adding a new item
 */

function pprf_new_fn(podid, postid, cpodid, authorid, iframeid, poditemid, parentName) {
  if (jQuery.isNumeric(podid) && jQuery.isNumeric(cpodid) && jQuery.isNumeric(authorid) && jQuery.isNumeric(poditemid)) {
    var para_obj = {
      'podid': podid,
      'postid': postid,
      'cpodid': cpodid,
      'authorid': authorid,
      'poditemid': poditemid,
      'action': 'admin_pprf_load_newly_added_fn',
      'security': ajax_script.nonce
    };
    var data_obj = para_obj;
    jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-' + 'loader').removeClass('hidden');
    jQuery.post(ajax_script.ajaxurl, data_obj, function (re_arr) {
      jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-' + 'loader').addClass('hidden');
      var return_arr = re_arr['data']; //console.log( return_arr );

      if (re_arr['success'] === true && typeof return_arr['id'] !== 'undefined' && jQuery.isNumeric(return_arr['id'])) {
        var iframe_str = item_html_fn(return_arr, podid, postid, cpodid, authorid, iframeid, poditemid, parentName, false);
        jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list').append(iframe_str); // if entries limit, toggle the add new 

        var itemsLeft_int = jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' > .pprf-redorder-list > li').length;
        var limit_int = parseInt(jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-entry-limit').val());

        if (limit_int != 0 && itemsLeft_int >= limit_int) {
          jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-add-new').addClass('hidden');
        }
      } // if add a new one, activeate the live items tab


      jQuery('#panda-repeater-fields-tabs-' + cpodid + '-' + poditemid + ' .pprf-tab .dashicons-portfolio').click(); //jQuery( document ).on('click', '#panda-repeater-fields-tabs-' + cpodid + '-' + poditemid + ' .pprf-tab .dashicons-portfolio' );

      pprf_odd_even_color_fn(cpodid + '-' + poditemid);
    });
  }
}

function item_html_fn($item_arr, podid, postid, cpodid, authorid, iframeid, poditemid, parentName, repeated_bln) {
  if (typeof $item_arr['id'] == 'undefined' || !jQuery.isNumeric($item_arr['id'])) {
    return '';
  } //console.log($item_arr);


  var trashed_str = '';
  var btnTrashed_str = 'pprf-btn-delete';
  var display_str = '';
  var editIcon_str = 'dashicons-edit';

  if (jQuery('#panda-repeater-trash-info-' + cpodid + '-' + poditemid).data('enable-trash') == 1) {
    if (typeof $item_arr['trashed'] != 'undefined' && $item_arr['trashed'] == 1) {
      trashed_str = 'pprf-trashed';
      btnTrashed_str = 'pprf-btn-trashed'; // if the portfolio foder is open, hide the trash one

      editIcon_str = 'dashicons-update ';

      if (jQuery('#panda-repeater-fields-tabs-' + cpodid + '-' + poditemid + ' .pprf-tab.active .dashicons').hasClass('dashicons-portfolio')) {
        display_str = 'display:none;';
      }
    } else {
      trashed_str = 'pprf-not-trashed';
      btnTrashed_str = 'pprf-btn-not-trashed';

      if (jQuery('#panda-repeater-fields-tabs-' + cpodid + '-' + poditemid + ' .pprf-tab.active .dashicons').hasClass('dashicons-trash')) {
        display_str = 'display:none;';
      }
    }
  }

  var repeated_str = '';

  if (repeated_bln == true) {
    repeated_str = '-repeated';
  }

  var delAct_str = ''; //jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-' + 'add-new .pprf-trash-btn' ).data('target');

  var ids_str = cpodid + '-' + $item_arr['id'] + '-' + poditemid;
  var response_str = $item_arr['id'];
  var title_str = $item_arr['title'];
  var label_str = '<strong>ID:</strong> ' + response_str + ' <strong>' + $item_arr['pprf_name_label'] + ': </strong> ' + title_str;

  if (typeof $item_arr['label'] != 'undefined' && $item_arr['label'] != '') {
    label_str = $item_arr['label'];
  }

  var nextBg_str = jQuery('#next-bg').data('bg');
  var fullUrl_str = PANDA_PODS_REPEATER_PAGE_URL[0] + 'iframe_id=panda-repeater-edit-' + ids_str + '&podid=' + podid + '&tb=' + cpodid + '&postid=' + postid + '&itemid=' + response_str + '&poditemid=' + poditemid;
  var html_str = '<li data-id="' + response_str + '" class="' + trashed_str + '" id="li-' + ids_str + repeated_str + '" style="' + display_str + '">' + '<div class="pprf-row  w100 pprf-left">' + '<div class="w100 pprf-left" id="pprf-row-brief-' + ids_str + repeated_str + '">' + '<div class="pprf-left pd8 pprf-left-col ' + nextBg_str + ' ">' + label_str + '</div>';

  if (repeated_bln == true) {
    html_str += '<div class="button pprf-right-col center pprf-dismiss-btn ' + delAct_str + ' ' + btnTrashed_str + '" role="button" data-podid="' + podid + '"  data-postid="' + postid + '"  data-tb="' + cpodid + '"  data-itemid="' + response_str + '"  data-userid="' + authorid + '"  data-iframe_id="panda-repeater-edit-' + ids_str + '"  data-poditemid="' + poditemid + '" data-target="' + ids_str + '" >' + '<span class="dashicons dashicons-dismiss pdt6 mgb0 "></span>' + '</div>' + '<div class="pprf-left pd8">Repeated</div>';
  } else {
    html_str += '<div class="button pprf-right-col center pprf-trash-btn ' + delAct_str + ' ' + btnTrashed_str + '" role="button" data-podid="' + podid + '"  data-postid="' + postid + '"  data-tb="' + cpodid + '"  data-itemid="' + response_str + '"  data-userid="' + authorid + '"  data-iframe_id="panda-repeater-edit-' + ids_str + '"  data-poditemid="' + poditemid + '" data-target="' + ids_str + '" >' + '<span class="dashicons dashicons-trash pdt6 mgb0 "></span>' + '<div id="panda-repeater-trash-' + ids_str + '-loader" class="pprf-left hidden mgl5">' + '<img src = "' + PANDA_PODS_REPEATER_URL[0] + '/images/dots-loading.gif" alt="loading" class="mgl8 loading pprf-left"/>' + '</div>' + '</div>' + '<div class="button pprf-right-col center pprf-save-btn" role="button" data-podid="' + podid + '"  data-postid="' + postid + '"  data-tb="' + cpodid + '"  data-itemid="' + response_str + '"  data-userid="' + authorid + '"  data-iframe_id="panda-repeater-edit-' + ids_str + '" data-poditemid="' + poditemid + '" data-target="' + ids_str + '" >' + '<img src = "' + PANDA_PODS_REPEATER_URL[0] + 'images/save-icon-tran.png" class="pprf-save-icon  mgt8 mgb2"/>' + '<div id="panda-repeater-save-' + ids_str + '-loader" class="pprf-left hidden mgl5">' + '<img src = "' + PANDA_PODS_REPEATER_URL[0] + 'images/dots-loading.gif" alt="loading" class="mgl8 pprf-left"/>' + '</div>' + '</div>' + '<div class="button pprf-edit pprf-row-load-iframe alignright pprf-right-col center pprf-edit-btn" role="button" data-target="' + ids_str + '" data-url="' + fullUrl_str + '">' + '<span class="dashicons ' + editIcon_str + ' pdt8 mgb0 pprf-edit-span"></span>' + '<div id="panda-repeater-edit-' + ids_str + '-loader" class="pprf-left hidden mgl5">' + '<img src = "' + PANDA_PODS_REPEATER_URL[0] + '/images/dots-loading.gif" alt="loading" class="mgl9 pprf-left"/>' + '</div>	' + '</div>';
  }

  html_str += '</div>' + '<div>' + '<iframe id="panda-repeater-edit-' + ids_str + '" frameborder="0" scrolling="no" src="" style="display:none; " class="panda-repeater-iframe w100"></iframe>' + '<div id="panda-repeater-edit-expand-' + ids_str + '" class="w100 pprf-left center pdt3 pdb3 pprf-expand-bar pprf-edit-expand" data-target="' + ids_str + '"  style="display:none;">Content missing? Click here to expand</div>' + '</div>' + '</div>' + '</li>';

  if (nextBg_str == 'pprf-purple-bg') {
    jQuery('#next-bg').data('bg', 'pprf-white-bg');
  } else {
    jQuery('#next-bg').data('bg', 'pprf-purple-bg');
  }

  return html_str;
}
/**
 * delete an item
 */


function pprf_delete_item_fn(podid, postid, cpodid, itemid, authorid, iframeid, poditemid, trashed) {
  if (jQuery.isNumeric(podid) && jQuery.isNumeric(cpodid) && jQuery.isNumeric(authorid) && jQuery.isNumeric(itemid) && jQuery.isNumeric(poditemid)) {
    var para_obj = {
      'podid': podid,
      'postid': postid,
      'cpodid': cpodid,
      'itemid': itemid,
      'authorid': authorid,
      'poditemid': poditemid,
      'action': 'admin_pprf_delete_item_fn',
      'trash': trashed,
      'security': ajax_script.nonce
    };
    var info_str = '';

    if (trashed == 0) {
      info_str = strs_obj.be_restored;
    }

    if (trashed == 1) {
      info_str = strs_obj.can_recover;
    }

    if (trashed == 2) {
      info_str = strs_obj.be_deleted;
    } //panda-repeater-edit-13-506 236


    var data_obj = para_obj;
    var passt_bln = confirm(strs_obj.you_sure + ' ' + info_str); //$('#overlord').removeClass('hidden');		

    if (passt_bln == true) {
      if (trashed == 0) {
        jQuery('#panda-repeater-edit-' + cpodid + '-' + itemid + '-' + poditemid + '-loader').removeClass('hidden');
      } else {
        jQuery('#panda-repeater-trash-' + cpodid + '-' + itemid + '-' + poditemid + '-loader').removeClass('hidden');
      } //jQuery( '#pprf-row-brief-' + cpodid + '-' + itemid + '-' + poditemid + ' .pprf-trash-btn .dashicons-trash' ).remove( );


      jQuery.post(ajax_script.ajaxurl, data_obj, function (response_arr) {
        if (response_arr['success'] === true) {
          var rsp_arr = response_arr['data'];

          if (rsp_arr.length != 0) {
            var ids_str = cpodid + '-' + itemid + '-' + poditemid;
            var exp_str = 'panda-repeater-edit-expand-' + ids_str;
            var iframe_str = 'panda-repeater-edit-' + ids_str;

            if (trashed == 0) {
              jQuery('#panda-repeater-edit-' + cpodid + '-' + itemid + '-' + poditemid + '-loader').addClass('hidden');
            } else {
              jQuery('#panda-repeater-trash-' + cpodid + '-' + itemid + '-' + poditemid + '-loader').addClass('hidden');
            } //jQuery( '#panda-repeater-fields-' + cpodid + '-' + poditemid + ' #' + iframeid ).remove( );


            if (trashed == 0) {
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').removeClass('dashicons-update');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').addClass('dashicons-edit');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').removeClass('pprf-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').addClass('pprf-not-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').css('display', 'none');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').addClass('pprf-btn-not-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').removeClass('pprf-btn-trashed');

              if (jQuery.trim(jQuery('#' + iframe_str).contents().find("body").html()) != '') {
                jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-save-btn .pprf-save-icon').attr('src', PANDA_PODS_REPEATER_URL[0] + 'images/save-icon.png');
              }
            }

            if (trashed == 1) {
              if (jQuery('#' + iframe_str) != 'undefined') {
                jQuery('#' + iframe_str).hide();
              }

              if (jQuery('#' + exp_str) != 'undefined') {
                jQuery('#' + exp_str).hide();
              }

              jQuery('#pprf-row-brief-' + ids_str + ' .dashicons').removeClass('dashicons-arrow-up');
              jQuery('#pprf-row-brief-' + ids_str + ' .dashicons').addClass('dashicons-edit');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').removeClass('pprf-not-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').addClass('pprf-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').css('display', 'none');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').addClass('dashicons-update');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').removeClass('dashicons-edit');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').addClass('pprf-btn-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').removeClass('pprf-btn-not-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-save-btn .pprf-save-icon').attr('src', PANDA_PODS_REPEATER_URL[0] + 'images/save-icon-tran.png');
            }

            if (trashed == 2) {
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' #' + iframeid).parent().parent().remove();
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').remove(); // if entries limit, toggle the add new 

              var itemsLeft_int = jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' > .pprf-redorder-list > li').length;
              var limit_int = parseInt(jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-entry-limit').val());

              if (limit_int != 0 && itemsLeft_int < limit_int) {
                jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-add-new').removeClass('hidden');
              } // integrate with simpods js


              if (typeof call_simpods_fn !== 'undefined' && jQuery.isFunction(call_simpods_fn)) {
                call_simpods_fn(response_arr);
              }
            } //document.getElementById( iframeid ).contentWindow.pprf_resize_fn() ;


            pprf_odd_even_color_fn(cpodid + '-' + poditemid);
          }
        }
      });
    }
  }
} //jQuery('.pprf-redorder-btn').click( function(){


jQuery(document).on('click', '.pprf-redorder-btn', function (evt) {
  var id = jQuery(this).data('id');
  jQuery(this).addClass('hidden');
  jQuery(this).parent().children('.pprf-save-redorder-btn').removeClass('hidden');
  jQuery(this).parent().children('.pprf-redorder-list-wrap').removeClass('hidden');
  jQuery(this).parent().children('.pprf-row').addClass('hidden');
  jQuery('#' + id + '-add-new').addClass('hidden');
}); //jQuery('.pprf-save-redorder-btn').click( function(){

jQuery(document).on('click', '.pprf-save-redorder-btn', function (evt) {
  var id = jQuery(this).data('id');
  jQuery(this).addClass('hidden');
  jQuery(this).parent().children('.pprf-redorder-list-wrap').addClass('hidden');
  jQuery(this).parent().children('.pprf-save-redorder-btn').addClass('hidden');
  jQuery(this).parent().children('.pprf-redorder-btn').removeClass('hidden');
  jQuery(this).parent().children('.pprf-row').removeClass('hidden');
  jQuery('#' + id + '-add-new').removeClass('hidden');
});
/**
 * load more
 */
//jQuery('.pprf-load-more-btn').click( function( evt ){

jQuery(document).on('click', '.pprf-load-more-btn', function (evt) {
  evt.preventDefault();
  jQuery('#pprf-load-more-wrap-' + jQuery(this).data('target') + ' .pprf-ajax-img').css('display', 'block');
  pprf_load_more_fn(jQuery(this).data('target'), jQuery(this));
});

function pprf_load_more_fn(target_str, ele_obj) {
  var loaded_arr = new Array();
  jQuery('#panda-repeater-fields-' + target_str + ' .pprf-redorder-list li').each(function (idx_int) {
    loaded_arr[idx_int] = parseInt(jQuery(this).data('id'));
  });
  var data_obj = {
    action: 'admin_pprf_load_more_fn',
    loaded: loaded_arr,
    security: ajax_script.nonce,
    pod_id: ele_obj.data('podid'),
    post_id: ele_obj.data('postid'),
    saved_tb: ele_obj.data('tb'),
    iframe_id: ele_obj.data('iframe_id'),
    pod_item_id: ele_obj.data('poditemid'),
    authorid: ele_obj.data('userid'),
    cp_title: ele_obj.data('cptitle'),
    trashable: ele_obj.data('enable-trash'),
    order: ele_obj.data('order'),
    order_by: ele_obj.data('order-by'),
    amount: jQuery('#panda-repeater-amount-' + target_str).val(),
    start: jQuery('#panda-repeater-start-from-' + target_str).val()
  };
  jQuery('#pprf-load-more-wrap-' + target_str + ' .pprf-load-more-report').text('');
  jQuery.post(ajax_script.ajaxurl, data_obj, function (response_obj) {
    var html_str = '';

    if (response_obj.success == true) {
      var trashed_int = 0;
      var $notTrash_int = 0;

      for (var i = 0; i < response_obj.data.length; i++) {
        var repeated_bln = false;

        if (jQuery('#pprf-load-more-wrap-' + target_str + ' .panda-repeater-to-load').val() == 'append_to') {
          // only need to check repeatition if it is on Append To.
          for (var j = 0; j < loaded_arr.length; j++) {
            //inArry doesn't work
            if (parseInt(response_obj.data[i]['id']) == loaded_arr[j]) {
              repeated_bln = true;
              break;
            }
          }
        }

        html_str += item_html_fn(response_obj.data[i], data_obj.pod_id, data_obj.post_id, data_obj.saved_tb, data_obj.authorid, data_obj.iframe_id, data_obj.pod_item_id, data_obj.cp_title, repeated_bln);

        if (response_obj.data[i]['trashed'] == 1) {
          trashed_int++;
        }
      }

      var info_str = response_obj.data.length;

      if (data_obj.trashable == 1) {
        info_str = parseInt(response_obj.data.length) - trashed_int + ' published, ' + trashed_int + ' trashed.';
      }

      if (jQuery('#pprf-load-more-wrap-' + target_str + ' .panda-repeater-to-load').val() == 'append_to') {
        jQuery('#panda-repeater-fields-' + data_obj.saved_tb + '-' + data_obj.pod_item_id + ' .pprf-redorder-list').append(html_str);
      } else {
        jQuery('#panda-repeater-fields-' + data_obj.saved_tb + '-' + data_obj.pod_item_id + ' .pprf-redorder-list').html(html_str);
      }

      pprf_odd_even_color_fn(data_obj.saved_tb + '-' + data_obj.pod_item_id);
      jQuery('#pprf-load-more-wrap-' + target_str + ' .pprf-ajax-img').css('display', 'none');

      if (response_obj.data.length != 0) {
        jQuery('#panda-repeater-start-from-' + target_str).val(parseInt(data_obj.start) + parseInt(response_obj.data.length));
      }

      jQuery('#pprf-load-more-wrap-' + target_str + ' .pprf-load-more-report').text(' | Loaded ' + info_str);
    } //item_html_fn( $item_arr, podid, postid, cpodid, authorid , iframeid, poditemid, parentName )
    //pprf_new_fn( ele_obj.data('podid'), ele_obj.data('postid'), ele_obj.data('tb'), ele_obj.data('userid'), ele_obj.data('iframe_id'), ele_obj.data('pod_item_id'), ele_obj.data('cptitle') );

  });
}
/**
 * reset colours for each row
 */


function pprf_odd_even_color_fn(ids_str) {
  jQuery('#panda-repeater-fields-' + ids_str + ' .pprf-left-col').removeClass('pprf-purple-bg');
  jQuery('#panda-repeater-fields-' + ids_str + ' .pprf-left-col').removeClass('pprf-white-bg');

  if (jQuery('#panda-repeater-fields-tabs-' + ids_str).length == 0) {
    jQuery('#panda-repeater-fields-' + ids_str + ' .pprf-left-col').each(function (idx_int) {
      if (idx_int % 2 == 0) {
        jQuery(this).addClass('pprf-white-bg');
      } else {
        jQuery(this).addClass('pprf-purple-bg');
      }
    });
  }

  jQuery('#panda-repeater-fields-' + ids_str + ' .pprf-not-trashed').each(function (idx_int) {
    if (idx_int % 2 == 0) {
      jQuery(this).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-white-bg');
    } else {
      jQuery(this).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-purple-bg');
    }
  });
  jQuery('#panda-repeater-fields-' + ids_str + ' .pprf-trashed').each(function (idx_int) {
    if (idx_int % 2 == 0) {
      jQuery(this).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-white-bg');
    } else {
      jQuery(this).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-purple-bg');
    }
  });
}
/**
 * if reassigned successfully, remove the item and reset colours
 */


function pprf_reassign_fn(cpodid, poditemid, itemid) {
  //console.log( cpodid, poditemid, itemid );
  jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').remove(); //document.getElementById( iframeid ).contentWindow.pprf_resize_fn() ;

  pprf_odd_even_color_fn(cpodid + '-' + poditemid);
}

jQuery(document).ready(function ($) {
  /**
   * fixHelperModified_fn for drag and drop
   */
  var fixHelperModified_fn = function fixHelperModified_fn(e, tr) {
    var $originals = tr.children();
    var $helper = tr.clone();
    $helper.children().each(function (index) {
      $(this).width($originals.eq(index).width());
    });
    return $helper;
  },
      updateIndex_fn = function updateIndex_fn(e, ui) {
    var theOrder_arr = $(this).sortable('toArray'); //console.log(theOrder_arr);

    var data_obj = {
      action: 'admin_pprf_update_order_fn',
      order: theOrder_arr,
      security: ajax_script.nonce
    };
    $.post(ajax_script.ajaxurl, data_obj, function (resp_arr) {
      //console.log( response_arr );
      pprf_odd_even_color_fn(resp_arr.data.pprf_id);
    });
  };

  if ($('.pprf-redorder-list.pandarf_order').length != 0) {
    $('.pprf-redorder-list.pandarf_order').sortable({
      helper: fixHelperModified_fn,
      cursor: 'move',
      opacity: 0.7,
      tolerance: 'intersect',
      update: updateIndex_fn,
      cancel: '.pprf-row-load-iframe, .pprf-save-btn, pprf-trash-btn',
      handle: '.pprf-left-col'
    });
  }

  $(document.body).on('click', '.pprf-row-load-iframe', function (e) {
    e.stopPropagation();
    var url_str = $(this).data('url');
    var ids_str = $(this).data('target');
    var exp_str = 'panda-repeater-edit-expand-' + ids_str;
    var iframe_str = 'panda-repeater-edit-' + ids_str;
    var trash_ele = $(this).parent().children('.pprf-trash-btn');

    if ($(this).children('.pprf-edit-span').hasClass('dashicons-update')) {
      // restore this item		
      pprf_delete_item_fn(trash_ele.data('podid'), trash_ele.data('postid'), trash_ele.data('tb'), trash_ele.data('itemid'), trash_ele.data('userid'), trash_ele.data('iframe_id'), trash_ele.data('poditemid'), 0);
    } else {
      var addEdit_str = ' .pprf-edit';

      if ($(this).hasClass('pprf-add')) {
        addEdit_str = '.pprf-add';
        iframe_str = 'panda-repeater-add-new-' + ids_str;
        exp_str = 'panda-repeater-add-new-expand-' + ids_str;
      }

      if ($('#pprf-row-brief-' + ids_str + ' .dashicons').hasClass('dashicons-edit')) {
        //if iframe not loaded
        if ($('#' + iframe_str).attr('src') == '') {
          $('#' + iframe_str).attr('src', url_str);
          $('#' + iframe_str + '-' + 'loader').removeClass('hidden');
        }

        $('#' + iframe_str).show('slow', function () {
          $('#pprf-row-brief-' + ids_str + '' + addEdit_str + ' .dashicons').addClass('dashicons-arrow-up');
          $('#pprf-row-brief-' + ids_str + '' + addEdit_str + ' .dashicons').removeClass('dashicons-edit');
        });
        $('#' + exp_str).show();
        $('#' + iframe_str).on('load', function () {
          $('#' + iframe_str + '-' + 'loader').addClass('hidden'); //change icon	

          $('#panda-repeater-save-' + ids_str + '-' + 'loader').parent().children('.pprf-save-icon').attr('src', PANDA_PODS_REPEATER_URL[0] + '/images/save-icon.png');
          $('#panda-repeater-save-' + ids_str + '-' + 'loader').parent().addClass('pprf-btn-ready');
          $('#panda-repeater-save-' + ids_str + '-' + 'loader').addClass('hidden'); //$('#pprf-row-brief-' + ids_str + '' ).addClass('hidden');	
          //$('#' + iframe_str )[0].contentWindow.pprf_resize_fn();
          //console.log( $(this).parent().height() );

          $('#pprf-row-brief-' + ids_str + '' + addEdit_str + ' .dashicons').addClass('dashicons-arrow-up');
          $('#pprf-row-brief-' + ids_str + '' + addEdit_str + ' .dashicons').removeClass('dashicons-edit');
        }); //	if( $('#pprf-row-brief-' + ids_str + ' .dashicons' ).hasClass('dashicons') ){	
        //}
      } else {
        $('#' + iframe_str).hide('slow', function () {
          $('#pprf-row-brief-' + ids_str + '' + addEdit_str + ' .dashicons').removeClass('dashicons-arrow-up');
          $('#pprf-row-brief-' + ids_str + '' + addEdit_str + ' .dashicons').addClass('dashicons-edit');
        });
        $('#' + exp_str).hide(); //	if( $('#pprf-row-brief-' + ids_str + ' .dashicons' ).hasClass('dashicons') ){	
        //	}
      }

      $('#pprf-row-brief-' + ids_str + ' .dashicons-trash').removeClass('dashicons-arrow-up');
    }
  });
  /**
   * click to explan its iframe
   */
  //$(".pprf-expand-bar").live( 'click', function(){

  $(document.body).on('click', '.pprf-expand-bar', function () {
    var ids = $(this).data('target');
    var iframe = 'panda-repeater-edit-' + ids;

    if ($(this).hasClass('pprf-add-expand')) {
      iframe = 'panda-repeater-add-new-' + ids;
    }

    if (typeof document.getElementById(iframe) != 'undefined') {
      if (typeof document.getElementById(iframe).contentWindow.pprf_resize_fn != 'undefined') {
        document.getElementById(iframe).contentWindow.pprf_resize_fn();
      }
    }
  });
  /**
   * click to delete
   */
  //$('.pprf-trash-btn').live( 'click', function(){

  $(document.body).on('click', '.pprf-trash-btn', function (e) {
    e.stopPropagation();
    var ids_str = $(this).data('target');
    var iframe_str = 'panda-repeater-edit-' + ids_str;

    if ($(this).hasClass('pprf-add-expand')) {
      iframe_str = 'panda-repeater-add-new-' + ids_str;
    }

    var trash_int = 0;

    if ($(this).hasClass('pprf-btn-not-trashed')) {
      trash_int = 1;
    }

    if ($(this).hasClass('pprf-btn-trashed')) {
      trash_int = 2;
    }

    if ($(this).hasClass('pprf-btn-delete')) {
      trash_int = 2;
    } //document.getElementById( iframe_str ).contentWindow.pprf_delete_item_fn();


    pprf_delete_item_fn($(this).data('podid'), $(this).data('postid'), $(this).data('tb'), $(this).data('itemid'), $(this).data('userid'), $(this).data('iframe_id'), $(this).data('poditemid'), trash_int);
  }); //$('.pprf-save-btn').live( 'click', function(){

  $(document.body).on('click', '.pprf-save-btn', function (e) {
    e.stopPropagation();

    if ($(this).hasClass('pprf-btn-ready')) {
      var ids_str = $(this).data('target');
      var iframe_str = 'panda-repeater-edit-' + ids_str;

      if ($(this).hasClass('pprf-save-new-btn')) {
        iframe_str = 'panda-repeater-add-new-' + ids_str;
      }

      $('#panda-repeater-save-' + ids_str + '-loader').removeClass('hidden');
      $('#' + iframe_str).contents().find('.pods-submit-button').trigger("click");
      pprfChanged_bln = false;
    }
  });
  /**
   * if a pods is is clicked, flag it as saved
   */

  $('.toplevel_page_panda-pods-repeater-field .pods-field-input').on('click keyup change', function () {
    // if( typeof $('#pprf-on-page-data').data('saved') != 'undefined' ){
    //			$('#pprf-on-page-data').data('saved', '1');			
    //		 }
    pprfChanged_bln = true;
  }); // $('#publishing-action .button, #save-action .button').click( function( evt ){

  $(document).on('click', '#publishing-action .button, #save-action .button', function () {
    if (pprfChanged_bln) {
      evt.preventDefault();
      var leave_bln = confirm(strs_obj.Ignore_changes);

      if (leave_bln == true) {
        pprfChanged_bln = false; //$( this ).click();

        $(this).trigger('click');
      }

      if (leave_bln == false) {
        return false;
      }
    }
  });
  /**
   * toggle trashed and current
   */
  // $('.pprf-tab .dashicons-trash').live('click', function(){

  $(document.body).on('click', '.pprf-tab .dashicons-trash', function () {
    $('#panda-repeater-fields-' + $(this).parent().data('target') + ' .pprf-trashed').css('display', 'block');
    $('#panda-repeater-fields-' + $(this).parent().data('target') + ' .pprf-not-trashed').css('display', 'none');
    $(this).parent().parent().children('.active').removeClass('active');
    $(this).parent().addClass('active');
    pprf_odd_even_color_fn($(this).parent().data('target'));
  }); //$('.pprf-tab .dashicons-portfolio').live('click', function(){

  $(document.body).on('click', '.pprf-tab .dashicons-portfolio', function () {
    $('#panda-repeater-fields-' + $(this).parent().data('target') + ' .pprf-trashed').css('display', 'none');
    $('#panda-repeater-fields-' + $(this).parent().data('target') + ' .pprf-not-trashed').css('display', 'block');
    $(this).parent().parent().children('.active').removeClass('active');
    $(this).parent().addClass('active');
    pprf_odd_even_color_fn($(this).parent().data('target'));
  });
  /**
   * remove repeated
   */
  //$( '.pprf-dismiss-btn').live('click', function(){ 

  $(document.body).on('click', '.pprf-dismiss-btn', function () {
    $('#li-' + $(this).data('target') + '-repeated').remove();
  });
});
var pprfChanged_bln = false;
//# sourceMappingURL=admin.js.map
