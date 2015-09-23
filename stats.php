<?php

if (!(isset($_COOKIE['logged_in_cust_id']))) {
    die("You are unauthorized to view this page.  <a href='admin2.php'>Please login</a>.");
}
include('app/app.php');

$variables = array(
		'c' => $c
	);
ebsco_render('stats.html', 'layout.html', $variables);

?>
