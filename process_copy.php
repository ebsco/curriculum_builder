<?php
  include_once("app/app.php");
  $count = 0;
  $notes = false;
  $order = false;

  $clean = strip_tags_deep($_GET);

  foreach ($clean as $key => $val) {
    switch ($key) {
        case "include_notes":
            $notes = true;
            break;
        case "preserve_order":
            $order = true;
            break;
        default:
            $readingsToAdd[$count] = $key;
            $count++;
            break;
    }
  }
  if (count($readingsToAdd) > 0) {
    $sql = "SELECT * FROM readings WHERE id IN (";
    foreach($readingsToAdd as $readingId) {
        $sql .= mysqli_real_escape_string($c,$readingId) . ",";
    }
    $sql = substr($sql,0,strlen($sql)-1);
    $sql .= ");";
    $results1 = mysqli_query($c,$sql);
    while ($row = mysqli_fetch_array($results1)) {
        $sql = "SELECT id FROM readings WHERE listid = " . mysqli_real_escape_string($c,decryptCookie($_COOKIE['currentListId'])) . " AND url = \"" . $row['url'] . "\" AND an = \"" . $row['an'] . "\" AND db = \"" . $row['db'] . "\";";
        $matches = mysqli_query($c,$sql);
        if (mysqli_num_rows($matches) <= 0) {
            if ($notes && $order) {
                $sql = 'INSERT INTO readings (listid, authorid, an, db, url, title, instruct, type, priority, notes) VALUES ('.mysqli_real_escape_string($c,decryptCookie($_COOKIE['currentListId'])).','.mysqli_real_escape_string($c,decryptCookie($_COOKIE['currentAuthorId'])).',"'.$row['an'].'","'.$row['db'].'","'.$row['url'].'","'.htmlentities($row['title']).'","'.htmlentities($row['instruct']).'",'.$row['type'].','.$row['priority'].',"'.htmlentities($row['notes']).'");';
            } else if ($notes && (!($order))) {
                $sql = 'INSERT INTO readings (listid, authorid, an, db, url, title, instruct, type, priority, notes) VALUES ('.mysqli_real_escape_string($c,decryptCookie($_COOKIE['currentListId'])).','.mysqli_real_escape_string($c,decryptCookie($_COOKIE['currentAuthorId'])).',"'.$row['an'].'","'.$row['db'].'","'.$row['url'].'","'.htmlentities($row['title']).'","'.htmlentities($row['instruct']).'",'.$row['type'].',1,"'.htmlentities($row['notes']).'");';
            } else if ($order && (!($notes))) {
                $sql = 'INSERT INTO readings (listid, authorid, an, db, url, title, instruct, type, priority, notes) VALUES ('.mysqli_real_escape_string($c,decryptCookie($_COOKIE['currentListId'])).','.mysqli_real_escape_string($c,decryptCookie($_COOKIE['currentAuthorId'])).',"'.$row['an'].'","'.$row['db'].'","'.$row['url'].'","'.htmlentities($row['title']).'","'.htmlentities($row['instruct']).'",'.$row['type'].','.$row['priority'].',"");';
            } else {
                $sql = 'INSERT INTO readings (listid, authorid, an, db, url, title, instruct, type, priority, notes) VALUES ('.mysqli_real_escape_string($c,decryptCookie($_COOKIE['currentListId'])).','.mysqli_real_escape_string($c,decryptCookie($_COOKIE['currentAuthorId'])).',"'.$row['an'].'","'.$row['db'].'","'.$row['url'].'","'.htmlentities($row['title']).'","'.htmlentities($row['instruct']).'",'.$row['type'].',1,"");';
            }
            mysqli_query($c,$sql);
        }
    }
  }
?>
<h2>Processing...</h2>
<meta http-equiv="refresh" content="0;url=reading_list.php" />
