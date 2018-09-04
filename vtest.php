<?php
session_start();
session_destroy();
session_start();

include_once('app/app.php');
$time = 0;

if ((isset($_REQUEST['profile']) && isset($_REQUEST['userid']) && isset($_REQUEST['password'])) || (isset($_COOKIE['credential_id']))) {
    $customparams = array();
    $customparams['userid'] = $_REQUEST['userid'];
    $customparams['password'] = $_REQUEST['password'];
    $customparams['profile'] = $_REQUEST['profile'];
    
    if (!isset($_COOKIE['credential_id'])) {
        $sql = "SELECT id FROM credentials WHERE userid = '".$_REQUEST['userid']."' AND profile = '".$_REQUEST['profile']."' AND password = '".$_REQUEST['password']."';";
        $results = mysqli_query($c,$sql);
        if (mysqli_num_rows($results) >= 1) {
            $row = mysqli_fetch_array($results);
        } else {
            $sql = "INSERT INTO credentials (userid, profile, password) VALUES ('".$_REQUEST['userid']."','".$_REQUEST['profile']."','".$_REQUEST['password']."');";
            mysqli_query($c,$sql);
            $sql = "SELECT id FROM credentials WHERE userid = '".$_REQUEST['userid']."' AND profile = '".$_REQUEST['profile']."' AND password = '".$_REQUEST['password']."';";
            $credentialsresults = mysqli_query($c,$sql);
            $row = mysqli_fetch_array($credentialsresults);
        }
        setcookie('credential_id',encryptCookie($row['id']),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
        setcookie('custom_custid',encryptCookie($_REQUEST['userid']),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
        setcookie('custom_password',encryptCookie($_REQUEST['password']),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
        setcookie('custom_profile',encryptCookie($_REQUEST['profile']),$time,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);
        $_SESSION['sessionToken']['profile'] = $_REQUEST['profile'];
        setcookie('login',encryptCookie($_REQUEST['profile']),0,"/",$_SERVER['SERVER_NAME'],FALSE,TRUE);       
        header("Location:vtest.php?userid=".$_REQUEST['userid']."&password=".$_REQUEST['password']."&profile=".$_REQUEST['profile']);
    } else {
        $errors = 0;
        $errorlog = '';
        
        $errorlog .= '<p>TestMySQLConnection - ';
    
        if (!$c) {
            $errorlog .= 'Failed</p>';
            $errors++;
        } else {
            $errorlog .= 'Passed</p>';
        }
    
        include('rest/EBSCOAPI.php');
        
        $errorlog .= '<p>TestMySQLPrivileges - ';
            
        $sql = 'INSERT INTO readings (listid, authorid, an, db, url, title, priority, type) VALUES (1,1,"testing-an", "testing-db","none ","Circumstances Surrounding the Community Needle-Stick Injuries in Georgia.",1,1);';
        $results = mysqli_query($c,$sql);
        
        if (!($results)) {
            $errorlog .= "Failed</p>";
            $errors++;
        } else {
            $errorlog .= "Passed</p>";
        }
        
        $sql = 'SELECT * FROM readings WHERE an = "testing-an" AND db = "testing-db";';
        $rows = mysqli_query($c,$sql);
    
        if ($rows) {
            if (mysqli_num_rows($rows) >= 1) {
                $row = mysqli_fetch_array($rows);
                $sql = "DELETE FROM readings WHERE id = " . $row['id'] . ";";
                mysqli_query($c,$sql);
            }
        }
    
        $errorlog .= '<p>TestSessionStore - ';
        $_SESSION['vtest'] = 'Testing';
        if (isset($_SESSION['vtest']) && ($_SESSION['vtest'] == 'Testing')) {
            $errorlog .= 'Passed</p>';
        } else {
            $errorlog .= 'Failed</p>';
            $errors++;
        }
        
        $errorlog .= '<p>TestAPIConnection - ';
        
        $url = '';
        $_SESSION['sessionToken']['sessionToken'] = '';   
    
        try
        {
            $api = new EBSCOAPI($c,$customparams);
            $theinfo = $api->getInfo();
            if (isset($theinfo['sort'])) {
                $errorlog .= 'Passed</p>';
            } else {
                $errorlog .= 'Failed: '.var_export($theinfo,TRUE).'</p>';
                $errors++;
            }
        }
        catch (Exception $e)
        {
            $errorlog .= 'Failed</p>';
            $errors++;
        }
        
        if ($errors > 0) {
            echo $errorlog;
        } else {
            echo 'OK';
        }
    }
} else {
?>
<form action="vtest.php" method="get">
    <p>user id: <input type="text" name="userid" placeholder="userID with access to group with API profile" /></p>
    <p>password: <input type="text" name="password" placeholder="password associated with userID" /></p>
    <p>profile: <input type="text" name="profile" placeholder="api profile id" /></p>
    <p><input type="submit" value="submit" /></p>
</form>
<?php
}
?>