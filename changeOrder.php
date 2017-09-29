<?php
    session_start();
    include "app/app.php";
    $clean = strip_tags_deep($_POST);
    if (isset($clean['reading_id']) && isset($clean['priority'])) {
        $reading_id = (integer)$clean['reading_id'];
        $priority = (integer)$clean['priority'];
        setReadingPriority($c,$reading_id,$priority);        
    }
?>