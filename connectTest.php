<?php
session_start();
?>
<html>
<head>
	<title>Connection Test</title>
</head>
<body>
	
<?php include("connect.php");
echo "<p>Hostname: " . $_SERVER['HTTP_HOST'] . "</p>";
if ($c) {
	echo "Connected to MySQL DB successfully!<br /><br />";
}
$sql = "SELECT * FROM readings;";
$rows = mysqli_query($c,$sql);
if ($rows) {
	while ($row = mysqli_fetch_array($rows)) {
		echo "Id: " . $row['id'] . " - " . $row['db'] . "/" . $row['an'];
		echo "<br />";
	}
}

$sql = 'INSERT INTO readings (listid, authorid, an, db, url, title, priority, type) VALUES (1,1,"testing-an", "testing-db","none ","Circumstances Surrounding the Community Needle-Stick Injuries in Georgia.",1,1);';
$results = mysqli_query($c,$sql);

if (!($results)) {
	echo "MySQL Error inserting new record.";
	die();
}

$sql = 'SELECT * FROM readings WHERE an = "testing-an" AND db = "testing-db";';
$rows = mysqli_query($c,$sql);
if ($rows) {
	if (mysqli_num_rows($rows) >= 1) {
		$row = mysqli_fetch_array($rows);
		echo "<br />Insertion SUCCESSFUL (Id: " . $row['id'] . " - " . $row['db'] . "/" . $row['an'] . ")<br /><br />";
		echo "DELETING SAMPLE RECORD..";
		$sql = "DELETE FROM readings WHERE id = " . $row['id'] . ";";
		mysqli_query($c,$sql);
	} else {
		echo "Insertion UNSUCCESSFUL.  Full READINGS table: ";
		$sql = "SELECT * FROM readings;";
		$rows = mysqli_query($c,$sql);
		if ($rows) {
			while ($row = mysqli_fetch_array($rows)) {
				print_r($row);
				echo "<br />";
			}
		}
	}
} else {
	echo "<br /><br />MySQL returned an error.<br /><br />";
}

echo phpinfo();
?>
</body>
</html>