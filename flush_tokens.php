<h2>Tokens flushed.</h2>
<?php
include("connect.php");

// check for v1.1 update
$sql = "DELETE FROM authtokens;";
$results = mysqli_query($c,$sql);
?>