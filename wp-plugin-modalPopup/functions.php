<?php

session_start();

// přidání odkazu na stránku do menu
function popupsPage() {
  $title = "Popups";
  $user = "administrator";
  $slug = "slug_popups";

  add_menu_page($title, $title, $user, $slug, "popups_page_content", "dashicons-align-center");
  add_submenu_page($slug, "Popups", "Dashborard", $user, $slug);
  add_submenu_page($slug, "Popups", "Create new", $user, "slug_popups_create", "popups_create_page");
}

function popups_page_content() {
  global $wpdb;

  $actionURL = admin_url('admin-post.php');

  $sqlQuery = "SELECT * FROM wp_popups";
  $sql = $wpdb->prepare($sqlQuery);
  $popups = $wpdb->get_results($sql);

  $sqlQuery = "SELECT option_value FROM wp_options WHERE option_name = 'popupPageID'";
  $sqlProvedeni = $wpdb->prepare($sqlQuery, $_GET["post"]);
  $popupPageID = $wpdb->get_row($sqlProvedeni)->option_value;

  echo("<div id='popups_dashboard'>");
  if (empty($popups)) {
    echo("You need to create some popups first.");
  } else {
    echo("<p class='center_info'>".(!empty($popupPageID) ? "Popup redirects to <a href='".get_edit_post_link($popupPageID)."'>this</a> page." : "Landing page for popups is <span class='bold warning'>not</span> set yet. You can do so by visiting <a href='".admin_url("edit.php?post_type=page")."'>this link</a> and editing desired page's settings.")."</p>");
    echo("<table>");
    echo("
      <thead>
        <tr>
          <td colspan='7'>Popup list</td>
        </tr>
        <tr>
          <th>popup ID</th>
          <th>Title</th>
          <th>Description</th>
          <th>Location</th>
          <th>Edit</th>
          <th>Primary popup</th>
          <th>Delete</th>
        </tr>
      </thead>
    ");
    echo("<tbody>");

    $primaryPopup = getActivePopup();

    foreach ($popups as $key => $value) {
      $value = (array) $value;
      $location = unserialize($value["options"])["location"];
      $content = wp_trim_words($value["content"], 10);
      echo("
        <tr>
          <td>
            {$value['ID']}
          </td>
          <td>
            {$value['title']}
          </td>
          <td>
            {$content}
          </td>
          <td>
            {$location}
          </td>
          <td>
            <a href='".admin_url("admin.php?page=slug_popups_create&popupID=").$value["ID"]."'><span class='dashicons dashicons-edit'></span></a>
          </td>
          <td>
            <input type='radio' value='{$value["ID"]}' form='popupsSave_form' name='primary' ".(($primaryPopup->ID == $value["ID"]) ? "checked" : "").">
          </td>
          <td>
            <form action='{$actionURL}' method='POST'>
              <input type='hidden' name='popupID' value='{$value["ID"]}'>
              <input type='hidden' name='action' value='popupDeleteAction'>
              <input type='submit' value='Delete'>
            </form>
          </td>
        </tr>
      ");
    }
    echo("
        <tr>
          <td colspan=7>
            <form id='popupsSave_form' action='{$actionURL}' method='POST'>
              <input type='hidden' name='action' value='popupSaveAction'>
              <input type='submit' value='Save settings'>
            </form>
          </td>
        </tr>
      </tbody>
    </table>");
  }
  echo("</div>");
}

function popups_create_page() {
  global $wpdb;
  $actionURL = admin_url('admin-post.php');

  if (isset($_GET["popupID"]) AND !empty($_GET["popupID"]) AND is_numeric($_GET["popupID"])) {
    $sqlQuery = "SELECT * FROM wp_popups WHERE ID = %s";
    $sqlProvedeni = $wpdb->prepare($sqlQuery, $_GET["popupID"]);
    $sqlResult = $wpdb->get_row($sqlProvedeni);
    $sqlResult = (array) $sqlResult;
  }

  if (isset($sqlResult) AND !empty($sqlResult)) {
    $title = "Edit popup (ID ".$_GET['popupID'].")";
    $popupSubmit = "update";
    $popupTitle = $sqlResult["title"];
    $popupDescription = $sqlResult["content"];
    $popupLocation = unserialize($sqlResult["options"])["location"];
    $popupAction = "popupUpdateAction";
  }

  if (!isset($_GET["popupID"]) OR empty($_GET["popupID"]) OR !is_numeric($_GET["popupID"]) OR !isset($sqlResult) OR empty($sqlResult)) {
    $title = "Create popup";
    $popupSubmit = "save";

    $popupTitle = isset($_SESSION["popupData"]["popupTitle"]) ? $_SESSION["popupData"]["popupTitle"] : "";
    $popupDescription = isset($_SESSION["popupData"]["popupDescription"]) ? $_SESSION["popupData"]["popupDescription"] : "";
    $popupError = isset($_SESSION["popupData"]["error"]) ? $_SESSION["popupData"]["error"] : "";
    $popupLocation = $_SESSION["popupData"]["popupLocation"];
    $popupAction = "popupCreateAction";
  }


  echo("
  <div id='popups_settings'>
    <form id='popups_form' action='{$actionURL}' method='POST'>
      <table>
        <thead>
          <tr>
            <td colspan=2>{$title}</td>
          </tr>
          ".(isset($_SESSION["popupData"]["error"]) ? "<tr><td colspan=2 class='popup_menu_error'>".$_SESSION["popupData"]["error"]."</td></tr>" : "")."
        </thead>
        <tbody>
          <tr>
            <td>
              <label for='popup_form_title'>Title</label>
            </td>
            <td>
              <input id='popup_form_title' type='text' name='popupTitle' placeholder='COVID-19 information' required value='{$popupTitle}'>
            </td>
          </tr>
          <tr>
            <td>
              <label for='popup_form_text'>Description</label>
            </td>
            <td>
              <textarea id='popup_form_text' name='popupDescription' placeholder='We want to share some info with you about ..'>{$popupDescription}</textarea>
            </td>
          </tr>
          <tr>
            <td>Location on this site</td>
            <td>
              <label>
                <input type='radio' name='popupLocation'
                ".((isset($popupLocation) AND $popupLocation == "top") ? " checked " : "")."
                value='top'>Top
              </label>
              <label>
                <input type='radio' name='popupLocation'
                ".(((isset($popupLocation) AND $popupLocation == "middle") OR ($popupLocation != "top" AND $popupLocation != "bottom")) ? " checked " : "").
                "value='middle'>Middle
              </label>
              <label>
                <input type='radio' name='popupLocation'
                ".((isset($popupLocation) AND $popupLocation == "bottom") ? " checked " : "")."
                value='bottom'>Bottom
              </label>
            </td>
          </tr>
          <tr>
            <td colspan=2>
              <input type='hidden' name='action' value='{$popupAction}'>
              <input type='submit' value='{$popupSubmit} popup'>
              ".(($popupSubmit == "update") ? "<input type='hidden' name='popupID' value='{$_GET["popupID"]}'>" : "")."
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
  ");

  session_destroy();
}

function addPopupModalJS() {
  global $wpdb;

  $sqlQuery = "SELECT option_value FROM wp_options WHERE option_name = 'popupPageID'";
  $sqlProvedeni = $wpdb->prepare($sqlQuery, $_GET["post"]);
  $pageID = $wpdb->get_row($sqlProvedeni)->option_value;

  $link = get_page_link($pageID);

  $primaryPopup = getActivePopup();

  echo("
  <script src='".plugins_url()."/popups/"."popups.js'></script>
  <script>
    window.onload = function(){
      if (getCookie('popup') != 'disabled') {
        showPopup('".$primaryPopup->title."', '".wp_trim_words($primaryPopup->content, 100)."', '".(unserialize($primaryPopup->options)["location"])."', '".$link."');
      }
    };
  </script>
  ");
}

function addPopupMenuCss() {
  wp_enqueue_style("popupsMenu_style", plugins_url()."/popups/"."menus.css", false);
}

// function addPopupMenuJS() {
//   wp_enqueue_script("popupsMenu_js", plugins_url()."/popups/"."menus.js", false);
// }

function popupCreateDB() {
  global $wpdb;

  $charset_collate = $wpdb->get_charset_collate();
  $tableName = $wpdb->prefix.'popups';

  $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `options` LONGTEXT
  ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

function getActivePopup() {
  global $wpdb;

  $sqlQuery = "
  SELECT * FROM wp_popups WHERE wp_popups.ID
  IN (SELECT option_value FROM wp_options WHERE option_name = 'primaryPopupID')
  ";
  $sql = $wpdb->prepare($sqlQuery);
  $sqlResult = $wpdb->get_row($sql);

  if (empty($sqlResult)) {
    // pokud by ve wp_options zůstalo ID popupu, který již neexistuje
    $sqlQuery = "DELETE FROM wp_options WHERE option_name = 'primaryPopupID')";
    $sql = $wpdb->prepare($sqlQuery);
    $sqlResult = $wpdb->get_results($sql);

    return(false);
  }

  return($sqlResult);
}

?>
