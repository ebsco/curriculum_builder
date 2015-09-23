<?php
// session_save_path("session");
session_start();
include('app/app.php');

$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));

$clean = strip_tags_deep($_REQUEST);

if (isset($_SESSION['results'])) {
  $results = $_SESSION['results'];
  if (isset($results['queryString'])) {
   $queryStringUrl = $results['queryString'];
}
} else {
  $queryStringUrl = urldecode(urldecode($clean['backpath']));
}
$sort = isset($clean['sort'])? array('action'=>$clean['sort']):array();
$resultsperpage = isset($clean['resultsperpage'])? array('action'=>$clean['resultsperpage']):array();
$pagenumber = isset($clean['pagenumber'])? array('action'=>$clean['pagenumber']):array();
$view = isset($clean['view'])? array('view'=>$clean['view']):array();

$searchTerm = $clean['query'];
$fieldCode = $clean['fieldcode'];
$params = array(
    'query'=>$searchTerm,
    'fieldcode'=>$fieldCode,
    'option'=>'y'
);
$params = array_merge($params,$sort);
$params = array_merge($params,$resultsperpage);
$params = array_merge($params,$pagenumber);
$params = array_merge($params,$view);
$params = http_build_query($params);
$url = "results.php?".$queryStringUrl.'&'.$params.'&backpath='.$clean['backpath'];

header("location: {$url}");    
?>
