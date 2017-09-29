<?php
  header('Content-Type: text/html; charset=UTF-8');
  require_once("connect.php");
  function strip_tags_deep($value)
  {
  return is_array($value) ? array_map('strip_tags_deep', $value) : htmlentities(strip_tags($value),ENT_QUOTES,"UTF-8");
  }
  
$clean = strip_tags_deep($_GET);
  foreach ($clean as $key => $val) {
    if (substr_count($key,"priority")) {
      $id = substr($key,8);
      $priority = $val;
      $sql = "UPDATE readings SET priority = " . mysqli_real_escape_string($c,$priority) . " WHERE id = " . mysqli_real_escape_string($c,$id) . ";";
      //echo $sql;
      mysqli_query($c,$sql);
    } else if (substr_count($key,"notes")) {
      $id = substr($key,5);
      $notes = htmlspecialchars($val);
      $sql = "UPDATE readings SET notes = \"" . mysqli_real_escape_string($c,$notes) . "\" WHERE id = " . mysqli_real_escape_string($c,$id) . ";";
      //echo $sql;
      mysqli_query($c,$sql);
    }
  }
  mysqli_close($c);
?>
<h2>Processing...</h2>
<meta http-equiv="REFRESH" content="0;url=reading_list.php" />