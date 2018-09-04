<?php

/**
 * EBSCO Connector class
 *
 * PHP version 5
 *
 */

 require_once('conf/keys.php');


/**
 * EBSCOException class
 * Used when EBSCO API calls return an error message
 */
class EBSCOException extends Exception { }


/**
 * EBSCO Connector class
 */
class EBSCOConnector
{
    /**
     * Error codes defined by EDS API
     *
     * @global integer EDS_UNKNOWN_PARAMETER  Unknown Parameter
     * @global integer EDS_INCORRECT_PARAMETER_FORMAT  Incorrect Parameter Format
     * @global integer EDS_INCORRECT_PARAMETER_FORMAT  Invalid Parameter Index
     * @global integer EDS_MISSING_PARAMETER  Missing Parameter
     * @global integer EDS_AUTH_TOKEN_INVALID  Auth Token Invalid
     * ...
     */
    const EDS_UNKNOWN_PARAMETER          = 100;
    const EDS_INCORRECT_PARAMETER_FORMAT = 101;
    const EDS_INVALID_PARAMETER_INDEX    = 102;
    const EDS_MISSING_PARAMETER          = 103;
    const EDS_AUTH_TOKEN_INVALID         = 104;
    const EDS_INCORRECT_ARGUMENTS_NUMBER = 105;
    const EDS_UNKNOWN_ERROR              = 106;
    const EDS_AUTH_TOKEN_MISSING         = 107;
    const EDS_SESSION_TOKEN_MISSING      = 108;
    const EDS_SESSION_TOKEN_INVALID      = 109;
    const EDS_INVALID_RECORD_FORMAT      = 110;
    const EDS_UNKNOWN_ACTION             = 111;
    const EDS_INVALID_ARGUMENT_VALUE     = 112;
    const EDS_CREATE_SESSION_ERROR       = 113;
    const EDS_REQUIRED_DATA_MISSING      = 114;
    const EDS_TRANSACTION_LOGGING_ERROR  = 115;
    const EDS_DUPLICATE_PARAMETER        = 116;
    const EDS_UNABLE_TO_AUTHENTICATE     = 117;
    const EDS_SEARCH_ERROR               = 118;
    const EDS_INVALID_PAGE_SIZE          = 119;
    const EDS_SESSION_SAVE_ERROR         = 120;
    const EDS_SESSION_ENDING_ERROR       = 121;
    const EDS_CACHING_RESULTSET_ERROR    = 122;


    /**
     * HTTP status codes constants
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    const HTTP_OK                    = 200;
    const HTTP_BAD_REQUEST           = 400;
    const HTTP_NOT_FOUND             = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
      
    /**
     * The URL of the EBSCO API server
     * @global string
     */
    private static $end_point ;

    /**
     * The URL of the EBSCO API server
     * @global string
     */
    private static $authentication_end_point ;

    /**
     * The password used for API transactions
     * @global string
     */
    private $password;


    /**
     * The user id used for API transactions
     * @global string
     */
    private $userId;

    /**
     * The interface ID used for API transactions
     * @global string
     */
    private $interfaceId;

    /**
     * The customer ID used for API transactions
     * @global string
     */
    private $orgId;
 
    /**
     * Constructor
     *
     * Setup the EBSCO API credentials
     *
     * @param none
     *
     * @access public
     */
    public function __construct($cust_userid,$cust_password,$cust_profile)
    {              
        self::$end_point = "http://eds-api.ebscohost.com/edsapi/rest";
        self::$authentication_end_point="https://eds-api.ebscohost.com/Authservice/rest";
        
        $this->userId = $cust_userid;
        $this->password = $cust_password;                
        $this->interfaceId = 'Curriculum Builder';
        $this->orgId = 'cbuilder';
        
        if (!(isset($_SESSION['debug']))) {
            $_SESSION['debug'] = "";
        }
    }


function encryptCookie($value){
   
   // serialize array values
   if(!$value){return false;}
   if (is_array($value)) {
      $value = serialize($value);
   }
   
   // generate an SHA-256 HMAC hash where data = session ID and key = defined constant
   $key = hash_hmac('sha256',session_id(),ENCRYPTION_KEY_HMAC);
   if (strlen($key) > 24) {
      $key = substr($key,0,24);
   }
   
   // generate random value for IV
   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);   

   // encrypt the data with the key and IV using AES-256
   $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $value, MCRYPT_MODE_CBC, $iv);

   // generate an SHA-256 HMAC hash where data = encrypted text and key = defined constant
   $signature = hash_hmac('sha256',$crypttext,SIG_HMAC);

   $cookiedough = array();
   $cookiedough['sig'] = $signature;
   $cookiedough['iv'] = $iv;
   $cookiedough['text'] = $crypttext;

   $final = serialize($cookiedough);

   return base64_encode($final); //encode for cookie
}

function decryptCookie($value){
   if(!$value){return false;}

   $value = base64_decode($value);
   
   $cookiedough = unserialize($value);

   // generate an SHA-256 HMAC hash where data = encrypted text and key = defined constant
   $snifftest = hash_hmac('sha256',$cookiedough['text'],SIG_HMAC);

   // if it matches the stored signature, you pass.
   if (!($cookiedough['sig'] == $snifftest)) {
     die("Oops!  It looks like something went wrong with your browser cookies.  Please ensure that cookies are enabled in your browser.  If the problem persists, please notify your library.");
   }
   
   // generate an SHA-256 HMAC hash where data = session ID and key = defined constant
   $key = hash_hmac('sha256',session_id(),ENCRYPTION_KEY_HMAC);
   if (strlen($key) > 24) {
      $key = substr($key,0,24);
   }
   
   $iv = $cookiedough['iv'];

   $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $cookiedough['text'], MCRYPT_MODE_CBC, $iv);

   $data = @unserialize($decrypttext);
   if ($decrypttext === 'b:0;' || $data !== false) {
      $decrypttext = $data;
   }
   
   if (is_array($decrypttext)) {
    return $decrypttext;
   } else {
       return trim($decrypttext);
   }
}

    /**
     * Request the authentication token
     *
     * @param none
     *
     * @return string   .The authentication token
     * @access public
     */
    public function requestAuthenticationToken()
    {

        $url = self::$authentication_end_point.'/UIDAuth';
        // Add the body of the request. Important.
        $params =<<<BODY
<UIDAuthRequestMessage xmlns="http://www.ebscohost.com/services/public/AuthService/Response/2012/06/01">
    <UserId>{$this->userId}</UserId>
    <Password>{$this->password}</Password>
    <InterfaceId>{$this->interfaceId}</InterfaceId>
</UIDAuthRequestMessage>
BODY;

        // Set the content type to 'application/xml'. Important, otherwise cURL will use the usual POST content type.
        $headers = array(
            'Content-Type: application/xml',
            'Conent-Length: ' . strlen($params)
        );
        $_SESSION['debug'] .= "<p>Requested New Authentication Token with call to ".$url." and body ".var_export($params,true)." with headers ".var_export($headers,TRUE)."</p>";
        $response = $this->request($url, $params, $headers, 'POST');
        $_SESSION['debug'] .= "<p>Received response: ".var_export($response,true);
        return $response;
    }


    /**
     * Request the session token
     *
     * @param array $headers  Authentication token
     *
     * @return string   .The session token
     * @access public
     */
    public function requestSessionToken($headers, $profile,$guest)
    {
        $url = self::$end_point . '/CreateSession';
        
        // Add the HTTP query parameters
        $params = array(
            'profile' => $profile,
            'org'     => $this->orgId,
            'guest'   => $guest
        );
        $_SESSION['debug'] .= "<p>Requested New Session Token with call to ".$url." and params ".var_export($params,true)." with headers ".var_export($headers,TRUE)."</p>";
        $params = http_build_query($params);
        
        $response = $this->request($url, $params, $headers);
        $_SESSION['debug'] .= "<p>Received response: ".var_export($response,true);
        return $response;
    }
    
    /**
     * End the session token
     * 
     * @param array $headers Session token
     * @access public
     */
    public function requestEndSessionToken($headers, $sessionToken){
        $url = self::$end_point.'/endsession';
        
        // Add the HTTP query parameters
        $params = array(
            'sessiontoken'=>$sessionToken
        );
        $params = http_build_query($params);              
        $this->request($url,$params,$headers);
    }

    /**
     * Request the search records
     *
     * @param array $params Search specific parameters
     * @param array $headers Authentication and session tokens
     *
     * @return array    An associative array of data
     * @access public
     */
    public function requestSearch($params, $headers)
    {
        $url = self::$end_point . '/Search';
        $response = $this->request($url, $params, $headers);
        return $response;
    }

    /**
     * Request a specific record
     *
     * @param array $params Retrieve specific parameters
     * @param array $headers Authentication and session tokens
     *
     * @return  array    An associative array of data
     * @access public
     */
    public function requestRetrieve($params, $headers)
    {
        $url = self::$end_point . '/Retrieve';

        $response = $this->request($url, $params, $headers);
        return $response;
    }


    /**
     * Request the info data
     *
     * @param null $params Not used
     * @param array $headers Authentication and session tokens
     *
     * @return  array    An associative array of data
     * @access public
     */
    public function requestInfo($params,$headers)
    {
        $url = self::$end_point . '/Info';

        $response = $this->request($url, $params, $headers);
        return $response;
    }


    /**
     * Send an HTTP request and inspect the response
     *
     * @param string $url         The url of the HTTP request
     * @param array  $params      The parameters of the HTTP request
     * @param array  $headers     The headers of the HTTP request
     * @param array  $body        The body of the HTTP request
     * @param string $method      The HTTP method, default is 'GET'
     *
     * @return object             SimpleXml
     * @access protected
     */
    protected function request($url, $params = null, $headers = null, $method = 'GET') 
    {
        $params = str_replace("%26amp%3B","%26",$params);

        $xml = false;
        //$return = false;

        // Create a cURL instance
        $ch = curl_init();

        // Set the cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Termporary

        // Set the query parameters and the url
        if (empty($params)) {
            // Only Info request has empty parameters
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            // GET method
            if ($method == 'GET') {
                $url .= '?' . $params;
                curl_setopt($ch, CURLOPT_URL, $url);
            // POST method
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
        } 

        // Set the header
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Send the request
        $response = curl_exec($ch);
        //Save XML file for debug mode      
        if(strstr($url,'Search')){
            $_SESSION['resultxml'] = $response;
        }
        if(strstr($url,'Retrieve')){
           $_SESSION['recordxml'] = $response;
        }
        // Parse the response
        // In case of errors, throw 2 type of exceptions
        // EBSCOException if the API returned an error message
        // Exception in all other cases. Should be improved for better handling
        if ($response === false) {
            throw new Exception(curl_error($ch));
            curl_close($ch);
        } else {         
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            switch ($code) {
                case self::HTTP_OK:
                    $xml = simplexml_load_string($response);
                    if ($xml === false) {
                         throw new Exception('There was an error connecting to the library.<p style="display:none;">Error while parsing the response for URL '.$url.' with headers '.var_export($headers,TRUE).':' . var_export($response,TRUE)."</p>");
                    } else {                    
                         return $xml;
                    }
                    break;
                case self::HTTP_BAD_REQUEST:
                    $xml = simplexml_load_string($response);
                    if ($xml === false) {
                         throw new Exception('There was an error connecting to the library.<p style="display:none;">Error while parsing the response for URL '.$url.' with headers '.var_export($headers,TRUE).':' . var_export($response,TRUE)."</p>");
                    } else {
                        // If the response is an API error
                        $error = ''; $code = 0;
                        $isError = isset($xml->ErrorNumber) || isset($xml->ErrorCode);
                        if ($isError) {
                            if (isset($xml->DetailedErrorDescription) && !empty($xml->DetailedErrorDescription)) {
                                $error = (string) $xml->DetailedErrorDescription;
                            } else if (isset($xml->ErrorDescription)) {
                                $error = (string) $xml->ErrorDescription;
                            } else if (isset($xml->Reason)) {
                                $error = (string) $xml->Reason;
                            }
                            if (isset($xml->ErrorNumber)) {
                                $code = (integer) $xml->ErrorNumber;
                            } else if (isset($xml->ErrorCode)) {
                                $code = (integer) $xml->ErrorCode;
                            }
                            $error .= "<p style='display:none;'> (Request was ".$url." with params ".$params;
                            if (!empty($headers)) {
                                $error .= " and headers ".var_export($headers,TRUE).")";
                            } else {
                                $error .= " with no headers.)";
                            }
                            if (isset($_SESSION['debug'])) {
                                $error .= " -- Session Debug: ".$_SESSION['debug'];
                            } else {
                                $error .= " -- No Session Debug";
                            }
                            $error .= "</p>";
                            throw new EBSCOException($error, $code);
                        } else {
                            throw new Exception('The request could not be understood by the server 
                            due to malformed syntax. Modify your search before retrying.');
                        }
                    }
                    break;
                case self::HTTP_NOT_FOUND:
                    throw new Exception('The resource you are looking for might have been removed, 
                        had its name changed, or is temporarily unavailable: looking for '.$url);
                    break;
                case self::HTTP_INTERNAL_SERVER_ERROR:
                    throw new Exception('The server encountered an unexpected condition which prevented 
                        it from fulfilling the request.');
                    break;
                // Other HTTP status codes
                default:
                    throw new Exception('Unexpected HTTP error.');
                    break;
            }
        }
    }
}


?>