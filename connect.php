<?php
	$c = mysqli_connect('HOSTNAME', 'USERNAME', 'PASSWORD');
	if (!$c) {
		die('Could not connect: ' . mysqli_error($c));
	}
	$mysqldb = mysqli_select_db($c, 'DATABASENAME');
?>
