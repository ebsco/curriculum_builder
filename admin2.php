<?php
    ini_set("session.cookie_httponly", 1);
    session_start(); 
    $time = 0; // store for session only
    
	include('app/app.php');

	$variables = array(
		'c' => $c
	);
	
	if (isset($_GET['autologin'])) {
	    $variables['autologin'] = $_GET['autologin'];    
	}
	
if (isset( $_GET['logout'] ) && $_GET['logout'] == "YES") {

	if (isset($_SERVER['HTTP_COOKIE'])) {
	    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
	    foreach($cookies as $cookie) {
		$parts = explode('=', $cookie);
		$name = trim($parts[0]);
		setcookie($name, '', time()-1000,'/',$_SERVER['SERVER_NAME'],FALSE,TRUE);
		setcookie($name, '', time()-1000);
	    }
	}

	header("Location:admin.php");
} else {
	if (isset($_COOKIE['forward_to_admin']) && decryptCookie($_COOKIE['forward_to_admin']) == "y") {
		$key = decryptCookie($_COOKIE['admin_key']);
		setcookie('oauth_consumer_key',encryptCookie($key),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);

		if ((isset($_POST['submit'])) && ($_POST['submit'] == "Update Settings")) {
		    $clean = strip_tags_deep($_POST);

		    $sql = "UPDATE oauth SET userid = ?,
		                             password = ?,
		                             profile = ?,
		                             libemail = ?,
		                             liblogo = ?,
		                             libname = ?,
		                             liblink = ?,
					     studentdata = ?,
					     EDSlabel = ?,
					     copyright = ?,
					     copylist = ?,
					     css = ?,
					     forceft = ?,
					     courselink = ?, 
					     newwindow = ?,
					     firstftonly = ?,
					     helppages = ?,
						 proxyprefix = ?,
						 proxyencode = ?,
						 searchlabel = ?,
						 empowered_roles = ?
					     WHERE oauth_consumer_key = ?";

		    $stmt = $c->prepare($sql);

		    $stmt->bind_param('ssssssssssssssssssssss',$clean['userid'],$clean['password'],$clean['profile'],$clean['libemail'],$clean['liblogo'],$clean['libname'],$clean['liblink'],$clean['studentdata'],$clean['EDSlabel'],$clean['copyright'],$clean['copylist'],$clean['css'],$clean['forceft'],$clean['courselink'],$clean['newwindow'],$clean['firstftonly'],$clean['helppages'],$clean['proxyprefix'],$clean['proxyencode'],$clean['searchlabel'],$clean['empowered_roles'],$key);

		    $stmt->execute();			    
    		}
		
		//locate the consumer id from the 'credentialconsumers' table and assign it to a session variable.
		$sql = $c->prepare("SELECT consumerid FROM credentialconsumers WHERE credentialid = ?");
		$custid = decryptCookie($_COOKIE['logged_in_cust_id']);
		$sql->bind_param('s', $custid);
		$sql->execute();
		$sql->store_result();
		$sql->bind_result($result);
		$count = 0;
		while ($sql->fetch()){
		        $count++;
			$consumeridsArray['logged_in_consumerid'][$count] = $result;
		}
		if (!(isset($consumeridsArray))) {
		    $consumeridsArray = array();
		}
		setcookie('consumeridsArray',encryptCookie($consumeridsArray),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
		if ($c->more_results()) {
		    $c->next_result();
		}
		
		$customparams = loadCustomParams($c,$key);
		
		$variables['consumeridsArray'] = $consumeridsArray;
		$variables['customparams'] = $customparams;
		
		ebsco_render('admin.html', 'layout.html', $variables);
	} else if (isset($_COOKIE['forward_to_admin']) && decryptCookie($_COOKIE['forward_to_admin']) == "n") {

		if(isset($_POST['admin_key'])){
			$clean = strip_tags_deep($_POST);
			setcookie('admin_key',encryptCookie($clean['admin_key']),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
			setcookie('admin_secret',encryptCookie($clean['admin_secret']),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
		        $variables['admin_key'] = $clean['admin_key'];
			$variables['admin_secret'] = $clean['admin_secret'];
		}
		ebsco_render('sign_on.html', 'layout.html', $variables);
	} else {
		ebsco_render('sign_on.html', 'layout.html', $variables);
	}
}

?>