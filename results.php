<?php
include('app/app.php');
$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));
include('rest/EBSCOAPI.php');

$api = new EBSCOAPI($c,$customparams);

$clean = strip_tags_deep($_REQUEST);
$getclean = strip_tags_deep($_GET);

// Build  the arguments for the Search API method
$searchTerm = $clean['query'];
$fieldCode = $clean['fieldcode']? $clean['fieldcode'] :'';

$start = isset($clean['pagenumber']) ? $clean['pagenumber'] : 1;
if (!(is_numeric($start))) {
    $start = 1;
}

$limit = isset($clean['resultsperpage'])?$clean['resultsperpage']:20;
if (!(is_numeric($limit))) {
    $limit = 20;
}

$sortBy = isset($clean['sort'])?$clean['sort']:'relevance';


$amount = isset($clean['view'])?$clean['view']:'detailed';
$mode = 'all';
$expander = isset($clean['expander'])? $clean['expander']:'';
$limiter = isset($clean['limiter'])? $clean['limiter']:'';
//$debug = isset($clean['debug'])? $clean['debug']:'';
$Info = $api->getInfo();

// If user come back from the detailed record 
// The same search will not call API again
if(isset($clean['back'] )&&isset($_SESSION['results'])){
    
    $results = $_SESSION['results'];
  if (isset($results['queryString'])) {
   $backpath = $results['queryString'];

}
    
} else if(isset($clean['back'])&&isset($clean['backpath'])){
    //$_SESSION['debug'] = "<p>PARAMS AFTER RESULTS.PHP: ".urldecode($clean['backpath'])."</p>";

    $results = $api->apiSearch(urldecode($clean['backpath']));

    $_SESSION['results'] = $results;
  if (isset($results['queryString'])) {
   $backpath = $results['queryString'];
} 

}else if(isset($clean['option'])){
// All page options will be handled here 
// New Search or refined search will call the API
    
    if (isset($_SESSION['results'])) {
        $results = $_SESSION['results'];  
        if (isset($results['queryString'])) {
   $queryStringUrl = $results['queryString'];
}
    } else {
        $queryStringUrl = urldecode(urldecode($clean['backpath']));
    }
    
    $action = isset($clean['action'])?$clean['action']:'';
    $actions = array();
    if(!empty($action)){        
       if(strstr($action, 'setsort(')){
           $sortBy = str_replace(array('setsort(',')'),array('',''), $action);
           $start = 1;
       } 
       if(strstr($action, 'setResultsperpage(')){
           $limit = str_replace(array('setResultsperpage(',')'),array('',''), $action);
       }
       if(strstr($action, 'GoToPage(')){
        $start = str_replace(array('GoToPage(',')'),array('',''), $action);
       }
       $actions['action'] = $action;
    }
    
    $view = isset($clean['view'])? array('view'=>$clean['view']):array();
    $params = array_merge($actions,$view);
    $url = $queryStringUrl.'&'.http_build_query($params);

    //$_SESSION['debug'] = "<p>PARAMS AFTER RESULTS.PHP: ".$url."</p>";

    $results = $api->apiSearch($url);    
    // Will save the result into the session with the new SessionToken as index    
    $_SESSION['results'] = $results;
  if (isset($results['queryString'])) {
   $backpath = $results['queryString'];
} 
}else if(isset($clean['refine'])||isset($_GET['login'])){
// New Search or refined search will call the API
    if(isset($clean['action'])){
    $actions = $clean['action'];
    }else{
    $actions = '';
    }
    $refineActions = array();
    if(is_array($actions)){
        for($i=0; $i<count($actions);$i++){
            $refineActions['action-'.($i+1)]= $actions[$i+1];
        }        
    }else{
        $refineActions['action'] = $actions;
    }
    
    if (isset($_SESSION['results'])) {
        $results = $_SESSION['results'];
        if (isset($results['queryString'])) {
   $queryStringUrl = $results['queryString'];
}
    } else {
        $queryStringUrl = urldecode($clean['backpath']);
    }
    $params = http_build_query($refineActions);
    
    $url = $queryStringUrl.'&'.$params;
    //$_SESSION['debug'] = "<p>PARAMS AFTER RESULTS.PHP: ".$url."</p>";
    $results = $api->apiSearch($url);

    $_SESSION['results'] = $results;
    if (isset($results['queryString'])) {
   $backpath = $results['queryString'];
}

    if(isset($clean['refine']))$start = 1;
         
}else{
       $query = array();

        // Basic search
        if(!empty($searchTerm)) {
            $term = urldecode($searchTerm);
            //$term = str_replace('"', '', $term); // Temporary
            $term = str_replace(',',"\,",$term);
            $term = str_replace(':', '\:', $term);
            
            // experiment with this - probably can eliminate for search term
            $term = str_replace('(', '\(', $term);
            $term = str_replace(')', '\)', $term);
            
            if($fieldCode!='keyword'){
            $query_str = implode(":", array($fieldCode, $term));
            }else{
            $query_str = $term;
            }
            $query["query"] = $query_str;

        // No search term, return an empty array
        } else {
            $results = array();            
        }
           
        // Add the HTTP query params
        
        $params = array(
            // Specifies the sort. Valid options are:
            // relevance, date, date2
            // date = Date descending
            // date2 = Date descending
            'sort'           => $sortBy,
            // Specifies the search mode. Valid options are:
            // bool, any, all, smart
            'searchmode'     => $mode,
            // Specifies the amount of data to return with the response. Valid options are:
            // Title: title only
            // Brief: Title + Source, Subjects
            // Detailed: Brief + full abstract
            'view'           => $amount,
            // Specifies whether or not to include facets
            'includefacets'  => 'y',
            'resultsperpage' => $limit,
            'pagenumber'     => $start,
            // Specifies whether or not to include highlighting in the search results
            'highlight'      => 'y',
            'expander'       => $expander,
            'relatedcontent'    => 'rs'
        );

        if (!(is_array($limiter))) {
            $params['limiter'] = $limiter;
        }
        $params = array_merge($params, $query);
        $params = http_build_query($params);
        if (is_array($limiter)) {
            foreach ($limiter as $selectedLimiter) {
                $params .= "&limiter=".urlencode($selectedLimiter);
            }
        }
    //$_SESSION['debug'] = "<p>PARAMS AFTER RESULTS.PHP: ".$params."</p>";

    $results = $api->apiSearch($params);
    if (isset($results['queryString'])) {
        if (isset($results['queryString'])) {
  if (isset($results['queryString'])) {
   $backpath = $results['queryString'];
};
}; 
    } else {
        $backpath = '';
    }
    //Cach the results for each session
    $_SESSION['results'] = $results;
}

// Error
if (isset($results['error'])) {
    $error = $results['error'];
    $results =  array();
} else {
    $error = null;
}

//save debug into session
// if($debug == 'y'||$debug == 'n'){
    // $_SESSION['debug'] = $debug;
// }

// Variables used in view
$variables = array(
    'searchTerm'     => $searchTerm,
    'fieldCode'      => urlencode($fieldCode),
    'results'        => $results,
    'error'          => $error,
    'start'          => $start,
    'limit'          => $limit,
    'refineSearchUrl'=> '',
    'amount'         => $amount,
    'sortBy'         => $sortBy,
    'id'             => 'results',
    'Info'           => $Info,
    'backpath'       => urlencode(urlencode($backpath)) ? urlencode(urlencode($backpath)):'',
    'c'              => $c,
    'customparams'   => $customparams
    
);

ebsco_render('results.html', 'layout.html', $variables);

mysqli_close($c);

?>