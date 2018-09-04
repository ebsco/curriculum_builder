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
<h2><?php echo _("Login");?></h2>
    		
<?php

include_once("app/app.php");

$time = 0; // store for session only
if (!isset($_COOKIE['forward_to_admin'])){
		setcookie('forward_to_admin',encryptCookie("n"),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
}

if ((isset($admin_key) && isset($admin_secret))) {

	$sqlstmt = "SELECT userid, password, profile FROM oauth WHERE oauth_consumer_key = '".$admin_key."' AND secret = '".$admin_secret."' ;";
        $result = mysqli_query($c,$sqlstmt);

        while ($row = mysqli_fetch_array($result)) {
	
		if (($row['userid'] != '') || ($row['password'] != '') || ($row['profile'] != '')) {
				$custom_id = $row['userid'];
				$cust_password = $row['password'];
				$cust_consumer_id = $row['profile'];
				
				$user_query = "SELECT id FROM credentials WHERE userid = ? AND password = ? AND profile = ? ;";
				 
				$stmt = $c->prepare($user_query);
				$stmt->bind_param('sss', $custom_id, $cust_password, $cust_consumer_id);
				$stmt->execute();
				$stmt->store_result();
				$stmt->bind_result($cust_id);

				if ($stmt->num_rows > 0) {
				// Note that if the cust_id/password/profile combo is not found this will
				// not run and noone will be logged in.
				while ($stmt->fetch()) {
					setcookie('logged_in_cust_id',encryptCookie($cust_id),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
					setcookie('forward_to_admin',encryptCookie("y"),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
					$c->next_result();
					mysqli_close($c);
					header("Location:admin2.php");
				}
				} else {
					setcookie('logged_in_cust_id',encryptCookie("none"),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
					setcookie('forward_to_admin',encryptCookie("y"),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
					$c->next_result();
					mysqli_close($c);
					header("Location:admin2.php");				
				}
		}
		if (!(isset($cust_id))) {
				setcookie('logged_in_cust_id',encryptCookie("none"),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
		}
		setcookie('forward_to_admin',encryptCookie("y"),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
		$c->next_result();
		mysqli_close($c);		
		header("Location:admin2.php");
	}


	if (! isset($_COOKIE['logged_in_cust_id'] ) ) {
		$message = "Login failed";
	}
}
?>

<strong>
<?php 
//display message, if one has been generated
if (isset($message)) {
	echo $message;
}
?>
</strong>

<form id="admin_login" action="admin2.php" method="post">
    <table>

<?php
  if (isset($autologin)) {
    $loginvars = explode(".....",$autologin);
  }
?>

        <tr><td><label style="font-size: 80%"><b><?php echo _("Key:");?></b> </label></td><td><input type="text" name="admin_key" value="<?php if (isset($loginvars[0])) { echo $loginvars[0]; } ?>" /></td></tr>
		<tr><td><label style="font-size: 80%"><b><?php echo _("Secret:");?></b> </label></td><td><input type="password" name="admin_secret" value="<?php if (isset($loginvars[1])) { echo $loginvars[1]; } ?>" /></td></tr>
        <tr><td></td><td><input type="submit" value="<?php echo _('Login');?>" /></td></tr>

	</table>
</form>
<?php
  if (isset($loginvars[0]) && (isset($loginvars[1]))) {
?>

<script type="text/javascript">
  document.getElementById("admin_login").submit();
</script>
<?php
  }
?>

