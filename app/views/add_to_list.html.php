<?php
	// save current language to cookie by default
	if (!isset($_SESSION["language"])){
		$_SESSION["language"]=$customparams['language'];
	}
	// check if parameters are passed from url, language change 
	if (isset($_REQUEST["language"])) {
		$_SESSION["language"]=urldecode($_REQUEST["language"]);
	}
	$language = $_SESSION["language"];
	if (stripos($language,"utf-8")===false) {
	   $language.=".UTF-8";
	}
	putenv("LC_ALL=$language");
	setlocale(LC_ALL, $language);
	if (defined('LC_MESSAGES')) // available if PHP was compiled with libintl
	{
	   setlocale(LC_MESSAGES, $language);
	}   
	else
	{       
	   setlocale(LC_ALL, $language);
	}       
			
	bindtextdomain("messages", dirname(__FILE__)."/locale");                                
	bind_textdomain_codeset('messages', 'UTF-8');
	textdomain("messages");



libxml_use_internal_errors(true);

$folderitemsarray = getFolderItems($c);

?>
<?php if (!(isInstructor())) {
echo '<div class="readingListLink"><a href="reading_list.php">Back to Reading List</a></div>';        
} ?>

<div id="toptabcontent">
    <?php
      if (isset($clean['resultId'])) {
    ?>
    <div class="topbar">
       <div style="padding-top: 6px; float: left" ><a style="color: #ffffff;margin-left: 15px;" href="results.php?<?php echo $encodedQuery;?>&fieldcode=<?php echo urlencode($clean['fieldcode']);?>&<?php echo $queryStringUrl; ?>&back=y&backpath=<?php echo urlencode($backpath); ?>"> << Back to Results</a></div>
      <div style="float: right;margin: 7px 20px 0 0;color: white">
          <?php if($clean['resultId']>1){  ?>
           <a href="recordSwich.php?<?php echo $encodedQuery;?>&fieldcode=<?php echo urlencode($clean['fieldcode']);?>&resultId=<?php echo ($clean['resultId']-1)?>&<?php echo $queryStringUrl; ?>&backpath=<?php echo urlencode($backpath); ?>"><span class="results-paging-previous">&nbsp;&nbsp;&nbsp;&nbsp;</span></a>
            <?php }
            echo $clean['resultId'].' of '.$clean['recordCount'];
           if($clean['resultId']<$clean['recordCount']){  ?>
           <a href="recordSwich.php?<?php echo $encodedQuery;?>&fieldcode=<?php echo urlencode($clean['fieldcode']);?>&resultId=<?php echo ($clean['resultId']+1)?>&<?php echo $queryStringUrl; ?>&backpath=<?php echo urlencode($backpath); ?>"><span class="results-paging-next">&nbsp;&nbsp;&nbsp;&nbsp;</span></a>
           <?php   } ?>
      </div>
    </div>
    <?php
      }
    ?>

     <div class="record table">
<?php if ($error) { ?>
    <div class="error">
        <?php echo _("Uh oh!  It looks like the article may no longer be available through your library.  Please let your library and teacher know about this error message: ");?><?php echo $error; ?>
    </div>
<?php } ?>
<?php if((!isset($_COOKIE['login']))&&$result['AccessLevel']==1){ ?>
         <p><?php echo _("This record from ");?><b>[<?php echo $result['DbLabel']; ?>]</b><?php echo _(" cannot be displayed to guests.");?><br><a href="login.php?path=record&db=<?php echo $clean['db']?>&an=<?php echo $clean['an']?>&<?php echo $encodedHighLigtTerm;?>&resultId=<?php echo $clean['resultId'] ?>&recordCount=<?php echo $clean['recordCount'] ?>&<?php echo $encodedQuery;?>&fieldcode=<?php echo urlencode($clean['fieldcode']); ?>"><?php echo _("Login")."</a>"._(" for full access.");?></p>
<?php }else{ ?>     
    <h1>
      <?php if (!empty($result['Items'])) { 
        echo $result['Items'][0]['Data'];
        $resultTitle = $result['Items'][0]['Data'];
       } ?>
    </h1>       
<?php if ((isInstructor())&&(!(isset($error)))) { ?>
<div class="foldersblock" style="padding-top:10px;padding-bottom:20px;">
<!--
  
Foldering
   Display 'add to folder' link to allow logged in users to save items.
   Uses AJAX to automatically insert and remove items from folders without reloading page.
  
-->

<!-- If the user is logged in -->
<!-- If the item is not in the folder -->
	<div id="notinfolder1" class="folderitem" style="font-size: 11px; display: <?php 
		if (itemInFolder($folderitemsarray,$result['An'],$result['DbId'])) {
  			echo "none";
		} else {
  			echo "inline";
		}
?>;">

<button class="addFolder" id="addbutton1" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $result['An']; ?>', '<?php echo $result['DbId']; ?>','none','none','<?php echo urlencode($resultTitle); ?>',1,1,1,1); alert('Added.'); window.close();"><?php echo _("Add to Reading List"?;?></button>

</div>
<!-- END item in NOT in folder -->
<!-- If the item is in the folder... -->
<div id="infolder1" class="folder" style="font-size: 11px; display: <?php 

		if (itemInFolder($folderitemsarray,$result['An'],$result['DbId'])) {
  			echo "inline";
		} else {
  			echo "none";
		}
?>;">
<button class="removeFolder" id="removebutton1" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $result['An']; ?>', '<?php echo $result['DbId']; ?>','none','none','<?php echo urlencode($resultTitle); ?>',2,1,1,1)"><?php echo _("Remove from Reading List");?></button>

</div>
<!-- END item is in the folder -->
<!-- END user is logged in -->
<!-- END folders -->
</div>
<?php } ?>
         <div>
             <div class="table-cell floatleft">
                 <?php if(!empty($result['PLink'])){?>
                 <ul class="table-cell-box">
                      <li>
                          <a href="<?php echo $result['PLink'] ?>">
                        <?php if (isset($customparams['EDSlabel'])) { echo $customparams['EDSlabel']; } else { echo 'See it in EDS'; } ?>
                        </a>
                      </li>
                  </ul>
                      <?php } ?> 
                 
                     <?php if(!empty($result['PDF'])||(isset($result['HTML'])&&$result['HTML']==1)){?>
                     <ul class="table-cell-box">
                     
                     
                     <?php if(!empty($result['PDF'])){?>
                      <li>
                          <a target="_blank" class="icon pdf fulltext" href="PDF.php?an=<?php echo $result['An']?>&db=<?php echo $result['DbId']?>">
                        PDF full text
                        </a>
                      </li>
                      <?php } ?>
                      <?php if($result['HTML']==1){ ?>
                      <?php if((!isset($_COOKIE['login']))&&$result['AccessLevel']==2){ ?> 
                      <li>
                         <a target="_blank" class="icon html fulltext" href="login.php?path=HTML&an=<?php echo $clean['an']; ?>&db=<?php echo $clean['db']; ?>&<?php echo $encodedHighLigtTerm ?>&resultId=<?php echo $clean['resultId'];?>&recordCount=<?php echo $clean['recordCount']?>&<?php echo $encodedQuery;?>&fieldcode=<?php echo urlencode($clean['fieldcode']); ?>">
                        <?php echo _("HTML full text");?>
                        </a>
                      </li>
                          <?php } else{?>
                      <li>
                          <a class="icon html fulltext" href="#html"><?php echo _("HTML Full Text");?></a>                       
                      </li>                      
                         <?php } ?>           
                      <?php } ?>
                      </ul>
                      <?php } ?>
                      
                      <?php if (!empty($result['CustomLinks'])) { ?>                     
                      <ul class="table-cell-box">
                          
                            <?php foreach ($result['CustomLinks'] as $customLink) { ?>
                                <li>
                                    <a href="<?php echo $customLink['Url']; ?>" title="<?php echo $customLink['MouseOverText']; ?>"><img src="<?php if ((isset($customLink['Icon'])) && (strlen($customLink['Icon']) > 0)) { echo $customLink['Icon']; } else { echo "web/iconFTAccessSm.gif"; } ?>" /> <?php echo $customLink['Text']; ?></a>
                                </li>
                            <?php } ?>
                       </ul>
                      <?php } ?>
                     <?php if (!empty($result['FullTextCustomLinks'])) { ?>                     
                      <ul class="table-cell-box">
                          <label>Full Text:</label><hr/>
                            <?php foreach ($result['FullTextCustomLinks'] as $customLink) { ?>
                                <li>
                                    <a href="<?php echo $customLink['Url']; ?>" title="<?php echo $customLink['MouseOverText']; ?>"><img src="<?php if ((isset($customLink['Icon'])) && (strlen($customLink['Icon']) > 0)) { echo $customLink['Icon']; } else { echo "web/iconFTAccessSm.gif"; } ?>" /> <?php echo $customLink['Text']; ?></a>
                                </li>
                            <?php } ?>
                       </ul>
                      <?php } ?>
                      <?php if (count($result['Items'])) {
                        
                        foreach($result['Items'] as $item) {
                           if ($item['Group'] == "URL") {
                              ?>
                              
                              <ul class="table-cell-box">
                                 
				 <li>
                                 <?php
                                          
                                          $docLinks = new DOMDocument();
                                          $docLinks->loadHTML(html_entity_decode($item['Data']));
                                          foreach($docLinks->getElementsByTagName('a') as $linkfromcatalog) {
                                             if ($linkfromcatalog->nodeValue == $linkfromcatalog->getAttribute('href')) {
                                                $linkfromcatalog->nodeValue = "Online Access";
                                             }
                                          }
                                          $newlinks = $docLinks->saveHTML();
                                          $newlinks = str_replace("<a","<img src='web/iconFTAccessSm.gif' /> <a",$newlinks);
                                          echo $newlinks;
                                    
                                    ?></li>
                              </div>
                              
                              <?php
                           }
                        }
                        
                      } ?>
             </div>
             <div style="margin-left: 20px" class="table-cell span-15">
              <table>                  
       <?php if (!empty($result['Items'])) { ?>
                                         
                     <?php for($i=1;$i<count($result['Items']);$i++) { ?>
                     <tr>
                         <td style="width: 150px; vertical-align: top"><strong>
                     <?php echo $result['Items'][$i]['Label']; ?>:
                       </strong></td>
                       <td>
                     <?php if($result['Items'][$i]['Label']=='URL'){ ?> 
                           <?php echo $result['Items'][$i]['Data'] ?>
                     <?php }else{ ?>   
                     <?php echo $result['Items'][$i]['Data']; ?>
                       </td>
                       <?php } ?>
                     </tr> 
                     <?php } ?>                                   
        <?php } ?>
        <?php if(!empty($result['pubType'])){ ?> 
                     <tr>
                         <td><strong><?php echo _("PubType:");?></strong></td>
                         <td><?php echo $result['pubType'] ?></td>
                     </tr>
        <?php } ?>
        <?php if (!empty($result['DbLabel'])) { ?>
            <tr>
                <td><strong>
                    <?php echo _("Database:");?>
            </strong></td>
                <td>
                    <?php echo $result['DbLabel']; ?>
                </td>
            </tr>
           
        <?php } ?>   
            <?php if((!isset($_COOKIE['login']))&&$result['AccessLevel']==2){ ?>
            <tr>
                <td><br></td>
                <td><br></td>
            </tr>
             <tr>
                 <td colspan="2"><?php echo _("This record from ");?><b>[<?php echo $result['DbLabel']; ?>]</b><?php echo _(" cannot be displayed to guests.");?><br><a href="login.php?path=record&db=<?php echo $clean['db']?>&an=<?php echo $clean['an']?>&<?php echo $encodedHighLigtTerm?>&resultId=<?php echo $clean['resultId'] ?>&recordCount=<?php echo $clean['recordCount'] ?>&<?php echo $encodedQuery;?>&fieldcode=<?php echo urlencode($clean['fieldcode']); ?>"><?php echo _("Login")."</a>"._(" for full access.");?></td>
            </tr>
            <?php } ?>
        </table> 
         <?php if(!empty($result['htmllink'])){?>
         <div id="html" style="margin-top:30px">
             <?php echo $result['htmllink'] ?>
         </div>
         <?php } ?>
         </div>
             <div class="jacket">
                <?php if(!empty($result['ImageInfo'])) { ?>              
                 <img width="150px" height="200px" src="<?php echo $result['ImageInfo']['medium']; ?>" />             
        <?php } ?>
             </div>
        </div>
      <?php } ?>  
         </div>
</div>

