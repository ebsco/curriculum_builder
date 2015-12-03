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

  if (isset($clean['action']) && ($clean['action'] == 'newfolder')) {
    if (decryptCookie($_COOKIE['currentListId']) == $clean['listid']) {
      add_new_folder($c,decryptCookie($_COOKIE['currentListId']),$clean['label']);
    } else {
      die("Error adding new folder");
    }
  } else if (isset($clean['action']) && ($clean['action'] == 'deletefolder')) {
    if (decryptCookie($_COOKIE['currentListId']) == $clean['listid']) {
      delete_folder($c,$clean['folderid']);
    }
  } else {
    if (!is_numeric($clean['folderid']) || !is_numeric($clean['readingid'])) {
      die("Error adding item to folder.");
    }
    
    if ($clean['folderid'] == "0") {
      $clean['folderid'] = null;
    }
    
    if (check_reading_in_list($c,$clean['readingid'],decryptCookie($_COOKIE['currentListId']))) {
      add_to_folder($c,$clean['readingid'],$clean['folderid']);    
    }
  }
  
?>
<h2>Processing...</h2>
<meta http-equiv="refresh" content="0;url=reading_list.php<?php
if (isset($clean['redirectid'])) {
  echo "?folderid=".$redirectid;
}
?>" />
