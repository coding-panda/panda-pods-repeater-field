=== Panda Pods Repeater Field ===
Contributors: Coding Panda
Donate link: http://www.multimediapanda.co.uk/product/panda-pods-repeater-field/
Tags: pods, repeater field, storage
Requires at least: 3.8
Tested up to: 4.8.1
Stable tag: 1.1.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Repeater fields for Pods Framework. Adding a repeatable field on pages.

== Description ==

If you are using Pods Framework for your post types and data storage, you may want a repeater field. Panda Pods Repeater Field offers you a solution. It takes the advantage of Pods table storage, so you don’t need to worry that the posts and postmeta data table may expand dramatically and slow down the page loading. This plugin is compatible with Pods Framework 2.6.1 or later. To download Pods Framework, please visit http://pods.io/. After each update, please clear the cache to make sure the CSS and JS are updated. Usually, Ctrl + F5 will do the trick.

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
10. Now you can see a Pods Table "comiccontent" we just created in the combo box. Select it, add or update the field and save Pod. You can also set field width, defaulted to 100%. You can also limit the number of entries of the repeater field.
11. If you are doing nested repeater fields, I recommend you set it to 100%. A sample of nested repeater fields.
12. OK, the set up is done. Now if you are adding a new Comic, 
13. you will see "Comic Box 1" in the More Fields area.
14. You can add as many new items to Comic Box 1 as you want, and they are only attached to the current Comic you are editing .
15. You can click top bar to contract it, the bottom bar to expand the window.
16. To find the parent_pod_id and parent_pod_field_id, go to Pods and click the main Pod (Comics in this tutorial).
17. Here, 2215 is parent_pod_id and 2222 is parent_pod_field_id
18. To find the parent_pod_post_id, open the main post and look at the URL. If you are using a Pods Adavnce Custom Type or a Custom Post Type as the main table, it is the "id" variable in the URL. If you add a repeater field to Settings, the parent post id is the same as parent_pod_id. If you add a repeater field to Users, the parent post id is the same as the user ID. 


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