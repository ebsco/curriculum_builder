<?php
    session_start();
    include "app/app.php";
    $clean = strip_tags_deep($_POST);
    if (isset($clean['reading_id'])) {
        $reading_id = (integer)$clean['reading_id'];
        recordStudentReading($c,decryptCookie($_COOKIE['lis_person_name_full']),decryptCookie($_COOKIE['lis_person_contact_email_primary']),$reading_id);        
    }
?>