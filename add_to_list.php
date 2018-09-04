<?php
include('app/app.php');
if (!(isset($_COOKIE['currentListId']))) {
    echo "<div class='readingListLink'>Please open a reading list via your course website before using this feature.</div>";
    die();
}

$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));

include('rest/EBSCOAPI.php');

$api = new EBSCOAPI($c,$customparams);

$clean = strip_tags_deep($_REQUEST);

if ((!(isset($clean['db']))) || (!(isset($clean['db'])))) {
    echo "<div class='readingListLink'>Error: couldn't add this to the reading list.</div>";
    die();
} else {
    $db = $clean['db'];
    $an = $clean['an'];
}
$highlight = "";
$result = $api->apiRetrieve($an, $db, $highlight);

if (isset($result['error'])) {
    $error = $result['error'];
    echo "<div class='readingListLink'>Error: ".$result['error']."</div>";
    die();
} else {
    $error = null;
}

$variables = array(
    'result' => $result,   
    'error'  => $error,
    'id'     => 'record',
    'c'      => $c,
    'customparams' => $customparams,
    'an'    => $an,
    'db'    => $db,
    'currentListId' => decryptCookie($_COOKIE['currentListId'])
);
	
ebsco_render('add_to_list.html', 'layout.html', $variables);
?>
