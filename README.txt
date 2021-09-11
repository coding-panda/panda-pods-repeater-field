=== Panda Pods Repeater Field ===
Contributors: Coding Panda
Donate link: http://www.multimediapanda.co.uk/product/panda-pods-repeater-field/
Tags: pods, repeater field, storage
Requires at least: 3.8
Tested up to: 5.8
Stable tag: 1.4.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Repeater fields for Pods Framework. Adding a repeatable field on pages.

== Description ==

Panda Pods Repeater Field is a plugin for Pods Framework. The beauty of it is that it is not just a repeater field. It is a quick way to set up a relational database and present the data on the same page. It takes the advantage of Pods table storage, so you don’t need to worry that the posts and postmeta data table may expand dramatically and slow down the page loading. This plugin is compatible with Pods Framework 2.6.1 or later. To download Pods Framework, please visit http://pods.io/. After each update, please clear the cache to make sure the CSS and JS are updated. Usually, Ctrl + F5 will do the trick.

= Introduction =
[youtube https://www.youtube.com/watch?v=8oUeROi62o8]
[youtube https://www.youtube.com/watch?v=H7YJLMPgG2U]


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/panda-pods-repeater-field` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin

== Frequently Asked Questions ==

= How to use it? =
Please see the screenshots for instructions.

= How to fetch data at the frontend =
From Version 1.1.4, you can use pods_field(). e.g. in a single post template, pods_field( 'field_name' ) to fetch the data for the current page, otherwise use pods_field( 'pods_name', 'post_id', 'field_name' ) to fetch any data you want anywhere. To fetch data in the settings area, use pods_field( 'pods_name', false, 'field_name' ). To fetch data in the users area, use pods_field( 'user', 'user_id', 'field_name' ).

You can use the filters: pandarf_pods_field_attrs( array(), $value_ukn, $row_arr, $params_arr, $pods_obj ) and pandarf_data_attrs( array(), $data_arr, $parentPod_str ) to alter the repeater data returned from pods_field().

From Version 1.1.6, if the field type is "file", it will return the file ids, then use WordPress APIs like get_attached_file(), wp_get_attachment_image_src() etc to get the file details. Relation types of 'user', 'post type', 'pod' and 'media' will now return the saved ID.

You can also use this API to fetch data: pandarf_items_fn( $fields_arr, $atts_arr, $showQuery_bln ). Please see the Screenshots section for how to find ids.

$fields_arr search repeater field table 
array(
	'id' => '', // row id
	'name' => '', // the common name field used by pods
	'child_pod_name' => '', // repeater table name
	'parent_pod_id' => '',// main table pod id
	'parent_pod_post_id' => '', // main table post id
	'parent_pod_field_id' => '', // main table pod Panda Pod Repeater Field id
)

$atts_arr for some basic mySQL commands (Optional)
array(
	'where' => '', // exter where, expected to be escaped
	'order' => 'ASC', 
	'order_by' => 'pandarf_order',
	'group_by' => '', 
	'start' => 0,
	'limit' => 0,
	'count_only' => false, // if true, it will return the amount of rows in an array.
	'add_tb_prefix'	=> true, // add $table_prefix to the table name. Default to ture
)

$showQuery_bln
If set to true, it will print out the sql command. For debugging purpose. Default to false.

An example of basic usage. IDs from the Screenshots section:
pandarf_items_fn( array( 'child_pod_name' => 'comic_item', 'parent_pod_id' => 2273, 'parent_pod_post_id' => 2275, 'parent_pod_field_id' => 2274 ) );

It will return the items attached to the post from Comic Contents.

= How to insert data at the frontend =
You can use this API to insert data: pandarf_insert_fn( $fields_arr, $prf_arr, $showQuery_bln ). Please see the screenshots for how to find ids.

$fields_arr extra fields other than panda repeater fields to insert: 
array( 
	'field_name' => '', 
	'field_name' => '' 
	... ...
)
$prf_arr the default repeater fields 
array(
	'child_pod_name' => '', // The name of the table for saving repeater field items.
	'parent_pod_id' => '', // main table pod id
	'parent_pod_post_id' => '', // main table post id
	'parent_pod_field_id' => '', // main table Panda Pods Pod Repeater Field id
	'user_id' => 0 // The author id
	'add_tb_prefix'	=> true, // add $table_prefix to the table name. Default to ture
)

$showQuery_bln
If set to true, it will print out the sql command. For debugging purpose. Default to false.

This API will return the wpdb inserted ID if successful, or false on failure. 

An example of basic usage. IDs from the Screenshots section:
$id_int = pandarf_insert_fn( array( 'name' => "hello panda" ), array( 'child_pod_name' => 'comic_item', 'parent_pod_id' => 2273, 'parent_pod_post_id' => 2275, 'parent_pod_field_id' => 2274, 'user_id' => $current_user->ID ) );

= How to allow the repeater field in a Pod from at the frontend =


add_filter('pprf_load_panda_repeater_allow_input', 'pprf_allow_frontend_input_fn', 10, 7 ); 

/**
 * The repeater field is only for admin area. If you want it to be available for the frontend users, you can use this filter
 * Please note nested fields are treated as frontend even the parent field loaded in the admin. No soluction for now
 *
 * @param  boolean $allow_bln allow the input item to be displayed
 * @param  array   $inAdmin_bln is in the admin area
 * @param  string  $name
 * @param  mixed   $value
 * @param  array   $options
 * @param  array   $pod
 * @param  int     $id
 * @return boolean still allow or not
 */
function pprf_allow_frontend_input_fn( $allow_bln, $inAdmin_bln, $name_str, $value_ukn, $options_arr, $pod_obj, $id_int  ){
	if( !is_admin() ){		
		if( $pod_obj->pod == 'your_pod_slug' ){
			$allow_bln	=	true;			
		}		
	}
	return  $allow_bln;
}

add_filter('pprf_load_panda_repeater_allow', 'pprf_allow_fn', 11, 2);

/**
 * The repeater field is only for logged in users with edit_posts capability 
 * If you want it to be available for the frontend users, you can use this filter
 *
 * @param  boolean $allow_bln allow the form to be displayed
 * @param  array   $get_arr variables from $_GET
 * @return boolean still allow or not
 */
function pprf_allow_fn( $allow_bln, $get_arr ){
	
	$pod_obj	=	pods('your_pod_slug');		
	if( $get_arr['podid'] == $pod_obj->pod_id ){
		$allow_bln = true;
	}
	return  $allow_bln;

}

== Screenshots ==

1. After the activation of Pods Framework, please also activate Advance Content Type and Table Storage in Pods Admin, under the Components section.
2. Activate Advance Content Type.
3. Activate Table Storage.
4. Here, you will learn how to use it through an example. First, we have to add a new Pod. In Pods, please click Create New.
5. and create a new Pod with Advance Content Type. In this tutorial, we call it "Comic Content"
6. We are going to use Comic Content as the storage of a repeater field.
7. Under the Admin UI tab, untick Show Admin Menu in Dashboard, because if you add an item to it, it is not linked to any parent posts.
8. Now we create a new post type in Pod. We name it "Comic" and we use Table Based storage, because we want to reduce the burden of the WordPress Posts table.
9. Once it has been created, we add a new field to Comic. We name it "Comic Box 1" here. At the bottom of the Field Type, you will find Pods Table As Repeater Field. Select it and then click the tab "Additional Field Options".
10. Now you can see a Pods Table "comic_content" we just created in the combo box. Select it, add or update the field and save Pod. You can set field width, defaulted to 100%. You can also limit the number of entries of the repeater field. Enable Trash will allow you to move items to Trash and Restore them if you still want them. Trashed items won't be pulled out by pods_field() and pandarf_items_fn(), but if you diable Trash later on, the trashed items will still be pulled out. There are other advanced options to choose. They should be self-explanatory.
11. If you are doing nested repeater fields, I recommend you set it to 100%. A sample of nested repeater fields.
12. OK, the set up is done. Now if you are adding a new Comic, 
13. you will see "Comic Box 1" in the More Fields area.
14. You can add as many new items to Comic Box 1 as you want, and they are only attached to the current Comic you are editing .
15. You can click top bar to contract it, the bottom bar to expand the window.
16. To find the parent_pod_id and parent_pod_field_id, go to Pods and click the main Pod (Comics in this tutorial).
17. Here, 2215 is parent_pod_id and 2222 is parent_pod_field_id
18. To find the parent_pod_post_id, open the main post and look at the URL. If you are using a Pods Adavnce Custom Type or a Custom Post Type as the main table, it is the "id" variable in the URL. If you add a repeater field to Settings, the parent post id is the same as parent_pod_id. If you add a repeater field to Users, the parent post id is the same as the user ID. 
19. The Load More functionality settings.
20. Instructions for the Load More functionality.
21. The Order option settings.
22. Display Order Info.

== Changelog ==

= 1.0.1 - 23rd Sept 2016 =
* Added: Added a filter to display item title

= 1.0.2 - 30rd Sept 2016 =
* Added: Enable pandarf_items_fn to search id and name fileds
* Added: Tell users to refresh pages after reordering

= 1.0.3 - 05th Oct 2016 =
* Added: allow custom where query in pandarf_items_fn 

= 1.0.4 - 06th Oct 2016 =
* debug: update pandarf_parent_post_id to number only, it was string before

= 1.0.5 - 14th Oct 2016 =
* debug: fixed the ordering for newly added items

= 1.0.6 - 21st Oct 2016 =
* debug: fixed the problem on user profile page 

= 1.0.7 - 28st Oct 2016 =
* debug: fixed the problem when using it within Advance Custom Type 

= 1.0.8 - 2nd Nov 2016 =
* debug: fixed the problem when order by pandarf_order at the frontend, cast string as number 

= 1.0.9 - 2nd Nov 2016 =
* debug: changed time from h:i:s to H:i:s 

= 1.1.0 - 1st Dec 2016 =
* add: add param - add_tb_prefix to API pandarf_items_fn() and pandarf_insert_fn so they can be used for all tables
* add: add action pods_post_delete_fn() to pods_api_post_delete_pod_item
* add: add action pprf_action_pods_post_delete_fn() to pods_post_delete_fn
* add: add filter pprf_filter_pods_post_delete_fn() to pods_api_post_delete_pod_item
* add: add action pprf_action_pods_post_save_fn() to pods_post_save_fn
* add: add filter pprf_filter_pods_post_save_fn() to pods_api_post_delete_pod_item
* add: auto-expanding when add and edit an item
* add: improved interface

= 1.1.1 - 4th Mar 2017 =
* debug: fixed the problem: after deleting an item, the label still stayed
* add: improved interface

= 1.1.2 - 22nd April 2017 =
* add: new close and expand bars on the top and bottom
* change: moved the delete button from the iframe to the parent window
* change: simplified re-order

= 1.1.3 - 13nd May 2017 =
* add: a Save button on the bar
* debug: fixed the problem when insert pandarf_order at the frontend, cast string as number 
* debug: remove add_action( 'save_post', array( $tableAsRepeater_cla, 'update_child_pod_fn' ), 10, 3 ), not needed any more

= 1.1.4 - 29th June 2017 =
* add: API to check if a field is a Panda Pods Repeater Field is_pandarf_fn( $fieldName_str )
* add: API to fetch repeater data for a data row from a database table pandarf_data_fn( $data_arr, $parentPod_str  )
* add: Integrated with pods_field()

= 1.1.5 - 09th July 2017 =
* debug: fixed the problem: newly created item fetched data from other parent posts when using pods_field

= 1.1.6 - 05th August 2017 =
* add: Now return file and user ids for relation types of 'user', 'post type', 'pod' and 'media'
* add: Now you can limit the number of entries

= 1.1.7 - 19th August 2017 =
* debug: fixed the problem if the number of entries was set to 0, the "Add New" would disappear.

= 1.1.8 - 15th October 2017 =
* debug: Now use pods_register_field_type() to register Panda Pods Repeater Field.
* debug: Fixed some styling problems
* debug: Fixed the pods relationship fields ordering problem
* add: Now give alerts if changes not saved

= 1.1.9 - 22nd October 2017 =
* debug: Fixed the problem that expanding and contrasting call the pprf_resize_fn() too many times and sometimes the iframe was not ready.
* add: Now items can be moved to trash and restored from trash

= 1.2.0 - 17nd December 2017 =
* change: Now saving table post_name instead of ID to solve the problem of migration. It won't affect the saved data, but you will have to update the field in Pods - pick the right one again in order to migrate properly.
* debug: fixed the problem that when using the same field name in two tables, it didn't bring back the right data.

= 1.2.1 - 5th March 2018 =
* change: Enhanced ajax security
* add: added support for frontend pods form
* fixed: newly added item could only be deleted, not trashed

= 1.3.0 - 27nd July 2018 =
* add: added load more functionality
* add: added order options
* add: re-order will update colours
* change: changed some code according to Pods official reviews

= 1.3.1 - 19th August 2018 =
* change: changed drag and drop tolerance from "pointer" to "intersect"
* debug: fixed a problem when Enable Load More was set to No, only ten items were loaded

= 1.3.2 - 23rd September 2018 =
* debug: fixed the date field not displaying problem

= 1.3.3 - 29th October 2018 =
* debug: somehow pods->delete() didn't work, use $wpdb query for now

= 1.3.4 - 03rd November 2018 =
* debug: fixed the problem for pods->delete() 
* add: now Admin Table Columns can be used for labels 

= 1.3.5 - 18th November 2018 =
* debug: fixed the problem when the order field was changed, the pandarf_items_fn function didn't respond to it 
* debug: fixed a typo

= 1.3.6 - 19th November 2018 =
* debug: fixed the problem when the order field was empty, it loaded data descendingly 
* change: adjusted some icons' padding and margin

= 1.3.7 - 9th December 2018 =
* debug: fixed the problem pods_field() didn't fetch data in ajax

= 1.3.8 - 10th March 2019 =
* add: optimised some code
* add: allow an item to be reassigned to another parent, a field using the same child table.

= 1.4.0 - 24th March 2019  =
* change: changed the way a repeater field is rendered out so it can be brought out at frontend for not logged in users.
* change: changed filter names from "pprf_load_panda_repeater_allow" to "pprf_load_panda_repeater_allow_input", "pprf_load_panda_repeater_allow_msg" to "pprf_load_panda_repeater_allow_input_msg".
* change: JavaScript 'live' to 'on'.
* add: allow chosen user roles to access the field.
* fix: The field was opened and closed immediately on mobile.

= 1.4.1 - 20th April 2019  =
* change: Made JavaScript alerts translatable. 
* add: Chinese language support.
* fix: error when a Pods table did not exist.
* fix: error on multisites.
* fix: error when a repeater field is a custom post type, although using a custom post type as a repeater field is not part of the plan.

= 1.4.2 - 27th May 2019  =
* add: added a filter pandarf_pods_field_fields.

= 1.4.3 - 22th June 2019  =
* add: locked down the font size in the form so it won't be affected by the theme
* change: changed $pods_obj->id to $pods_obj->id() in pandarf_pods_field_fn() to fix the problem in rest api

= 1.4.4 - 22ND September 2019 =
* fix: TypeError: Backbone.Marionette is undefined
* fix: $(...).live is not a function error using jquery

= 1.4.5 - 2nd November 2019 =
* add: Added some code to add the relationship fields if they are missing. Useful for migrating the repeater fields or create them by code.
* fix: the $ not a function problem when clicking the Load button.
* Change: Resize the window after running all JavaScripts.

= 1.4.6 - 4th February 2020 =
* fix: Fixed the problem that the trash, save and edit buttons had to be clicked twice on mobiles to work.
* add: some styling for the load more div.
* add: Integration with Simpods Area Field.
* add: Remove all tags from label outputs for security purpose and limit the label characters to 80. If a label contains images, videos, audio, shortcodes display relevant icons.

= 1.4.7 - 29th July 2020 =
* fix: Fixed the problem that the database class was not included in the pandarf_items_fn function.

= 1.4.8 - 9th December 2020 =
* add: Catch up with Simpods 3.0.0 on area field
* add: Minified CSS and JavaScript files.
* add: add a filter load_pprf_scripts_frontend to toggle loading PPRF scripts and styles at the front end.
* add: added more frontend css.
* fix: some JavaScript to catch up with WordPress 5.6 update

= 1.4.9 - 7th March 2021 =
* change: use index.php for pandarepeaterfield.php.
* change: changed click() to trigger('click').
* change: Reassign can also work for parent table with meta storage type.
* change: caught up with the change of wp_localize_script in WordPress 5.7
* add: Pods 2.8 use parent intead of pod_id. This verson catches up with this change.
* fix: A bug in the order query.

= 1.4.10 - 30th March 2021 =
* add: When displaying labels with admin columns, if it is a simple relationship, display labels instead of values.
* fix: A bug when using PHP 8

== Upgrade Notice ==

= 1.0.6 =
Fixed the problem on user profile page 

= 1.0.7 =
Fixed the problem when using it within Advance Custom Type 

= 1.0.8 =
Fixed the problem when order by pandarf_order at the frontend 

= 1.0.9 =
Changed time from h:i:s to H:i:s 

= 1.1.0 =
Add param - add_tb_prefix to API pandarf_items_fn() and pandarf_insert_fn so they can be used for all tables
Add action pods_post_delete_fn() to pods_api_post_delete_pod_item
Add action pprf_action_pods_post_delete_fn() to pods_post_delete_fn
Add filter pprf_filter_pods_post_delete_fn() to pods_api_post_delete_pod_item
Add action pprf_action_pods_post_save_fn() to pods_post_save_fn
Add filter pprf_filter_pods_post_save_fn() to pods_api_post_delete_pod_item
Add auto-expanding when add and edit an item
Improve interface

= 1.1.1 =
Fixed the problem: after deleting an item, the label still stayed
Improve interface

= 1.1.2 =
New close and expand bars on the top and bottom
Moved the delete button from the iframe to the parent window
Simplified re-order

= 1.1.3 =
A Save button on the bar
Fixed the problem when insert pandarf_order at the frontend, cast string as number 
Remove add_action( 'save_post', array( $tableAsRepeater_cla, 'update_child_pod_fn' ), 10, 3 ), not needed any more

= 1.1.4 =
Add: API to check if a field is a Panda Pods Repeater Field is_pandarf_fn( $fieldName_str )
Add: API to fetch repeater data for a data row from a database table pandarf_data_fn( $data_arr, $parentPod_str  )
Add: Integrated with pods_field()

= 1.1.5 =
Fixed the problem: newly created item fetched data from other parent posts when using pods_field

= 1.1.6 =
Add: now return file and user ids for relation types of 'user', 'post type', 'pod' and 'media'
Add: Now you can limit the number of entries

= 1.1.7 =
Debug: fixed the problem if the number of entries was set to 0, the "Add New" would disappear.

= 1.1.8 =
Debug: now use pods_register_field_type() to register Panda Pods Repeater Field.
Debug: Fixed some styling problems
Debug: Fixed the pods relationship fields ordering problem
Add: Now give alerts if changes not saved

= 1.1.9 =
Debug: fixed the problem that expanding and contrasting call the pprf_resize_fn() too many times and sometimes the iframe was not ready.
Add: Now items can be moved to trash and restored from trash.

= 1.2.0 =
Change: Now saving table post_name instead of ID to solve the problem of migration. It won't affect the saved data, but you will have to update the field in Pods - pick the right one again in order to migrate properly.
Debug: fixed the problem that when using the same field name in two tables, it didn't bring back the right data.

= 1.2.1 =
Change: Enhanced ajax security
Add: added support for frontend pods form
Fixed: newly added item could only be deleted, not trashed

= 1.3.0 =
Add: added load more functionality
Add: added order options
Add: re-order will update colours
Change: changed the code according to Pods official reviews

= 1.3.1 =
Change: changed drag and drop tolerance from "pointer" to "intersect"
Fixed: fixed a problem when Enable Load More was set to No, only ten items were loaded

= 1.3.2 =
Debug: fixed the date field not displaying problem.

= 1.3.3 =
Debug: somehow pods->delete() didn't work, use $wpdb query for now

= 1.3.4 =
* Debug: fixed the problem for pods->delete() 
* Add: now Admin Table Columns can be used for labels 

= 1.3.5 =
* Debug: fixed the problem when the order field was changed, the pandarf_items_fn function didn't respond to it 
* Debug: fixed a typo

= 1.3.6 =
* Debug: fixed the problem when the order field was empty, it loaded data descendingly
* Change: adjusted some icons' padding and margin

= 1.3.7 =
* Debug: fixed the problem pods_field() didn't fetch data in ajax

= 1.3.8 =
* Add: optimised some code
* Add: allow an item to be reassigned to another parent, a field using the same child table.

= 1.4.0 =
* Change: changed the way a repeater field is rendered out so it can be brought out at frontend for not logged in users.
* Change: changed filter names from "pprf_load_panda_repeater_allow" to "pprf_load_panda_repeater_allow_input", "pprf_load_panda_repeater_allow_msg" to "pprf_load_panda_repeater_allow_input_msg".
* Change: JavaScript 'live' to 'on'
* Add: allow chosen user roles to access the field.
* Fix: The field was opened and closed immediately on mobile.

= 1.4.1 =
* Change: Made JavaScript alerts translatable. 
* Add: Chinese language support.
* Fix: error when a Pods table did not exist.
* Fix: error on multisites.
* Fix: error when a repeater field is a custom post type, although using a custom post type as a repeater field is not part of the plan.

= 1.4.2 =
* Add: added a filter pandarf_pods_field_fields.

= 1.4.3 =
* Add: locked down the font size in the form so it won't be affected by the theme
* Change: changed $pods_obj->id to $pods_obj->id() in pandarf_pods_field_fn() to fix the problem in rest api

= 1.4.4 =
* Fix: TypeError: Backbone.Marionette is undefined
* Fix: $(...).live is not a function error using jquery

= 1.4.5 =
* Add: Added some code to add the relationship fields if they are missing. Useful for migrating the repeater fields or create them by code.
* Fix: the $ not a function problem when clicking the Load button.
* Change: Resize the window after running all JavaScripts.

= 1.4.6 =
* Fix: Fixed the problem that the trash, save and edit buttons had to be clicked twice on mobiles to work.
* Add: some styling for the load more div.
* Add: Integration with Simpods Area Field.
* Add: Remove all tags from label outputs for security purpose and limit the label characters to 80. If a label contains images, videos, audio, shortcodes display relevant icons.

= 1.4.7 =
* Fix: Fixed the problem that the database class was not included in the pandarf_items_fn function.

= 1.4.8 =
* add: Catch up with Simpods 3.0.0 on area field
* add: Minified CSS and JavaScript files.
* add: add a filter load_pprf_scripts_frontend to toggle loading PPRF scripts and styles at the front end.
* add: added more frontend css.
* fix: some JavaScript to catch up with WordPress 5.6 update

= 1.4.9 =
* change: use index.php for pandarepeaterfield.php.
* change: changed click() to trigger('click').
* change: Reassign can also work for parent table with meta storage type.
* change: caught up with the change of wp_localize_script in WordPress 5.7
* add: Pods 2.8 use parent intead of pod_id. This verson catches up with this change.
* fix: A bug in the order query.

= 1.4.10 =
* add: When displaying labels with admin columns, if it is a simple relationship, display labels instead of values.
* fix: A bug when using PHP 8