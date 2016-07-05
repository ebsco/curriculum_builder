<script type="text/javascript">
 function textCounter(field,field2,maxlimit)
{
 var countfield = document.getElementById(field2);
 if ( field.value.length > maxlimit ) {
  countfield.innerHTML = (field.value.length - maxlimit)+" over recommended length.  <span style='color:red;'>Notes may not save properly.</span>";
 } else {
  countfield.innerText = maxlimit - field.value.length;
 }
}

	function enableEditFolder(editfolderid) {
		$('#foldernameedit'+editfolderid).css("display","inline");
		$('#confirmedit'+editfolderid).css("display","inline");
		$('#foldername'+editfolderid).css("display","none");
		$('#editicon'+editfolderid).css("display","none");
		$('#foldernameedit'+editfolderid).keyup(function (e) {
			if (e.keyCode == 13) {
				toggleName(editfolderid);
			}
		});
	}
	
	function toggleName(editfolderid) {
		$.ajax({
			type: "GET",
			url: "folderedit.php?action=editname&folderid="+editfolderid+"&value="+encodeURIComponent($('#foldernewname'+editfolderid).val()),
			success: function(){
	 
			}			
		});
		$('#folderlabel'+editfolderid).text($('#foldernewname'+editfolderid).val());
		$('#foldernameedit'+editfolderid).css("display","none");
		$('#confirmedit'+editfolderid).css("display","none");
		$('#foldername'+editfolderid).css("display","inline");
		$('#editicon'+editfolderid).css("display","inline");		
	}
		
    function addHit(data1)
    {
	$.ajax({
	   type: "POST",
	   url: "recordstat.php",
	   data: "reading_id="+data1,
	   success: function(){

	   }
	 });
    }
    function togglethat(nameOfDiv) {
	var contentbox = "#" + nameOfDiv + "box";
	var arrows = "#" + nameOfDiv + "title .toggleicon";
	$(contentbox).slideToggle();
	$(arrows).slideToggle(0);
    }
<?php if (isInstructor()) { ?>
  $(function() {
    $( "#readingList" ).sortable({
	start: function () {
	},
	stop: function () {
	},
	update: function( event, ui ) {
	    var priority = 0;
	    $( ".reading").each(function(){
		priority += 1;
		$(this).find('.sortInput').val(priority);
		var itemid = $(this).find('.idnum').text();
		$.ajax({
		   type: "POST",
		   url: "changeOrder.php",
		   data: "reading_id="+itemid+"&priority="+priority,
		   success: function(){

		   }
		 });
	    });
	}
    });
    $( "#readingList" ).enableSelection();
    $( "#readingList" ).sortable({ axis: 'y' });

    $( "#folderList" ).sortable({
	start: function () {
	},
	stop: function () {
	},
	update: function( event, ui ) {
		console.log("Folder reorder detected.");
	    var priority = 0;
	    $( ".folderobject").each(function(){
				priority += 1;
				$(this).find('.foldersortorder').val(priority);
				var itemid = $(this).find('.folderidnum').text();
				$.ajax({
				   type: "GET",
				   url: "folderedit.php?action=setorder&folderid="+itemid+"&value="+priority,
				   success: function(){
		
				   }
		 });
	    });
	}
    });
    $( "#folderList" ).enableSelection();
    $( "#folderList" ).sortable({ axis: 'y' });

  });
<?php } ?>
</script>
  <style>
    #readingList div span.ui-icon-arrowthick-2-n-s { position: absolute; }
    .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
	background-color: white;
	background-image: none;
    }
    .ui-state-default a, .ui-state-default a:link, .ui-state-default a:visited {
	color: blue;
    }
    .ui-state-highlight {
	height: 100px;
	width: 100%;
	background-color: #666666;
    }
    .pubtype {
	float: left;
	font-size: smaller;
	text-align: center;
	padding-right: 20px;
	width: 65px;
    }
    .pubtype img {
	max-width: 65px;
	max-height: 80px;
    }
    .sortInput {
	font-size: smaller;
	padding: 2px;
    }
    .topreadings {
	background-color: #CCCCCC;
	margin: 0px;
	padding: 5px;
    }
    .readingboxcontent {
	padding: 20px;
    }
    .sortable-placeholder {
	width: 100%;
	height: 100px;
	background-color: #666666;
    }
  </style>
<script type="text/javascript" src="websites.js"></script>

<?php

    if (isset($_GET['private']) && isInstructor()) {
        if (($_GET['private'] == 0) || ($_GET['private'] == 1)) {
            setListPrivacy($c,$_GET['private']);
        }
    }

	if (isset( $_COOKIE['import_folder_message'] )) {
		$import_folder_message = decryptCookie($_COOKIE['import_folder_message']);
		unset($_COOKIE['import_folder_message']);
		setcookie('import_folder_message', "", -1);
	}
    
    $privacy = getListPrivacy($c);
?>

        <?php
            if (isInstructor()) {
        ?>
        <div class="readingListLink"><a href="index.php"><strong><?php if (strlen($customparams['searchlabel']) > 0) { echo $customparams['searchlabel']; } else { echo "Search Library Resources"; } ?></strong></a> | <a href="copy_list.php"><strong>Import from Existing List</strong></a> | <a href="import_folder.php"><strong>Import from EBSCO Folder (beta)</strong></a> | This list is
	<?php
	
        if ($privacy == 0) { 
			?><strong><a href="reading_list.php?private=1">public</a></strong>.<?php
        } else {
            ?>
            <strong><a href="reading_list.php?private=0">private</a></strong>.
            <?php
        }
	
	?>
	</div>
        <?php
	
	if ($customparams['studentdata'] == "y") {
		$students = getStudentNamesList($c,decryptCookie($_COOKIE['currentListId']));
		$studentsStr = implode("</li><li>",$students);
	    echo '<div class="readingListLink"><span id="listStatstitle" onclick="togglethat(\'listStats\');"><strong>';
	    echo sizeof($students) . " student(s) have accessed this list.";
	    echo '</strong> <img src="web/right.png" class="toggleicon" /><img src="web/down.png" class="toggleicon" style="display:none;" /></span><div id="listStatsbox" style="display:none;">';
		echo '<div class="studentdata">Students that have accessed this list: ';
		if (sizeof($students) > 0) {
    		echo "<ul><li>".$studentsStr."</li></ul>";
		} else {
		    echo "No students have accessed this list.  A list of names will appear here when at least one student accesses it.";
		}
		echo '</div></div></div>';
	}

	    ?>
		<?php
		if(isset ($import_folder_message)){
			?><!--<div class="readingListLink"><h3 style="color:red;"><?php echo $import_folder_message;
			?></h3></div>--><?php		
			unset($import_folder_message);
		}
		?>

<div class="readingListLink" id="createFolder">
            <span id="foldertitle" onclick="togglethat('folder');"><strong>Add Folder</strong> <img src="web/right.png" class="toggleicon" /><img src="web/down.png" class="toggleicon" style="display:none;" /></span>
	    <div id="folderbox" style="display: none;">
            <table border="0">
                <tr>
                    <td><input type="text" name="foldertext" id="folder-text" size="50" placeholder="Folder name" /></td>
                    <td><button onclick="preAddToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>)">Create Folder</button></td>
                </tr>
            </table>
	    </div>
        </div>
		
		<div class="readingListLink" id="addInstructions">
            <span id="instructiontitle" onclick="togglethat('instruction');"><strong>Add Text or Instructions</strong> <img src="web/right.png" class="toggleicon" /><img src="web/down.png" class="toggleicon" style="display:none;" /></span>
	    <div id="instructionbox" style="display: none;">
            <table border="0">
                <tr>
                    <td>Text</td>
		    <?php if (count($listoffolders) > 0) { ?>
			<td>Folder</td>
		    <?php } ?>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><input type="text" name="text" id="inst-text" size="50" placeholder="e.g., Read Chapter 5 in Textbook" /></td>
		    <?php if (count($listoffolders) > 0) { ?>
<td><select id="folderselector-inst">
	<option value="0"><em>Main list of Readings</em></option>
	<?php
	    foreach ($listoffolders as $folder) {
		?>
		    <option value="<?php echo $folder['id']; ?>" <?php if (isset($clean['folderid']) && ($clean['folderid'] == $folder['id'])) { echo 'selected="selected"';}?>><?php echo $folder['label']; ?></option>
		<?php
	    }
	?>
    </select></td><?php } ?>
                    <td><button onclick="preAddToInstructions(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>)">Add to Reading List</button></td>
                </tr>
            </table>
	    </div>
        </div>

		<div class="readingListLink" id="addWebsite">
            <span id="weblinktitle" onclick="togglethat('weblink');"><strong>Add Web Resource</strong> <img src="web/right.png" class="toggleicon" /><img src="web/down.png" class="toggleicon" style="display:none;" /></span>
	    <div id="weblinkbox" style="display: none;">
            <table border="0">
                <tr>
                    <td>URL</td>
                    <td>Title</td>
		    <?php if (count($listoffolders) > 0) { ?>
			<td>Folder</td>
		    <?php } ?>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><input type="text" name="url" id="ws-url" size="40" placeholder="e.g., http://library.edu/" /></td>
                    <td><input type="text" name="title" id="ws-title" size="25" placeholder="Link label" /></td>
		    <?php if (count($listoffolders) > 0) { ?>
<td><select id="folderselector-ws">
	<option value="0"><em>Main list of Readings</em></option>
	<?php
	    foreach ($listoffolders as $folder) {
		?>
		    <option value="<?php echo $folder['id']; ?>" <?php if (isset($clean['folderid']) && ($clean['folderid'] == $folder['id'])) { echo 'selected="selected"';}?>><?php echo $folder['label']; ?></option>
		<?php
	    }
	?>
    </select></td><?php } ?>
                    <td><button onclick="preAddToWebsites(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>)">Add to Reading List</button></td>
                </tr>
            </table>
	    </div>
        </div>

        <?php } ?>
    <?php if (count($listoffolders) > 0) {
	?>
    
	    <div class="folders readingListLink">
		<?php
		    if (isset($clean['folderid'])) {
			?>
			    <a href="reading_list.php"><strong><img src="web/back.png" style="border: none; max-height: 12px;"> Back to main list</strong></a>
			<?php
		    } else {
			?>
			    <strong>This list has folders:</strong>
			<?php
		    }
		?>

		<ul id="folderList" class="listoffolders">
	<?php
	    foreach ($listoffolders as $folder) {
		?>
		    <li class="folderobject"><span style="display: none;" class="folderidnum"><?php echo $folder['id']; ?></span><input type="hidden" class="foldersortorder" value="<?php echo $folder['sortorder']; ?>" /><span id="foldername<?php echo $folder['id']; ?>"><?php
		    if (isset($clean['folderid']) && ($folder['id'] == $clean['folderid'])) {
			echo "<strong id=\"folderlabel".$folder['id']."\">".$folder['label']."</strong></span> <span id=\"foldernameedit".$folder['id']."\" style=\"display:none;\"><input id=\"foldernewname".$folder['id']."\" type=\"text\" value=\"".$folder['label']."\" style=\"height: 12px;font-size: small;\" /></span> (currently selected)";
		    } else {
			echo '<a href="reading_list.php?folderid='.$folder['id'].'" id="folderlabel'.$folder['id'].'">'.$folder['label']."</a></span> <span id=\"foldernameedit".$folder['id']."\" style=\"display:none;\"><input id=\"foldernewname".$folder['id']."\" type=\"text\" value=\"".$folder['label']."\" style=\"height: 12px;font-size: small;\" /></span> (".folderitemcount($c,$folder['id'])." items)";
		    }
		    
		    if (isInstructor()) {
		    ?> <img src="web/confirm.gif" onclick="toggleName(<?php echo $folder['id']; ?>)" id="confirmedit<?php echo $folder['id']; ?>" onclick="confirmfoldernameedit();" style="display:none;max-height: 12px; border: none;" /><img src="web/edit.png" id="editicon<?php echo $folder['id']; ?>" onclick="enableEditFolder(<?php echo $folder['id']; ?>);" style="max-height: 12px; border: none;" /> <img src="web/drag.png" style="max-height: 12px; border: none;"> <img src="web/delete.png" style="max-height: 12px; border: none;" onclick="return deletefolderobject(<?php echo $folder['id'].",".decryptCookie($_COOKIE['currentListId']); ?>);"/></li>
		<?php
		    }
	    }
	?>		</ul>
	    </div>


    <?php } ?>
    <form action="reorder-reading-list.php" method="GET" id="reordernotes">

        <div id="readingList">

            <?php
                $count = 0;
                if (count($readingList) > 0) {
                    foreach ($readingList as $reading) {
			$comparison = '';
			$found = 0;
			if (($reading['type'] == 1) && ($useCache == false)) {
			    if (isset($results['records'])) {
				foreach ($results['records'] as $result) {
				    if ($found == 0) {
					if (($reading['an'] == $result['An']) && ($reading['db'] == $result['DbId'])) {
					    $found = 1;
					    $readingMetadata = $result;					    
					} else {					    
					    if (!empty($result['RecordInfo']['BibEntity']['Titles'])){
						foreach($result['RecordInfo']['BibEntity']['Titles'] as $Ti){
						    $title = $Ti['TitleFull'];
						}
					    }
					    
					    $readingtitle = html_entity_decode($reading['title'], ENT_QUOTES, 'UTF-8');
					    //$readingtitle = $reading['title'];
					    $comparison .= "<br /><br /><textarea style='width:100%;'>".$readingtitle." (from MYSQL)\n".htmlentities($title)." (from API)</textarea>";
					    if (((htmlentities($title) == $readingtitle) || ($title == $readingtitle)) && ($reading['an'] == $result['An'])) {
						$found = 1;
						$readingMetadata = $result;
					    }					    
					}
				    }
				}
			    } else {

			    }
			} else {
			    $readingMetadata = array();
			    $found = 1;
			}
			
                        $count++;
			if (($reading['type'] == 1) && ($found == 0)) {
			    //echo $comparison;
			    echo "<div class='reading notfound ui-state-default' style='display:block'>";
			    $comparison = '';
			} else {
                            echo "<div class='reading ui-state-default' style='display:block'>";
			}
			
                        if (isInstructor()) { ?>
			    <div class="topreadings" id="topbar<?php echo $reading['id']; ?>"><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><span style="padding-left:20px;font-size:smaller;"><em>Sort Order:</em> <input type="text" size="3" class="sortInput" onclick="document.getElementById('savebutton<?php echo $reading['id']; ?>').style.display = '';" name="priority<?php echo $reading["id"]; ?>" value="<?php echo $reading["priority"]; ?>" /><input type="submit" class="savebutton" id="savebutton<?php echo $reading['id']; ?>" style="display: none;" value="Save Changes" /></span> <div class='idnum' style='display:none;'><?php echo $reading['id']; ?></div>

    <div style="text-align:right;float:right;">
    
    
<div id="notinfolder<?php echo $count; ?>" class="folderitem" style="font-size: smaller; display: none;">
        
    <span><span style="font-size:smaller; padding:3px;" class="addFolder">Removing...</span></span>
    </div>
    <!-- END item in NOT in folder -->
    <!-- If the item is in the folder... -->
    <div id="infolder<?php echo $count; ?>" class="folder" style="font-size: smaller; display: block;">
    <?php if (sizeof($listoffolders) > 0) { ?>
    <span class="folderitemsedit">Move to folder: <select style="font-size:smaller;" id="folderselector<?php echo $reading['id']; ?>" onchange="addreadingtofolder(this,<?php echo $reading['id']; ?>);">

	<option value="0"><em>No Folder</em></option>
	<?php
	    foreach ($listoffolders as $folder) {
		?>
		    <option value="<?php echo $folder['id']; ?>" <?php if ($reading['folderid'] == $folder['id']) { echo 'selected="selected"';}?>><?php echo $folder['label']; ?></option>
		<?php
	    }
	?>
    </select><?php } ?></span><button type="button"style="font-size:smaller; padding:3px; margin-top:0px;" class="removeFolder" id="removebutton<?php echo $count;?>" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $reading['an']; ?>', '<?php echo $reading['db']; ?>','<?php echo $reading['url']; ?>','<?php echo urlencode(html_entity_decode(urldecode($reading['instruct']))); ?>','<?php echo urlencode($reading['title']); ?>',2,<?php echo $count; ?>,<?php echo $reading["priority"]; ?>,<?php echo $reading["type"]; ?>); return false;">Delete</button>

</div>


</div>
</div>
                        <?php
                        }
?>
<div class="readingboxcontent"<?php if (!empty($readingMetadata['pubType'])) { echo ' style="min-height:85px"'; }?>>
<?php
                  if (!empty($readingMetadata['pubType'])) { ?>

                <div class="pubtype table-cell" style="text-align: center; float:left;">  

                    <?php if (!empty($readingMetadata['ImageInfo'])) { ?>                    

                                <img class="pt-cover" src="<?php echo fixprotocol($readingMetadata['ImageInfo']['thumb']); ?>" />                                                                       

                    <?php }else{ 

                     $pubTypeId =  $readingMetadata['PubTypeId'];                    

                     $pubTypeClass = "pt-".$pubTypeId;

                    ?>

                    <span class="pt-icon <?php echo $pubTypeClass?>"></span>

                    <?php } ?>

                    <div class="pt-label"><?php echo $readingMetadata['pubType'] ?></div>

                </div>     

                <?php }
		echo "<div class='readingmetablock' style='overflow:hidden;'>";
                        echo "<span class='readingtitle'>";
			
                        if (($reading["type"] == 1) && ($useCache == false)) {
			    if ($found == 1) {
				echo "<a ";
				if ((!(isInstructor())) && ($customparams['studentdata'] == "y")){
				    echo "onclick='addHit(".$reading["id"].")' ";
				}
				echo "href='record.php?an=" . $reading["an"] . "&db=" . $reading["db"];
				if (isset($clean['folderid'])) { echo "&folderid=" . $clean['folderid']; }
				echo "'>";

				if (!empty($readingMetadata['RecordInfo']['BibEntity']['Titles'])){
				    foreach($readingMetadata['RecordInfo']['BibEntity']['Titles'] as $Ti){
					echo  $Ti['TitleFull'];
				    }
				} else {
				    echo $reading['title'];
			        }
				
				echo "</a>";
				
				if(!empty($readingMetadata['Items']['TiAtl'])){ 
				    foreach($readingMetadata['Items']['TiAtl'] as $TiAtl){ 
					echo $TiAtl['Data']; 
				    }
                                }
			    } else {
				echo "There was a problem loading this reading.  Your library may no longer subscribe to <em>".$reading["title"]."</em>.  Please contact your library for further assistance.";
			    }
                        } else if ($reading["type"] == 1) {
                            echo "<strong><a onclick='addHit(".$reading["id"].")' href='record.php?an=" . $reading["an"] . "&db=" . $reading["db"];
			    if (isset($clean['folderid'])) { echo "&folderid=" . $clean['folderid']; }
			    echo "'>" . html_entity_decode($reading["title"]) . "</a></strong>";
			} else if ($reading["type"] == 2) {
                            echo "<a onclick='addHit(".$reading["id"].")' href='" . urldecode($reading["url"]) . "' target='_blank'>" . html_entity_decode($reading["title"]) . "</a>  <em>(website launches in a new window)</em>";
                        } else if ($reading["type"] == 3) {
                            echo "<strong>".html_entity_decode($reading["instruct"])."</strong>";
                        }
			echo "</span>";
			
			if (($reading['type'] == 1) && $found && (!($useCache))) {
			    echo "<br /><span class='readingMetadata'>";
			    ?>

                        <?php if (!empty($readingMetadata['Items']['Au'])) { ?>
			    <div class="authors">
				<span>
				    <span style="font-style: italic;">By : </span>                                            
				     <?php foreach($readingMetadata['Items']['Au'] as $Author){ ?>
				        <?php if (isInstructor()) { ?>
					<?php echo $Author['Data']; ?>;
					<?php } else {
					    echo strip_tags($Author['Data'],'<sup><sup/></sup>');
					} ?>
				     <?php } ?>
				</span>                        
			    </div>
                        <?php } ?>

			<div class="authors">
                        <span style="font-style: italic; ">
                        <?php if(isset($readingMetadata['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'])){?>                                                 
                             <?php foreach($readingMetadata['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'] as $title){ ?>
                               <?php echo $title['TitleFull']?>,                                  
                        <?php }}?>
                        </span>
                        <?php if(!empty($readingMetadata['RecordInfo']['BibEntity']['Identifiers'])){
                                 foreach($readingMetadata['RecordInfo']['BibEntity']['Identifiers'] as $identifier){
                                     $pieces = explode('-',$identifier['Type']); 
                                     if(isset($pieces[1])){                                       
                                       echo strtoupper($pieces[0]).'-'.ucfirst( $pieces[1]);
                                       }else{ 
                                       echo strtoupper($pieces[0]);
                                       }?>: <?php echo $identifier['Value']?>,                                                                
                        <?php }} ?>
                        <?php if(isset($readingMetadata['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'])){?>
                             <?php foreach($readingMetadata['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'] as $identifier){
                                    $pieces = explode('-',$identifier['Type']);
                                    if(isset($pieces[1])){                                        
                                       }else{ 
                                       echo strtoupper($pieces[0]);
                                       }?>: <?php echo $identifier['Value']?>, 
                             <?php }?>  
                        <?php }?>
                        <?php if(isset($readingMetadata['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'])){?>
                             <?php foreach($readingMetadata['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'] as $date){ ?>
                                 Published: <?php echo $date['M']?>/<?php echo $date['D']?>/<?php echo $date['Y']?>, 
                             <?php }?> 
                        <?php }?>
                        <?php if(isset($readingMetadata['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'])){ 
                                foreach($readingMetadata['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'] as $number){?>
                                  <?php $type = str_replace('volume','Vol',$number['Type']); $type = str_replace('issue','Issue',$type); ?>
                                    <?php echo $type;?>: <?php echo $number['Value']; ?>, 
                        <?php } } ?>
                        <?php if(!empty($readingMetadata['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage'])){?>
                                 Start Page: <?php echo $readingMetadata['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage']?>, 
                        <?php } ?>                        
                        <?php if(!empty($readingMetadata['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination'])){ ?>
                                 Page Count: <?php echo $readingMetadata['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination']?>, 
                        <?php } ?>
                        <?php if(!empty($readingMetadata['RecordInfo']['BibEntity']['Languages'])){ ?>
                        <?php foreach($readingMetadata['RecordInfo']['BibEntity']['Languages'] as $language){ ?> 
                                 Language: <?php echo $language['Text']?>
                        <?php } }?>
                        </div>
			
			<?php
			    echo "</span>";
			}
                        
			
			
			
		     if ($reading['type'] == 1) {
                     $fulltextlinkfound = false;

                      ?>





                      <?php if(($readingMetadata['HTML']==1) || (!empty($readingMetadata['PDF']))) { ?>
                      <div class="links fulltextlink-rl">

                         <?php if($readingMetadata['HTML']==1){
                           $fulltextlinkfound = true;
                           ?> 

                          <?php if((!isset($_COOKIE['login']))&&$readingMetadata['AccessLevel']==2){ ?> 

                        <a <?php
							if ((!(isInstructor())) && ($customparams['studentdata'] == "y")){
				    echo "onclick='addHit(".$reading["id"].")' ";
				}
			?>class="icon html fulltext" href="login.php?path=HTML&an=<?php echo $readingMetadata['An']; ?>&db=<?php echo $readingMetadata['DbId']; ?>&<?php echo $encodedHighLigtTerm ?>&resultId=<?php echo $readingMetadata['ResultId'];?>&recordCount=<?php echo $readingMetadata['recordCount']?>&<?php echo $encodedSearchTerm;?>&fieldcode=<?php echo $fieldCode;
			if (isset($clean['folderid'])) { echo "&folderid=" . $clean['folderid']; }
			?>">Full Text</a>

                          <?php } else {?>

                        <a <?php
							if ((!(isInstructor())) && ($customparams['studentdata'] == "y")){
				    echo "onclick='addHit(".$reading["id"].")' ";
				}
			?>class="icon html fulltext" href="record.php?an=<?php echo $readingMetadata['An']; ?>&db=<?php echo $readingMetadata['DbId']; ?><?php 
			if (isset($clean['folderid'])) { echo "&folderid=" . $clean['folderid']; }
			?>#html">Full Text</a>

                         <?php } ?>                          

                        <?php } ?>

                        <?php if(!empty($readingMetadata['PDF'])){
                           $fulltextlinkfound = true;
                           ?> 

                          <a target="_blank" <?php
							if ((!(isInstructor())) && ($customparams['studentdata'] == "y")){
				    echo "onclick='addHit(".$reading["id"].")' ";
				}
			?>class="icon pdf fulltext" href="PDF.php?an=<?php echo $readingMetadata['An']?>&db=<?php echo $readingMetadata['DbId']?>">Full Text</a>

                        <?php } ?>

                      </div>

                      <?php }
                      
                      if (!empty($readingMetadata['CustomLinks'])){ ?>

                      <div class="custom-links">

                      <?php if (count($readingMetadata['CustomLinks'])<=3){?> 

                    

                            <?php foreach ($readingMetadata['CustomLinks'] as $customLink) {
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                              $fulltextlinkfound = true;
                              ?>

                                <div class="fulltextlink-rl">

                                 <a target="_blank" <?php
							if ((!(isInstructor())) && ($customparams['studentdata'] == "y")){
				    echo "onclick='addHit(".$reading["id"].")' ";
				}
			?>href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php if (isset($customLink['Icon']) && (strlen($customLink['Icon']) > 0)) { ?><img src="<?php echo fixprotocol($customLink['Icon']); ?>" /><?php } else { echo "<img src='web/iconFTAccessSm.gif' />"; } ?> <?php echo $customLink['Text']; ?></a>

                                </div>

                            <?php }
                            } ?>

                    

                      <?php } else {?>

                    

                            <?php for($i=0; $i<3 ; $i++){
                                if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;

                                $customLink = $readingMetadata['CustomLinks'][$i];

                                ?>

                                <div class="fulltextlink-rl"> 

                                   <a target="_blank" <?php
							if ((!(isInstructor())) && ($customparams['studentdata'] == "y")){
				    echo "onclick='addHit(".$reading["id"].")' ";
				}
			?>href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php echo $customLink['Name']; ?></a>

                                </div>

                            <?php } ?>

                    

                      <?php }
                      } ?>                   

                      </div>                      

                      <?php } ?>

                      <?php if (!empty($readingMetadata['FullTextCustomLinks'])){ ?>

                      <div class="custom-links">

                      <?php if (count($readingMetadata['FullTextCustomLinks'])<=3){?>                     

                            <?php foreach ($readingMetadata['FullTextCustomLinks'] as $customLink) {
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;
                              
                              ?>

                                <div class="fulltextlink-rl">

                                 <a target="_blank" <?php
							if ((!(isInstructor())) && ($customparams['studentdata'] == "y")){
				    echo "onclick='addHit(".$reading["id"].")' ";
				}
			?>href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php if ((isset($customLink['Icon'])) && (strlen($customLink['Icon']))) { ?><img src="<?php echo fixprotocol($customLink['Icon']); ?>" /><?php }  else { echo "<img src='web/iconFTAccessSm.gif' />"; } ?> <?php echo $customLink['Name']; ?></a>

                                </div>

                            <?php } ?>                    

                      <?php }
                      } else {?>                    

                            <?php for($i=0; $i<3 ; $i++){
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;

                                $customLink = $readingMetadata['FullTextCustomLinks'][$i];

                                ?>

                                <div class="fulltextlink-rl"> 

                                   <a target="_blank" <?php
							if ((!(isInstructor())) && ($customparams['studentdata'] == "y")){
				    echo "onclick='addHit(".$reading["id"].")' ";
				}
			?>href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php echo $customLink['Name']; ?></a>

                                </div>

                            <?php }
                            
                            } ?>

                    

                      <?php } ?>                   

                      </div>                     

                      <?php } ?>
                      <?php if (count($readingMetadata['Items'])) {
                        
                        foreach($readingMetadata['Items'] as $item) {
                           if ($item[0]['Group'] == "URL") {
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;

                              ?>
                              
                              <div class="custom-links">
                                 <div class="fulltextlink-rl">
                                 <?php

                                          if (substr_count($item[0]['Data'],"http")) {
                                             $customlinkURL = substr($item[0]['Data'],strpos($item[0]['Data'],"http"));
                                             
                                             if (strpos($customlinkURL," ") > -1) {
                                             $customlinkURL = substr($customlinkURL,0,strpos($customlinkURL," "));
                                             }

                                             if (strpos($customlinkURL,"<") > -1) {
                                             $customlinkURL = substr($customlinkURL,0,strpos($customlinkURL,"<"));
                                             }

                                             if (strpos($customlinkURL,"\"") > -1) {
                                             $customlinkURL = substr($customlinkURL,0,strpos($customlinkURL,"\""));
                                             }
                                             
                                             echo "<a target='_blank' ";
							if ((!(isInstructor())) && ($customparams['studentdata'] == "y")){
				    echo "onclick='addHit(".$reading["id"].")' ";
				}
					     echo "href='".$customlinkURL."'><img src='web/iconFTAccessSm.gif' /> Online Access</a>";
                                          } else {
                                             $docLinks = new DOMDocument();
                                             $docLinks->loadHTML(html_entity_decode($item[0]['Data']));
                                                foreach($docLinks->getElementsByTagName('a') as $linkfromcatalog) {
                                                   if ($linkfromcatalog->nodeValue == $linkfromcatalog->getAttribute('href')) {
                                                      $linkfromcatalog->nodeValue = "Online Access";
                                                   }
                                                }
                                             $newlinks = $docLinks->saveHTML();
                                             $newlinks = str_replace("<a","<img src='web/iconFTAccessSm.gif' /> <a target='_blank'",$newlinks);
                                             echo $newlinks;
                                          }                                    
                                    ?></div>
                              </div>
                              
                              <?php
                           }
                        }
                        }
                        
                      } 
		     }
			
			
			
			
                        if (isInstructor()) {
			    if (($customparams['studentdata'] == "y") && ($reading['type'] != "3")) {
				    $students = getStudentNamesReadings($c,$reading["id"]);
				    $studentsStr = implode("</li><li>",$students);
				    echo '<div class="studentcount" style="padding-top:10px;" id="readingdata'.$reading['id'].'title" onclick="togglethat(\'readingdata'.$reading['id'].'\')"><strong>';
				    echo sizeof($students).' student(s) have clicked this reading.</strong>';
				    echo '<img src="web/right.png" class="toggleicon" /><img src="web/down.png" class="toggleicon" style="display:none;" /></div><div class="studentdata" id="readingdata'.$reading['id'].'box" style="display:none;">Students that have clicked this reading: ';
				    if (sizeof($students) > 0) {
				    echo "<ul><li>".$studentsStr."</li></ul>";
				    } else {
					echo "No students have read this item.  A list of names will appear here when at least one student clicks this item.";
				    }
				    echo '</div>';
			    }
                            echo '<div class="notes"><span id="notes'.$reading['id'].'" onclick="togglethat(\'notes'.$reading['id'].'\');"><strong>';
			    if (strlen($reading['notes']) > 0) {
				echo 'Edit';
			    } else {
				echo 'Add';
			    }
			    echo ' Notes</strong>  <img src="web/right.png" class="toggleicon" /><img src="web/down.png" class="toggleicon" style="display:none;" /></span><div id="notes'.$reading['id'].'box" style="display:none;"><textarea onkeyup="textCounter(this,\'textcounter'.$reading['id'].'\',5000);" name="notes' . $reading["id"] . '" style="width:100%">' . html_entity_decode($reading["notes"]) . '</textarea><br /><span style="font-size:smaller; color: #666666;">Characters remaining: <span id="textcounter'.$reading['id'].'">'.(5000 - strlen(html_entity_decode($reading["notes"]))).'</span></span><br /><button class="addFolder">Save Notes</button></div></div>';
                        } else {
                            if (strlen($reading["notes"]) > 0) {
                                echo '<div class="studentnotes">' . html_entity_decode($reading["notes"]) . '</div>';
                            }
                        }
                        
                        if (isInstructor()) {
                            ?>
    <div class="buttons">
    <!-- foldering... -->                        
    <table style="display: none;">
    <tr>
	<td>
    <div id="notinfolder<?php echo $count; ?>" class="folderitem" style="font-size: 11px; display: none; margin:12px 0 12px 0;">
        
    <span><span class="addFolder">Removing... please wait.</span></span>
    </div>
    <!-- END item in NOT in folder -->
    <!-- If the item is in the folder... -->
    <div id="infolder<?php echo $count; ?>" class="folder" style="font-size: 11px; display: block; margin:12px 0 12px 0;">
    
    <button class="removeFolder" id="removebutton<?php echo $count;?>" onclick="addToFolder(xmlhttp,<?php echo ($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $reading['an']; ?>', '<?php echo $reading['db']; ?>','<?php echo $reading['url']; ?>','<?php echo urlencode(html_entity_decode(urldecode($reading['instruct']))); ?>','<?php echo urlencode($reading['title']); ?>',2,<?php echo $count; ?>,<?php echo $reading["priority"]; ?>,<?php echo $reading["type"]; ?>); return false;">Remove from Reading List</button>

    </div>
	    
	</td>
	<td>
    <button class="addFolder">Update Notes and Sort Order</button>	    
	</td>
    </tr>
</table></div>                     
                            <?php
                        }
                        echo "</div></div></div>";
                    }
                } else {
                    echo "<div class='reading'><div class='readingboxcontent'>Currently, there are no readings.</div></div>";
                }
		if ($count == 0) {
		    if (sizeof($listoffolders) > 0) {
			echo "<div class='reading'><div class='readingboxcontent'>Select a folder above to see readings.</div></div>";
		    } else {
			echo "<div class='reading'><div class='readingboxcontent'>Currently, there are no readings on this list.</div></div>";
		    }
		}

            ?>

        </div>
        </form>
