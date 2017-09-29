<?php
// session_save_path("session");
session_start();
include "app/app.php";
$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));

include "rest/EBSCOAPI.php";

$clean = strip_tags_deep($_REQUEST);

$api =  new EBSCOAPI($c,$customparams);
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

$addLimiterActions=array();
$removeLimiterActions=array();

/*
 * Check which expander check boxes are checked, which are not checked
 * if is checked add the action to addExpanderActions
 * if is not checked, add remove action to removeExpanderActions when the expander is found in applied expanders
 * or do nothing when not found in applied expanders.
 */
$i = 1;
foreach($Info['limiters'] as $limiter){
    if(isset($clean[$limiter['Id']])){
        $addLimiterActions['action['.$i.']'] = str_replace('value', 'y',$limiter['Action']);
        $i++;
    }else{
        foreach($results['appliedLimiters'] as $filter){
            if($filter['Id']==$limiter['Id']){
                $removeLimiterActions['action['.$i.']'] = str_replace('value', 'y',$filter['removeAction']);
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
$params = array_merge($params,$addLimiterActions);
$params = array_merge($params,$removeLimiterActions);
$url = 'results.php?'.http_build_query($params).'&'.$queryStringUrl;

header("location: {$url}");    
?>
