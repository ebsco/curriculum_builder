<?php

include("app/app.php");

if (isset($_COOKIE['logged_in_cust_id'])) {
    $credentialconsumerid = decryptCookie($_COOKIE['logged_in_cust_id']);
} else {
    die("You are unauthorized to perform this action.");
}

$rows = export_readings($c,$credentialconsumerid);

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
fputcsv($output, array('Course', 'List Title', 'AN', 'DB Code', 'Reading Title'));

// loop over the rows, outputting them
while ($row = mysqli_fetch_assoc($rows)) fputcsv($output, $row);
?>