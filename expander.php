<?php
// session_save_path("session");
session_start();
include "app/app.php";
$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));
include "rest/EBSCOAPI.php";

$clean = strip_tags_deep($_REQUEST);

$api = new EBSCOAPI($c,$customparams);
$Info = $api->getInfo();

if (isset($_SESSION['results'])) {
  $results = $_SESSION['results'];
  if (isset($results['queryString'])) {
   $queryStringUrl = $results['queryString'];
}
} else {
    $queryStringUrl = urldecode(urldecode($clean['backpath']));
    $results = $api->apiSearch($queryStringUrl);    
    $_SESSION['results'] = $results;
  if (isset($results['queryString'])) {
   $backpath = $results['queryString'];
}
}

$addExpanderActions = array();
$removeExpanderAction = array();
/*
 * Check which expander check boxes are checked, which are not checked
 * if is checked add the action to addExpanderActions
 * if is not checked, add remove action to removeExpanderActions when the expander is found in applied expanders
 * or do nothing when not found in applied expanders.
 */
$i=1;
foreach($Info['expanders'] as $expander){
    if(isset($clean[$expander['Id']])){
        $addExpanderActions['action['.$i.']'] = $expander['Action'];
        $i++;
    }else{
        foreach($results['appliedExpanders'] as $filter){
            if($filter['Id']==$expander['Id']){
                $removeExpanderAction['action['.$i.']'] = $filter['removeAction'];
                $i++;
            }
        }
    }
}

$searchTerm = $clean['query'];
$fieldCode = $clean['fieldcode'];
$params = array(
    'refine'=>'y',
    'query' => $searchTerm,
    'fieldcode'=>$fieldCode,
);
$params = array_merge($params,$addExpanderActions);
$params = array_merge($params,$removeExpanderAction);
$url = 'results.php?'.http_build_query($params).'&'.$queryStringUrl;

header("location: {$url}");    
?>
