<?php
  include_once("app/app.php");
  $count = 0;
  $notes = false;
  $order = false;

  $clean = strip_tags_deep($_REQUEST);
  
  foreach ($clean['listid'] as $ids) {
  
  //Delete the list from the 'lists' table
  $sql = $c->prepare("DELETE FROM lists WHERE id = ?;");
  $sql->bind_param('i', $ids);
  $sql->execute();
  //echo "<p>" . $sql . "</p>";
  //mysqli_query($c,$sql);
  
  //delete the records tying the deleted lists to the authors that created them
  unset($sql);
  $sql = $c->prepare("DELETE FROM authorlists WHERE listid = ?;");
  $sql->bind_param('i',$ids);
  $sql->execute();
  //echo "<p>" . $sql . "</p>";
  //mysqli_query($c,$sql);

  //delete the readings the list had contained from the 'readings' table
  unset($sql);
  $sql = $c->prepare("DELETE FROM readings WHERE listid = ?;");
  $sql->bind_param('i',$ids);
  $sql->execute();
  //echo "<p>" . $sql . "</p>";
  //mysqli_query($c,$sql);

  }
  
  mysqli_close($c);
?>
<h2>Processing...</h2>
<meta http-equiv="refresh" content="0;url=admin2.php" />
