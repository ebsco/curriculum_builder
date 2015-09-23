<?php
session_start();

include('app/app.php');
include('rest/EBSCOAPI.php');
$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));

$clean = strip_tags_deep($_REQUEST);

if (isset($_SESSION['results'])) {
  $results = $_SESSION['results'];
  if (isset($results['queryString'])) {
   $queryStringUrl = $results['queryString'];
}
} else {
  $queryStringUrl = urldecode($clean['backpath']);
}

$api = new EBSCOAPI($c,$customparams);

$resultId = $clean['resultId'];
$query = $clean['query'];
$fieldCode = $clean['fieldcode'];
$start = isset($clean['pagenumber']) ? $clean['pagenumber'] : 1;
$limit = isset($clean['resultsperpage'])?$clean['resultsperpage']:20;

if($resultId>$start*$limit){
    $start = $start+1;
    $url = $queryStringUrl."&action=GoToPage($start)";
    $results = $api->apiSearch($url);
    $_SESSION['results'] = $results;
  if (isset($results['queryString'])) {
   $backpath = $results['queryString'];
}
   
} else if($resultId<(($start-1)*$limit)+1){
    $start = $start-1;
    $url = $queryStringUrl."&action=GoToPage($start)";
    $results = $api->apiSearch($url);   
    $_SESSION['results'] = $results;
  if (isset($results['queryString'])) {
   $backpath = $results['queryString'];
}
   
} else if(isset($_SESSION['results'])){
    
    $results = $_SESSION['results'];
    if (isset($results['queryString'])) {
   $backpath = $results['queryString'];
}
    
} else {
    $results = $api->apiSearch($queryStringUrl);    
    $_SESSION['results'] = $results;
  if (isset($results['queryString'])) {
   $backpath = $results['queryString'];
}

}

$recordCount = $results['recordCount'];

foreach($results['records'] as $record){
    if($record['ResultId']==$resultId){
        $db = $record['DbId'];
        $an = $record['An'];
        $rId = $record['ResultId'];
        $params = array(
            'db'=>$db,
            'an'=>$an,
            'highlight'=>$query,
            'resultId'=>$rId,
            'recordCount'=>$recordCount,
            'query'=>$query,
            'fieldcode'=>$fieldCode,
            'backpath'=>urlencode($backpath)
        );
        $params = http_build_query($params);
        header("location:record.php?".$params);
        break;
    }
}


?>

