<?php
include('app/app.php');
$variables = array(
        'c' => $c,
        'username' => $username,
        'password' => $password
);
ebsco_render('manageaccess.html', 'layout.html',$variables);
?>
