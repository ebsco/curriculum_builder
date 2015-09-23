<?php
include('app/app.php');
$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));

include('rest/EBSCOAPI.php');

$api = new EBSCOAPI($c,$customparams);

$clean = strip_tags_deep($_REQUEST);

$db = $clean['db'];
$an = $clean['an'];

if (isset($clean['backpath'])) {
    $backpath = $clean['backpath'];
} else {
    $backpath = '';
}

if (isset($clean['highlight'])) {
$highlight = $clean['highlight'];
$highlight = str_replace(array(" ","&","-"), array(",",",",","), $highlight);
} else {
    $highlight = "";
}

$result = $api->apiRetrieve($an, $db, $highlight);

$debug = isset($clean['debug'])? $clean['debug']:'';

// Set error
if (isset($result['error'])) {
    $error = $result['error'];
} else {
    $error = null;
}

//save debug into session
if($debug == 'y'||$debug == 'n'){
    $_SESSION['debug'] = $debug;
}
// Variables used in view
$variables = array(
    'result' => $result,   
    'error'  => $error,
    'id'     => 'record',
    'debug'  => isset($_SESSION['debug'])? $_SESSION['debug']:'',
    'backpath' => $backpath,
    'c'      => $c,
    'clean'  => $clean,
    'customparams' => $customparams
);

ebsco_render('record.html', 'layout.html', $variables);

?>