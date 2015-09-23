<script type="text/javascript">
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
	    //$( ".buttons" ).css("display","none");
	    //$( ".notes" ).css("display","none");
	    //$( ".studentcount" ).css("display","none");
	    //$( ".reading ").css("height","auto");
	    //$( ".authors ").css("display","none");
	    //$( ".pt-icon").css("display","none");
	    //$( ".pt-cover").css("display","none");

	},
	stop: function () {
	    //$( ".buttons" ).css("display","block");
	    //$( ".notes" ).css("display","block");
	    //$( ".studentcount" ).css("display","block");
	    //$( ".reading ").css("height","");
	    //$( ".authors ").css("display","block");
	    //$( ".pt-icon").css("display","");
	    //$( ".pt-cover").css("display","");

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
        <div class="readingListLink"><a href="index.php"><strong>Search for Library Resources</strong></a> | <a href="copy_list.php"><strong>Import from Existing List</strong></a> | <a href="import_folder.php"><strong>Import from EBSCO Folder (beta)</strong></a> | This list is
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

		<div class="readingListLink" id="addInstructions">
            <span id="instructiontitle" onclick="togglethat('instruction');"><strong>Add Text or Instructions</strong> <img src="web/right.png" class="toggleicon" /><img src="web/down.png" class="toggleicon" style="display:none;" /></span>
	    <div id="instructionbox" style="display: none;">
            <table border="0">
                <tr>
                    <td>Text</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><input type="text" name="text" id="inst-text" size="50" placeholder="e.g., Read Chapter 5 in Textbook" /></td>
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
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><input type="text" name="url" id="ws-url" size="40" placeholder="e.g., http://library.edu/" /></td>
                    <td><input type="text" name="title" id="ws-title" size="25" placeholder="Link label" /></td>
                    <td><button onclick="preAddToWebsites(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>)">Add to Reading List</button></td>
                </tr>
            </table>
	    </div>
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
					    if ((htmlentities($title) == $readingtitle) || ($title == $readingtitle)) {
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
    
    <button type="button"style="font-size:smaller; padding:3px; margin-top:0px;" class="removeFolder" id="removebutton<?php echo $count;?>" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $reading['an']; ?>', '<?php echo $reading['db']; ?>','<?php echo $reading['url']; ?>','<?php echo urlencode(html_entity_decode(urldecode($reading['instruct']))); ?>','<?php echo urlencode($reading['title']); ?>',2,<?php echo $count; ?>,<?php echo $reading["priority"]; ?>,<?php echo $reading["type"]; ?>); return false;">Delete</button>

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

                                <img class="pt-cover" src="<?php echo $readingMetadata['ImageInfo']['thumb']; ?>" />                                                                       

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
				echo "href='record.php?an=" . $reading["an"] . "&db=" . $reading["db"] . "'>";

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
                            echo "<strong><a onclick='addHit(".$reading["id"].")' href='record.php?an=" . $reading["an"] . "&db=" . $reading["db"] . "'>" . html_entity_decode($reading["title"]) . "</a></strong>";
			} else if ($reading["type"] == 2) {
                            echo "<a onclick='addHit(".$reading["id"].")' href='" . $reading["url"] . "' target='_blank'>" . html_entity_decode($reading["title"]) . "</a>  <em>(website launches in a new window)</em>";
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
			    echo ' Notes</strong>  <img src="web/right.png" class="toggleicon" /><img src="web/down.png" class="toggleicon" style="display:none;" /></span><div id="notes'.$reading['id'].'box" style="display:none;"><textarea name="notes' . $reading["id"] . '" style="width:100%">' . html_entity_decode($reading["notes"]) . '</textarea><br /><button class="addFolder">Save Notes</button></div></div>';
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
    
    <button class="removeFolder" id="removebutton<?php echo $count;?>" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $reading['an']; ?>', '<?php echo $reading['db']; ?>','<?php echo $reading['url']; ?>','<?php echo urlencode(html_entity_decode(urldecode($reading['instruct']))); ?>','<?php echo urlencode($reading['title']); ?>',2,<?php echo $count; ?>,<?php echo $reading["priority"]; ?>,<?php echo $reading["type"]; ?>); return false;">Remove from Reading List</button>

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
		    echo "<div class='reading'><div class='readingboxcontent'>Currently, there are no readings on this list.</div></div>";
		}

            ?>

        </div>
        </form>
