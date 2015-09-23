<?php
$time = 0; // store for session only
if (isset ($_POST['BiblioInfo'])){
	$clean = strip_tags_deep($_POST);
	unset ($_POST['BiblioInfo']);
	$biblioInfo = $clean['BiblioInfo'];
	$biblioArray = explode("\n", $biblioInfo);
	
	$titles = array();
	$ANs = array();
	$DBsPrep = array();
	$ANsPrep = array();
	$DBs = array();
	
	foreach ($biblioArray as $line){
		//Get the title of all the readings and put them in an array called $titles
		if (preg_match("/^TI/", $line)){
			$titles[] = trim(substr($line, 4));
		}

		/*Get the DBs for all of the readings and put them in an array called $DBsPrep. The prep is added because this code will put two values
		into the array for every reading instead of one. This is because the biblio record has 2 identical lines containing the DB and both
		will be picked up by this code. We'll fix this after the foreach loop (being run on $biblioArray) is complete.
		*/
		if (preg_match("/^UR-/", $line)){
			//Because the DB is located within the UR line this seperates the UR into pieces, and a regex is used to find the DB.
			$lineArray = explode("&", $line);
			foreach($lineArray as $partOfLine){
				if (preg_match("/^amp;db=/", $partOfLine)){
					$DBsPrep[] = trim(substr($partOfLine,7));
				}
				if (preg_match("/^amp;AN=/", $partOfLine)){
					$ANsPrep[] = trim(substr($partOfLine,7));
				}
			}
		}
	}
	//Convert $DBsPrep to $DBs by getting rid of the extra entry for each reading. 
	$nbr = count($DBsPrep);
	for ($i = 0 ; $i < $nbr ; $i += 2) {
		$DBs[] = $DBsPrep[$i];
	}

	//Convert $DBsPrep to $DBs by getting rid of the extra entry for each reading. 
	$nbr = count($ANsPrep);
	for ($i = 0 ; $i < $nbr ; $i += 2) {
		$ANs[] = $ANsPrep[$i];
	}

		
	//There should always be an equal number of elements in these array. This will ensure that is the case
	$titlecount = count($titles);
	$ANcount = count($ANs);
	$DBcount = count($DBs);
	if ($titlecount != $ANcount || $titlecount != $DBcount || $ANcount != $DBcount || $titlecount == 0){
		?> <div class="readingListLink"> <h3><?php 
		echo "Oops! An error occurred. The data for one or more records is incomplete. Please try again, being sure to include the entire record for each reading.";
		?></h3></div><?php
	} else {
		for ($i=0; $i < $titlecount; $i++){
			$sql = $c->prepare("INSERT INTO readings (listid, authorid, an, db, title, priority, url, type) VALUES (?,?,?,?,?,1,'none',1);");
			$sql->bind_param('iisss', decryptCookie($_COOKIE['currentListId']), decryptCookie($_COOKIE['currentAuthorId']), $ANs[$i], $DBs[$i], $titles[$i]);
			$sql->execute();
		}
		if ($titlecount == 1){
			setcookie('import_folder_message',encryptCookie("1 reading added"),$time,'/');
		} else {
			setcookie('import_folder_message',encryptCookie("$titlecount readings added"),$time,'/');
		}
		header("Location:reading_list.php");
	}
	//Add statement saying how many items added to reading list.
	
} ?>
<div class="readingListLink"><h2> Enter bibliographic info from the list into this box:</h2>
<form action="import_folder.php" id="EBSCOFolderForm" method="post">
<textarea name="BiblioInfo" form="EBSCOFolderForm" rows="6" cols="50"></textarea></br>
<input type="submit" value="Generate list">
</form></div>
