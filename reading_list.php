<?php

include_once('app/app.php');

$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));
include('rest/EBSCOAPI.php');

$clean = strip_tags_deep($_GET);

// temporary - to not collect data on linked lists
if (isset($_COOKIE['link'])) {
    $customparams['studentdata'] = 'n';
}

if (($customparams['studentdata'] == "y") && (!(isInstructor()))) {
    $email = isset($_COOKIE['lis_person_contact_email_primary']) ? decryptCookie($_COOKIE['lis_person_contact_email_primary']) : '';
    recordStudentAccess($c,decryptCookie($_COOKIE['lis_person_name_full']),$email,decryptCookie($_COOKIE['currentListId']));
}

if (isset($clean['folderid'])) {
    if (folderExists($c,$clean['folderid'])) {
        $readingList = getFolderContents($c,$clean['folderid']);
    } else {
        header("Location: reading_list.php");
    }
} else {
    $readingList = getReadingList($c);    
}

$listoffolders = getFolderList($c);

$useCache = false;
if (sizeof($readingList) >= 75) {
    $results = array();
    $useCache = true;
} else {

    $api = new EBSCOAPI($c,$customparams);
    
    $listOfANs  = array();
    foreach ($readingList as $reading) {
        $listOfANs[] = "AN ".$reading['an'];
    }
    
    if (sizeof($listOfANs) > 0) {
    
    $query['query'] = implode(" OR ",$listOfANs);
    $searchTerm = $query;
    $fieldCode = '';
    $start = 1;
    $limit = 100;
    $sortBy = 'relevance';
    $amount = 'detailed';
    $mode = 'all';
    $expander = '';
    $limiter = '';
    $debug = '';
    
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
        'includefacets'  => 'n',
        'resultsperpage' => $limit,
        'pagenumber'     => $start,
        // Specifies whether or not to include highlighting in the search results
        'highlight'      => 'n',
        'expander'       => $expander,
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
    
    $results = $api->apiSearch($params);
    
    } else {
    $results = array();  
    }
}

$variables = array(
    'results'        => $results,
    'readingList'    => $readingList,
    'c'              => $c,
    'customparams'   => $customparams,
    'useCache'       => $useCache,
    'listoffolders'  => $listoffolders,
    'clean'          => $clean
);

ebsco_render('reading_list.html', 'layout.html',$variables);

?>
