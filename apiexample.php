<?php
    header('Content-Type: text/html; charset=UTF-8');
    session_start();

    try {
        $url = "https://eit.ebscohost.com/Services/SearchService.asmx/Info?prof=ns054863.main.eitws&pwd=ebs5078";
        $session = curl_init($url); 	                        // Open the Curl session
            
        curl_setopt($session, CURLOPT_HEADER, false); 	        // Don't return HTTP headers
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);        // Do return the contents of the call
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($session); 	                        // Make the call
                
        if ($response === false) {
            throw new Exception(curl_error($session)."<br />cURL Error Number: ".curl_errno($session));
            curl_close($session);
        } else {
            echo "<p><strong>An HTTPS cURL request to an unrelated server:</strong></p>";
            echo "<p><em>URL:</em><br />".$url."</p>";
            echo "<textarea style='width:100%'>".$response."</textarea></p>";            
            curl_close($session);                                       // And close the session
        }
    } catch (Exception $e) {
         echo $e->getMessage();
    }
    
    
    if (isset($_GET['userid'])) {
        $userid = $_GET['userid'];        
    } else {
        $userid = 'marluth_cbuilder';
    }
    
    if (isset($_GET['pass'])) {
        $pass = $_GET['pass'];
    } else {
        $pass = 'GAtIynSgmH';
    }
    
    if (isset($_GET['prof'])) {
        $prof = $_GET['prof'];
    } else {
        $prof = 'cbuilder';
    }
    
    $url = "https://eds-api.ebscohost.com/Authservice/rest/UIDAuth";
            
    $params =<<<BODY
<UIDAuthRequestMessage xmlns="http://www.ebscohost.com/services/public/AuthService/Response/2012/06/01">
    <UserId>{$userid}</UserId>
    <Password>{$pass}</Password>
    <InterfaceId>Curriculum Builder</InterfaceId>
</UIDAuthRequestMessage>
BODY;
            
    // Set the content type to 'application/xml'. Important, otherwise cURL will use the usual POST content type.
    $headers = array(
        'Content-Type: application/xml',
        'Conent-Length: ' . strlen($params)
    );            

    echo "<p><strong>Request Details</strong></p>";
    echo "<p><em>URL:</em><br />".$url."<br /><br />";
    echo "<em>Headers:</em><br />";
    foreach($headers as $header) {
        echo $header . "<br />";
    }
    echo "<br /><em>Body of Message:</em></br /><textarea style='width:400px; height:100px;'>".$params."</textarea></p>";
    
    echo "<p><strong>Attempting to connect and response:</strong><br />";
    
    
try {
    $session = curl_init($url); 	                        // Open the Curl session
        
    curl_setopt($session, CURLOPT_HEADER, false); 	        // Don't return HTTP headers
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);        // Do return the contents of the call
    curl_setopt($session, CURLOPT_POSTFIELDS, $params);
    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($session); 	                        // Make the call
            
    if ($response === false) {
        throw new Exception(curl_error($session)."<br />cURL Error Number: ".curl_errno($session));
        curl_close($session);
    } else {
        echo "<textarea style='width:100%'>".$response."</textarea>";            
        curl_close($session);                                       // And close the session
    }
} catch (Exception $e) {
     echo $e->getMessage();
}

    echo "</p>";

?>
