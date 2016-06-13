<?php
libxml_use_internal_errors(true);

$folderitemsarray = getFolderItems($c);

if (isset($_SESSION['results'])) {
 $results = $_SESSION['results'];   
 if (isset($results['queryString'])) {
   $queryStringUrl = $results['queryString'];
   $emplimqs = parseQueryString($queryStringUrl);
}
} else {
 $queryStringUrl = $backpath;
}
if (isset($clean['query'])) {
$encodedQuery = http_build_query(array('query'=>$clean['query']));
} else {
$encodedQuery = http_build_query(array('query'=>''));    
}
if (isset($clean['highlight'])) {
$encodedHighLigtTerm = http_build_query(array('highlight'=>$clean['highlight']));    
} else {
$encodedHighLigtTerm = http_build_query(array('highlight'=>''));
}
?>
<?php if (!(isInstructor())) {

echo '<div class="readingListLink"><a href="reading_list.php';
if (isset($clean['folderid'])) { echo "?folderid=" . $clean['folderid']; }
echo '">Back to Reading List</a></div>';    
    
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
 <?php if($debug=='y'){?>
    <div style="float:right; padding-bottom: 10px"><a target="_blank" href="debug.php?record=y">Retrieve response XML</a></div>
<?php } ?>
     <div class="record table">
<?php if ($error) { ?>
    <div class="error">
        Uh oh!  It looks like the article may no longer be available through your library.  Please let your library and teacher know about this error message: <?php echo $error; ?>
    </div>
<?php } ?>
<?php if((!isset($_COOKIE['login']))&&$result['AccessLevel']==1){ ?>
         <p>This record from <b>[<?php echo $result['DbLabel']; ?>]</b> cannot be displayed to guests.<br><a href="login.php?path=record&db=<?php echo $clean['db']?>&an=<?php echo $clean['an']?>&<?php echo $encodedHighLigtTerm;?>&resultId=<?php echo $clean['resultId'] ?>&recordCount=<?php echo $clean['recordCount'] ?>&<?php echo $encodedQuery;?>&fieldcode=<?php echo urlencode($clean['fieldcode']); ?>">Login</a> for full access.</p>
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

<button class="addFolder" id="addbutton1" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $result['An']; ?>', '<?php echo $result['DbId']; ?>','none','none','<?php echo urlencode($resultTitle); ?>',1,1,1,1)">Add to Reading List</button>

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
<button class="removeFolder" id="removebutton1" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $result['An']; ?>', '<?php echo $result['DbId']; ?>','none','none','<?php echo urlencode($resultTitle); ?>',2,1,1,1)">Remove from Reading List</button>

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
                          <a target="_blank" href="<?php echo $result['PLink'] ?>">
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
                        HTML full text
                        </a>
                      </li>
                          <?php } else{?>
                      <li>
                          <a class="icon html fulltext" href="#html">HTML Full Text</a>                       
                      </li>                      
                         <?php } ?>           
                      <?php } ?>
                      </ul>
                      <?php } ?>
                      
                      <?php if (!empty($result['CustomLinks'])) { ?>                     
                      <ul class="table-cell-box">
                          
                            <?php foreach ($result['CustomLinks'] as $customLink) { ?>
                                <li>
                                    <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']) ?>" title="<?php echo $customLink['MouseOverText']; ?>"><img src="<?php if ((isset($customLink['Icon'])) && (strlen($customLink['Icon']) > 0)) { echo fixprotocol($customLink['Icon']); } else { echo "web/iconFTAccessSm.gif"; } ?>" /> <?php echo $customLink['Text']; ?></a>
                                </li>
                            <?php } ?>
                       </ul>
                      <?php } ?>
                     <?php if (!empty($result['FullTextCustomLinks'])) { ?>                     
                      <ul class="table-cell-box">
                          <label>Full Text:</label><hr/>
                            <?php foreach ($result['FullTextCustomLinks'] as $customLink) { ?>
                                <li>
                                    <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']) ?>" title="<?php echo $customLink['MouseOverText']; ?>"><img src="<?php if ((isset($customLink['Icon'])) && (strlen($customLink['Icon']) > 0)) { echo fixprotocol($customLink['Icon']); } else { echo "web/iconFTAccessSm.gif"; } ?>" /> <?php echo $customLink['Text']; ?></a>
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

                                          if (substr_count($item['Data'],"http")) {
                                             $customlinkURL = substr($item['Data'],strpos($item['Data'],"http"));
                                             
                                             if (strpos($customlinkURL," ") > -1) {
                                             $customlinkURL = substr($customlinkURL,0,strpos($customlinkURL," "));
                                             }

                                             if (strpos($customlinkURL,"<") > -1) {
                                             $customlinkURL = substr($customlinkURL,0,strpos($customlinkURL,"<"));
                                             }

                                             if (strpos($customlinkURL,"\"") > -1) {
                                             $customlinkURL = substr($customlinkURL,0,strpos($customlinkURL,"\""));
                                             }
                                             
                                             echo "<a target='_blank' href='".$customlinkURL."'><img src='web/iconFTAccessSm.gif' /> Online Access</a>";
                                          } else {
                                             $docLinks = new DOMDocument();
                                             $docLinks->loadHTML(html_entity_decode($item['Data']));
                                                foreach($docLinks->getElementsByTagName('a') as $linkfromcatalog) {
                                                   if ($linkfromcatalog->nodeValue == $linkfromcatalog->getAttribute('href')) {
                                                      $linkfromcatalog->nodeValue = "Online Access";
                                                   }
                                                }
                                             $newlinks = $docLinks->saveHTML();
                                             $newlinks = str_replace("<a","<img src='web/iconFTAccessSm.gif' /> <a target='_blank'",$newlinks);
                                             echo $newlinks;
                                          }                                    
                                    ?>
				</li>
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
                     <?php if (strlen($result['Items'][$i]['Label']) > 1) { echo $result['Items'][$i]['Label'] . ":"; } ?>
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
                         <td><strong>PubType:</strong></td>
                         <td><?php echo $result['pubType'] ?></td>
                     </tr>
        <?php } ?>
        <?php if (!empty($result['DbLabel'])) { ?>
            <tr>
                <td><strong>
                    Database:
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
                 <td colspan="2">This record from <b>[<?php echo $result['DbLabel']; ?>]</b> cannot be displayed to guests.<br><a href="login.php?path=record&db=<?php echo $clean['db']?>&an=<?php echo $clean['an']?>&<?php echo $encodedHighLigtTerm?>&resultId=<?php echo $clean['resultId'] ?>&recordCount=<?php echo $clean['recordCount'] ?>&<?php echo $encodedQuery;?>&fieldcode=<?php echo urlencode($clean['fieldcode']); ?>">Login</a> for full access.</td>
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
                 <img width="150px" height="200px" src="<?php echo fixprotocol($result['ImageInfo']['medium']); ?>" />             
        <?php } ?>
             </div>
        </div>
      <?php } ?>  
         </div>
</div>
<script type="text/javascript">
   $('a.searchlinks').each(function () {
      var linktarget = $(this).attr("href");
      linktarget = linktarget + "<?php echo $emplimqs; ?>";
      $(this).attr("href",linktarget);
   });
</script>
