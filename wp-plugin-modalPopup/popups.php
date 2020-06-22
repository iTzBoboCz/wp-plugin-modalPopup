<?php
/**
 * Plugin Name: Popups
 * Plugin URI: http://ondrejp.cz
 * Description: Vyskakovací okna
 * Version: 0.1a
 * Author: Ondřej Pešek
 * Author URI: http://ondrejp.cz
 * License: GPLv2
*/

require_once("functions.php");
require_once("actions.php");
require_once("page_metabox.php");

add_action("add_meta_boxes", "popups_box_add", 10, 1);
add_action("save_post", "popups_box_save");

register_activation_hook(__FILE__, 'popupCreateDB');

add_action("admin_menu", "popupsPage");

// add_action("wp_head", "addPopupModalCss");
add_action("wp_head", "addPopupModalJS");

if ($_GET["page"] == "slug_popups" OR $_GET["page"] == "slug_popups_create" OR
($_GET["action"] == "edit" AND basename($_SERVER["PHP_SELF"], ".php") == "post") OR
($_GET["post_type"] == "page" AND basename($_SERVER["PHP_SELF"], ".php") == "post-new")) {
  add_action("admin_enqueue_scripts", "addPopupMenuCss");
  // add_action("admin_enqueue_scripts", "addPopupMenuJS");
}

add_action("admin_post_popupCreateAction", "popupCreateAction");
add_action("admin_post_popupUpdateAction", "popupUpdateAction");
add_action("admin_post_popupDeleteAction", "popupDeleteAction");
add_action("admin_post_popupSaveAction", "popupSaveAction");

// register_activation_hook(__FILE__, 'redirectPageEnable');
// register_deactivation_hook(__FILE__, 'redirectPageDisable');
