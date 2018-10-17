<?php
include("app/app.php");
if (isset($_COOKIE['logged_in_cust_id'])) {
    $credentialconsumerid = decryptCookie($_COOKIE['logged_in_cust_id']);
} else {
    die("You are unauthorized to perform this action.");
}
$rows = student_export_all_sql($c,$credentialconsumerid);
if ($rows) {
    
} else {
    die("No readings to export.");
}
// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=studentdata.csv');
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