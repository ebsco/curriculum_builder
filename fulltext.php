<?php
include('app/app.php');
$customparams = loadCustomParams($c,decryptCookie($_COOKIE['oauth_consumer_key']));

include('rest/EBSCOAPI.php');

$api = new EBSCOAPI($c,$customparams);

$clean = strip_tags_deep($_REQUEST);

$db = $clean['db'];
$an = $clean['an'];

//echo $db . " - " . $an;

if(!isset($_COOKIE['login'])) {
    $end = lastIndexOf($_SERVER['PHP_SELF'],"/");    
    $loginURL = $proxyprefix . $baseURLfull . $authURL . "&path=fulltext&an=" . $an . "&db=" . $db;
    //echo $loginURL;
    header("Location: " . $loginURL);
} else {
   
$result = $api->apiRetrieve($an, $db);

// Set error
if (isset($result['error'])) {
    $error = $result['error'];
} else {
    $error = null;
}

// Link priority: 
// 1. PDF and HTML full-text native to EDS
// 2. eBook full-text links
// 3. Custom links

// get PDF or HTML redirect links
if (!empty($result['pdflink'])) { 
  $redirectURL = $result['pdflink'];
}
else if (!empty($result['htmllink'])) {
  $redirectURL = $result['PLink'];
}

// get eBook redirect links
if ($db == "nlebk") {
  $redirectURL = $result['PLink'];
}

if (!isset($redirectURL)) {
  foreach ($result['Items'] as $item) {
    if (isset($item['DirectURL'])) {
      $redirectURL = $item['DirectURL'];
      // for EBL eBooks, bypass the metadata screen, go straight to READ ONLINE
      $redirectURL = str_replace('FullRecord.aspx', 'Read.aspx', $redirectURL);
    }
  }
}


//get CustomLink redirects
if (!isset($redirectURL)) {
  if (!empty($result['CustomLinks'])) {
    foreach ($result['CustomLinks'] as $customLink) {
      if ($customLink['Category'] == 'ill') {
        $redirectURL = 'record.php?an=' . $an . '&db=' . $db;
      } else if (isset($customLink['Url'])) {
        $redirectURL = $customLink['Url']; 
      }
    }
  }
}

//last ditch - is there an item in group URL?
if (!isset($redirectURL)) {
  foreach ($result['Items'] as $item) {
    if ($item['Group'] == 'URL') {
      $redirectURL = $item['Data'];
      $linkStart = strpos($redirectURL,'href="http')+6;
      $linkEnd = strpos($redirectURL,'"',$linkStart);
      $redirectURL = substr($redirectURL,$linkStart,$linkEnd-$linkStart);
      break;
    }
  }
}


// some resources prevent headers from deep linking - in these cases, we use HTTP-EQUIV="Refresh"
if ((strpos($redirectURL,'galegroup')) || (strpos($redirectURL,'films.com'))) {
  ?>
  <html>
  <head>
    <title>Redirecting you to <?php echo $result['Items']['Title']['Data']; ?></title>
  <meta http-equiv="REFRESH" content="0;url=<?php echo $redirectURL; ?>" />
  </head>
  <body>
  <p style="weight:bold;">Redirecting you to <a href="<?php echo $redirectURL; ?>"><?php echo $result['Items']['Title']['Data']; ?>...</a></p>
  </body>
  </html>
  <?php
} else {
  $redirectURL = 'Location: ' . $redirectURL;
  header($redirectURL);
}

}

?>