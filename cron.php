<?php include("connect.php");
$sql = "SELECT * FROM oauth WHERE expires < CURDATE() - INTERVAL 90 DAY;";
$rows = mysqli_query($c,$sql);
if ($rows) {
	while ($row = mysqli_fetch_array($rows)) {
		echo "On ".date("M j, Y").", deleted: " . $row['oauth_consumer_key'] . " - Expired: " . date("M j, Y",strtotime($row['expires']));
		echo "<br />";
	}
}
$sql = "DELETE FROM oauth WHERE expires < CURDATE() - INTERVAL 90 DAY;";
mysqli_query($c,$sql);
?>