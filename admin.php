<?php
	if (isset($_SERVER['HTTP_COOKIE'])) {
	    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
	    foreach($cookies as $cookie) {
		$parts = explode('=', $cookie);
		$name = trim($parts[0]);
		setcookie($name, '', time()-1000);
		setcookie($name, '', time()-1000, '/', $_SERVER['SERVER_NAME'],FALSE,TRUE);
	    }
	}
?>
<meta http-equiv="REFRESH" content="0;url=admin2.php<?php if (isset($_GET['autologin'])) { echo "?autologin=" . $_GET['autologin']; } ?>" />