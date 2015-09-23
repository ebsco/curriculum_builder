<?php
include_once('app/app.php');
$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));
$variables = array(
        'c' => $c,
        'customparams' => $customparams
);
ebsco_render('basic_search.html', 'layout.html',$variables);
?>