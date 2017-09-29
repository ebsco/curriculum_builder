<?php
include('app/app.php');
$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));
require_once 'rest/EBSCOAPI.php';      

$clean = strip_tags_deep($_REQUEST);
        
        $an = $clean['an'];
        $db = $clean['db'];
        
        $api = new EBSCOAPI($c,$customparams);
        $record = $api->apiRetrieve($an, $db);
        //Call Retrieve Method to get the PDF Link from the record
        
        if(empty($record['pdflink'])){
             echo "<p>Oops!  There has been an error.  Please click back in your browser and try the link again.  If you continue to see this error, please contact your library.  Error: PDF-1.</p>";
        }else{          
            header("location: {$record['pdflink']}");   
        }
        
        
?>
