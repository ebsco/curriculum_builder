<?php

include("app/app.php");

if (isset($_COOKIE['consumeridsArray'])) {
    $consumerids = decryptCookie($_COOKIE['consumeridsArray']);
} else {
    die("You are unauthorized to perform this action.");
}

$rows = export_all_sql($c,$consumerids);

if ($rows) {
    
} else {
    die("No readings to export.");
}

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
$fields = mysqli_fetch_fields($rows);
$names = array();
foreach ($fields as $val){
	$names[] = $val->name;
}
       
fputcsv($output,$names);
// loop over the rows, outputting them
while ($row = mysqli_fetch_assoc($rows)) fputcsv($output, $row);

?>