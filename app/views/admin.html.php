<?php
$time = 0; // store for session only
if (! isset($_COOKIE['logged_in_cust_id'] ) ) {
  setcookie('message',encryptCookie("You were not logged in, please login"),$time,'/');
  setcookie('forward_to_admin',encryptCookie(" "),$time,'/');
  header( "admin2.php" ) ;
}
include_once("app/app.php");

?>
<style type="text/css">
  #currentList { display: none; }
</style>
<?php
  if (!(($customparams['userid'] == "") || ($customparams['password'] == "") || ($customparams['profile'] == ""))) {
  ?>
<div class="readingListLink">
  <strong><a href="stats.php">Statistics</a></strong> | <strong><a href="exportsql.php" target="_blank">Download Export File</a></strong> | <strong><a href="importsql.php" target="_blank">Import from Export File</a></strong>
</div>
<?php
  }
  ?>
<div class="readingListLink">
  <h3>Curriculum Builder Settings</h3>
  <div class="warning">Be careful editing these settings, as they will override the settings originally set up in your LMS's LTI configuration screen.  Please launch at least one reading list from your LMS to populate these fields, or contact eds@ebscohost.com for assistance with these settings.<br /><br />If you change the <strong>user id</strong>, <strong>password</strong> or <strong>API profile ID</strong>, you will need to log out and log back in to see accurate statistics and lists.</div>

    <script type="text/javascript">
    function validateForm()
    {
    var a=document.forms["customparamform"]["userid"].value;
    var b=document.forms["customparamform"]["password"].value;
    var c=document.forms["customparamform"]["profile"].value;
    var d=document.forms["customparamform"]["liblogo"].value;
    var e=document.forms["customparamform"]["css"].value;
    
    if (a==null || a=="",b==null || b=="",c==null || c=="")
      {
	alert("Please fill in all required fields.");
	return false;
      } else {
      }
    
    
    if (!(d==null || d=="")) {
      if (d.substring(0,4) != "http") {
	alert("URLs must begin with http or https.");
	return false;
      }
    }
    
    if (!(e==null || e=="")) {
      if (e.substring(0,4) != "http") {
	alert("URLs must begin with http or https.");
	return false;
      }
    }
    
    return true;
    }
    </script>
  <form action="admin2.php" name="customparamform" method="POST">
  <table>
    <tr>
      <td>Your EBSCO User ID</td>
      <td><input type="text" placeholder="Required" name="userid" value="<?php echo $customparams['userid']; ?>" /></td>
    </tr>
    <tr>
      <td>Your EBSCO Password</td>
      <td><input type="text" placeholder="Required" name="password" value="<?php echo $customparams['password']; ?>" /></td>
    </tr>
<tr>
      <td>Your EDS API Profile</td>
      <td><input type="text" placeholder="Required" name="profile" value="<?php echo $customparams['profile']; ?>" /></td>
    </tr>
<tr>
      <td>LMS Roles that can edit lists (comma-separated)</td>
      <td><input type="text" placeholder="e.g., Instructor" name="empowered_roles" value="<?php echo $customparams['empowered_roles']; ?>" /></td>
    </tr>
<tr>
      <td>Proxy Prefix for CustomLinks</td>
      <td><input type="text" placeholder="Proxy prefix here; if using WAM, you can use {targetURLdomain} to specify the domain of your WAM proxy - the remainder will be placed at the end" name="proxyprefix" value="<?php echo $customparams['proxyprefix']; ?>" /></td>
    </tr>
<tr>
      <td>Proxy should encode target URL</td>
      <td><input type="radio" name="proxyencode" value="y" <?php if ($customparams['proxyencode'] == "y") { echo 'checked="checked"'; } ?>/> Yes | <input type="radio" name="proxyencode" value="n" <?php if ($customparams['proxyencode'] == "n") { echo 'checked="checked"'; } ?>/> No</td>    </tr>
<tr>
      <td>URL for your library logo</td>
      <td><input type="text" placeholder="http://url.to.your/logo.jpg" name="liblogo" value="<?php echo $customparams['liblogo']; ?>" /></td>
    </tr>
<tr>
      <td>Contact Email</td>
      <td><input type="text" placeholder="Email for contact person for tool" name="libemail" value="<?php echo $customparams['libemail']; ?>" /></td>
    </tr>
<tr>
      <td>Link to Library Home Page</td>
      <td><input type="text" placeholder="URL to Library Home Page" name="liblink" value="<?php echo $customparams['liblink']; ?>" /></td>
    </tr>
<tr>
      <td>Text to display at top of screen</td>
      <td><input type="text" placeholder="Branding text at top of screen" name="libname" value="<?php echo $customparams['libname']; ?>" /></td>
    </tr>
<tr>
      <td>Link to EDS Label</td>
      <td><input type="text" name="EDSlabel" placeholder="Label for the link that points back to EDS from a detailed record" value="<?php echo $customparams['EDSlabel']; ?>" /></td>
    </tr>
<tr>
      <td>Label for Search Box</td>
      <td><input type="text" name="searchlabel" placeholder="Label for the link above the search box" value="<?php echo $customparams['searchlabel']; ?>" /></td>
    </tr>
<tr>
      <td>Custom CSS File</td>
      <td><input type="text" name="css" placeholder="URL for CSS Stylesheet" value="<?php echo $customparams['css']; ?>" /></td>
    </tr>
<tr>
      <td>Collect student names as they access assigned readings?</td>
      <td><input type="radio" name="studentdata" value="y" <?php if ($customparams['studentdata'] == "y") { echo 'checked="checked"'; } ?>/> Yes | <input type="radio" name="studentdata" value="n" <?php if ($customparams['studentdata'] == "n") { echo 'checked="checked"'; } ?>/> No</td>
    </tr>
<tr>
      <td>Link to Help Pages</td>
      <td><input type="text" name="helppages" placeholder="URL for LibGuide or other help pages" value="<?php echo $customparams['helppages']; ?>" /></td>
</tr>
<tr>
      <td>Footer text</td>
      <td><input type="text" name="copyright" placeholder="e.g., a copyright notice" value="<?php if (isset($customparams['copyright'])) { echo $customparams['copyright']; } ?>" /></td>
    </tr>
<tr>
      <td>Copy Course Support</td>
      <td><input type="radio" name="copylist" value="y" <?php if ($customparams['copylist'] == "y") { echo 'checked="checked"'; } ?>/> Yes | <input type="radio" name="copylist" value="n" <?php if ($customparams['copylist'] == "n") { echo 'checked="checked"'; } ?>/> No</td>
    </tr>
<tr>
      <td>Remove Full Text and Available in Library Collection Limiters from view</td>
      <td><input type="radio" name="forceft" value="y" <?php if ($customparams['forceft'] == "y") { echo 'checked="checked"'; } ?>/> Yes | <input type="radio" name="forceft" value="n" <?php if ($customparams['forceft'] == "n") { echo 'checked="checked"'; } ?>/> No</td>
    </tr>
<tr>
      <td>Show 'Return to Course' Link at top of page</td>
      <td><input type="radio" name="courselink" value="y" <?php if ($customparams['courselink'] == "y") { echo 'checked="checked"'; } ?>/> Yes | <input type="radio" name="courselink" value="n" <?php if ($customparams['courselink'] == "n") { echo 'checked="checked"'; } ?>/> No</td>
    </tr>
<tr>
      <td>Open all Lists in a New Window</td>
      <td><input type="radio" name="newwindow" value="y" <?php if ($customparams['newwindow'] == "y") { echo 'checked="checked"'; } ?>/> Yes | <input type="radio" name="newwindow" value="n" <?php if ($customparams['newwindow'] == "n") { echo 'checked="checked"'; } ?>/> No</td>
    </tr>
<tr>
      <td>Show First Full Text Link Only</td>
      <td><input type="radio" name="firstftonly" value="y" <?php if ($customparams['firstftonly'] == "y") { echo 'checked="checked"'; } ?>/> Yes | <input type="radio" name="firstftonly" value="n" <?php if ($customparams['firstftonly'] == "n") { echo 'checked="checked"'; } ?>/> No</td>
</tr>
<tr>

  <td>   </td>
  <td><input type="submit" name="submit" value="Update Settings" /></td>
</tr>
  </table>
  </form>
</div>
<?php
    if ((!(($customparams['userid'] == "") || ($customparams['password'] == "") || ($customparams['profile'] == ""))) && (decryptCookie($_COOKIE['logged_in_cust_id']) != "none")){
?>
<div class="readingListLink">
<script type="text/javascript">
    function launchlist () {
        var selectedList = document.getElementById('selectList').value;
        window.open("preview_list.php?listid="+selectedList, "_blank", "toolbar=no, scrollbars=yes, resizable=yes, top=50, left=50, width=400, height=600");
    }
</script>
  <h3>View Lists</h3>
    <p>Select one: </p>
        <select name="copyid" id="selectList">
    <?php
    foreach ($consumeridsArray['logged_in_consumerid'] as $consumerid) {
          $querystring = 'SELECT id FROM credentialconsumers WHERE credentialid = ' . decryptCookie($_COOKIE['logged_in_cust_id']) . ' AND consumerid = "' . $consumerid . '";';
          $credconsumresults = mysqli_query($c,$querystring);
          $credconsumrow = mysqli_fetch_array($credconsumresults);
	  $credconsumer = $credconsumrow['id'];
	  
	  $sql = $c->prepare("SELECT id, course, linklabel, private, last_access, linkid FROM lists WHERE credentialconsumerid = ? ORDER BY course;");
      
	  $sql->bind_param('i', $credconsumer); // set parameter so it only pulls lists from the user's institution
	  $sql->execute();
	  $sql->store_result();
	  $sql->bind_result($mylists_id, $mylists_course, $mylists_linklabel, $mylists_private, $mylists_last_access, $mylists_linkid); 
	  
	  if ($sql->num_rows > 0) { //check to see if there are any results
	  while($sql->fetch()) {

	      if (strlen($mylists_linklabel) <= 0) {  //if no lable add one
		  $mylists_linklabel = 'Untitled List';
	      }
	      if (strlen($mylists_linklabel) >= 100) {  // if label is too long, truncate it
		  $mylists_linklabel = substr($mylists_linklabel,0,99) . "...";
	      }
	      echo '<option value="' . $mylists_id . '"';
	      if (isset($clean['listid'])) {    
		  if ($clean['listid'] == $mylists_id) {
		      echo ' selected="selected"';
		  }
	      }
	      echo '>'.$mylists_course . ': ' . $mylists_linklabel;
	      
	      if ($mylists_private == 1) {
		  echo ' (private)';
	      }
	      echo '</option>';
	  }
	  
      } else {
	  echo '<p>You have no other reading lists.</p>';
      }
    }
    ?>
        </select> <button onclick="launchlist(); return false;" id="previewbutton">Preview Selected List</button>
</div>
<div class="readingListLink">
  <h3>Delete Lists</h3>
<?php

	//create and execute the query.
    echo '<form id="mylist" action="delete_list.php" method="get"><select id="mylists" name="listid[]" multiple="multiple" size="15">';
    foreach ($consumeridsArray['logged_in_consumerid'] as $consumerid) {
          $querystring = 'SELECT id FROM credentialconsumers WHERE credentialid = ' . decryptCookie($_COOKIE['logged_in_cust_id']) . ' AND consumerid = "' . $consumerid . '";';
          $credconsumresults = mysqli_query($c,$querystring);
          $credconsumrow = mysqli_fetch_array($credconsumresults);
	  $credconsumer = $credconsumrow['id'];
	  
	  $sql = $c->prepare("SELECT id, course, linklabel, private, last_access, linkid FROM lists WHERE credentialconsumerid = ? ORDER BY last_access;");
      
	  $sql->bind_param('i', $credconsumer); // set parameter so it only pulls lists from the user's institution
	  $sql->execute();
	  $sql->store_result();
	  $sql->bind_result($mylists_id, $mylists_course, $mylists_linklabel, $mylists_private, $mylists_last_access, $mylists_linkid); 
	  
	  if ($sql->num_rows > 0) { //check to see if there are any results
	  while($sql->fetch()) {

	      if (strlen($mylists_linklabel) <= 0) {  //if no lable add one
		  $mylists_linklabel = 'Untitled List';
	      }
	      if (strlen($mylists_linklabel) >= 100) {  // if label is too long, truncate it
		  $mylists_linklabel = substr($mylists_linklabel,0,99) . "...";
	      }
	      echo '<option value="' . $mylists_id . '"';
	      if (isset($clean['listid'])) {    
		  if ($clean['listid'] == $mylists_id) {
		      echo ' selected="selected"';
		  }
	      }
	      echo '>Id #' .$mylists_linkid . ': ' .$mylists_course . ' - ' . $mylists_linklabel;
	      if ($mylists_last_access == '') {
		echo ' (NEVER accessed)';
	      } else {
		echo ' (LAST ACCESSED '. date("F j, Y",strtotime($mylists_last_access)) . ')';
	      }
	      
	      if ($mylists_private == 1) {
		  echo ' (private)';
	      }
	      echo '</option>';
	  }
	  
      } else {
	  echo '<p>You have no other reading lists.</p>';
      }
    }
    echo '</select> <br /> <input type="submit" value="Delete Selected Lists" name="submit" /></form>';

?></div><?php
    }
    mysqli_close($c);
?>