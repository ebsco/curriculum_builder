<h2>Beginning update process...</h2>
<?php
include("connect.php");

// check for v1.1 update
$sql = "SHOW COLUMNS FROM lists;";
$results = mysqli_query($c,$sql);
$last_access = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'last_access') {
        $last_access = 1;
    }
}
if ($last_access == 0) {
    $sql = "ALTER TABLE lists ADD last_access TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;";
    mysqli_query($c,$sql);
    echo "<p><strong>Adding timestamp to lists</strong>.  This will allow you to see when a list was last accessed in order to determine if you can delete it permanently.</p>";
} else {
    echo "<p><em>Version 1.1 Update</em>: <strong>Timestamp</strong> is already included in your installation.  No update needed.</p>";
}

// check for v2.0 update
$sql = "SHOW tables;";
$results = mysqli_query($c,$sql);
$i = 0;
while ($row = mysqli_fetch_array($results)) {
    $tablearray[$i] = $row[0];
    $i++;
}

if (!in_array("authtokens",$tablearray)) {
    $sql = "CREATE TABLE authtokens (
  id int(11) NOT NULL AUTO_INCREMENT,
  token varchar(200) NOT NULL,
  timeout int(11) NOT NULL,
  tokentimestamp int(11) NOT NULL,
  credentialid int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";
    mysqli_query($c,$sql);
    echo "<p><em>Version 2.0 Update</em>: Table <strong>authtokens</strong> added to database.</p>";
} else {
    echo "<p><em>Version 2.0 Update</em>: Table <strong>authtokens</strong> already in database.</p>";
}
if (!in_array("credentialconsumers",$tablearray)) {
    $sql = "CREATE TABLE credentialconsumers (
  id int(11) NOT NULL AUTO_INCREMENT,
  credentialid int(11) NOT NULL,
  consumerid varchar(200) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;";
    mysqli_query($c,$sql);
    echo "<p><em>Version 2.0 Update</em>: Table <strong>credentialconsumers</strong> added to database.</p>";

} else {
    echo "<p><em>Version 2.0 Update</em>: Table <strong>credentialconsumers</strong> already in database.</p>";
}
if (!in_array("credentials",$tablearray)) {
    $sql = "CREATE TABLE credentials (
  id int(11) NOT NULL AUTO_INCREMENT,
  userid varchar(200) NOT NULL,
  password varchar(200) NOT NULL,
  profile varchar(100) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;";
    mysqli_query($c,$sql);
    echo "<p><em>Version 2.0 Update</em>: Table <strong>credentials</strong> added to database.</p>";

} else {
    echo "<p><em>Version 2.0 Update</em>: Table <strong>credentials</strong> already in database.</p>";
}
if (!in_array("oauth",$tablearray)) {
    $sql = "CREATE TABLE oauth (
  id int(11) NOT NULL AUTO_INCREMENT,
  oauth_consumer_key varchar(100) NOT NULL,
  secret varchar(100) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;";
    $results = mysqli_query($c,$sql);
    echo "<p><em>Version 2.0 Update</em>: Table <strong>oauth</strong> added to database.</p>";

} else {
    echo "<p><em>Version 2.0 Update</em>: Table <strong>oauth</strong> already in database.</p>";
}

?>

<?php
$sql = "SHOW COLUMNS FROM lists;";
$results = mysqli_query($c,$sql);
$last_access = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'credentialconsumerid') {
        $last_access = 1;
    }
}
if ($last_access == 0) {
    $sql = "ALTER TABLE lists ADD credentialconsumerid int(11) NOT NULL DEFAULT 0;";
    mysqli_query($c,$sql);
    echo "<p><strong>Adding credentialconsumerid to lists</strong>.  This will allow you to more accurately track statistics and manage your institution's lists.</p>";
    $sql = "ALTER TABLE lists MODIFY institution varchar(200) NULL;";    
    mysqli_query($c,$sql);
} else {
    echo "<p><em>Version 2.1 Update</em>: <strong>credentialconsumerid</strong> is already included in your installation.  No update needed.</p>";
}

?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$last_access = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'expires') {
        $last_access = 1;
    }
}
if ($last_access == 0) {
    $sql = "ALTER TABLE oauth ADD expires timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;";
    mysqli_query($c,$sql);
    echo "<p><strong>Adding exipiration to consumer keys/secrets</strong>.  This will allow you to more tightly control access to the tool.</p>";
    $sql = "UPDATE oauth SET expires=CURRENT_TIMESTAMP;";
    mysqli_query($c,$sql);
} else {
    echo "<p><em>Version 2.0 Update</em>: <strong>Expiration of consumer keys</strong> is already included in your installation.  No update needed.</p>";
}

?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'userid') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD libemail VARCHAR( 100 ) NULL , ADD libname VARCHAR( 200 ) NULL , ADD liblink VARCHAR( 400 ) NULL , ADD liblogo VARCHAR( 400 ) NULL , ADD profile VARCHAR( 20 ) NULL , ADD userid VARCHAR( 20 ) NULL , ADD password VARCHAR( 20 ) NULL; ";
    mysqli_query($c,$sql);
    echo "<p><strong>Adding Custom Parameter Fields</strong>.  This will allow you to edit settings from the admin panel.</p>";
} else {
    echo "<p><em>Version 2.1 Update</em>: <strong>Custom Parameter Fields</strong> is already included in your installation.  No update needed.</p>";
}

?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'studentdata') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD studentdata VARCHAR ( 1 ) DEFAULT 'n'; ";
    mysqli_query($c,$sql);
    echo "<p><strong>Adding Student Data option</strong>.  This will allow you to collect student data.</p>";
    $sql = "CREATE TABLE studentreading (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(200) DEFAULT NULL,
  email varchar(200) DEFAULT NULL,
  readingid int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
    mysqli_query($c,$sql);

    $sql = "CREATE TABLE studentaccess (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(200) DEFAULT NULL,
  email varchar(200) DEFAULT NULL,
  listid int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
    mysqli_query($c,$sql);
    
    echo "<p><strong>Adding Student Data Tables</strong>.  This will allow you to collect student data.</p>";

} else {
    echo "<p><em>Version 2.1b Update</em>: <strong>Student Data</strong> is already included in your installation.  No update needed.</p>";
}
?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'EDSlabel') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD EDSlabel VARCHAR ( 200 ) NULL; ";
    mysqli_query($c,$sql);
    echo "<p><strong>Adding EDS Label Option</strong>.  This will allow you to relabel the link back to EDS on the detailed record.</p>";
} else {
    echo "<p><em>Version 2.1c Update</em>: <strong>Adding EDS Label Option</strong> is already included in your installation.  No update needed.</p>";
}
?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'copyright') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD copyright TEXT; ";
    mysqli_query($c,$sql);
    echo "<p><strong>Adding Copyright Option</strong>.  This will allow you to add a copyright notice to the bottom of the page.</p>";
} else {
    echo "<p><em>Version 2.1c Update</em>: <strong>Copyright notice</strong> is already included in your installation.  No update needed.</p>";
}
?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'copylist') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD copylist VARCHAR (1) DEFAULT 'y'; ";
    mysqli_query($c,$sql);
    echo "<p>".mysqli_error($c)."<strong>Adding ability to copy lists from prior semesters</strong>.  This will prompt users to import an existing list if the title of the list matches an older list.</p>";
} else {
    echo "<p><em>Version 2.1d Update</em>: <strong>Copy List</strong> is already included in your installation.  No update needed.</p>";
}
?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'css') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD css VARCHAR (200); ";
    mysqli_query($c,$sql);
    echo "<p>".mysqli_error($c)."<strong>Adding ability to submit custom CSS</strong>.  You must host custom CSS in a web accessible location.</p>";
} else {
    echo "<p><em>Version 2.1e Update</em>: <strong>Custom CSS</strong> is already included in your installation.  No update needed.</p>";
}
?>

<?php
$sql = "SHOW COLUMNS FROM readings;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'instruct') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE readings ADD instruct TEXT; ";
    mysqli_query($c,$sql);
    echo "<p>".mysqli_error($c)."<strong>Adding ability to add instructions</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.1f Update</em>: <strong>Reading Instructions</strong> is already included in your installation.  No update needed.</p>";
}
?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'forceft') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD forceft VARCHAR(1) DEFAULT 'y'; ";
    mysqli_query($c,$sql);
    echo "<p>".mysqli_error($c)."<strong>Adding ability to hide full text limiters</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.1g Update</em>: <strong>Full Text Limiter Hiding</strong> is already included in your installation.  No update needed.</p>";
}
?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'courselink') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD courselink VARCHAR(1) DEFAULT 'n'; ";
    mysqli_query($c,$sql);
    echo "<p>".mysqli_error($c)."<strong>Add Course Link</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.1h Update</em>: <strong>Course Links</strong> is already included in your installation.  No update needed.</p>";
}
?>

<?php
$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'quicklaunch') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD quicklaunch VARCHAR(1) DEFAULT 'n'; ";
    mysqli_query($c,$sql);
    echo "<p>".mysqli_error($c)."<strong>Quick Launch</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.1i Update</em>: <strong>Quick Launch</strong> is already included in your installation.  No update needed.</p>";
}

$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'newwindow') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD newwindow VARCHAR(1) DEFAULT 'n'; ";
    mysqli_query($c,$sql);

    $sql = "ALTER TABLE authors MODIFY fullname VARCHAR(300);";
    mysqli_query($c,$sql);
    
    $sql = "ALTER TABLE authors MODIFY email VARCHAR(300);";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>New Window Option</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.1j Update</em>: <strong>New Window Option</strong> is already included in your installation.  No update needed.</p>";
}

// v.2.2

if (!in_array("folders",$tablearray)) {
    $sql = "CREATE TABLE folders (
  id int(11) NOT NULL AUTO_INCREMENT,
  label text NOT NULL,
  listid int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";
    mysqli_query($c,$sql);
    echo "<p><em>Version 2.2 Update</em>: Table <strong>Folders</strong> added to database.</p>";
} else {
    echo "<p><em>Version 2.2 Update</em>: Table <strong>Folders</strong> already in database.</p>";
}

$sql = "SHOW COLUMNS FROM readings;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'folderid') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE readings ADD folderid INT(11); ";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>Folders</strong> to Readings Table.</p>";
} else {
    echo "<p><em>Version 2.2 Update</em>: <strong>Folders</strong> is already included in your installation.  No update needed.</p>";
}


$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'firstftonly') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD firstftonly VARCHAR(1) DEFAULT 'y'; ";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>First Full Text option</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.2 Update</em>: <strong>First Full Text option</strong> is already included in your installation.  No update needed.</p>";
}

$sql = "SHOW COLUMNS FROM authors;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'lms_id') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE authors ADD lms_id VARCHAR(200);";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>LMS User ID</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.2 Update</em>: <strong>LMS User ID</strong> is already included in your installation.  No update needed.</p>";
}

$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'helppages') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD helppages text; ";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>Help Pages option</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.2 Update</em>: <strong>Help Pages option</strong> is already included in your installation.  No update needed.</p>";
}


$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'proxyprefix') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD proxyprefix VARCHAR(100); ";
    mysqli_query($c,$sql);
    $sql = "ALTER TABLE oauth ADD proxyencode VARCHAR(1) DEFAULT 'n'; ";
    mysqli_query($c,$sql);
    $sql = "ALTER TABLE oauth ADD searchlabel VARCHAR(200) DEFAULT 'Search Library Resources'; ";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>Proxy information and Search Label</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.3a Update</em>: <strong>Proxy information and Search Label</strong> is already included in your installation.  No update needed.</p>";
}


$sql = "SHOW COLUMNS FROM folders;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'sortorder') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE folders ADD sortorder INT(11) DEFAULT 0; ";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>Folder Sort Order</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.3b Update</em>: <strong>Folder Sort Order</strong> is already included in your installation.  No update needed.</p>";
}

$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'empowered_roles') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD empowered_roles VARCHAR(200) DEFAULT 'Instructor,TeachingAssistant'; ";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>Role Empowerment</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.3c Update</em>: <strong>Role Empowerment</strong> is already included in your installation.  No update needed.</p>";
}

$sql = "SHOW COLUMNS FROM oauth;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'language') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE oauth ADD language VARCHAR(25); ";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>language</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.3c Update</em>: <strong>language</strong> is already included in your installation.  No update needed.</p>";
}

$sql = "SHOW COLUMNS FROM studentreading;";
$results = mysqli_query($c,$sql);
$v3 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'accessed_time') {
        $v3 = 1;
    }
}
if ($v3 == 0) {
    $sql = "ALTER TABLE studentreading ADD user_id VARCHAR(200); ";
    mysqli_query($c,$sql);
    $sql = "ALTER TABLE studentreading ADD accessed_time datetime; ";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)." Adding <strong>Granular Student Statistics</strong> to lists.</p>";
} else {
    echo "<p><em>Version 2.3c Update</em>: <strong>Granular Student Statistics</strong> is already included in your installation.  No update needed.</p>";
}

$sql = "SHOW COLUMNS FROM lists;";
$results = mysqli_query($c,$sql);
$v4 = 0;

while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'consumerid') {
        $v4 = 1;
    }
}
if ($v4 == 0) {
    $sql = "ALTER TABLE lists ADD consumerid VARCHAR(200); ";
    mysqli_query($c,$sql);
    $sql = "UPDATE lists INNER JOIN credentialconsumers ON lists.credentialconsumerid = credentialconsumers.id SET lists.consumerid = credentialconsumers.consumerid";
    mysqli_query($c,$sql);
    
    echo "<p>".mysqli_error($c)."Adjusting for upcoming <strong>UID Password Changes</strong></p>";
} else {
    echo "<p><em>Version 2.3d Update</em>: <strong>UID Password Changes</strong> is already included in your installation.  No update needed.</p>";
}

$sql = "SHOW COLUMNS FROM lists;";
$results = mysqli_query($c,$sql);
$v5 = 0;
while ($row = mysqli_fetch_array($results)) {
    if ($row['Field'] == 'oauth_consumer_key') {
        $v5 = 1;
    }
}
if ($v5 == 0) {
    $sql = "ALTER TABLE lists ADD oauth_consumer_key VARCHAR(200);";
    mysqli_query($c,$sql);
    $sql = "UPDATE lists INNER JOIN credentialconsumers ON lists.credentialconsumerid = credentialconsumers.id INNER JOIN credentials ON credentials.id = credentialconsumers.credentialid INNER JOIN oauth ON oauth.userid = credentials.userid AND oauth.password = credentials.password AND oauth.profile = credentials.profile SET lists.oauth_consumer_key = oauth.oauth_consumer_key;";
    mysqli_query($c,$sql);
    echo "<p>".mysqli_error($c)."Version 2.3e: Adjusting for upcoming <strong>UID Password Changes</strong> part 2 - Consumer Key fixes</p>";
} else {
    echo "<p><em>Version 2.3e Update</em>: <strong>UID Password Changes</strong> is already included in your installation.  No update needed.</p>";
}

?>



<p><strong>Update complete.</strong></p>