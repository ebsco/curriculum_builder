<?php
    ini_set("session.cookie_httponly", 1);
    session_start();
    
    include_once('app/app.php');

    $time = 0; // store for session only //store cookie for one hour
    
    $clean = strip_tags_deep($_POST);
    $clean_Get = strip_tags_deep($_GET);
    
    // clear any previous launch cookies
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time()-1000,'/',$_SERVER['SERVER_NAME'],FALSE,TRUE);
        }
    }
        
    // Insure we have a valid launch
    if ( empty($clean["oauth_consumer_key"]) ) {
        die("You are missing a valid consumer key.  Please contact your LMS Administrator to ensure the proper consumer key and secret have been configured.<br/>");
    }
    $oauth_consumer_key = $clean["oauth_consumer_key"];

    // Find the secret - either form the parameter as a string or
    // look it up in a database from parameters we are given
    $secret = false;
    $row = false;
    
    $sql = 'SELECT secret, newwindow FROM oauth WHERE oauth_consumer_key = ?';
 
    /* Prepare statement */
    $stmt = $c->prepare($sql);
    if($stmt === false) {
      trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->errno . ' ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('s', $oauth_consumer_key);
     
    /* Execute statement */
    $stmt->execute();
    
    /* Fetch result to array */
    $result = $stmt->get_result();
    
    $num_rows = mysqli_num_rows($result);
    
    if ( $num_rows != 1 ) {
        die("<p style='display:none;'>".var_export($clean,TRUE)."</p><br/><br/>Your consumer key is incorrect.  Please contact us at eds@ebscohost.com to obtain your consumer key.  Currently, your consumer key is set to: ".$oauth_consumer_key.".  If this is your LMS username, please check to make sure your web browser's autocomplete feature is turned off for this site.");
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            $secret = $row['secret'];
            $newwindow = $row['newwindow'];
            break;
        }
        if ( ! is_string($secret) ) {
            die("Could not retrieve secret oauth_consumer_key=".$oauth_consumer_key);
        }
    }

    // Verify the message signature
    $store = new TrivialOAuthDataStore();
    $store->add_consumer($oauth_consumer_key, $secret);

    $server = new OAuthServer($store);

    $method = new OAuthSignatureMethod_HMAC_SHA1();
    $server->add_signature_method($method);
    $request = OAuthRequest::from_request();
    
    $basestring = $request->get_signature_base_string();

    try {
        $server->verify_request($request);
        $valid = true;
    } catch (Exception $e) {
        die("It appears that your consumer secret is incorrect.  Please contact us at eds@ebscohost.com to set your consumer secret. Errno 1." . $e->getMessage());
    }
    
    // if we made it here, valid LTI launch

    mysqli_close($c);
?>
<form id="lti_form" action="lti2.php" method="post" <?php

if ($newwindow == 'y') {
    echo ' target="_blank"';
}

?>>
<?php
    foreach ( $clean as $foo=>$bar ) {
        echo '<input type="hidden" name="' . htmlentities(strip_tags($foo),ENT_QUOTES) . '" value="' . htmlentities(strip_tags($bar),ENT_QUOTES) . '" />';
    }
    foreach ( $clean_Get as $foo=>$bar) {
        echo '<input type="hidden" name="' . htmlentities(strip_tags($foo),ENT_QUOTES) . '" value="' . htmlentities(strip_tags($bar),ENT_QUOTES) . '" />';
    }
?>
    <input type="submit" value="Click for Readings" />
</form>

<script type="text/javascript">
  document.getElementById("lti_form").submit();
</script>