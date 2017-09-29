<?php
    ini_set("session.cookie_httponly", 1);
    session_start();
    
    include_once('app/app.php');
    
    $clean = strip_tags_deep($_POST);
    $time = 0; // store for session only //store cookie for one hour
    
    // d2l stores the consumer_instance_guid in a different place than other LMS
    // this moves the data into the expected place
    if (isset($clean['tool_consumer_info_product_family_code'])) {
        if ($clean['tool_consumer_info_product_family_code'] == "desire2learn") {
            $clean['tool_consumer_instance_guid'] = $clean['ext_tc_profile_url'];
        }
    }

    // some Sakai instances do not specify a tool_consumer_instance_guid
    if (!isset($clean['tool_consumer_instance_guid'])) {
        $clean['tool_consumer_instance_guid'] = 'noGUID' . $clean['oauth_consumer_key'];
    }
    
    if (isset($clean['user_id'])) {
        if (!(isset($clean['lis_person_contact_email_primary']))) {
            $clean['lis_person_contact_email_primary'] = $clean['tool_consumer_instance_guid'].".".$clean['user_id']."-noEmailShared";
        }
        if (!(isset($clean['lis_person_name_full']))) {
            $clean['lis_person_name_full'] = $clean['tool_consumer_instance_guid'].".".$clean['user_id']."-noNameShared";
        }
        $clean['user_id'] = $clean['tool_consumer_instance_guid']."-".$clean['user_id'];
    }
    
    // upon new launch, eliminate any existing session tokens to prevent bad API calls from old sessions    
    if (isset($_COOKIE['sessionToken'])) {
        unset($_COOKIE['sessionToken']);
    }

    // legacy instructions had labeled the userid/password as custid/password - this allows both
    if (isset($clean['custom_ebsco_userid'])) {
        $clean['custom_custid'] = $clean['custom_ebsco_userid'];
    }
    if (isset($clean['custom_ebsco_password'])) {
        $clean['custom_password'] = $clean['custom_ebsco_password'];
    }
    
    if (isset($clean['list_history'])) {
        $clean['custom_list_history'] = $clean['list_history'];
    }
    
    $oauth_consumer_key = $clean['oauth_consumer_key'];
    // this loads in custom settings, such as email, api credentials, logo, etc.  
    $customparams = loadCustomParams($c,$oauth_consumer_key,$clean);

    // whitelist of accepted parameters
    $accepted = array('user_id', 'oauth_consumer_key', 'roles', 'context_label', 'context_title', 'lis_person_name_full', 'lis_person_contact_email_primary', 'resource_link_title', 'resource_link_id', 'tool_consumer_instance_guid', 'launch_presentation_return_url', 'link', 'custom_link');

    if (isset($clean['custom_link'])) {
        $clean['link'] = $clean['custom_link'];
    }
    
    // accomodate other roles
    
    if (strlen($customparams['empowered_roles']) > 0) {
        $acceptedRoles = explode(",",$customparams['empowered_roles']);
        foreach($acceptedRoles as $role) {
            if (substr_count(strtolower($clean['roles']),trim(strtolower($role))) > 0) {
                $clean['roles'] = "urn:lti:instrole:ims/lis/Instructor";
            }
        }
    } else {
        if (!((substr_count($clean['roles'],"Instructor") > 0))) {
            if (substr_count($clean['roles'],"TeachingAssistant") > 0) {
                $clean['roles'] = "urn:lti:instrole:ims/lis/Instructor";
            }
        }
    }
    
    if (isset($clean['link'])) {
        $clean['roles'] = 'student (overriden by link)';
        // if link is specified, check to see it if exists.
        $sql = 'SELECT id, linklabel, course FROM lists WHERE credentialconsumerid = ? AND linkid = ?';
        $stmt = $c->prepare($sql);
        $stmt->bind_param('ss',$clean['credential_consumer_id'],$clean['link']);
        $stmt->execute();
        $foundList = $stmt->get_result();
        
        if ($foundList) {

            if (mysqli_num_rows($foundList) <= 0) {
                $clean['resource_link_id'] = $clean['link'];
            }
        }
    }
    
    // transfer variables to session    
    foreach ( $clean as $foo=>$bar ) {
        if ( in_array( $foo, $accepted ) && !empty($bar) ) {
            $encryptedC = encryptCookie($bar);
            setcookie($foo,$encryptedC,$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
        }
    }

    $sql = 'SELECT * FROM credentials WHERE '.
    'userid = ? AND '.
    'profile = ? AND '.
    'password = ?';
    
    /* Prepare statement */
    $stmt = $c->prepare($sql);
    if($stmt === false) {
      trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->errno . ' ' . $conn->error, E_USER_ERROR);
    }

    $_SESSION['debug'] = "<p>Credentials: ".$customparams['userid']." / ".$customparams['profile']." / ".$customparams['password']."</p>";
    /* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('sss',$customparams['userid'],$customparams['profile'],$customparams['password']);
     
    /* Execute statement */
    $stmt->execute();
    
    /* Fetch result to array */
    $credentialsresults = $stmt->get_result();
    
    if ($credentialsresults) {
        if (mysqli_num_rows($credentialsresults) <= 0) {

            $sql = "INSERT INTO credentials (userid, profile, password) VALUES (?,?,?);";
            $stmt = $c->prepare($sql);    
            $stmt->bind_param('sss',$customparams['userid'],$customparams['profile'],$customparams['password']);
            $stmt->execute();

            $sql = "SELECT id FROM credentials WHERE userid = ? AND profile = ? AND password = ?;";
            $stmt = $c->prepare($sql);
            $stmt->bind_param('sss',$customparams['userid'],$customparams['profile'],$customparams['password']);
            $stmt->execute();
            $credentialsresults = $stmt->get_result();
        }
        $row = mysqli_fetch_array($credentialsresults);
        $_SESSION['debug'] .= "<p>MySQL Resp: ".var_export($row,TRUE)."</p>";
        setcookie('credential_id', encryptCookie($row['id']),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
        $clean['credential_id'] = $row['id'];
    } else {
        die("It looks like the application was unable to connect to your MySQL server, or had trouble looking for the reading list.  Here is the MySQL error: (1)");
    }
        
    $sql = 'SELECT id FROM credentialconsumers WHERE credentialid = ? AND consumerid = ?';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ss',$clean['credential_id'],$clean['tool_consumer_instance_guid']);
    $stmt->execute();
    $credconsumerresults = $stmt->get_result();
    
    if ($credconsumerresults) {
        if (mysqli_num_rows($credconsumerresults) <= 0) {
            $sql = 'INSERT INTO credentialconsumers (credentialid,consumerid) VALUES (?,?)';
            $stmt = $c->prepare($sql);
            $stmt->bind_param('ss',$clean['credential_id'],$clean['tool_consumer_instance_guid']);
            $stmt->execute();

            $sql = 'SELECT id FROM credentialconsumers WHERE credentialid = ? AND consumerid = ?';
            $stmt = $c->prepare($sql);
            $stmt->bind_param('ss',$clean['credential_id'],$clean['tool_consumer_instance_guid']);
            $stmt->execute();
            $credconsumerresults = $stmt->get_result();
        }
        $row = mysqli_fetch_array($credconsumerresults);
        setcookie('credential_consumer_id', encryptCookie($row['id']),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
        $clean['credential_consumer_id'] = $row['id'];
    } else {
        die("It looks like the application was unable to connect to your MySQL server, or had trouble looking for the reading list. Here is the MySQL error: 2");     
    }
    
    // look to see if this list already exists
    $sql = 'SELECT id, linklabel, course FROM lists WHERE credentialconsumerid = ? AND linkid = ?';
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ss',$clean['credential_consumer_id'],$clean['resource_link_id']);
    $stmt->execute();
    $foundList = $stmt->get_result();
    $copy_target = "none";    
    $newlist = FALSE;
    if ($foundList) {
        // if the list was not found, make a new one and load it into variable foundList
        // or if custom_list_history is set, base this new list off of prior list
        
        if (mysqli_num_rows($foundList) <= 0) {

            if ((isset($clean['custom_list_history'])) && (strlen($clean['custom_list_history']) > 0)) {
                $listhistory = explode(",",$clean['custom_list_history']);
                $copy_target = $listhistory[0];
            }
            if (!((substr_count($clean['roles'],"Instructor") > 0))) {
                die("<p>Uh oh!  It looks like your course instructor hasn't added any readings to this list yet.</p><p>If you are a course instructor, it looks as if your account does not indicate that you are.  Your current role appears to be: ".$clean['roles']."<br/><br/>".$sql." with ".$clean['credential_consumer_id']."-".$clean['resource_link_id']."</p>");
            }

            $sql = 'INSERT INTO lists (linklabel, course, credentialconsumerid, linkid, private) VALUES (?,?,?,?,1)';
            $stmt = $c->prepare($sql);
            $stmt->bind_param('ssss',$clean['resource_link_title'],$clean['context_label'],$clean['credential_consumer_id'],$clean['resource_link_id']);
            $stmt->execute();

            $sql = 'SELECT id, linklabel, course FROM lists WHERE credentialconsumerid = ? AND linkid = ?';
            $stmt = $c->prepare($sql);
            $stmt->bind_param('ss',$clean['credential_consumer_id'],$clean['resource_link_id']);
            $stmt->execute();
            $foundList = $stmt->get_result();

            $newlist = TRUE;
        } 
    } else {
        die("It looks like the application was unable to connect to your MySQL server, or had trouble looking for the reading list.  Here is the MySQL error: 3");
    }

    // load foundlist into ROW variable    
    $row = mysqli_fetch_array($foundList);

    // update 'last access' timestamp
    $sqlUpdateTimestamp = 'UPDATE lists SET last_access=now() WHERE id = ?';
    $stmt = $c->prepare($sqlUpdateTimestamp);
    $stmt->bind_param('i',$row['id']);
    $stmt->execute();
    
    if (!(isset($clean['link']))) {
        // update the name of the list if the label of the link in the LMS changed
        if ($row['linklabel'] != $clean['resource_link_title']) {
            $sqlFixLabel = 'UPDATE lists SET linklabel = ? WHERE id = ?';
            $stmt = $c->prepare($sqlFixLabel);
            $stmt->bind_param('si',$clean['resource_link_title'],$row['id']);
            $stmt->execute();
        }
    }
    
    $currentListId = $row['id'];
    // place the id of the current list into the session    
    setcookie('currentListId', encryptCookie($row['id']), $time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
    setcookie('currentLinkId', encryptCookie($clean['resource_link_id']), $time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);

    if ((substr_count($clean['roles'],"Instructor") > 0)) {
        $sql = 'SELECT id, fullname, email, lms_id FROM authors WHERE id IN (SELECT authorid FROM authorlists WHERE listid = ?) AND lms_id = ?';
        $stmt = $c->prepare($sql);
        $stmt->bind_param('is',$row['id'],$clean['user_id']);
        $stmt->execute();
        $authorresults = $stmt->get_result();

        $newauthor = FALSE;
        $added = FALSE;
        
        if ($authorresults) {
            // if the person accessing the list is a course instructor AND is NOT associated with the list...
            if (mysqli_num_rows($authorresults) <= 0) {
                $sql = "SELECT id, fullname, email, lms_id FROM authors WHERE lms_id = ?;";
                $stmt = $c->prepare($sql);
                $stmt->bind_param('s',$clean['user_id']);
                $stmt->execute();
                $authorExists = $stmt->get_result();
                        
                if ($authorExists) {
                    if (mysqli_num_rows($authorExists) <= 0) {
                        
                        $sql = "SELECT id, fullname, email, lms_id FROM authors WHERE email = ? AND lms_id IS NULL;";
                        $stmt = $c->prepare($sql);
                        $stmt->bind_param('s',$clean['lis_person_contact_email_primary']);
                        $stmt->execute();
                        $authorUIDExists = $stmt->get_result();
                        
                        if ($authorUIDExists) {

                            if (mysqli_num_rows($authorUIDExists) > 0) {

                                $foundAuthor = mysqli_fetch_array($authorUIDExists);
                                $authorID = $foundAuthor['id'];

                                $sql = 'UPDATE authors SET lms_id = ? WHERE id = ?';
                                $stmt = $c->prepare($sql);
                                $stmt->bind_param('si',$clean['user_id'],$authorID);
                                $stmt->execute();                                
                            } else {

                                $sql = 'INSERT INTO authors (fullname, email, lms_id) VALUES (?,?, ?);';
                                $stmt = $c->prepare($sql);
                                $stmt->bind_param('sss',$clean['lis_person_name_full'],$clean['lis_person_contact_email_primary'],$clean['user_id']);
                                $stmt->execute();
                                
                                $sql = 'SELECT id FROM authors WHERE lms_id = ?';
                                $stmt = $c->prepare($sql);
                                $stmt->bind_param('s',$clean['user_id']);
                                $stmt->execute();
                                $gettingAuthorID = $stmt->get_result();                        
                                                        
                                $gettingAuthorIDrow = mysqli_fetch_array($gettingAuthorID);
                                $authorID = $gettingAuthorIDrow['id'];
                                
                                $newauthor = TRUE;                                
                            }
                        }

                    } else {
                        $foundAuthor = mysqli_fetch_array($authorExists);
                        $authorID = $foundAuthor['id'];
                    }
                } else {
                    die("It looks like the application was unable to connect to your MySQL server, or had trouble looking for the reading list.  Here is the MySQL error: 4");
                }

                $sql = "SELECT id FROM authorlists WHERE authorid = ? AND listid = ?;";
                $stmt = $c->prepare($sql);
                $stmt->bind_param('ii',$authorID,$row['id']);
                $stmt->execute();                
                $finalcheckforauthor = $stmt->get_result();

                if ($finalcheckforauthor) {
                    if (mysqli_num_rows($finalcheckforauthor) <= 0) {
                
                        // add this instructor to the authors list for this reading list
                        $sql = "INSERT INTO authorlists (authorid, listid) VALUES (?,?)";
                        $stmt = $c->prepare($sql);
                        $stmt->bind_param('ii',$authorID,$row['id']);
                        $stmt->execute();
                        $added = TRUE;
                    }
                } else {
                    die("It looks like the application was unable to connect to your MySQL server, or had trouble looking for the reading list.  Here is the MySQL error: 4");
                }

            } else {
                $authorIDfetch = mysqli_fetch_array($authorresults);
                $authorID = $authorIDfetch['id'];
            }
        } else {
            die("It looks like the application was unable to connect to your MySQL server, or had trouble looking for the reading list.  Here is the MySQL error: 5");
        }
        if (is_integer($authorID)) {
            setcookie('currentAuthorId', encryptCookie($authorID), $time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
        } else {
            $authorID = 0;
            setcookie('currentAuthorId', encryptCookie("0"), $time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
        }
    }


    $foundCopy = FALSE;

    if (($newlist) && ($copy_target != "none")) {
        copyListByResourceLinkId($c,$copy_target,$currentListId,$authorID);
    } else if (($newlist) && ((isset($customparams['copylist'])) && ($customparams['copylist'] == 'y'))) {
        if (substr_count($clean['roles'],"Instructor") > 0) {

            $sql = "SELECT id, linklabel, course, linkid FROM lists WHERE (id IN (SELECT listid FROM authorlists WHERE authorid = ?) AND linklabel = ? AND linkid != ? AND credentialconsumerid = ?) OR (private = 0 AND credentialconsumerid = ? AND linklabel = ?) ORDER BY private, last_access DESC;";
            $stmt = $c->prepare($sql);
            $stmt->bind_param('issiis',$authorID,$clean['resource_link_title'],$clean['resource_link_id'],$clean['credential_consumer_id'],$clean['credential_consumer_id'],$clean['resource_link_title']);

        } else {
            $sql = "SELECT id, linklabel, course, linkid FROM lists WHERE private = 0 AND credentialconsumerid = ? AND linklabel = ? ORDER BY private, last_access DESC";            
            $stmt = $c->prepare($sql);
            $stmt->bind_param('is',$clean['credential_consumer_id'],$clean['resource_link_title']);
        }
        $stmt->execute();
        $results = $stmt->get_result();  

        if ((substr_count($clean['roles'],"Instructor") > 0) && (mysqli_num_rows($results) > 0)) {
        $foundCopy = TRUE;

            ?>
<script type="text/javascript">
    function previewButtonToggle() {
        var selectedList = document.getElementById('selectList').value;
        if (selectedList == '0') {
            document.getElementById('previewbutton').style.display = 'none';
            document.getElementById('CopyButton').style.display = 'none';
            document.getElementById('noCopyButton').style.display = 'inline';
        } else {
            document.getElementById('previewbutton').style.display = 'inline';
            document.getElementById('CopyButton').style.display = 'inline';
            document.getElementById('noCopyButton').style.display = 'none';            
        }
    }
    
    function launchlist () {
        var selectedList = document.getElementById('selectList').value;
        window.open("preview_list.php?listid="+selectedList, "_blank", "toolbar=no, scrollbars=yes, resizable=yes, top=50, left=50, width=400, height=600");
    }
    
</script>
        <link rel="stylesheet" href="web/styles.css" type="text/css" media="screen" />
<form action="auth.php" method="POST" style="margin:10px;">
    <h3>It looks like there's another list with a the same name.  Would you like to copy it?</h3>
        <select name="copyid" id="selectList" onchange="previewButtonToggle();">
            <option value="0">Start fresh - do not copy an existing list</option>
    <?php
        while ($row = mysqli_fetch_array($results)) {
            $sqlcount = "SELECT id FROM readings WHERE listid = ?";
            $stmt = $c->prepare($sqlcount);
            $stmt->bind_param('i',$row['id']);
            $stmt->execute();
            $resultscount = $stmt->get_result();

            $countitems = mysqli_num_rows($resultscount);
            echo '<option value="'.$row['id'].'">Copy <strong>'.$row['course'].'</strong>: '.$row['linklabel'].' ['.$countitems.' item(s)]</option>';
        }
    ?>
        </select> <button onclick="launchlist(); return false;" id="previewbutton" style="display:none;">Preview Selected List</button> <input name="submit" id="noCopyButton" value="Continue WITHOUT Copying" type="submit" /><input name="submit" type="submit" id="CopyButton" value="Copy Selected List" style="display:none;" />
</form>
            <?php
        } else if ((isset($customparams['quicklaunch'])) && ($customparams['quicklaunch'] == 'y') && (mysqli_num_rows($results) > 0)) {
            $foundCopy = TRUE;
            ?>
<form action="auth.php" method="POST" style="margin:10px;" id="copy_list_form">
    <input name="copyid" id="selectList" value="<?php
    
    $row = mysqli_fetch_array($results);
    echo $row['id'];
    
    ?>" type="hidden" />
    <input name="submit" type="submit" id="CopyButton" value="Continue to Reading List" />
</form>
<script type="text/javascript">
  document.getElementById("copy_list_form").submit();
</script>
            <?php
        }
    }

    
    mysqli_close($c);
  if (!($foundCopy)) {

?>
<b>Loading...</b>

<?php
  if ((substr_count($clean["roles"],"Instructor")) > 0) {
?>
<meta http-equiv="REFRESH" content="0;url=auth.php" />
<!--<a href="auth.php">Continue (Instructor)</a>-->
<?php
  } else {
?>
<meta http-equiv="REFRESH" content="0;url=auth.php?path=reading_list" />
<!--<a href="auth.php?path=reading_list">Continue (Student)</a>-->
<?php
  }
  }

?>
