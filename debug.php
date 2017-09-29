<?php
  session_start();
  header("Content-Type: application/xml");

  function strip_tags_deep($value)
  {
    return is_array($value) ? array_map('strip_tags_deep', $value) : htmlentities(strip_tags($value),ENT_QUOTES);
  }

  $clean = strip_tags_deep($_REQUEST);

  if(isset($clean['result'])){
    echo $_SESSION['resultxml'];
  }else if(isset($clean['record'])){
    echo $_SESSION['recordxml'];
  }
?>
