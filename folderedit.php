<?php
  header('Content-Type: text/html; charset=UTF-8');

  if (!(isset($_COOKIE['currentListId']))) {
      echo "<div class='readingListLink'>Please open a reading list via your course website before using this feature.</div>";
      die();
  }

  include_once("app/app.php");

  if (!isInstructor()) {
    die("Only course instructors may perform this action.");
  }

  $clean = strip_tags_deep($_GET);
  
  if (isset($clean['action']) && isset($clean['folderid'])) {
    if (($clean['action'] == 'editname') && (isset($clean['value']))) {
      setFolderName($c,$clean['folderid'],$clean['value']);
    }
    if (($clean['action'] == 'setorder') && (isset($clean['value']))) {
      setFolderOrder($c,$clean['folderid'],$clean['value']);
    }
  }
  
?>