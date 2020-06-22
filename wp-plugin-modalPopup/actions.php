<?php

function popupCreateAction() {
  global $wpdb;

  if (empty($_POST["popupTitle"]) OR empty($_POST["popupDescription"])) {
    $_SESSION["popupData"] = $_POST;
    $_SESSION["popupData"]["error"] = "Some culmns are empty!";
    wp_redirect(admin_url('admin.php?page=slug_popups_create'));
    exit;
  }

  // pokud nebylo umístění odesláno, nastaví se automaticky na střed
  if (!isset($_POST["popupLocation"])) {
    $_POST["popupLocation"] = "middle";
  }

  $popupOptions = [
    "location" => $_POST["popupLocation"]
  ];

  $popupOptions = serialize($popupOptions);

  $data = [
    'title' => $_POST["popupTitle"],
    'content' => $_POST["popupDescription"],
    'options' => $popupOptions
  ];

  // $sqlQuery = "INSERT INTO `wp_popups` (`title`, `content`, `options`) VALUES (:title, :description, :options)";
  $wpdb->insert("wp_popups", $data);

  // přesměrování na list s popupy
  wp_redirect(admin_url('admin.php?page=slug_popups'));
}

function popupUpdateAction() {
  global $wpdb;

  // pokud nebylo umístění odesláno, nastaví se automaticky na střed
  if (!isset($_POST["popupLocation"])) {
    $_POST["popupLocation"] = "middle";
  }

  $popupOptions = [
    "location" => $_POST["popupLocation"]
  ];

  $popupOptions = serialize($popupOptions);

  $data = [
    'title' => $_POST["popupTitle"],
    'content' => $_POST["popupDescription"],
    'options' => $popupOptions
  ];

  $sqlResult = $wpdb->update("wp_popups", $data, ["ID" => $_POST["popupID"]]);

  // přesměrování na list s popupy
  wp_redirect(admin_url('admin.php?page=slug_popups'));
}

function popupDeleteAction() {
  global $wpdb;

  $data = [
    'ID' => $_POST["popupID"]
  ];

  $sqlQuery = "SELECT option_value FROM wp_options WHERE option_name = 'primaryPopupID'";
  $sqlProvedeni = $wpdb->prepare($sqlQuery, $_GET["popupID"]);
  $sqlResult = $wpdb->get_row($sqlProvedeni)->option_value;

  // pokud je tento popup primární, vymaže se i tato volba z wp_options
  if ($sqlResult == $_POST["popupID"]) {
    $sql = "DELETE FROM wp_options WHERE `option_name` = 'primaryPopupID'";
    $wpdb->query($wpdb->prepare($sql));
  }

  $sql = "DELETE FROM wp_popups WHERE `ID` = {$_POST["popupID"]}";
  $wpdb->query($wpdb->prepare($sql, $data));

  // přesměrování na list s popupy
  wp_redirect(admin_url('admin.php?page=slug_popups'));
}

function popupSaveAction() {
  global $wpdb;

  if (!isset($_POST["primary"]) OR empty($_POST["primary"])) {
    wp_redirect(admin_url('admin.php?page=slug_popups'));
    exit;
  }

  $sqlQuery = "SELECT option_value FROM wp_options WHERE option_name = 'primaryPopupID'";
  $sqlProvedeni = $wpdb->prepare($sqlQuery, $_GET["popupID"]);
  $sqlResult = $wpdb->get_row($sqlProvedeni)->option_value;

  $data = [
    'option_name' => "primaryPopupID",
    'option_value' => $_POST["primary"]
  ];

  if (!isset($sqlResult)) {

    $wpdb->insert("wp_options", $data);
    wp_redirect(admin_url('admin.php?page=slug_popups'));
    exit;
  }

  if ($sqlResult == $_POST["primary"]) {
    wp_redirect(admin_url('admin.php?page=slug_popups'));
    exit;
  }

  $sqlResult = $wpdb->update("wp_options", $data, ["option_name" => "primaryPopupID"]);
  wp_redirect(admin_url('admin.php?page=slug_popups'));
}

?>
