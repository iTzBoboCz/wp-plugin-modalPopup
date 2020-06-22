<?php

function popups_box_add() {
  add_meta_box(
    'popups-meta-box',
    __('Popups link'),
    'popups_box_render',
    'page',
    'side',
    'default'
  );
}

// obsah boxu, který se přidává na editovací stránku
function popups_box_render() {
  global $wpdb;

  $sqlQuery = "SELECT option_value FROM wp_options WHERE option_name = 'popupPageID'";
  $sqlProvedeni = $wpdb->prepare($sqlQuery, $_GET["post"]);
  $sqlResult = $wpdb->get_row($sqlProvedeni)->option_value;

  echo("
  <div id='popups_meta_box'>
    <p class='bold'>Use this page for landing after clicking on a popup?</p>
    <label><input type='radio' value='yes' name='popups_page_checkbox' ".(($_GET["post"] == $sqlResult) ? "checked" : "").">Yes</label><br>
    <label><input type='radio' value='no' name='popups_page_checkbox' ".((empty($sqlResult) OR $_GET["post"] != $sqlResult) ? "checked" : "").">No</label><br>
    <p class='bold warning'>* 'Yes' will replace current popup landing page</p>
  <div>
  ");
}

// funkce, která se provádí při uložení/aktualizování stránky - kontrola, ukládání do DB
function popups_box_save($post_ID) {
  global $wpdb;

  $checkbox = $_POST["popups_page_checkbox"];

  $sqlQuery = "SELECT option_value FROM wp_options WHERE option_name = 'popupPageID'";
  $sqlProvedeni = $wpdb->prepare($sqlQuery);
  $sqlResult = $wpdb->get_row($sqlProvedeni)->option_value;

  if (!empty($checkbox) AND $checkbox != "no") {

    if ($post_ID != $sqlResult) {
      $data = [
        'option_name' => "popupPageID",
        'option_value' => $post_ID
      ];

      // ukládání
      if (empty($sqlResult)) {
        $wpdb->insert("wp_options", $data);

      } else {
        $wpdb->update("wp_options", $data, ["option_name" => "popupPageID"]);
      }
    }
  } elseif (($post_ID == $sqlResult) AND $checkbox == "no") {
    $sql = "DELETE FROM wp_options WHERE `option_name` = 'popupPageID'";
    $wpdb->query($wpdb->prepare($sql));
  }

}
