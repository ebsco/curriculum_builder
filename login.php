<?php 
include('app/app.php');
include('rest/EBSCOAPI.php');

$clean = strip_tags_deep($_REQUEST);

$fail = isset($clean['fail'])?$clean['fail']:'';

if($clean['path']=="record"){
$db = $clean['db'];
$an = $clean['an'];
$highlight = $clean['highlight'];
$query = $clean['query'];
$fieldCode = $clean['fieldcode'];

$varables = array(
    'path' => 'record',
    'db' => $db,
    'an' => $an,
    'highlight'=>$highlight,
    'query'=> $query,
    'fieldCode' => $fieldCode,
    'resultId' => $clean['resultId'],
    'recordCount' => $clean['recordCount']
);
}

else if($clean['path']=="PDF"){
    $db = $clean['db'];
$an = $clean['an'];

$varables = array(
    'path' => 'PDF',
    'db' => $db,
    'an' => $an
);
}

else if($clean['path']=="HTML"){
$db = $clean['db'];
$an = $clean['an'];
$highlight = $clean['highlight'];
$query = $clean['query'];
$fieldCode = $clean['fieldcode'];

$varables = array(
    'path' => 'HTML',
    'db' => $db,
    'an' => $an,
    'highlight'=>$highlight,
    'resultId' => $clean['resultId'],
    'recordCount' => $clean['recordCount'],
    'query' => $query,
    'fieldCode'=>$fieldCode,
    'c'=>$c
);
}

else if($clean['path']=="results"){
   $query = $clean['query'];
   $fieldCode = $clean['fieldcode'];
   
   $varables = array(
       'path' => 'results',
       'query' => $query,
       'fieldCode'=>$fieldCode
   );
}

else {
    $varables = array(
        'path' => 'index'
    );
}

$varables['fail'] = $fail;
ebsco_render('login.html', 'layout.html',$varables);
?>

