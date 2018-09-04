<?php


/**
 * EBSCO API class
 *
 * PHP version 5
 *
 */

require_once 'EBSCOConnector.php';
require_once 'EBSCOResponse.php';
require_once('conf/keys.php');

/**
 * EBSCO API class
 */
class EBSCOAPI
{
    /**
     * The authentication token used for API transactions
     * @global string
     */
    private $authenticationToken;
    
    /**
     * The session token for API transactions
     * @global string
     */
    private $sessionToken;

    /**
     * The EBSCOConnector object used for API transactions
     * @global object EBSCOConnector
     */
    private $connector;
    
    private static $c;
    private static $cust_userid;
    private static $cust_password;
    private static $cust_profile;
    
    public function __construct($mysqliconnection,$customparams) {
        self::$c = $mysqliconnection;
        self::$cust_userid = $customparams['userid'];
        self::$cust_password = $customparams['password'];
        self::$cust_profile = $customparams['profile'];
    }
    /**
     * Create a new EBSCOConnector object or reuse an existing one
     *
     * @param none
     *
     * @return EBSCOConnector object
     * @access public
     */
    public function connector()
    {
        if (empty($this->connector)) {
            $this->connector = new EBSCOConnector(self::$cust_userid,self::$cust_password,self::$cust_profile);
        }

        return $this->connector;
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
     * Create a new EBSCOResponse object
     *
     * @param object $response
     *
     * @return EBSCOResponse object
     * @access public
     */
    public function response($response)
    {
        $responseObj = new EBSCOResponse($response);
        return $responseObj;
    }


    /**
     * Request authentication and session tokens, then send the API request.
     * Retry the request if authentication errors occur
     *
     * @param string  $action     The EBSCOConnector method name
     * @param array   $params     The parameters for the HTTP request
     * @param integer $attempts   The number of retries. The default number is 3 but can be increased.
     * 3 retries can handle a situation when both autentication and session tokens need to be refreshed + the current API call
     *
     * @return array              An associative array with results.
     * @access protected
     */
    protected function request($action, $params = null, $attempts = 3)
    {
        try {

            $authenticationToken = $this->getAuthToken();
            //$sessionToken = $this ->getSessionToken($authenticationToken);
                        
            if(empty($authenticationToken)){
               $authenticationToken = $this -> getAuthToken();
            }
            
            $sessionToken = $this ->getSessionToken($authenticationToken);
            if(empty($sessionToken)){
                $sessionToken = $this -> getSessionToken($authenticationToken,'y');
            }
                   
            $headers = array(
                'x-authenticationToken: ' . $authenticationToken,
                'x-sessionToken: ' . $sessionToken
            );
            $response = call_user_func_array(array($this->connector(), "request{$action}"), array($params, $headers));
            $result = $this->response($response)->result();
            $results = $result;             
            return $results;
        } catch(EBSCOException $e) {
            try {
                // Retry the request if there were authentication errors
                $code = $e->getCode();
                switch ($code) {
                    case EBSCOConnector::EDS_AUTH_TOKEN_INVALID:
                        $authenticationToken = $this->apiAuthenticationToken();
                        $credential_id = decryptCookie($_COOKIE['credential_id']);
                        $sql = "UPDATE authtokens SET token = ?, timeout = ?, tokentimestamp = ? WHERE credentialid = ?"; 
                        $stmt = self::$c->prepare($sql);
                        $stmt->bind_param('siii',$authenticationToken['authenticationToken'],$authenticationToken['authenticationTimeout'],$authenticationToken['authenticationTimeStamp'],$credential_id);
                        $stmt->execute();
                    
                        if ($attempts > 0) {
                            return $this->request($action, $params, --$attempts);
                        }
                        break;
                    case EBSCOConnector::EDS_SESSION_TOKEN_INVALID:
                        $sessionToken = $this ->getSessionToken($authenticationToken,'y');
                        //$headers = array(
                //'x-authenticationToken: ' . $authenticationToken,
                //'x-sessionToken: ' . $sessionToken
            //);
                        if ($attempts > 0) {
                            return $this->request($action, $params, --$attempts);
                        }
                        break;
                    default:
                        $result = array(
                            'error' => $e->getMessage()
                        );
                        return $result;
                        break;
                }
            }  catch(Exception $e) {
                $result = array(
                    'error' => $e->getMessage()
                );
                return $result;
            }
        } catch(Exception $e) {
            $result = array(
                'error' => $e->getMessage()
            );
            return $result;
        }
    }
    
    /*
     * Get authentication token from appication scop 
     * Check authToen's expiration 
     * if expired get a new authToken and re-new the time stamp
     * 
     * @param none
     * 
     * @access public
     */
    public function getAuthToken(){
        $timeout = 0;
        $timestamp = 0;
        $credential_id = decryptCookie($_COOKIE['credential_id']);

        $sql = "SELECT token, timeout, tokentimestamp FROM authtokens WHERE credentialid = ?";
        $_SESSION['debug'].="<p>--getAuthToken() SQL: ".$sql."-- (credential ID = ".$credential_id.")--</p>";
        $stmt = self::$c->prepare($sql);
        $stmt->bind_param('i',$credential_id);
        $stmt->execute();
        $authtokenlookup = $stmt->get_result();

        if ($authtokenlookup) {
            if (mysqli_num_rows($authtokenlookup) >= 1) {
                $row = mysqli_fetch_array($authtokenlookup);
                
                $authToken = $row['token'];
                $timeout = $row['timeout'];
                $timestamp = $row['tokentimestamp'];
                $_SESSION['debug'].="<p>--Found an authToken in MySQL with details: ".var_export($row,TRUE)."--</p>";

            } else {
                $sql = "INSERT INTO authtokens (credentialid) VALUES (?)";
                $stmt = self::$c->prepare($sql);
                $stmt->bind_param('i',$credential_id);
                $stmt->execute();
            }
        } else {
            die("It looks like the application was unable to connect to your MySQL server, or had trouble looking for the reading list.  Here is the MySQL error: 6");
        }
        // add five minutes to timestamp, that way it prevents token from expiring before it uses it
        if(time()-($timestamp)>=$timeout){
                $_SESSION['debug'].="<p>Found existing authtoken to be expired. Requesting new one...</p>";
                $result = $this->apiAuthenticationToken();
                $_SESSION['debug'].="<p>New one...".var_export($result,TRUE)."</p>";

                $sql = "UPDATE authtokens SET token = ?, timeout = ?, tokentimestamp = ? WHERE credentialid = ?"; 
                $stmt = self::$c->prepare($sql);
                $stmt->bind_param('siii',$result['authenticationToken'],$result['authenticationTimeout'],$result['authenticationTimeStamp'],$credential_id);
                $stmt->execute();
                return $result['authenticationToken'];
        }else{
            $_SESSION['debug'] .= 'No new auth token generated  ' . time() . ' minus ' . $timestamp . ' is less than ' . $timeout;   
            return $authToken;
        }
    }
    
    /**
     * Wrapper for authentication API call
     *
     * @param none
     *
     * @access public
     */
    public function apiAuthenticationToken()
    {
        $response = $this->connector()->requestAuthenticationToken();
        $result = $this->response($response)->result();
        return $result;
    }

    /**
     * Get session token for a profile 
     * If session token is not available 
     * a new session token will be generated
     * 
     * @param Authentication token, Profile 
     * @access public
     */
    public function getSessionToken($authenToken, $invalid='n'){
        $token = ''; 

        // Check user's login status
        if(isset($_COOKIE['login'])){              
               if($invalid=='y'){                    
                   $profile = self::$cust_profile;
                   $_SESSION['debug'] .= "--GetSession with INVALID is YES--";
                   $sessionToken = $this->apiSessionToken($authenToken, $profile,'n');
                   $_SESSION['debug'] .= "---apiSessionToken got ".var_export($sessionToken,TRUE)."---";

                   $time = 0; // store for session only //store cookie for one hour
                   setcookie('sessionToken',encryptCookie($sessionToken),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
                   $_SESSION['sessionToken'] = $sessionToken;
               } else {
                   if (isset($_SESSION['sessionToken'])) {
                        $sessionToken = $_SESSION['sessionToken'];
                        $_SESSION['debug'] .= "---Using EXISTING session token from SESSION var: ".var_export($sessionToken,TRUE)."---";
                   } else {
                        $sessionToken = decryptCookie($_COOKIE['sessionToken']);
                        $_SESSION['debug'] .= "---Using EXISTING session token from Cookie var: ".var_export($sessionToken,TRUE)."---";
                   }
               }
               $token = $sessionToken['sessionToken'];            
        } else {
            die("The reading list tool requires the use of cookies.  Please insure you allow cookies from this site.");
        }
        return $token;
    }

    /**
     * Wrapper for session API call
     *
     * @param Authentication token
     *
     * @access public
     */
    public function apiSessionToken($authenToken, $profile, $guest)
    {
        // Add authentication tokens to headers
        $headers = array(
            'x-authenticationToken: ' . $authenToken
        );
        $response = $this->connector()->requestSessionToken($headers, $profile,$guest);
        $result = $this->response($response)->result();
        $token = array(
            'sessionToken'=>$result,
            'profile' => $profile
        );   
         return $token;
    }
   
    /**
     * Wrapper for end session API call
     *
     * @param Authentication token
     *
     * @access public
     */
    public function apiEndSessionToken($authenToken, $sessionToken){
        
        // Add authentication tokens to headers
        $headers = array(
            'x-authenticationToken: '.$authenToken
        );
        
        $this -> connector()->requestEndSessionToken($headers, $sessionToken);
    }

    /**
     * Wrapper for search API call
     *
     * @param 
     *
     * @throws object             PEAR Error
     * @return array              An array of query results
     * @access public
     */
    public function apiSearch($params) {
        $results = $this->request('Search', $params);
        return $results;
    }


    /**
     * Wrapper for retrieve API call
     *
     * @param array  $an          The accession number
     * @param string $start       The short database name
     *
     * @throws object             PEAR Error
     * @return array              An associative array of data
     * @access public
     */
    public function apiRetrieve($an, $db, $term = '')
    {
        // Add the HTTP query params
        $params = array(
            'an'        => $an,
            'dbid'      => $db,
            'highlightterms' => urlencode($term) // Get currect param name
        );
        $params = http_build_query($params);
        $result = $this->request('Retrieve', $params);
        return $result;
    }


    /**
     * Wrapper for info API call
     *
     * @return array              An associative array of data
     * @access public
     */
    public function getInfo()
    {
        if(isset($_SESSION['info'])){
            $InfoArray = $_SESSION['info'];
            $timestamp = $InfoArray['timestamp'];
            if(time()-$timestamp>=3600){        
                // Get new Info for the profile
                $InfoArray = $this->apiInfo();
                $_SESSION['info'] = $InfoArray;
                $info = $InfoArray['Info'];           
            }else{
                $info = $InfoArray['Info'];
            }
        }else{              
            // Get new Info for the profile
            $InfoArray = $this->apiInfo();
            $_SESSION['info'] = $InfoArray;
            $info = $InfoArray['Info'];          
        }
        return $info;
    }
    
    public function apiInfo(){
        
        $response = $this->request('Info','');
        $Info = array(
            'Info' => $response,
            'timestamp'=>time()
        ); 
        return $Info;
    }
}
?>