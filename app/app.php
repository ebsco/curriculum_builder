<?php

if(!isset($_SESSION)) 
{ 
    session_start(); 
} 
require_once("connect.php");
require_once("app/OAuth.php");
require_once("conf/keys.php");

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

// Return the root directory relative to current PHP file
// which is /app
function root() {
     return dirname(__FILE__) . '/';
}

// Render a template
function render_template($locals, $fileName) {
    extract($locals);
    ob_start();
    include(root() . 'views/' . $fileName . '.php');
    return ob_get_clean();
}

// Render a view
function ebsco_render($fileName, $templateName, $variableArray=array()) {
    $variableArray['content'] = render_template($variableArray, $fileName);
    print render_template($variableArray, $templateName);
}

// A basic pagination that displays maximum 10 pages
function paginate($recordCount, $limit, $page, $searchTerm, $fieldCode, $backpath) {
    $backpath = urlencode($backpath);
    $output = '';
    $linkCount =ceil($recordCount/$limit);
    if (!empty($page)) {
        if($page>$linkCount){
            $page = $linkCount;
        }   
    } else {
        $page = 1;
    }
    $base_url = "pageOptions.php?$searchTerm&fieldcode=$fieldCode&backpath=$backpath";
    
    if($page%10 != 0){
    $f = floor($page/10);
    }
    else {
    $f=floor($page/10)-1;
    }
    $s = $page-1;
    if ($linkCount >= 1) {
        $output = '<p>';
        if($s>0){
                $output .= "<a href=\"{$base_url}&pagenumber=GoToPage({$s})\"><span class='results-paging-previous'>&nbsp;&nbsp;&nbsp;&nbsp;</span></a>";
            }
    if($f < floor($linkCount/10)){
        for ($i = $f*10; $i < $f*10+10; $i++) {
            $p = $i+1;                     
            if ($p != $page) {
                $output .= "<a href=\"{$base_url}&pagenumber=GoToPage({$p})\"><u>{$p}</u></a>";
            } else {
                $output .= '<strong>'.$p.'</strong>';
            }          
        }
    }else{
        for ($i = $f*10; $i < $linkCount; $i++) {
            $p = $i+1;                    
            if ($p != $page) {
                $output .= "<a href=\"{$base_url}&pagenumber=GoToPage({$p})\">{$p}</a>";
            } else {
                $output .= $p;
            }        
        }
    }   $p_1 = $page+1;
     if($p_1 <= $linkCount){
        $output .= "<a href=\"{$base_url}&pagenumber=GoToPage({$p_1})\"><span class='results-paging-next'>&nbsp;&nbsp;&nbsp;&nbsp;</span></a>";
     }
        $output .= '<br class="clear" /></p>';
    }
    return $output;
}

function getLTIlinkID () {
     return htmlentities(decryptCookie($_COOKIE["resource_link_id"]));
}

function getConsumerID () {
     return htmlentities(decryptCookie($_COOKIE["tool_consumer_instance_guid"]));
}

function getCredentialConsumerID () {
     return htmlentities(decryptCookie($_COOKIE['credential_consumer_id']));
}

function getCourseLabel () {
     return htmlentities(decryptCookie($_COOKIE["context_label"]));
}

function getEmail () {
     return decryptCookie($_COOKIE["lis_person_contact_email_primary"]);
}

function course_link_id () {
     $courseid = getConsumerID() . "-" . getCourseLabel() . "-" . getLTIlinkID() . "-" . getEmail();
     return $courseid;
}

function getFolderItems($c) {

	$sql = 'SELECT an, db FROM readings WHERE listid = ?';
        $stmt = $c->prepare($sql);
        $currentListId = decryptCookie($_COOKIE['currentListId']);
        $stmt->bind_param('i',$currentListId);
        $stmt->execute();
        $folderitems = $stmt->get_result();
        
        if ($folderitems) {
          $numFolderItems = mysqli_num_rows($folderitems);
          if ($numFolderItems > 0) {
                  for ($i = 0; $i < $numFolderItems; $i++) {
                          $folderitemsarray[$i] = mysqli_fetch_array($folderitems);
                  }
          } else {
               $folderitemsarray = new stdClass();               
          }
        } else {
               $folderitemsarray = new stdClass();
        }
	return $folderitemsarray;
} 

function itemInFolder($folderitems = array(),$an=0,$db=0) {
  $found = 0;
  if (count($folderitems) > 0) {
       foreach ($folderitems as $row) {
         if (($row['an'] == $an) && ($row['db'] == $db)) {
           $found = 1;
         }
       }
  }
  return $found;
}

function getReadingListPreview ($c,$listid) {

     $sql = 'SELECT id, an, db, title, url, type, priority, instruct, notes FROM readings WHERE listid = ? ORDER BY priority, title;';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('i',$listid);
    $stmt->execute();
    $folderitems = $stmt->get_result();

     if ($folderitems) {
          $numFolderItems = mysqli_num_rows($folderitems);
          if ($numFolderItems > 0) {
                  for ($i = 0; $i < $numFolderItems; $i++) {
                          $folderitemsarray[$i] = mysqli_fetch_array($folderitems);
                  }
          } else {
               $folderitemsarray = new stdClass();
          }
          return $folderitemsarray;
     }
     $folderitemsarray = new stdClass();
     return $folderitemsarray;    
}

function getReadingListPreviewMetadata ($c,$listid) {
    $sql = 'SELECT id, linklabel, course, last_access FROM lists WHERE id = ?';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('i',$listid);
    $stmt->execute();
    $results = $stmt->get_result();
    
    if (mysqli_num_rows($results) > 0) {
        $info = mysqli_fetch_array($results);
        return $info;
    } else {
        $emptyArray = array();
        return $emptyArray;
    }
}

function getReadingList ($c) {
    $sql = 'SELECT id, an, db, title, url, type, priority, instruct, notes, folderid FROM readings WHERE listid = ? AND folderid IS NULL ORDER BY priority, title;';
    $stmt = $c->prepare($sql);
    
    $listid = decryptCookie($_COOKIE['currentListId']);
    $stmt->bind_param('i',$listid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
    
     if ($folderitems) {
          $numFolderItems = mysqli_num_rows($folderitems);
          if ($numFolderItems > 0) {
                  for ($i = 0; $i < $numFolderItems; $i++) {
                          $folderitemsarray[$i] = mysqli_fetch_array($folderitems);
                  }
          } else {
               $folderitemsarray = new stdClass();
          }
          return $folderitemsarray;
     }
     $folderitemsarray = new stdClass();
     return $folderitemsarray;
}

function getOtherReadingList ($c,$targetlistid) {
     $sql = 'SELECT id, an, db, title, url, type, priority, instruct, notes FROM readings WHERE listid = ? ORDER BY priority, title;';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('i',$targetlistid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
    
     $numFolderItems = mysqli_num_rows($folderitems);
     if ($numFolderItems > 0) {
             for ($i = 0; $i < $numFolderItems; $i++) {
                     $folderitemsarray[$i] = mysqli_fetch_array($folderitems);
             }
     } else {
          $folderitemsarray = new stdClass();
     }
     return $folderitemsarray;  
}

function getListPrivacy ($c) {
    $listid = decryptCookie($_COOKIE['currentListId']);
     $sql = 'SELECT private FROM lists WHERE id = ?';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('i',$listid);
    $stmt->execute();
    $results = $stmt->get_result();

     if ($results) {
          $row = mysqli_fetch_array($results);
          return $row['private'];
     }
     return 0;
}

function setListPrivacy ($c,$privacy) {
    $listid = decryptCookie($_COOKIE['currentListId']);

     $privacy = (integer)$privacy;
     if (is_int($privacy)) {
        $sql = 'UPDATE lists SET private = ? WHERE id = ?';
        $stmt = $c->prepare($sql);
        $stmt->bind_param('ii',$privacy,$listid);
        $stmt->execute();
     }
}

function setReadingPriority ($c,$readingId,$priority) {
    $priority = (integer)$priority;
    $readingId = (integer)$readingId;
    if (is_int($priority) && is_int($readingId)) {
        $sql = 'UPDATE readings SET priority = ? WHERE id = ?';
        $stmt = $c->prepare($sql);
        $stmt->bind_param('ii',$priority,$readingId);
        $stmt->execute();
    }
}

function copyList ($c,$from,$to) {
    $fromint = (integer)$from;
    $from = (string)$fromint;
    $toint = (integer)$to;
    $to = (string)$toint;
    $sql = "SELECT * FROM readings WHERE listid = ?";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('i',$from);
    $stmt->execute();
    $results = $stmt->get_result();
    $currentAuthorId = decryptCookie($_COOKIE['currentAuthorId']);
    
    while ($row = mysqli_fetch_array($results)) {
        if (isset($_COOKIE['currentAuthorId'])) {
            $sqlr = "INSERT INTO readings (listid,authorid,an,db,title,priority,notes,url,type,instruct) VALUES (?,?,?,?,?,?,?,?,?,?);";
            $stmt = $c->prepare($sqlr);
            $stmt->bind_param('iisssissis',$to,$currentAuthorId,$row['an'],$row['db'],$row['title'],$row['priority'],$row['notes'],$row['url'],$row['type'],$row['instruct']);
        } else {
            $sqlr = "INSERT INTO readings (listid,authorid,an,db,title,priority,notes,url,type,instruct) VALUES (?,?,?,?,?,?,?,?,?,?);";            
            $stmt = $c->prepare($sqlr);
            $stmt->bind_param('iisssissis',$to,$row['authorid'],$row['an'],$row['db'],$row['title'],$row['priority'],$row['notes'],$row['url'],$row['type'],$row['instruct']);
        }
        $stmt->execute();
    }
}

function getLinkLabel () {
     return htmlentities(decryptCookie($_COOKIE["resource_link_title"]));
}

function getFullName () {
     return htmlentities(decryptCookie($_COOKIE["lis_person_name_full"]));
}

function loadCustomParams() {
    if (func_get_arg(1) == '') {
        die("You can only access reading lists from within a course.");
    }
    $c = func_get_arg(0);
    $oauth_consumer_key = func_get_arg(1);
    
    $sql = 'SELECT libemail, libname, liblogo, liblink, profile, userid, password, studentdata, EDSlabel, copyright, copylist, css, forceft, courselink, quicklaunch, newwindow, firstftonly, helppages, searchlabel, proxyprefix, proxyencode FROM oauth WHERE oauth_consumer_key = ?';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('s',$oauth_consumer_key);
    $stmt->execute();

    $results = $stmt->get_result();
    
    if (mysqli_num_rows($results) > 0) {
        $row = mysqli_fetch_array($results);
        if (($row['profile'] != '') && ($row['userid'] != '') && ($row['password'] != '')) {
            return $row;
        }
    } else {
        die("Invalid consumer key (set to ".var_export(func_get_arg(1),TRUE)." ). Please check your LTI configuration.  Error a1.");
    }
    if (func_num_args() > 2) {
        $postarray = func_get_arg(2);
        if ((isset($postarray['custom_custid'])) && (isset($postarray['custom_password'])) && (isset($postarray['custom_profile']))) {
            $sql = 'UPDATE oauth SET libemail = ?, libname = ?, liblogo = ?, liblink = ?, profile = ?, userid = ?, password = ? WHERE oauth_consumer_key = ?';

            $stmt = $c->prepare($sql);
            $stmt->bind_param('ssssssss',$postarray['custom_libemail'],$postarray['custom_libname'],$postarray['custom_liblogo'],$postarray['custom_liblink'],$postarray['custom_profile'],$postarray['custom_custid'],$postarray['custom_password'],$oauth_consumer_key);
            $stmt->execute();

            $sql = 'SELECT libemail, libname, liblogo, liblink, profile, userid, password, studentdata FROM oauth WHERE oauth_consumer_key = ?';
            $stmt = $c->prepare($sql);
            $stmt->bind_param('s',$oauth_consumer_key);
            $stmt->execute();
            $results = $stmt->get_result();

            if (mysqli_num_rows($results) > 0) {
                $row = mysqli_fetch_array($results);
                if (($row['profile'] != '') && ($row['userid'] != '') && ($row['password'] != '')) {
                    return $row;
                } else {
                    die ("Error writing to database.  Please try again, and if the problem persists, please contact EBSCO Customer Support at support@ebscohost.com with the following error code: a3.");
                }
            } else {
                die("Error a2.");
            }        
        } else {
            die('Your EDS API credentials have not been set.  Please log in to <a href="http://curriculumbuilder.ebscohost.com/admin.php">the admin panel</a> to set these values.');
        }
    } else if (isset($_COOKIE['forward_to_admin']) && decryptCookie($_COOKIE['forward_to_admin']) == "y") {
        return $row;
    } else {
        die("Invalid consumer key.  Please check your LTI configuration.  Error a3.");
    }
}

function isInstructor () {
     if (isset($_COOKIE['roles'])) {
        if (substr_count(strtolower(decryptCookie($_COOKIE['roles'])),"instructor") > 0)
             return true;
     }
     return false;
}

function strip_tags_deep($value)
{
  return is_array($value) ? array_map('strip_tags_deep', $value) : htmlspecialchars(strip_tags(str_ireplace("javascript:","",$value)), ENT_NOQUOTES);
}

function strip_htmlentities($value)
{
  return is_array($value) ? array_map('strip_htmlentities', $value) : html_entity_decode($value,ENT_NOQUOTES);
}

function strip_tags_for_display($value)
{
  return is_array($value) ? array_map('strip_tags_for_display', $value) :  str_replace(array('\'', '"','&',';'), '', html_entity_decode($value,ENT_NOQUOTES|ENT_QUOTES));
}

function replace_quotes($value)
{
  return is_array($value) ? array_map('replace_quotes', $value) : str_replace('"', '\"', $value);   
}

class TrivialOAuthDataStore extends OAuthDataStore {
    private $consumers = array();

    function add_consumer($consumer_key, $consumer_secret) {
        $this->consumers[$consumer_key] = $consumer_secret;
    }

    function lookup_consumer($consumer_key) {
        if ( strpos($consumer_key, "http://" ) === 0 ) {
            $consumer = new OAuthConsumer($consumer_key,"secret", NULL);
            return $consumer;
        }
        if ( $this->consumers[$consumer_key] ) {
            $consumer = new OAuthConsumer($consumer_key,$this->consumers[$consumer_key], NULL);
            return $consumer;
        }
        return NULL;
    }

    function lookup_token($consumer, $token_type, $token) {
        return new OAuthToken($consumer, "");
    }

    // Return NULL if the nonce has not been used
    // Return $nonce if the nonce was previously used
    function lookup_nonce($consumer, $token, $nonce, $timestamp) {
        // Should add some clever logic to keep nonces from
        // being reused - for no we are really trusting
	// that the timestamp will save us
        return NULL;
    }

    function new_request_token($consumer) {
        return NULL;
    }

    function new_access_token($token, $consumer) {
        return NULL;
    }
}

function recordStudentAccess($c,$name,$email,$listid) {

    $sql = "SELECT * FROM studentaccess WHERE name = ? AND email = ? AND listid = ?";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ssi',$name,$email,$listid);
    $stmt->execute();
    $results = $stmt->get_result();

    if (mysqli_num_rows($results) == 0) {
        $sql = "INSERT INTO studentaccess (name,email,listid) VALUES (?,?,?);";
        $stmt = $c->prepare($sql);
        $stmt->bind_param('ssi',$name,$email,$listid);
        $stmt->execute();
    }
}

function recordStudentReading($c,$name,$email,$readingid) {
    $sql = "SELECT * FROM studentreading WHERE name = ? AND email = ? AND readingid = ?";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ssi',$name,$email,$readingid);
    $stmt->execute();
    $results = $stmt->get_result();
    
    if (mysqli_num_rows($results) == 0) {
        $sql = "INSERT INTO studentreading (name,email,readingid) VALUES (?,?,?);";
        $stmt = $c->prepare($sql);
        $stmt->bind_param('ssi',$name,$email,$readingid);
        $stmt->execute();
    }    
}

function getStudentNamesReadings($c,$readingid) {
    $sql = "SELECT name FROM studentreading WHERE readingid = ?";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('i',$readingid);
    $stmt->execute();
    $results = $stmt->get_result();
    
    $students = array();
    if (mysqli_num_rows($results) > 0) {
        while ($row = mysqli_fetch_array($results)) {
            $students[] = $row["name"];
        }
    }
    return $students;
}

function getStudentNamesList($c,$listid) {
    $sql = "SELECT name FROM studentaccess WHERE listid = ?";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('i',$listid);
    $stmt->execute();
    $results = $stmt->get_result();
    
    $students = array();
    if (mysqli_num_rows($results) > 0) {
        while ($row = mysqli_fetch_array($results)) {
            $students[] = $row["name"];
        }
    }
    return $students;
}

function add_to_folder($c,$readingid,$folderid) {
    $sql = "UPDATE readings SET folderid = ? WHERE id = ?";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ii',$folderid,$readingid);
    $stmt->execute();
}

function getFolderContents($c,$folderid) {
    $sql = 'SELECT id, an, db, title, url, type, priority, instruct, notes, folderid FROM readings WHERE listid = ? AND folderid = ? ORDER BY priority, title;';
    $stmt = $c->prepare($sql);
    
    $listid = decryptCookie($_COOKIE['currentListId']);
    $stmt->bind_param('ii',$listid,$folderid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
    
     if ($folderitems) {
          $numFolderItems = mysqli_num_rows($folderitems);
          if ($numFolderItems > 0) {
                  for ($i = 0; $i < $numFolderItems; $i++) {
                          $folderitemsarray[$i] = mysqli_fetch_array($folderitems);
                  }
          } else {
               $folderitemsarray = new stdClass();
          }
          return $folderitemsarray;
     }
     $folderitemsarray = new stdClass();
     return $folderitemsarray;   
}

function check_reading_in_list($c,$readingid,$listid) {
    $sql = 'SELECT id FROM readings WHERE id = ? AND listid = ?;';
    $stmt = $c->prepare($sql);
    
    $stmt->bind_param('ii',$readingid,$listid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
    
    if ($folderitems) {
        $numFolderItems = mysqli_num_rows($folderitems);
        if ($numFolderItems > 0) {
            return true;
        }
    }
    return false;
}

function getFolderList($c) {
    $sql = 'SELECT id, label, sortorder FROM folders WHERE listid = ? ORDER BY sortorder ASC, label;';
    $stmt = $c->prepare($sql);
    
    $listid = decryptCookie($_COOKIE['currentListId']);
    $stmt->bind_param('i',$listid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
    
     if ($folderitems) {
          $numFolderItems = mysqli_num_rows($folderitems);
          if ($numFolderItems > 0) {
                  for ($i = 0; $i < $numFolderItems; $i++) {
                          $folderitemsarray[$i] = mysqli_fetch_array($folderitems);
                  }
          } else {
               $folderitemsarray = array();
          }
          return $folderitemsarray;
     }
     $folderitemsarray = array();
     return $folderitemsarray;   
}

function add_new_folder($c,$listid,$label) {
    $sql = 'INSERT INTO folders (label,listid) VALUES (?,?);';
    $stmt = $c->prepare($sql);
    
    $stmt->bind_param('si',$label,$listid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
}

function folderitemcount($c,$folderid) {
    $sql = 'SELECT id FROM readings WHERE folderid = ?;';
    $stmt = $c->prepare($sql);
    
    $stmt->bind_param('i',$folderid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
    
     if ($folderitems) {
          $numFolderItems = mysqli_num_rows($folderitems);
            return $numFolderItems;
     } else {
        return 0;
     }
}

function delete_folder($c,$folderid) {
    $sql = 'DELETE FROM readings WHERE folderid = ?;';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('i',$folderid);
    $stmt->execute();
    $sql = 'DELETE FROM folders WHERE id = ?;';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('i',$folderid);
    $stmt->execute();
}

function folderExists($c,$folderid) {
    $sql = 'SELECT id FROM folders WHERE id = ?;';
    $stmt = $c->prepare($sql);
    
    $stmt->bind_param('i',$folderid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
    
     if ($folderitems) {
          $numFolderItems = mysqli_num_rows($folderitems);
          if ($numFolderItems > 0) {
            return true;
          } else {
            return false;
          }
     } else {
        return false;
     }  
}

function export_readings($c,$credentialconsumerid) {
    $sql = 'SELECT lists.course, lists.linklabel, readings.an, readings.db, readings.title FROM lists,readings WHERE readings.listid = lists.id AND readings.type=1 AND lists.credentialconsumerid = ?;';
    $stmt = $c->prepare($sql);
    
    $stmt->bind_param('i',$credentialconsumerid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
    
     if ($folderitems) {
          $numFolderItems = mysqli_num_rows($folderitems);
          if ($numFolderItems > 0) {
            return $folderitems;
          } else {
            return false;
          }
     } else {
        return false;
     }  
}

function export_all_sql($c,$credentialconsumerid) {
    $sql = 'SELECT authors.fullname AS authors_fullname, authors.email AS authors_emails, authors.lms_id AS authors_lmsid, readings.id AS readings_id, readings.an AS readings_an, readings.db AS readings_db, readings.title AS readings_title, readings.priority AS readings_priority, readings.notes AS readings_notes, readings.url AS readings_url, readings.type AS readings_type, readings.instruct AS readings_instruct, readings.folderid AS readings_folderid, folders.label AS folders_label, lists.linklabel AS lists_linklabel, lists.course AS lists_course, lists.linkid AS lists_linkid, lists.private AS lists_private, lists.last_access AS lists_last_access, credentials.userid AS credentials_userid, credentials.password AS credentials_password, credentials.profile AS credentials_profile, credentialconsumers.`credentialid` AS credentialconsumers_credentialid, credentialconsumers.consumerid AS credentialconsumers_consumerid FROM readings INNER JOIN lists ON readings.listid = lists.id INNER JOIN credentialconsumers ON credentialconsumers.id = lists.credentialconsumerid INNER JOIN credentials ON credentials.id = credentialconsumers.credentialid INNER JOIN authors ON authors.id = readings.authorid LEFT OUTER JOIN folders ON folders.id = readings.folderid WHERE credentials.id=?;';
    $stmt = $c->prepare($sql);
    
    $stmt->bind_param('i',$credentialconsumerid);
    $stmt->execute();
    $folderitems = $stmt->get_result();
    
     if ($folderitems) {
          $numFolderItems = mysqli_num_rows($folderitems);
          if ($numFolderItems > 0) {
            return $folderitems;
          } else {
            return false;
          }
     } else {
        return false;
     }  
}

function fixprotocol ($url) {
    return preg_replace('/http:/', '', $url, 1);    
}

function textinbrief ($text,$charcount) {
    $pos=strpos($text, ' ', $charcount);
    return substr($text,0,$pos ); 
}

function setFolderName($c,$folderid,$value) {
    $sql = 'UPDATE folders SET label = ? WHERE id = ?';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('si',$value,$folderid);
    $stmt->execute();
}

function setFolderOrder($c,$folderid,$value) {
    $sql = 'UPDATE folders SET sortorder = ? WHERE id = ?';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ii',$value,$folderid);
    $stmt->execute();
}

function processProxy ($url, $proxyprefix, $proxyencode) {
    if ((isset($proxyprefix)) && (strlen($proxyprefix) > 0)) {
        if (substr_count($proxyprefix,"{targetURLdomain}")) {
            $targetURL = parse_url($url);
            if (isset($targetURL['query']) && (strlen($targetURL['query']) > 0)) {                
                $path = $targetURL['path']."?".$targetURL['query'];
            } else {
                $path = $targetURL['path'];
            }

            if ($proxyencode == "y") {
                return str_replace("{targetURLdomain}",$targetURL['host'],$proxyprefix) . urlencode($path);
            } else {
                return str_replace("{targetURLdomain}",$targetURL['host'],$proxyprefix) . $path;
            }
        } else {
            if ($proxyencode == "y") {
                return trim($proxyprefix) . urlencode($url);
            } else {
                return trim($proxyprefix) . $url;
            }
        }
    } else {
        return $url;
    }
}

function parseQueryString ($str) {
  # result array
  $arr = array();

  # split on outer delimiter
  $pairs = explode('&', $str);

  # loop through each pair
  foreach ($pairs as $i) {
    # split into name and value
    list($name,$value) = explode('=', $i, 2);
   
    # if name already exists
    if( isset($arr[$name]) ) {
      # stick multiple values into an array
      if( is_array($arr[$name]) ) {
        $arr[$name][] = $value;
      }
      else {
        $arr[$name] = array($arr[$name], $value);
      }
    }
    # otherwise, simply stick it in a scalar
    else {
      $arr[$name] = $value;
    }
  }

  $explimqs = '';
  
  if (isset($arr['expander'])) {
    if (is_array($arr['expander'])) {
        foreach($arr['expander'] as $expander) {
            $explimqs .= "&expander[]=".$expander;
        }
    } else {
        $explimqs .= "&expander=".$arr['expander'];
    }    
  }
  
  if (isset($arr['limiter'])) {
    if (is_array($arr['limiter'])) {
        foreach($arr['limiter'] as $limiter) {
            $explimqs .= "&limiter[]=".$limiter;
        }
    } else {
        $explimqs .= "&limiter=".$arr['limiter'];
    }
  }
  # return result array
  
  if (isset($arr['view'])) {
    $explimqs .= "&view=".$arr['view'];
  }
  
  if (isset($arr['resultsperpage'])) {
    $explimqs .= "&resultsperpage=".$arr['resultsperpage'];
  }
  
  if (isset($arr['highlight'])) {
    $explimqs .= "&highligh=".$arr['highlight'];
  }
  return $explimqs;

}

?>