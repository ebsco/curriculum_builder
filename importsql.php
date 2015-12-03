<?php
header('Content-Type: text/html; charset=UTF-8');
$time = 0; // store for session only
include("app/app.php");
if (! isset($_COOKIE['logged_in_cust_id'] ) ) {
  setcookie('message',encryptCookie("You were not logged in, please login"),$time,'/');
  setcookie('forward_to_admin',encryptCookie(" "),$time,'/');
  header( "Location: admin2.php" ) ;
}
if (!isset($_FILES['export_file'])){
 ?>
	<form action="importsql.php" method="post" enctype="multipart/form-data">
		<div>Choose a file to upload:</div>
		<input type="hidden" name="MAX_FILE_SIZE" value="300000" />
		<input type="file" name="export_file" />
		<input type="submit" name="submit" value="Upload" />
	</form>
<?php
} 
else{
	//Convert CSV file into named array $values
	$values = csv_to_array('export_file');
	if ($values != false){
		
		//Get number of readings to know how many entries to make
	    $readingscount = count($values['readings_title']);

	    //For each reading, check values and insert as needed
	    for($i=0;$i<$readingscount;$i++){ 

			//Check for user/profile/password.
		    $items=get_user_info($c,$i,$values);

	    	//INSERT userid/password/profile if not exist.
	    	if ($items->num_rows != 1){
	    		$success=set_user_info($c,$i,$values);
	    		$items=get_user_info($c,$i,$values);
	    		$row = mysqli_fetch_assoc($items);
	    		if ($success == TRUE){ import_success('Successfully Added User: ',$row['userid']);}
		        else { import_failure('Failed to Add User: ',$values['credentials_userid'][$i]);}
	    	}

	    	else{
	    		$row = mysqli_fetch_assoc($items);
	    	}

	    	//Update array credential id
	    	$values['credentialconsumers_credentialid'][$i] = $row['id'];

	    	//Check credentialconsumers table for credential id.
	    	$items=get_credential_consumer_id($c,$i,$values);

	    	//INSERT INTO credentialconsumers table if not there.
	    	if ($items->num_rows != 1){
	    		$success=set_credential_consumer_id($c,$i,$values);
	    		$items=get_credential_consumer_id($c,$i,$values);
	    		$row = mysqli_fetch_assoc($items);
	    		if ($success == TRUE){ import_success('Successfully Added Credential Consumer Connection!','');}
		        else { import_failure('Failed to Add Credential Consumer Connection!','');}
	    	}
	    	else{
	    		$row = mysqli_fetch_assoc($items);
	    	}

	    	//Update array credentialconsumer_id
	    	$values['credentialconsumers_id'][$i] = $row['id'];

	    	//Check if list exists with lists.linkid and lists.credentialconsumerid
	    	$items=get_list_link_id($c,$i,$values);

	    	//INSERT INTO lists table if not there.
	    	if ($items->num_rows != 1){
	    		$success=set_list_link_id($c,$i,$values);
	    		$items=get_list_link_id($c,$i,$values);
	    		$row = mysqli_fetch_assoc($items);
	    		if ($success == TRUE){ import_success('Successfully Added List: ',$row['linklabel']);}
		        else { import_failure('Failed to Add List: ',$values['lists_linklabel'][$i]);}
	    	}
	    	else{
	    		$row = mysqli_fetch_assoc($items);
	    	}
	    	//Update array list_id
	    	$values['list_id'][$i] = $row['id'];

	    	//Check if author exists with authors.email
	    	$items=get_author_id($c,$i,$values);

	    	//INSERT INTO authors table if not there.
	    	if ($items->num_rows != 1){
	    		$success=set_author_id($c,$i,$values);
	    		$items=get_author_id($c,$i,$values);
	    		$row = mysqli_fetch_assoc($items);
	    		if ($success == TRUE){ import_success('Successfully Added Author: ',$row['fullname']);}
		        else { import_failure('Failed to Add List: ',$values['authors_fullname'][$i]);}
	    	}
	    	else{
	    		$row = mysqli_fetch_assoc($items);
	    	}
	    	//Update array list_id
	    	$values['authors_id'][$i] = $row['id'];

	    	//Check if authorlist exists with listid and authorid
	    	$items=get_authorlists_id($c,$i,$values);

	    	//INSERT INTO authorlist table if not there.
	    	if ($items->num_rows != 1){
	    		$success=set_authorlists_id($c,$i,$values);
	    		$items=get_authorlists_id($c,$i,$values);
	    		$row = mysqli_fetch_assoc($items);
	    		$authorlistalert = $values['authors_fullname'][$i]." to list ".$values['lists_linklabel'][$i];
	    		if ($success == TRUE){ import_success('Successfully Connected Author to List: ',$authorlistalert);}
		        else { import_failure('Failed to Connect Author to List: ',$authorlistalert);}
	    	}
	    	else{
	    		$row = mysqli_fetch_assoc($items);
	    	}
	    	//Update array list_id
	    	$values['authorlists_id'][$i] = $row['id'];

	    	//IF folder exists, check if folder with label and listid exists
	    	if ($values['folders_label'][$i] != ""){
		    	$items=get_folder_id($c,$i,$values);

		    	//INSERT INTO folders table if not there.
		    	if ($items->num_rows != 1){
		    		$success=set_folder_id($c,$i,$values);
		    		$items=get_folder_id($c,$i,$values);
		    		$row = mysqli_fetch_assoc($items);
	    		if ($success == TRUE){ import_success('Successfully Added Folder: ',$row['label']);}
		        else { import_failure('Failed to Add User: ',$values['folders_label'][$i]);}
		    	}
		    	$row = mysqli_fetch_assoc($items);
		    	//Update array folder_id
		    	$values['readings_folderid'][$i] = $row['id'];
		    }
		    else {
		    	$values['readings_folderid'][$i] = "";
		    }

		    //Check if reading exists by all params
		    $items=get_reading($c,$i,$values);

	    	//INSERT INTO readings table if not there.
	    	if ($items->num_rows != 1){
	    		$success=set_reading($c,$i,$values);
	    		$items=get_reading($c,$i,$values);
	    		$row = mysqli_fetch_assoc($items);
	    		if ($success == TRUE){ import_success('Successfully Added Reading: ',$row['title']);}
		        else { import_failure('Failed to Add Reading: ',$value['readings_title'][$i]); }
	    	}
	    }
	    ?>
		<h3 style='color:green;'><strong>IMPORT PROCESS COMPLETE</strong></h3>
	<?php
	}	
}
?>
<a href="admin2.php"><button>Return to Admin</button></a>
<?php

function get_user_info($c,$i,$values){
	$sql = "SELECT credentials.id,credentials.userid FROM credentials WHERE credentials.userid = ? AND credentials.password = ? AND credentials.profile = ?;";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('sss',$values['credentials_userid'][$i],$values['credentials_password'][$i],$values['credentials_profile'][$i]);
    $stmt->execute();
    $items = $stmt->get_result();
    return $items;
}

function set_user_info($c,$i,$values){
	$sqlinsert = "INSERT INTO credentials (userid,password,profile) VALUES (?,?,?);";
	$istmt = $c->prepare($sqlinsert);
	$istmt->bind_param('sss',$values['credentials_userid'][$i],$values['credentials_password'][$i],$values['credentials_profile'][$i]);
	$success = $istmt->execute();
	return $success;
}

function get_credential_consumer_id($c,$i,$values){
	$sql = "SELECT credentialconsumers.id FROM credentialconsumers WHERE credentialconsumers.credentialid = ? AND credentialconsumers.consumerid = ?;";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ss',$values['credentialconsumers_credentialid'][$i],$values['credentialconsumers_consumerid'][$i]);
    $stmt->execute();
    $items = $stmt->get_result();
    return $items;
}

function set_credential_consumer_id($c,$i,$values){
	$sqlinsert = "INSERT INTO credentialconsumers (credentialid,consumerid) VALUES (?,?);";
	$istmt = $c->prepare($sqlinsert);
	$istmt->bind_param('ss',$values['credentialconsumers_credentialid'][$i],$values['credentialconsumers_consumerid'][$i]);
	$success = $istmt->execute();
	return $success;
}

function get_list_link_id($c,$i,$values){
	$sql = "SELECT lists.id,lists.linklabel FROM lists WHERE lists.linkid = ? AND lists.credentialconsumerid = ?;";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ss',$values['lists_linkid'][$i],$values['credentialconsumers_id'][$i]);
    $stmt->execute();
    $items = $stmt->get_result();
    return $items;
}

function set_list_link_id($c,$i,$values){
	$sqlinsert = "INSERT INTO lists (institution,linklabel,course,linkid,private,last_access,credentialconsumerid) VALUES (NULL,?,?,?,?,?,?);";
	$istmt = $c->prepare($sqlinsert);
	$istmt->bind_param('sssssi',$values['lists_linklabel'][$i],$values['lists_course'][$i],$values['lists_linkid'][$i],$values['lists_private'][$i],$values['lists_last_access'][$i],$values['credentialconsumers_id'][$i]);
	$success = $istmt->execute();
	return $success;
}

function get_author_id($c,$i,$values){
	$lmsid = import_check_null($values['authors_lmsid'][$i]);

    $email = import_check_null($values['authors_emails'][$i]);

	$sql = "SELECT authors.id,authors.fullname FROM authors WHERE authors.lms_id = ?;";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('s',$lmsid);
    $stmt->execute();
    $items = $stmt->get_result();

    if ($items->num_rows != 1){
		$sql = "SELECT authors.id,authors.fullname FROM authors WHERE authors.email = ?;";
	    $stmt = $c->prepare($sql);
	    $stmt->bind_param('s',$email);
	    $stmt->execute();
	    $items = $stmt->get_result();
	}
    return $items;
}

function set_author_id($c,$i,$values){

	$fullname = import_check_null($values['authors_fullname'][$i]);
	$email = import_check_null($values['authors_emails'][$i]);
	$lmsid = import_check_null($values['authors_lmsid'][$i]);

	$sqlinsert = "INSERT INTO authors (fullname,email,lms_id) VALUES (?,?,?);";
	$istmt = $c->prepare($sqlinsert);
	$istmt->bind_param('sss',$fullname,$email,$lmsid);
	$success = $istmt->execute();
	return $success;
}

function get_folder_id($c,$i,$values){
	$sql = "SELECT folders.id,folders.label FROM folders WHERE folders.label = ? AND folders.listid = ?;";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ss',$values['folders_label'][$i],$values['list_id'][$i]);
    $stmt->execute();
    $items = $stmt->get_result();
    return $items;
}

function set_folder_id($c,$i,$values){
	$sqlinsert = "INSERT INTO folders (label,listid) VALUES (?,?);";
	$istmt = $c->prepare($sqlinsert);
	$istmt->bind_param('si',$values['folders_label'][$i],$values['list_id'][$i]);
	$success = $istmt->execute();
	return $success;
}

function get_authorlists_id($c,$i,$values){
	$sql = "SELECT authorlists.id FROM authorlists WHERE authorlists.listid = ? AND authorlists.authorid = ?;";
    $stmt = $c->prepare($sql);
    $stmt->bind_param('ii',$values['list_id'][$i],$values['authors_id'][$i]);
    $stmt->execute();
    $items = $stmt->get_result();
    return $items;
}

function set_authorlists_id($c,$i,$values){
	$sqlinsert = "INSERT INTO authorlists (listid,authorid) VALUES (?,?);";
	$istmt = $c->prepare($sqlinsert);
	$istmt->bind_param('ii',$values['list_id'][$i],$values['authors_id'][$i]);
	$success = $istmt->execute();
	return $success;
}

function get_reading($c,$i,$values){
	
	$sql = "SELECT readings.id,readings.title FROM readings WHERE readings.listid = ? AND readings.authorid = ? AND readings.an = ? AND readings.db = ? AND readings.title = ? AND readings.priority = ? AND readings.notes <=> ? AND readings.url = ? AND readings.type = ? AND readings.instruct <=> ? AND readings.folderid <=> ?;";
    
    $stmt = $c->prepare($sql);
    $notes = import_check_null($values['readings_notes'][$i]);
    $folderid = import_check_null($values['readings_folderid'][$i]);

    $stmt->bind_param('iisssissisi', $values['list_id'][$i], $values['authors_id'][$i], $values['readings_an'][$i], $values['readings_db'][$i], $values['readings_title'][$i],$values['readings_priority'][$i], $notes, $values['readings_url'][$i], $values['readings_type'][$i], $values['readings_instruct'][$i], $folderid);

    $stmt->execute();
    $items = $stmt->get_result();
    return $items;
}

function set_reading($c,$i,$values){
	$sqlinsert = "INSERT INTO readings (listid,authorid,an,db,title,priority,notes,url,type,instruct,folderid) VALUES (?,?,?,?,?,?,?,?,?,?,?);";
	$istmt = $c->prepare($sqlinsert);
    
    $notes = import_check_null($values['readings_notes'][$i]);
    $folderid = import_check_null($values['readings_folderid'][$i]);

	$istmt->bind_param('iisssississ', $values['list_id'][$i], $values['authors_id'][$i], $values['readings_an'][$i], $values['readings_db'][$i], $values['readings_title'][$i],$values['readings_priority'][$i], $notes, $values['readings_url'][$i], $values['readings_type'][$i], $values['readings_instruct'][$i], $folderid);
	$success = $istmt->execute();
	return $success;
}

function import_success($message,$value){

	echo '<p style="color:white;background-color:#06BF06;padding:5px;margin-bottom:5px;">'.$message.$value.'</p>';
}

function import_failure($message,$value){

	echo '<p style="color:white;background-color:#BF0F0F;padding:5px;margin-bottom:5px;">'.$message.$value.'</p>';
}

function csv_to_array($filename){
	ini_set('auto_detect_line_endings', true);
	$row=1;
	$values = array();

	//Make sure it's a csv file
	if ($_FILES[$filename]["type"] == "text/csv"){
		if (($handle = fopen($_FILES[$filename]['tmp_name'], "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000 , ',')) !== FALSE) {
		        $num = count($data);

		        //If file is invalid
		        if ($num != 24){
		        	echo "INVALID FILE: ERROR CODE 2.  Please contact support for assistance.</br>";
		        	fclose($handle);
		        	unlink($_FILES[$filename]['tmp_name']);
		        	return false;
		        }

		        //Create Array Keys
		        if ($row==1){
			        for ($d=0; $d < $num; $d++) {
			        	$title = $data[$d];
			        	$values[$title] = array();
			        	//echo $data[$d];
			        }
			    }

			    //Create Array Values
			    if ($row>1){
			    	$fields = array();
			    	for ($d=0; $d < $num; $d++) {
			    		if ($data[$d] == NULL){
			    			$fields[] = "";
			    		}
			    		else{
			    			$fieldData = strip_tags_deep($data[$d]);
			    			$fields[] = $fieldData;
			    		}
			        }
			        $x = 0;
				    foreach($values as $k => &$v){
			    			array_push($v, $fields[$x]);
			    		$x++;   
				    }   	
		        }		
			$row++;
			} 	
	    }
   		fclose($handle);
   		return $values;
   	}
   	else {
   		echo "INVALID FILE: ERROR CODE 1.  Please contact support for assistance.</br>";
   		unlink($_FILES[$filename]['tmp_name']);
    	return false;
   	}
}

function import_check_null($value) {
    if ($value == ""){
    	$value = NULL;
    }
    return $value;
}
//ERROR CODE 1: Not a CSV file.
//ERROR CODE 2: File length is not correct.
?>