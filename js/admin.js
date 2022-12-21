"use strict";

/**
 * The JS file for the repeater field
 *
 * @package panda_pods_repeater_field
 * @author  Dongjie Xu
 */

/**
 * Collection for JS functions
 *
 * @package panda_pods_repeater_field
 * @author Dongjie Xu
 * @since 09/02/2016
 */
function pprf_resize_iframe(obj) {
  obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}

function pprf_update_iframe_size(x, y) {
  if (x != '') {
    jQuery('#' + x).height(jQuery('#' + x).height()).animate({
      height: y
    }, 500);
  }
}

function pprf_update_size(x) {
  if (x != '') {
    var pprf_original_height = jQuery(x + ' html #wpbody-content').height() + 60;
    jQuery('#' + x).height(jQuery('#' + x).height()).animate({
      height: pprf_original_height
    }, 500);
    ;
  }
}

function pprf_update_parent_iframe(x) {
  if (x != '') {
    var y = jQuery('#' + x + ' html #wpbody-content').height(); // 4px is the small gap at the bottom

    jQuery('#' + x).height(jQuery('#' + x).height()).animate({
      height: y
    }, 500);
  }
}

var pprf_parent_height = jQuery('html').height();
/**
 * Insert a new row to the page after adding a new item.
 */

function pprf_new(podid, postid, cpodid, authorid, iframeid, poditemid, parent_name) {
  if (false === isNaN(podid) && false === isNaN(cpodid) && false === isNaN(authorid) && false === isNaN(poditemid)) {
    var para_obj = {
      'podid': podid,
      'postid': postid,
      'cpodid': cpodid,
      'authorid': authorid,
      'poditemid': poditemid,
      'action': 'admin_pprf_load_newly_added',
      'security': ajax_script.nonce
    };
    var data_obj = para_obj;
    jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-loader').removeClass('hidden');
    jQuery.post(ajax_script.ajaxurl, data_obj, function (responses) {
      jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-loader').addClass('hidden');
      var response_data = responses['data'];

      if (responses['success'] === true && typeof response_data['id'] !== 'undefined' && false === isNaN(response_data['id'])) {
        var iframe_build = pprf_build_item_html(response_data, podid, postid, cpodid, authorid, iframeid, poditemid, parent_name, false);
        jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list').append(iframe_build); // If entries limit, toggle the add new.

        var items_left_count = jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' > .pprf-redorder-list > li').length;
        var limit_int = parseInt(jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-entry-limit').val());

        if (limit_int != 0 && items_left_count >= limit_int) {
          jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-add-new').addClass('hidden');
        }
      } // If add a new one, activeate the live items tab.


      jQuery('#panda-repeater-fields-tabs-' + cpodid + '-' + poditemid + ' .pprf-tab .dashicons-portfolio').click();
      pprf_odd_even_color(cpodid + '-' + poditemid);
    });
  }
}

function pprf_build_item_html(items, podid, postid, cpodid, authorid, iframeid, poditemid, parent_name, is_repeated) {
  if (typeof items['id'] == 'undefined' || true === isNaN(items['id']) || true === isNaN(podid)) {
    return '';
  }

  var trashed_css = '';
  var btn_trashed_css = 'pprf-btn-delete';
  var display_style = '';
  var edit_icon_css = 'dashicons-edit';

  if (jQuery('#panda-repeater-trash-info-' + cpodid + '-' + poditemid).data('enable-trash') == 1) {
    if (typeof items['trashed'] != 'undefined' && items['trashed'] == 1) {
      trashed_css = 'pprf-trashed';
      btn_trashed_css = 'pprf-btn-trashed'; // If the portfolio foder is open, hide the trash one.

      edit_icon_css = 'dashicons-update ';

      if (jQuery('#panda-repeater-fields-tabs-' + cpodid + '-' + poditemid + ' .pprf-tab.active .dashicons').hasClass('dashicons-portfolio')) {
        display_style = 'display:none;';
      }
    } else {
      trashed_css = 'pprf-not-trashed';
      btn_trashed_css = 'pprf-btn-not-trashed';

      if (jQuery('#panda-repeater-fields-tabs-' + cpodid + '-' + poditemid + ' .pprf-tab.active .dashicons').hasClass('dashicons-trash')) {
        display_style = 'display:none;';
      }
    }
  }

  var repeated_css_append = '';

  if (is_repeated == true) {
    repeated_css_append = '-repeated';
  }

  var delete_action = '';
  var ids_in_css = cpodid + '-' + items['id'] + '-' + poditemid;
  var responding_id = items['id'];
  var the_title = items['title'];
  var the_label = the_title;

  if (typeof items['label'] != 'undefined' && items['label'] != '') {
    the_label = items['label'];
  }

  var next_background_css = jQuery('#next-bg').data('bg');
  var full_url = PANDA_PODS_REPEATER_PAGE_URL[0] + 'iframe_id=panda-repeater-edit-' + ids_in_css + '&podid=' + podid + '&tb=' + cpodid + '&postid=' + postid + '&itemid=' + responding_id + '&poditemid=' + poditemid + '&pprf_nonce=' + PANDA_PODS_REPEATER_CONSTANTS.nonce;
  var html_build = '<li data-id="' + responding_id + '" class="' + trashed_css + '" id="li-' + ids_in_css + repeated_css_append + '" style="' + display_style + '">' + '<div class="pprf-row  w100 pprf-left">' + '<div class="w100 pprf-left" id="pprf-row-brief-' + ids_in_css + repeated_css_append + '">' + '<div class="pprf-left pd8 pprf-left-col ' + next_background_css + ' ">' + the_label + '</div>';

  if (is_repeated == true) {
    html_build += '<div class="button pprf-right-col center pprf-dismiss-btn ' + delete_action + ' ' + btn_trashed_css + '" role="button" data-podid="' + podid + '"  data-postid="' + postid + '"  data-tb="' + cpodid + '"  data-itemid="' + responding_id + '"  data-userid="' + authorid + '"  data-iframe_id="panda-repeater-edit-' + ids_in_css + '"  data-poditemid="' + poditemid + '" data-target="' + ids_in_css + '" >' + '<span class="dashicons dashicons-dismiss pdt6 mgb0 "></span>' + '</div>' + '<div class="pprf-left pd8">Repeated</div>';
  } else {
    html_build += '<div class="button pprf-right-col center pprf-trash-btn ' + delete_action + ' ' + btn_trashed_css + '" role="button" data-podid="' + podid + '"  data-postid="' + postid + '"  data-tb="' + cpodid + '"  data-itemid="' + responding_id + '"  data-userid="' + authorid + '"  data-iframe_id="panda-repeater-edit-' + ids_in_css + '"  data-poditemid="' + poditemid + '" data-target="' + ids_in_css + '" >' + '<span class="dashicons dashicons-trash pdt6 mgb0 "></span>' + '<div id="panda-repeater-trash-' + ids_in_css + '-loader" class="pprf-left hidden mgl5">' + '<img src = "' + PANDA_PODS_REPEATER_CONSTANTS.url + '/images/dots-loading.gif" alt="loading" class="mgl8 loading pprf-left"/>' + '</div>' + '</div>' + '<div class="button pprf-right-col center pprf-save-btn" role="button" data-podid="' + podid + '"  data-postid="' + postid + '"  data-tb="' + cpodid + '"  data-itemid="' + responding_id + '"  data-userid="' + authorid + '"  data-iframe_id="panda-repeater-edit-' + ids_in_css + '" data-poditemid="' + poditemid + '" data-target="' + ids_in_css + '" >' + '<img src = "' + PANDA_PODS_REPEATER_CONSTANTS.url + 'images/save-icon-tran.png" class="pprf-save-icon  mgt8 mgb2"/>' + '<div id="panda-repeater-save-' + ids_in_css + '-loader" class="pprf-left hidden mgl5">' + '<img src = "' + PANDA_PODS_REPEATER_CONSTANTS.url + 'images/dots-loading.gif" alt="loading" class="mgl8 pprf-left"/>' + '</div>' + '</div>' + '<div class="button pprf-edit pprf-row-load-iframe alignright pprf-right-col center pprf-edit-btn" role="button" data-target="' + ids_in_css + '" data-url="' + full_url + '">' + '<span class="dashicons ' + edit_icon_css + ' pdt8 mgb0 pprf-edit-span"></span>' + '<div id="panda-repeater-edit-' + ids_in_css + '-loader" class="pprf-left hidden mgl5">' + '<img src = "' + PANDA_PODS_REPEATER_CONSTANTS.url + '/images/dots-loading.gif" alt="loading" class="mgl9 pprf-left"/>' + '</div>	' + '</div>';
  }

  html_build += '</div>' + '<div>' + '<iframe id="panda-repeater-edit-' + ids_in_css + '" frameborder="0" scrolling="no" src="" style="display:none; " class="panda-repeater-iframe w100"></iframe>' + '<div id="panda-repeater-edit-expand-' + ids_in_css + '" class="w100 pprf-left center pdt3 pdb3 pprf-expand-bar pprf-edit-expand" data-target="' + ids_in_css + '"  style="display:none;">Content missing? Click here to expand</div>' + '</div>' + '</div>' + '</li>';

  if (next_background_css === 'pprf-purple-bg') {
    jQuery('#next-bg').data('bg', 'pprf-white-bg');
  } else {
    jQuery('#next-bg').data('bg', 'pprf-purple-bg');
  }

  return html_build;
}
/**
 * Delete an item
 */


function pprf_delete_item(podid, postid, cpodid, itemid, authorid, iframeid, poditemid, trashed) {
  if (false === isNaN(podid) && false === isNaN(cpodid) && false === isNaN(authorid) && false === isNaN(itemid) && false === isNaN(poditemid)) {
    var para_obj = {
      'podid': podid,
      'postid': postid,
      'cpodid': cpodid,
      'itemid': itemid,
      'authorid': authorid,
      'poditemid': poditemid,
      'action': 'admin_pprf_delete_item',
      'trash': trashed,
      'security': ajax_script.nonce
    };
    var info_text = '';

    if (trashed == 0) {
      info_text = strs_obj.be_restored;
    }

    if (trashed == 1) {
      info_text = strs_obj.can_recover;
    }

    if (trashed == 2) {
      info_text = strs_obj.be_deleted;
    }

    var data_obj = para_obj;
    var passed = confirm(strs_obj.you_sure + ' ' + info_text);

    if (passed === true) {
      if (trashed == 0) {
        jQuery('#panda-repeater-edit-' + cpodid + '-' + itemid + '-' + poditemid + '-loader').removeClass('hidden');
      } else {
        jQuery('#panda-repeater-trash-' + cpodid + '-' + itemid + '-' + poditemid + '-loader').removeClass('hidden');
      }

      jQuery.post(ajax_script.ajaxurl, data_obj, function (responses) {
        if (responses['success'] === true) {
          var response_data = responses['data'];

          if (response_data.length != 0) {
            var ids_in_css = cpodid + '-' + itemid + '-' + poditemid;
            var exp_str = 'panda-repeater-edit-expand-' + ids_in_css;
            var iframe_build = 'panda-repeater-edit-' + ids_in_css;

            if (trashed == 0) {
              jQuery('#panda-repeater-edit-' + cpodid + '-' + itemid + '-' + poditemid + '-loader').addClass('hidden');
            } else {
              jQuery('#panda-repeater-trash-' + cpodid + '-' + itemid + '-' + poditemid + '-loader').addClass('hidden');
            }

            if (trashed == 0) {
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').removeClass('dashicons-update');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').addClass('dashicons-edit');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').removeClass('pprf-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').addClass('pprf-not-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').css('display', 'none');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').addClass('pprf-btn-not-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').removeClass('pprf-btn-trashed');

              if (jQuery.trim(jQuery('#' + iframe_build).contents().find("body").html()) != '') {
                jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-save-btn .pprf-save-icon').attr('src', PANDA_PODS_REPEATER_CONSTANTS.url + 'images/save-icon.png');
              }
            }

            if (trashed == 1) {
              if (jQuery('#' + iframe_build) != 'undefined') {
                jQuery('#' + iframe_build).hide();
              }

              if (jQuery('#' + exp_str) != 'undefined') {
                jQuery('#' + exp_str).hide();
              }

              jQuery('#pprf-row-brief-' + ids_in_css + ' .dashicons').removeClass('dashicons-arrow-up');
              jQuery('#pprf-row-brief-' + ids_in_css + ' .dashicons').addClass('dashicons-edit');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').removeClass('pprf-not-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').addClass('pprf-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').css('display', 'none');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').addClass('dashicons-update');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-row-load-iframe .pprf-edit-span').removeClass('dashicons-edit');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').addClass('pprf-btn-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-trash-btn').removeClass('pprf-btn-not-trashed');
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"] .pprf-save-btn .pprf-save-icon').attr('src', PANDA_PODS_REPEATER_CONSTANTS.url + 'images/save-icon-tran.png');
            }

            if (trashed == 2) {
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' #' + iframeid).parent().parent().remove();
              jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').remove(); // If entries limit, toggle the add new.

              var items_left_count = jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' > .pprf-redorder-list > li').length;
              var limit_int = parseInt(jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-entry-limit').val());

              if (limit_int != 0 && items_left_count < limit_int) {
                jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + '-add-new').removeClass('hidden');
              } // Integrate with simpods js.


              if (typeof call_simpods !== 'undefined' && jQuery.isFunction(call_simpods)) {
                call_simpods(responses);
              }
            }

            pprf_odd_even_color(cpodid + '-' + poditemid);
          }
        }
      });
    }
  }
}

jQuery(document).on('click', '.pprf-redorder-btn', function (evt) {
  var id = jQuery(this).data('id');
  jQuery(this).addClass('hidden');
  jQuery(this).parent().children('.pprf-save-redorder-btn').removeClass('hidden');
  jQuery(this).parent().children('.pprf-redorder-list-wrap').removeClass('hidden');
  jQuery(this).parent().children('.pprf-row').addClass('hidden');
  jQuery('#' + id + '-add-new').addClass('hidden');
});
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
 * Load more.
 */

jQuery(document).on('click', '.pprf-load-more-btn', function (evt) {
  evt.preventDefault();
  jQuery('#pprf-load-more-wrap-' + jQuery(this).data('target') + ' .pprf-ajax-img').css('display', 'block');
  pprf_load_more(jQuery(this).data('target'), jQuery(this));
});

function pprf_load_more(target_str, ele_obj) {
  var loaded_arr = new Array();
  jQuery('#panda-repeater-fields-' + target_str + ' .pprf-redorder-list li').each(function (idx_int) {
    loaded_arr[idx_int] = parseInt(jQuery(this).data('id'));
  });
  var data_obj = {
    action: 'admin_pprf_load_more',
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
    var html_build = '';

    if (response_obj.success == true) {
      var trashed_count = 0;
      var data_length = response_obj.data.length;
      var loaded_length = loaded_arr.length;

      for (var i = 0; i < data_length; i++) {
        var is_repeated = false;

        if (jQuery('#pprf-load-more-wrap-' + target_str + ' .panda-repeater-to-load').val() === 'append_to') {
          // Only need to check repeatition if it is on Append To.
          for (var j = 0; j < loaded_length; j++) {
            // InArry doesn't work.
            if (parseInt(response_obj.data[i]['id']) == loaded_arr[j]) {
              is_repeated = true;
              break;
            }
          }
        }

        html_build += pprf_build_item_html(response_obj.data[i], data_obj.pod_id, data_obj.post_id, data_obj.saved_tb, data_obj.authorid, data_obj.iframe_id, data_obj.pod_item_id, data_obj.cp_title, is_repeated);

        if (response_obj.data[i]['trashed'] == 1) {
          trashed_count++;
        }
      }

      var info_text = response_obj.data.length;

      if (data_obj.trashable == 1) {
        info_text = parseInt(response_obj.data.length) - trashed_count + ' published, ' + trashed_count + ' trashed.';
      }

      if (jQuery('#pprf-load-more-wrap-' + target_str + ' .panda-repeater-to-load').val() == 'append_to') {
        jQuery('#panda-repeater-fields-' + data_obj.saved_tb + '-' + data_obj.pod_item_id + ' .pprf-redorder-list').append(html_build);
      } else {
        jQuery('#panda-repeater-fields-' + data_obj.saved_tb + '-' + data_obj.pod_item_id + ' .pprf-redorder-list').html(html_build);
      }

      pprf_odd_even_color(data_obj.saved_tb + '-' + data_obj.pod_item_id);
      jQuery('#pprf-load-more-wrap-' + target_str + ' .pprf-ajax-img').css('display', 'none');

      if (response_obj.data.length != 0) {
        jQuery('#panda-repeater-start-from-' + target_str).val(parseInt(data_obj.start) + parseInt(response_obj.data.length));
      }

      jQuery('#pprf-load-more-wrap-' + target_str + ' .pprf-load-more-report').text(' | Loaded ' + info_text);
    }
  });
}
/**
 * Reset colours for each row.
 */


function pprf_odd_even_color(ids) {
  jQuery('#panda-repeater-fields-' + ids + ' .pprf-left-col').removeClass('pprf-purple-bg');
  jQuery('#panda-repeater-fields-' + ids + ' .pprf-left-col').removeClass('pprf-white-bg');

  if (jQuery('#panda-repeater-fields-tabs-' + ids).length == 0) {
    jQuery('#panda-repeater-fields-' + ids + ' .pprf-left-col').each(function (idx) {
      if (idx % 2 == 0) {
        jQuery(this).addClass('pprf-white-bg');
      } else {
        jQuery(this).addClass('pprf-purple-bg');
      }
    });
  }

  jQuery('#panda-repeater-fields-' + ids + ' .pprf-not-trashed').each(function (idx) {
    if (idx % 2 == 0) {
      jQuery(this).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-white-bg');
    } else {
      jQuery(this).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-purple-bg');
    }
  });
  jQuery('#panda-repeater-fields-' + ids + ' .pprf-trashed').each(function (idx) {
    if (idx % 2 == 0) {
      jQuery(this).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-white-bg');
    } else {
      jQuery(this).children('.pprf-row').children().children('.pprf-left-col').addClass('pprf-purple-bg');
    }
  });
}
/**
 * If reassigned successfully, remove the item and reset colours.
 */


function pprf_reassign(cpodid, poditemid, itemid) {
  jQuery('#panda-repeater-fields-' + cpodid + '-' + poditemid + ' .pprf-redorder-list li[data-id="' + itemid + '"]').remove();
  pprf_odd_even_color(cpodid + '-' + poditemid);
}

jQuery(document).ready(function ($) {
  /**
   * Fix_helper_modified for drag and drop.
   */
  var fix_helper_modified = function fix_helper_modified(e, tr) {
    var $originals = tr.children();
    var $helper = tr.clone();
    $helper.children().each(function (index) {
      $(this).width($originals.eq(index).width());
    });
    return $helper;
  },
      update_index = function update_index(e, ui) {
    var the_order = $(this).sortable('toArray');
    var data_obj = {
      action: 'admin_pprf_update_order',
      order: the_order,
      security: ajax_script.nonce
    };
    $.post(ajax_script.ajaxurl, data_obj, function (resp_arr) {
      pprf_odd_even_color(resp_arr.data.pprf_id);
    });
  };

  if ($('.pprf-redorder-list.pandarf_order').length != 0) {
    $('.pprf-redorder-list.pandarf_order').sortable({
      helper: fix_helper_modified,
      cursor: 'move',
      opacity: 0.7,
      tolerance: 'intersect',
      update: update_index,
      cancel: '.pprf-row-load-iframe, .pprf-save-btn, pprf-trash-btn',
      handle: '.pprf-left-col'
    });
  }

  $(document.body).on('click', '.pprf-row-load-iframe', function (e) {
    e.stopPropagation();
    var url_str = $(this).data('url');
    var ids_in_css = $(this).data('target');
    var exp_str = 'panda-repeater-edit-expand-' + ids_in_css;
    var iframe_build = 'panda-repeater-edit-' + ids_in_css;
    var trash_ele = $(this).parent().children('.pprf-trash-btn');

    if ($(this).children('.pprf-edit-span').hasClass('dashicons-update')) {
      // Restore this item.
      pprf_delete_item(trash_ele.data('podid'), trash_ele.data('postid'), trash_ele.data('tb'), trash_ele.data('itemid'), trash_ele.data('userid'), trash_ele.data('iframe_id'), trash_ele.data('poditemid'), 0);
    } else {
      var addEdit_str = ' .pprf-edit';

      if ($(this).hasClass('pprf-add')) {
        addEdit_str = '.pprf-add';
        iframe_build = 'panda-repeater-add-new-' + ids_in_css;
        exp_str = 'panda-repeater-add-new-expand-' + ids_in_css;
      }

      if ($('#pprf-row-brief-' + ids_in_css + ' .dashicons').hasClass('dashicons-edit')) {
        // If iframe not loaded.
        if ($('#' + iframe_build).attr('src') == '') {
          $('#' + iframe_build).attr('src', url_str);
          $('#' + iframe_build + '-loader').removeClass('hidden');
        }

        $('#' + iframe_build).show('slow', function () {
          $('#pprf-row-brief-' + ids_in_css + '' + addEdit_str + ' .dashicons').addClass('dashicons-arrow-up');
          $('#pprf-row-brief-' + ids_in_css + '' + addEdit_str + ' .dashicons').removeClass('dashicons-edit');
        });
        $('#' + exp_str).show();
        $('#' + iframe_build).on('load', function () {
          $('#' + iframe_build + '-loader').addClass('hidden'); // Change icon.

          $('#panda-repeater-save-' + ids_in_css + '-loader').parent().children('.pprf-save-icon').attr('src', PANDA_PODS_REPEATER_CONSTANTS.url + '/images/save-icon.png');
          $('#panda-repeater-save-' + ids_in_css + '-loader').parent().addClass('pprf-btn-ready');
          $('#panda-repeater-save-' + ids_in_css + '-loader').addClass('hidden');
          $('#pprf-row-brief-' + ids_in_css + addEdit_str + ' .dashicons').addClass('dashicons-arrow-up');
          $('#pprf-row-brief-' + ids_in_css + addEdit_str + ' .dashicons').removeClass('dashicons-edit');
        });
      } else {
        $('#' + iframe_build).hide('slow', function () {
          $('#pprf-row-brief-' + ids_in_css + addEdit_str + ' .dashicons').removeClass('dashicons-arrow-up');
          $('#pprf-row-brief-' + ids_in_css + addEdit_str + ' .dashicons').addClass('dashicons-edit');
        });
        $('#' + exp_str).hide();
      }

      $('#pprf-row-brief-' + ids_in_css + ' .dashicons-trash').removeClass('dashicons-arrow-up');
    }
  });
  /**
   * Click to explan its iframe.
   */

  $(document.body).on('click', '.pprf-expand-bar', function () {
    var ids = $(this).data('target');
    var iframe = 'panda-repeater-edit-' + ids;

    if ($(this).hasClass('pprf-add-expand')) {
      iframe = 'panda-repeater-add-new-' + ids;
    }

    if (typeof document.getElementById(iframe) != 'undefined') {
      if (typeof document.getElementById(iframe).contentWindow.pprf_resize_window != 'undefined') {
        document.getElementById(iframe).contentWindow.pprf_resize_window();
      }
    }
  });
  /**
   * Click to delete.
   */

  $(document.body).on('click', '.pprf-trash-btn', function (e) {
    e.stopPropagation();
    var ids = $(this).data('target');
    var iframe = 'panda-repeater-edit-' + ids;

    if ($(this).hasClass('pprf-add-expand')) {
      iframe = 'panda-repeater-add-new-' + ids;
    }

    var trash = 0;

    if ($(this).hasClass('pprf-btn-not-trashed')) {
      trash = 1;
    }

    if ($(this).hasClass('pprf-btn-trashed')) {
      trash = 2;
    }

    if ($(this).hasClass('pprf-btn-delete')) {
      trash = 2;
    }

    pprf_delete_item($(this).data('podid'), $(this).data('postid'), $(this).data('tb'), $(this).data('itemid'), $(this).data('userid'), $(this).data('iframe_id'), $(this).data('poditemid'), trash);
  });
  $(document.body).on('click', '.pprf-save-btn', function (e) {
    e.stopPropagation();

    if ($(this).hasClass('pprf-btn-ready')) {
      var ids = $(this).data('target');
      var iframe = 'panda-repeater-edit-' + ids;

      if ($(this).hasClass('pprf-save-new-btn')) {
        iframe = 'panda-repeater-add-new-' + ids;
      }

      $('#panda-repeater-save-' + ids + '-loader').removeClass('hidden');
      $('#' + iframe).contents().find('.pods-submit-button').trigger("click");
      pprf_is_changed = false;
    }
  });
  /**
   * If a pods is is clicked, flag it as saved.
   */

  $('.toplevel_page_panda-pods-repeater-field .pods-field-input').on('click keyup change', function () {
    pprf_is_changed = true;
  });
  $(document).on('click', '#publishing-action .button, #save-action .button', function () {
    if (pprf_is_changed) {
      evt.preventDefault();
      var leave = confirm(strs_obj.Ignore_changes);

      if (leave == true) {
        pprf_is_changed = false;
        $(this).trigger('click');
      }

      if (leave == false) {
        return false;
      }
    }
  });
  /**
   * Toggle trashed and current.
   */

  $(document.body).on('click', '.pprf-tab .dashicons-trash', function () {
    $('#panda-repeater-fields-' + $(this).parent().data('target') + ' .pprf-trashed').css('display', 'block');
    $('#panda-repeater-fields-' + $(this).parent().data('target') + ' .pprf-not-trashed').css('display', 'none');
    $(this).parent().parent().children('.active').removeClass('active');
    $(this).parent().addClass('active');
    pprf_odd_even_color($(this).parent().data('target'));
  });
  $(document.body).on('click', '.pprf-tab .dashicons-portfolio', function () {
    $('#panda-repeater-fields-' + $(this).parent().data('target') + ' .pprf-trashed').css('display', 'none');
    $('#panda-repeater-fields-' + $(this).parent().data('target') + ' .pprf-not-trashed').css('display', 'block');
    $(this).parent().parent().children('.active').removeClass('active');
    $(this).parent().addClass('active');
    pprf_odd_even_color($(this).parent().data('target'));
  });
  /**
   * Remove repeated.
   */

  $(document.body).on('click', '.pprf-dismiss-btn', function () {
    $('#li-' + $(this).data('target') + '-repeated').remove();
  });
});
var pprf_is_changed = false;
//# sourceMappingURL=admin.js.map
