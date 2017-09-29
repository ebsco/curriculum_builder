<?php
session_start();

$time = 0; // store for session only //store cookie for one hour

include('app/app.php');
include('rest/EBSCOAPI.php');

$clean = strip_tags_deep($_REQUEST);

if (!(isset($_COOKIE))) {
     die("This site requires the use of cookies.  Please enable cookies in your web browser.");
} else if (!(isset($_COOKIE['oauth_consumer_key']))) {
     die("It looks like browser security settings may have blocked this content.  Please try a different browser, or lower your security settings.");
}
$cookieDCd = decryptCookie($_COOKIE['oauth_consumer_key']);

$_SESSION['debug'] .= "<p>Cookie dump: ";
$accepted = array('oauth_consumer_key', 'roles', 'context_label', 'user_id', 'context_title', 'lis_person_name_full', 'lis_person_contact_email_primary', 'resource_link_title', 'resource_link_id', 'tool_consumer_instance_guid', 'launch_presentation_return_url');

foreach($_COOKIE as $index => $thecookie) {
     if (in_array($index, $accepted)) {
         $_SESSION['debug'].="<br />".$index.": ".decryptCookie($thecookie);
     }
}
$_SESSION['debug'] .= "</p>";

$customparams = loadCustomParams($c,$cookieDCd);
$profile = $customparams['profile'];

try {
     $api = new EBSCOAPI($c,$customparams);
} catch (Exception $e) {
     die ("It looks like your user id and password for your EDS API profile are incorrect.  Please check your settings in the <a href='http://curriculumbuilder.ebscohost.com/admin.php' target='_top'>admin panel</a>.<p style='display:none;'>".var_export($customparams,TRUE)."</p>");
}

try {
     $_SESSION['debug'] .= "<p>Using AuthToken ".$api->getAuthToken()."</p>";
     $newSessionToken = $api->apiSessionToken($api->getAuthToken(), $profile,'n');
} catch (Exception $e) {
     echo "<div style='display:none;'>".$_SESSION['debug']."</div>";
     die ("It looks like your profile id for your EDS API profile is incorrect.  Please check your settings in the <a href='http://curriculumbuilder.ebscohost.com/admin.php' target='_top'>admin panel</a>.<p style='display:none;'>".var_export($customparams,TRUE)."</p><p style='display:none;'>".$e->getMessage()."</p>");
}

setcookie('sessionToken',encryptCookie($newSessionToken),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
setcookie('login',encryptCookie($profile),0,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);       

if(isset($_COOKIE['Guest'])){
     setcookie('Guest','',time()-3600); 
}    

if (isset($clean['path'])) {      
     $path = $clean['path'];
} else {
     $path = "default";
}

if (isset($clean['copyid'])) {
     if ($clean['copyid'] == '0') {
          
     } else {
          copyList($c,$clean['copyid'],decryptCookie($_COOKIE['currentListId']));
          $path = "reading_list";
     }
}


if($path=="reading_list"){
     header("location: $path.php");
} else {
     header("location: index.php");
}
       
?>
