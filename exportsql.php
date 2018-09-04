<?php

include("app/app.php");

if (isset($_COOKIE['consumeridsArray'])) {
    $consumerids = decryptCookie($_COOKIE['consumeridsArray']);
} else {
    die("You are unauthorized to perform this action.");
}

$queryResults = export_all_sql($c,$consumerids);

if ($queryResults) {
    
} else {
    die("No readings to export.");
}

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
       
fputcsv($output,$queryResults["headers"]);
// loop over the rows, outputting them
while ($row = array_pop($queryResults["results"])) fputcsv($output, $row);

?>
