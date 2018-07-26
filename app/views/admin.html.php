<?php
	// save current language to cookie by default
	if (!isset($_SESSION["language"])){
		if(!isset($customparams)){
			$customparams['language']= "en_US.UTF-8";
	}		
	$_SESSION["language"]=$customparams['language'];
	}
	// check if parameters are passed from url, language change 
	if (isset($_REQUEST["language"])) {
		$_SESSION["language"]=urldecode($_REQUEST["language"]);
	}
	$language = $_SESSION["language"];
	if (stripos($language,"utf-8")===false) {
	   $language.=".UTF-8";
	}
	putenv("LC_ALL=$language");
	setlocale(LC_ALL, $language);
	if (defined('LC_MESSAGES')) // available if PHP was compiled with libintl
	{
	   setlocale(LC_MESSAGES, $language);
	}   
	else
	{       
	   setlocale(LC_ALL, $language);
	}       
			
	bindtextdomain("messages", dirname(__FILE__)."/locale");                                
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain("messages");

?>
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
  td {
	  border-bottom: thin solid #eeeeee;
	  padding: 3px;
  }
</style>
<?php
  if (!(($customparams['userid'] == "") || ($customparams['password'] == "") || ($customparams['profile'] == ""))) {
  ?>
<div class="readingListLink">
  <strong><a href="stats.php"><?php echo _("Statistics");?></a></strong> | <strong><a href="exportsql.php" target="_blank"><?php echo _("Download Export File");?></a></strong><!-- | <strong><a href="importsql.php" target="_blank" title="Used when migrating to and from different Curriculum Builder instalations"><?php echo _("Import from Export File");?></a></strong><?php if ($customparams['studentdata'] != "n") { ?>--> | <strong><a href="exportsql-studentdata.php" target="_blank"><?php echo _("Download Student Data");?></a></strong><?php } ?>
</div>
<?php
  }
  ?>
<div class="readingListLink">
  <h3><?php echo _("Curriculum Builder Settings");?></h3>
  <div class="warning"><?php echo _("Be careful editing these settings, as they will override the settings originally set up in your LMS's LTI configuration screen.  Please launch at least one reading list from your LMS to populate these fields, or contact eds@ebscohost.com for assistance with these settings.");?><br /><br /><?php echo _("If you change the ")."<strong>"._("user id")."</strong>, <strong>"._("password")."</strong>"." "._("or")." <strong>"._("API profile ID")."</strong>, "._("you will need to log out and log back in to see accurate statistics and lists.")."</div>";?>

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
	function toggleHelp(idToToggle) {
	  $('#'+idToToggle).slideToggle();
	}
    </script>
  <form action="admin2.php" name="customparamform" method="POST">
  <table style="width: 100%">
    <tr>
      <td style="width: 300px;"><?php echo _("EBSCO User ID")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('useridhelp');" /><div class="adminhelptext" id="useridhelp"><?php echo _("Select")." ";?><a href="web/uidexample.png" target="_blank"><?php echo _("a User ID & Password combo from EBSCOadmin's Authentication section.")." ";?></a><?php echo _("The username/password combo must be associated with the group containing the profile specified below.");?></div></td>
      <td><input type="text" style="width:100%" placeholder="<?php echo _('Required');?>" name="userid" value="<?php echo $customparams['userid']; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo _("EBSCO Password")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('passwordhelp');" /><div class="adminhelptext" id="passwordhelp"><?php echo _("Select")." ";?><a href="web/uidexample.png" target="_blank"><?php echo _("a User ID & Password combo from EBSCOadmin's Authentication section.")." ";?></a><?php echo _("The username/password combo must be associated with the group containing the profile specified below.");?></div></td>
      <td><input type="text" style="width:100%" placeholder="<?php echo _('Required');?>" name="password" value="<?php echo $customparams['password']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("EDS API Profile ID")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('profilehelp');" /><div class="adminhelptext" id="profilehelp"><?php echo _("This the the EDS API profile you want to use in Curriculum Builder. It needs to be in the group that the User ID and Password you entered above has access to.");?></div></td>
      <td><input type="text" style="width:100%" placeholder="<?php echo _('Required');?>" name="profile" value="<?php echo $customparams['profile']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("LMS Roles that can edit lists (comma-separated)")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('empowered_roleshelp');" /><div class="adminhelptext" id="empowered_roleshelp"><?php echo _("By default, only Instructors and Teaching Assistants can edit lists. However, you can add roles here to enable other types of users to edit lists.")."<br /><br /><strong>"._("Default:")."</strong>"." "._("Instructor,TeachingAssistant");?></div></td>
      <td><input type="text" style="width:100%" placeholder="<?php echo _('e.g., Instructor');?>" name="empowered_roles" value="<?php echo $customparams['empowered_roles']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Proxy Prefix for CustomLinks")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('proxyhelp');" /><div class="adminhelptext" id="proxyhelp"><?php echo _("If filled in, this proxy will be applied to all CustomLinks that appear in Curriculum Builder.");?><br /><br ><strong><?php echo _("Examples");?></strong><br />http://myproxy.myuni.edu/login?url=<br />http://0-{targetURLdomain}.myproxy.myuni.eds</div></td>
      <td><input type="text" style="width:100%" placeholder="<?php echo _('Proxy prefix here; if using WAM, you can use {targetURLdomain} to specify the domain of your WAM proxy - the remainder will be placed at the end');?>" name="proxyprefix" value="<?php echo $customparams['proxyprefix']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Proxy should encode target URL")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('proxyencodehelp');" /><div class="adminhelptext" id="proxyencodehelp"><?php echo _("If your proxy prefix ends with");?> <strong>?qurl=</strong>, <?php echo _("this probably needs to be set to")." ";?><strong><?php echo _("Yes");?></strong>; <?php echo _("otherwise");?> <strong><?php echo _("No");?></strong> <?php echo _("is probably correct.");?></div></td>
      <td><input type="radio" name="proxyencode" value="y" <?php if ($customparams['proxyencode'] == "y") { echo 'checked="checked"'; } ?>/><?php echo _("Yes")." ";?> | <input type="radio" name="proxyencode" value="n" <?php if ($customparams['proxyencode'] == "n") { echo 'checked="checked"'; } ?>/><?php echo " "._("No");?></td>    </tr>
<tr>
      <td><?php echo _("URL for your library logo")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('logohelp');" /><div class="adminhelptext" id="logohelp"><?php echo _("If your LMS accesses Curriculum Builder via HTTPS, your logo should also be available via HTTPS.");?></div></td>
      <td><input type="text" style="width:100%" placeholder="http://url.to.your/logo.jpg" name="liblogo" value="<?php echo $customparams['liblogo']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Contact Email");?></td>
      <td><input type="text" style="width:100%" placeholder="<?php echo _('Email for contact person for tool');?>" name="libemail" value="<?php echo $customparams['libemail']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Link to Library Home Page");?></td>
      <td><input type="text" style="width:100%" placeholder="URL to Library Home Page" name="liblink" value="<?php echo $customparams['liblink']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Text to display at top of screen")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('brandingtexthelp');" /><div class="adminhelptext" id="brandingtexthelp"><?php echo _("This text will appear underneath the title of the list in the header. URLs will automatically be hyperlinked.");?></div></td>
      <td><input type="text" style="width:100%" placeholder="<?php echo _('Branding text at top of screen');?>" name="libname" value="<?php echo $customparams['libname']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Label for Link to EDS")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('edslinkhelp');" /><div class="adminhelptext" id="edslinkhelp"><?php echo _("This is the label for the link that will appear on the left side of detailed records that directs the user to the EBSCO Discovery Service record for the reading. If left blank, no link to EDS will appear.");?></div></td>
      <td><input type="text" style="width:100%" name="EDSlabel" placeholder="<?php echo _('e.g., See Record in EDS');?>" value="<?php echo $customparams['EDSlabel']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Label for Search Box");?></td>
      <td><input type="text" style="width:100%" name="searchlabel" placeholder="<?php echo _('e.g., Search for Library Resources');?>" value="<?php echo $customparams['searchlabel']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Custom CSS File")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('csshelp');" /><div class="adminhelptext" id="csshelp"><strong><?php echo _("Optional.");?></strong><?php echo " "._("Enter the URL of a CSS file. Styles in this file will be applied to Curriculum Builder screens. Must be at an HTTPS address if your LMS is HTTPS only.");?></div></td>
      <td><input type="text" style="width:100%" name="css" placeholder="<?php echo _('Optional.  URL for CSS Stylesheet');?>" value="<?php echo $customparams['css']; ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Collect Student Data")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('studentdatahelp');" /><div class="adminhelptext" id="studentdatahelp"><strong><?php echo _("Detailed")." ";?></strong><?php echo _("will collect students names, email addresses, and IDs. These data will be used to inform course instructors when a student clicks on a reading. It will also populate these fields in the reports provided to the library. To disable email collection only, your LMS instructor can elect to NOT share this information with the tool provider.");?><br /><br /><strong><?php echo _("Anonymized")." ";?></strong><?php echo _("will not provide faculty with names of students who clicked on readings. However, libraries will be provided with a report showing when and which readings were accessed with only the LMS userID.");?><br /><br /><strong><?php echo _("None")." ";?></strong><?php echo _("will collect no student activity. No reporting will be available.");?></div></td>
      <td><input type="radio" name="studentdata" value="y" <?php if ($customparams['studentdata'] == "y") { echo 'checked="checked"'; } ?>/> <?php echo _("Detailed")." ";?>| <input type="radio" name="studentdata" value="a" <?php if ($customparams['studentdata'] == "a") { echo 'checked="checked"'; } ?>/><?php echo _("Anonymized")." ";?>| <input type="radio" name="studentdata" value="n" <?php if ($customparams['studentdata'] == "n") { echo 'checked="checked"'; } ?>/><?php echo _("None");?></td>
    </tr>
<tr>
      <td><?php echo _("Link to Help Pages");?></td>
      <td><input type="text" style="width:100%" name="helppages" placeholder="<?php echo _('URL for LibGuide or other help pages');?>" value="<?php echo $customparams['helppages']; ?>" /></td>
</tr>
<tr>
      <td><?php echo _("Footer text");?></td>
      <td><input type="text" style="width:100%" name="copyright" placeholder="<?php echo _('e.g., a copyright notice');?>" value="<?php if (isset($customparams['copyright'])) { echo $customparams['copyright']; } ?>" /></td>
    </tr>
<tr>
      <td><?php echo _("Copy Course Support")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('copycoursehelp');" /><div class="adminhelptext" id="copycoursehelp"><?php echo _("Selecting")." "."<strong>"._("Yes")."</strong>"." "._("for this option means instructors will be prompted at the launch of a new list to either create a new list from scratch, or select from a list of similarly named lists to use to prepopulate the new list. If")." ";?><strong><?php echo _("No");?></strong><?php echo " "._("is selected, all new lists will be blank.");?></div></td>
      <td><input type="radio" name="copylist" value="y" <?php if ($customparams['copylist'] == "y") { echo 'checked="checked"'; } ?>/><?php echo _("Yes")." ";?> | <input type="radio" name="copylist" value="n" <?php if ($customparams['copylist'] == "n") { echo 'checked="checked"'; } ?>/><?php echo " "._("No");?></td>
    </tr>
<tr>
      <td><?php echo _("Hide")." ";?><em><?php echo _("Full Text")." ";?></em><?php echo _("and")." ";?><em><?php echo _("Available in Library Collection")."</em>"." "._("Limiters")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('fthelp');" /><div class="adminhelptext" id="fthelp"><?php echo _("Hiding these limiters will prevent faculty from unchecking these, making it impossible to add items outside of your collections to the reading list.");?></div></td>
      <td><input type="radio" name="forceft" value="y" <?php if ($customparams['forceft'] == "y") { echo 'checked="checked"'; } ?>/><?php echo _("Yes")." ";?> | <input type="radio" name="forceft" value="n" <?php if ($customparams['forceft'] == "n") { echo 'checked="checked"'; } ?>/><?php echo " "._("No");?></td>
    </tr>
<tr>
      <td><?php echo _("Show 'Return to Course' Link at top of page")." ";?><img src="web/help.png" style="max-height:12px;" title="Click for help with this feature" onclick="toggleHelp('returnhelp');" /><div class="adminhelptext" id="returnhelp"><?php echo _("This link is provided by the LMS. If the LMS is configured correctly, it will redirect the user back to the course's site.");?></div></td>
      <td><input type="radio" name="courselink" value="y" <?php if ($customparams['courselink'] == "y") { echo 'checked="checked"'; } ?>/><?php echo _("Yes")." ";?> | <input type="radio" name="courselink" value="n" <?php if ($customparams['courselink'] == "n") { echo 'checked="checked"'; } ?>/><?php echo " "._("No");?></td>
    </tr>
<tr>
      <td><?php echo _("Open all Lists in a New Window");?></td>
      <td><input type="radio" name="newwindow" value="y" <?php if ($customparams['newwindow'] == "y") { echo 'checked="checked"'; } ?>/><?php echo _("Yes")." ";?> | <input type="radio" name="newwindow" value="n" <?php if ($customparams['newwindow'] == "n") { echo 'checked="checked"'; } ?>/><?php echo " "._("No");?></td>
    </tr>
<tr>
      <td><?php echo _("Show First Full Text Link Only");?></td>
      <td><input type="radio" name="firstftonly" value="y" <?php if ($customparams['firstftonly'] == "y") { echo 'checked="checked"'; } ?>/><?php echo _("Yes")." ";?> | <input type="radio" name="firstftonly" value="n" <?php if ($customparams['firstftonly'] == "n") { echo 'checked="checked"'; } ?>/><?php echo " "._("No");?></td>
</tr>
<tr>
      <td><?php echo _("Language");?></td>
      <td>
		<select name="language">
			<option value="es_CO.UTF-8" <?php if ($customparams['language'] == 'es_CO.UTF-8') echo 'selected' ?>><?php echo _("Spanish");?></option>
			<option value="en_US.UTF-8" <?php if ($customparams['language'] == 'en_US.UTF-8') echo 'selected' ?>><?php echo _("English");?></option>
			<option value="de_DE.UTF-8" <?php if ($customparams['language'] == 'de_DE.UTF-8') echo 'selected' ?>><?php echo _("German");?></option>
			<option value="it_IT.UTF-8" <?php if ($customparams['language'] == 'it_IT.UTF-8') echo 'selected' ?>><?php echo _("Italian");?></option>
			<option value="pt_PT.UTF-8" <?php if ($customparams['language'] == 'pt_PT.UTF-8') echo 'selected' ?>><?php echo _("Portuguese");?></option>
			<option value="fr_FR.UTF-8" <?php if ($customparams['language'] == 'fr_FR.UTF-8') echo 'selected' ?>><?php echo _("French");?></option>
			<option value="th_TH.UTF-8" <?php if ($customparams['language'] == 'th_TH.UTF-8') echo 'selected' ?>><?php echo _("Thai");?></option>
			<option value="zh_TW.UTF-8" <?php if ($customparams['language'] == 'zh_TW.UTF-8') echo 'selected' ?>><?php echo _("traditional Chinese");?></option>
			<option value="zh_CN.UTF-8" <?php if ($customparams['language'] == 'zh_CN.UTF-8') echo 'selected' ?>><?php echo _("Simplified Chinese");?></option>
			<option value="ja_JP.UTF-8" <?php if ($customparams['language'] == 'ja_JP.UTF-8') echo 'selected' ?>><?php echo _("Japanese");?></option>
			<option value="pl_PL.UTF-8" <?php if ($customparams['language'] == 'pl_PL.UTF-8') echo 'selected' ?>><?php echo _("Polish");?></option>
			<option value="sv_SE.UTF-8" <?php if ($customparams['language'] == 'sv_SE.UTF-8') echo 'selected' ?>><?php echo _("Swedish");?></option>
			<option value="he_IL.UTF-8" <?php if ($customparams['language'] == 'he_IL.UTF-8') echo 'selected' ?>><?php echo _("Hebrew");?></option>
            <option value="ko_KR.UTF-8" <?php if ($customparams['language'] == 'ko_KR.UTF-8') echo 'selected' ?>><?php echo _("Korean");?></option>
		</select>
	  </td>
</tr>
<tr>

  <td>   </td>
  <td><input type="submit" name="submit" value="<?php echo _('Update Settings');?>" /></td>
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
  <h3><?php echo _("View Lists");?></h3>
    <p><?php echo _("Select one:")." ";?></p>
        <select name="copyid" id="selectList">
    <?php
    foreach ($consumeridsArray['logged_in_consumerid'] as $consumerid) {
	  
	  $sql = $c->prepare("SELECT id, course, linklabel, private, last_access, linkid FROM lists WHERE consumerid = ? ORDER BY course;");
      
	  $sql->bind_param('s', $consumerid); // set parameter so it only pulls lists from the user's institution
	  $sql->execute();
	  $sql->store_result();
	  $sql->bind_result($mylists_id, $mylists_course, $mylists_linklabel, $mylists_private, $mylists_last_access, $mylists_linkid); 
	  
	  if ($sql->num_rows > 0) { //check to see if there are any results
          echo '<option disabled>=== '.$consumerid.' ('.$sql->num_rows.') ===</option>';
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
        </select> <button onclick="launchlist(); return false;" id="previewbutton"><?php echo _("Preview Selected List");?></button>
</div>
<div class="readingListLink">
  <h3><?php echo _("Delete Lists");?></h3>
<?php
       
    $queries = "";
	//create and execute the query.
    echo '<form id="mylist" action="delete_list.php" method="get"><select id="mylists" style="width:100%;" name="listid[]" multiple="multiple" size="15">';
    foreach ($consumeridsArray['logged_in_consumerid'] as $consumerid) {
	  

$sql = $c->prepare("SELECT id, course, linklabel, private, last_access, linkid FROM lists WHERE consumerid = ? ORDER BY last_access;");
$queries .= "SELECT id, course, linklabel, private, last_access, linkid FROM lists WHERE consumerid = \"".$consumerid."\" ORDER BY last_access;<br/>";
	  $sql->bind_param('s', $consumerid); // set parameter so it only pulls lists from the user's institution
	  $sql->execute();
	  $sql->store_result();
	  $sql->bind_result($mylists_id, $mylists_course, $mylists_linklabel, $mylists_private, $mylists_last_access, $mylists_linkid); 
	  
	  if ($sql->num_rows > 0) { //check to see if there are any results
          echo '<option disabled>=== '.$consumerid.' ('.$sql->num_rows.') ===</option>';
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
	      echo '>' .$mylists_course . ' - ' . $mylists_linklabel;
	      if ($mylists_last_access == '') {
		echo ' (NEVER accessed)';
	      } else {
		echo ' (LAST ACCESSED '. date("F j, Y",strtotime($mylists_last_access)) . ')';
	      }
	      
	      if ($mylists_private == 1) {
		  echo ' (private)';
	      }

              echo ' - Id #' .$mylists_linkid; 
	      echo '</option>';
	  }
	  
      } else {
	  echo '<p>You have no other reading lists.</p>';
      }
    }
    echo '</select> <br /> <input type="submit" value="'._("Delete Selected Lists").'" name="submit" /></form>';
    //echo $queries;
?></div><?php
    }
    mysqli_close($c);
?>
