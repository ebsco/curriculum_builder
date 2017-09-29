<style type="text/css">
  #currentList { display: none; }
</style>
<?php
$time = 0; // store for session only
if (! isset($_COOKIE['logged_in_cust_id'] ) ) {
  setcookie('message',encryptCookie("You must be logged in to access that service, please login"),$time,'/');
  setcookie('forward_to_admin',encryptCookie(" "),$time,'/');
  header( "admin.php2" ) ;
}
  $clean = strip_tags_deep($_REQUEST);
	
  /* if (isInstructor()) { */
?><div class="readingListLink"><h3 style="color:red;">Warning: you are about to delete the following lists permanently.  This action cannot be undone.  Any instructor that tries to access these lists from their course will find an empty list.</h3><?php

  if (isset($clean['listid'])) {
    ?>
    <form action="process_delete.php" method="get">
    <?php
    $id_list = join(",",$clean['listid']); //create a comma delimited list of all the ids to be deleted
    $count = 0;
    foreach ($clean['listid'] as $id_list) {
    
	//form and execute the mysqli 
	$sql = $c->prepare("SELECT id, course, linklabel, private, last_access FROM lists WHERE id = ?;");
	$sql->bind_param('i',$id_list);
	$sql->execute();
	$sql->bind_result($lists_id, $lists_course, $lists_linklabel, $lists_private, $lists_last_access);
	
    //$results = mysqli_query($c,$sql);
	
	//create a hidden input in our form for each of the items to be deleted
    while ($sql->fetch()) {
            $count++;               
      echo '<input type="hidden" name="listid[]" value="' . $lists_id . '" />';
      
      if (strlen($lists_linklabel) <= 0) {
          $lists_linklabel = 'Untitled List';
      }
      if (strlen($lists_linklabel) >= 100) {
          $lists_linklabel = substr($lists_linklabel,0,99) . "...";
      }
      
      echo "<p>" . $lists_course . ' - ' . $lists_linklabel . " <strong>";
      
      if ($lists_last_access == '') {
        echo ' (NEVER accessed since update)';
      } else {
        echo ' (LAST ACCESSED '. date("F j, Y",strtotime($lists_last_access)) . ')';
      }
      echo "</strong></p>";
    }
    }    
?>    <p><input type="submit" name="confirm" value="DELETE <?php if ($count <= 1) { echo "this list"; } else { echo "these " . $count . " lists"; } ?>" /> or <a href="admin2.php">Cancel</a></p></form> <?php
  } else {
    echo "No lists selected to delete.  <a href=\"admin2.php\">Back to Administration Screen</a>.";
  }
    mysqli_close($c);
?>
</div>
<?php
  /* } else {
    echo "<p>You are unauthorized to access this page.</p>";
  } */
?>
