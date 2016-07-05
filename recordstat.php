<?php
    session_start();
    include "app/app.php";
    $clean = strip_tags_deep($_POST);
    if (isset($clean['reading_id'])) {
        $reading_id = (integer)$clean['reading_id'];
        if (isset($_COOKIE['lis_person_name_full'])) {
            $fullname = decryptCookie($_COOKIE['lis_person_name_full']);            
        } else {
            $fullname = "";
        }

        if (isset($_COOKIE['lis_person_contact_email_primary'])) {
            decryptCookie($_COOKIE['lis_person_contact_email_primary']);
        } else {
            $emailaddress = "";
        }

        recordStudentReading($c,decryptCookie($_COOKIE['user_id']),$fullname,$emailaddress,$reading_id);        
    }
?>