<?php
   /*
    * Use this page to determine if LTI basic parameters are begin passed to your system appropriately.
    *
    * */
   
    echo "<p>Received from LTI Consumer: </p>";
    foreach ( $_POST as $foo=>$bar ) {
        echo "['" . $foo . "'] = " . $bar . "<br />";
    }
?>
<p>To use this tool, create a new LTI Tool in your Learning Management System. When configuring the tool, select the privacy options that pass both a user's name and email address to the tool, and set the launch URL to the URL for this page.</p>