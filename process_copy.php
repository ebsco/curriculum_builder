<?php
  include_once("app/app.php");
  $count = 0;
  $notes = false;
  $order = false;
  $folders = false;

  $clean = strip_tags_deep($_GET);

  foreach ($clean as $key => $val) {
    switch ($key) {
        case "include_notes":
            $notes = true;
            break;
        case "preserve_order":
            $order = true;
            break;
        case "include_folders":
            $folders = true;
            break;
        default:
            $readingsToAdd[$count] = $key;
            $count++;
            break;
    }
  }
  
  copyListWithOptions($c,decryptCookie($_COOKIE['currentListId']),$readingsToAdd,$notes,$order,$folders);
  
?>
<h2>Processing...</h2>
<meta http-equiv="refresh" content="0;url=reading_list.php" />

