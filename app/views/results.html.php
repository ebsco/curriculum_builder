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

if (isset($results['queryString'])) {
   $queryStringUrl = $results['queryString'];
   $emplimqs = parseQueryString($queryStringUrl);
}


if (!(($fieldCode == 'AU') || ($fieldCode == 'TI'))){
   $fieldCode = 'keyword';
}




// URL used by facets links

$refineParams = array(

    'refine' => 'y',

    'query'  => strip_tags(html_entity_decode(urldecode($searchTerm))),

    'fieldcode' => $fieldCode

);

$refineParams = http_build_query($refineParams);

$refineSearchUrl = "results.php?".$refineParams;

$encodedSearchTerm = http_build_query(array('query'=>urlencode(html_entity_decode(urldecode($searchTerm)))));

$encodedHighLigtTerm = http_build_query(array('highlight'=>$searchTerm));

if (!(isInstructor())) {
?>
<div class="readingListLink" id="currentList">
   <a href="reading_list.php"><?php echo _("Back to Reading List");?></a>
</div>
<?php
}
?>
<div id="toptabcontent">

    <div class="topSearchBox">

        <form action="results.php">

    <p>

        <input type="text" name="query" style="width: 350px;" id="lookfor" value="<?php echo str_replace("&amp;","&",htmlspecialchars(strip_tags(urldecode($searchTerm)))); ?>"/>  
        <input type="hidden" name="expander" value="<?php
            $expanderarray = array();
            foreach ($Info['expanders'] as $expanderoption) {
                if ((isset($expanderoption['DefaultOn'])) && ($expanderoption['DefaultOn'] == "y")) {
                    $expanderarray[] = $expanderoption['Id'];
                }
            }
            echo implode(',',$expanderarray);
        ?>" />

        <?php
            foreach ($Info['limiters'] as $limiteroption) {
                if ((isset($limiteroption['DefaultOn'])) && ($limiteroption['DefaultOn'] == "y")) {
                    echo '<input type="hidden" name="limiter[]" value="'.$limiteroption['Id'].':y" />';
                }
            }  
        ?>

        <?php 

        $selected1 = '';

        $selected2 = '';

        $selected3 = '';

        if($fieldCode == 'keyword'){

            $selected1 = "selected = 'selected'";

        } 

        if($fieldCode == 'AU'){

            $selected2 = "selected = 'selected'";

        }

        if($fieldCode == 'TI'){

            $selected3 = "selected = 'selected'";

        } ?>

        <select name="fieldcode">

        <option id="type-keyword" name="fieldcode" value="keyword" <?php echo $selected1 ?> ><?php echo _("Keyword");?></option>

        <?php if(!empty($Info['search'])){ ?>

        <?php foreach($Info['search'] as $searchField){

              if($searchField['Label']=='Author'){

                  $fieldc= $searchField['Code']; ?>

                  <option id="type-author" name="fieldcode" value="<?php echo $fieldc; ?>"<?php echo $selected2; ?> ><?php echo _("Author");?></option>

        <?php }

              if($searchField['Label']=='Title'){

                  $fieldc = $searchField['Code']; ?>

                  <option id="type-title" name="fieldcode" value="<?php echo $fieldc; ?>"<?php echo $selected3 ?> ><?php echo _("Title");?></option>     

        <?php      }

              } ?>

        <?php } ?>             

        </select>

        <input type="submit" value="<?php echo _('Search');?>" />

        

    </p>

    </form>

    </div>

<div class="table">

    <div class="table-row">

        <div class="table-cell">         

            <div><h4><?php echo _("Refine Search");?></h4></div>

            

<?php if(!empty($results['appliedFacets'])||!empty($results['appliedLimiters'])||!empty($results['appliedExpanders'])){ ?>

<div class="filters">

    <strong><?php echo _("Remove Facets");?></strong>

    <ul class="filters">

<!-- applied facets -->

        <?php if (!empty($results['appliedFacets'])) { ?>

        <?php foreach ($results['appliedFacets'] as $filter) { ?>

        <?php foreach ($filter['facetValue'] as $facetValue){ 

              $action = http_build_query(array('action'=>$facetValue['removeAction']));

        ?>

        <li>

        <a href="<?php echo $refineSearchUrl.'&'.$queryStringUrl.'&'.$action. '&backpath='.$backpath; ?>">                 

            <img  src="web/delete.png"/>                      

        </a>

        <a href="<?php echo $refineSearchUrl.'&'.$queryStringUrl.'&'.$action. '&backpath='.$backpath; ?>"><?php echo $facetValue['Id']; ?>: <?php echo $facetValue['value']; ?></a>

        </li>

        <?php } } }?>

<!-- Applied limiters -->

        <?php if (!empty($results['appliedLimiters'])) { ?>    

        <?php foreach ($results['appliedLimiters'] as $filter) {

                  $limiterLabel = '';

                  foreach($Info['limiters'] as $limiter){

                      if($limiter['Id']==$filter['Id']){

                          $limiterLabel = $limiter['Label'];

                          break;

                      }

                  }

                  $action = http_build_query(array('action'=>$filter['removeAction']));

                  

        ?>
        <?php
           if ((($limiter['Id']=='FT') || ($limiter['Id']=='FT1')) && ($customparams['forceft'] == 'y')) {
            
           } else {
        ?>
        <li>

        <a href="<?php echo $refineSearchUrl.'&'.$queryStringUrl.'&'.$action. '&backpath='.$backpath; ?>">                 

            <img  src="web/delete.png"/>                      

        </a>

        <a href="<?php echo $refineSearchUrl.'&'.$queryStringUrl.'&'.$action. '&backpath='.$backpath; ?>"><?php echo _("Limiter: ");?><?php echo $limiterLabel; ?></a>

        </li>

        <?php } } }?>        

<!-- Applied expanders -->

        <?php if (!empty($results['appliedExpanders'])) { ?>

        <?php foreach ($results['appliedExpanders'] as $filter) {

                    $expanderLabel = '';

                    foreach($Info['expanders'] as $exp){

                        if($exp['Id']==$filter['Id']){

                            $expanderLabel = $exp['Label'];

                            break;

                        }

                    }

                    $action = http_build_query(array('action'=>$filter['removeAction']));

             ?>

        <li>

        <a href="<?php echo $refineSearchUrl.'&'.$queryStringUrl.'&'.$action. '&backpath='.$backpath; ?>">                 

            <img  src="web/delete.png"/>                      

        </a>

        <a href="<?php echo $refineSearchUrl.'&'.$queryStringUrl.'&'.$action. '&backpath='.$backpath; ?>"><?php echo _("Expander: ");?><?php echo $expanderLabel; ?></a>

        </li>

        <?php } } ?>        

    </ul>

</div>

<?php } ?>

<?php if(!empty($Info['limiters'])){?>

<div class="facets" style="font-size: 80%">

                <dl class="facet-label">

                    <dt><?php echo _("Limit your results");?></dt>

                </dl>

                <dl class="facet-label" >

                    <form action="limiter.php" method="get">

                        <input type="hidden" name="backpath" value="<?php echo $backpath; ?>" />

                   <?php for($i=0;$i<count($Info['limiters']);$i++){ ?>

                   <?php   $limiter=$Info['limiters'][$i]; ?>

                     <?php if($limiter['Type'] =='select'){?>

                      <?php if(empty($results['appliedLimiters'])){ ?>

                      <?php
                        if ((($limiter['Id'] == 'FT') || ($limiter['Id'] == 'FT1')) && ($customparams['forceft'] == 'y')) {
                           
                        } else {
                      ?>
                      <div class="limiteritem"><input type="checkbox" value="<?php echo $limiter['Action'];?>" name="<?php echo $limiter['Id']; ?>" /><?php echo gettext($limiter['Label']); ?></div>

                      <?php } }else{

                                 $flag = FALSE;

                                 foreach($results['appliedLimiters'] as $filter){

                                    if($limiter['Id']==$filter['Id']){ 

                                        $flag = TRUE;

                                        break;

                                    }

                                 }    

                               if($flag==TRUE){
                                 
                                 if ((($limiter['Id'] == 'FT') || ($limiter['Id'] == 'FT1')) && ($customparams['forceft'] == 'y')) {
                                    
                                 } else {
                                 ?>

                                      <div class="limiteritem"><input type="checkbox" value="<?php echo $limiter['Action'];?>" name="<?php echo $limiter['Id']; ?>" checked="checked" /><?php echo gettext($limiter['Label']); ?></div>                           

                      <?php } }else{
                        
                        if ((($limiter['Id'] == 'FT') || ($limiter['Id'] == 'FT1')) && ($customparams['forceft'] == 'y')) {
                           
                        } else {
                        ?>

                                      <div class="limiteritem"><input type="checkbox" value="<?php echo $limiter['Action'];?>" name="<?php echo $limiter['Id']; ?>" /><?php echo _($limiter['Label']); ?></div>

                      <?php }}}}}?>

                    <input type="hidden" value="<?php echo strip_tags(urlencode(html_entity_decode(urldecode($searchTerm))));?>" name="query" />

                    <input type="hidden" value="<?php echo $fieldCode;?>"  name="fieldcode" />

                    

                    <input type="submit" value="<?php echo _('Update');?>" />

                    </form>               

                </dl>              

</div>

<?php } ?>

<div class="facets" style="font-size: 80%">

                <dl class="facet-label">

                    <dt><?php echo _("Expand your results");?></dt>

                </dl>

                <dl class="facet-label">

                <form action="expander.php">

                    <input type="hidden" name="backpath" value="<?php echo $backpath; ?>" />

                    <?php foreach($Info['expanders'] as $exp){

                       if(empty($results['appliedExpanders'])){ ?>

                           <div class="limiteritem"><input type="checkbox" value="<?php echo $exp['Action'];?>" name="<?php echo $exp['Id']; ?>" /><?php echo _($exp['Label']);?></div>

                    <?php }else{

                        $flag = FALSE;

                        foreach($results['appliedExpanders'] as $aexp){

                            if($aexp['Id']==$exp['Id']){

                                $flag=TRUE;

                                break;

                            }

                        }

                        

                        if($flag==TRUE){ ?>

                           <div class="limiteritem"><input type="checkbox" value="<?php echo $exp['Action'];?>" name="<?php echo $exp['Id']; ?>"  checked="checked"/><?php echo _($exp['Label']);?></div>

                   <?php }else{ ?>

                            <div class="limiteritem"><input type="checkbox" value="<?php echo $exp['Action'];?>" name="<?php echo $exp['Id']; ?>" /><?php echo _($exp['Label']);?></div>

                   <?php   }

                    } 

                    }?>                 

                    <input type="hidden" value="<?php echo strip_tags(urlencode(html_entity_decode(urldecode($searchTerm))));?>" name="query" />

                    <input type="hidden" value="<?php echo $fieldCode;?>"  name="fieldcode" />

                    <input type="submit" value="<?php echo _('Update');?>"/>

                </form>

                </dl>

</div>            

<?php if (!empty($results['facets'])) { $i=0; ?>

    <div class="facets">

        <?php foreach ($results['facets'] as $facet) { $i++; ?>

        

        <?php if(!empty($facet['Label'])){ ?>

        <script type="text/javascript">            

                 $(document).ready(function(){             

                 $("#flip<?php echo $i ?>").click(function(){              

                 $("#panel<?php echo $i ?>").slideToggle("slow");

                 if($("#plus<?php echo $i ?>").html()=='[+]'){

                     $("#plus<?php echo $i ?>").html('[-]');

                 }else{

                     $("#plus<?php echo $i ?>").html('[+]');

                 }

                 

                 });   

                });

        </script>

        

            <div class="facet" style="font-size: 80%">                

                <dl class="facet-label" id="flip<?php echo $i ?>">

                    <dt><a style="font-weight: lighter;" id="plus<?php echo $i ?>" href="javascript:;" >[+]</a><?php echo gettext($facet['Label']); ?></dt>

                </dl>

                <dl class="facet-values" id="panel<?php echo $i ?>">

                    

                        

                    <?php foreach ($facet['Values'] as $facetValue) { 

                     $action = http_build_query(array('action'=>$facetValue['Action']));

                    ?>

                        <dd>

                                                     

                            <a href="<?php echo $refineSearchUrl.'&'.$queryStringUrl.'&'.$action. '&backpath='.$backpath; ?>">

                             

                                <?php echo _($facetValue['Value']); ?>

                            </a>

                            (<?php echo $facetValue['Count']; ?>)

                        </dd>

                    <?php } ?>                  

                </dl>

            </div>

          <?php } ?>

        <?php } ?>

    </div>

<?php } ?>



        </div>

<div class="table-cell" style="width:100%;">

<?php if($debug=='y'){?>

    <div style="float:right"><a target="_blank" href="debug.php?result=y"><?php echo _("Search response XML");?></a></div>

<?php } ?>

<div class="top-menu">

    <h2><?php echo _("Results");?></h2> 

<?php if ($error) { ?>

    <div class="error">

        <?php echo $error; ?>

    </div>

<?php } ?>



<?php if (!empty($results)) { ?>

    <div class="statistics">

        <?php echo _("Showing");?> <strong><?php if($results['recordCount']>0){ echo ($start - 1) * $limit + 1;} else { echo 0; } ?>  - <?php if((($start - 1) * $limit + $limit)>=$results['recordCount']){ echo $results['recordCount']; } else { echo ($start - 1) * $limit + $limit;} ?></strong>  

            <?php echo _("of");?> <strong><?php echo $results['recordCount']; ?></strong>

            <?php echo _("for");?> <strong><?php echo str_replace("&amp;","&",htmlspecialchars(strip_tags(urldecode($searchTerm)))); ?></strong>

    </div><br>            

    <div class ="topbar-resultList">

        <div class="optionsControls">

            <ul style="margin:3px 4px 4px 4px">              

                <li class="options-controls-li">                   

                    <form action="pageOptions.php">

                        <input type="hidden" name="backpath" value="<?php echo $backpath; ?>" />

                        <label><b><?php echo _("Sort");?></b></label>

                        <select onchange="this.form.submit()" name="sort" > 

                            <?php foreach($Info['sort'] as $s){ 

                                  if($sortBy==$s['Id']){ ?>

                                <option selected="selected" value="<?php echo $s['Action']; ?>"><?php echo _($s['Label']) ?></option>

                            <?php }else{ ?>

                                <option value="<?php echo $s['Action']; ?>"><?php echo _($s['Label']) ?></option>

                            <?php }}?>

                        </select>

                        <input type="hidden" value="<?php echo strip_tags(urlencode(html_entity_decode(urldecode($searchTerm))));?>" name="query" />

                        <input type="hidden" value="<?php echo $fieldCode;?>"  name="fieldcode" />      

                    </form>

                </li>

                 <li class="options-controls-li">

                      <?php $option = array(

                          'Detailed' => '',

                          'Brief' => '',

                          'Title' => '',                      

                          );

                              if($amount== 'detailed'){

                                  $option['Detailed']= '  selected="selected"';

                              }

                              if($amount== 'brief'){

                                  $option['Brief']= '  selected="selected"';

                              }

                              if($amount== 'title'){

                                  $option['Title']= '  selected="selected"';

                              }                              

                    ?>    

                    <form action="pageOptions.php">

                        <input type="hidden" name="backpath" value="<?php echo $backpath; ?>" />

                        <label><b><?php echo _("Page options");?></b></label>

                        <select onchange="this.form.submit()" name="view">

                            <option  <?php echo $option['Detailed']?> value="detailed"><?php echo _("Detailed");?></option>

                            <option  <?php echo $option['Brief']?> value="brief"><?php echo _("Brief");?></option>

                            <option  <?php echo $option['Title']?> value="title"><?php echo _("Title Only");?></option>

                        </select>

                        <input type="hidden" value="<?php echo strip_tags(urlencode(html_entity_decode(urldecode($searchTerm))));?>" name="query" />

                        <input type="hidden" value="<?php echo $fieldCode;?>"  name="fieldcode" />  

                    </form>

                 </li>

                    <li class="options-controls-li">

                    

                    <?php $select = array(

                          '5' => '',

                          '10' => '',

                        '20' => '',

                        '30' => '',

                        '40' => '',

                        '50' => ''

                    );

                              if($limit== 5){

                                  $select['5']= '  selected="selected"';

                              }

                              if($limit== 10){

                                  $select['10']= '  selected="selected"';

                              }

                              if($limit== 20){

                                  $select['20']= '  selected="selected"';

                              }

                              if($limit== 30){

                                  $select['30']= '  selected="selected"';

                              }

                              if($limit== 40){

                                  $select['40']= '  selected="selected"';

                              }

                              if($limit== 50){

                                  $select['50']= '  selected="selected"';

                              }                          

                    ?>                          

                     <form action="pageOptions.php">

                        <input type="hidden" name="backpath" value="<?php echo $backpath; ?>" />

                        <label><b><?php echo _("Results per page");?></b></label>

                        <select onchange="this.form.submit()" name="resultsperpage">

                            <option <?php echo $select['5']?> value="setResultsperpage(5)">5</option>

                            <option <?php echo $select['10']?> value="setResultsperpage(10)">10</option>

                            <option <?php echo $select['20']?> value="setResultsperpage(20)">20</option>

                            <option <?php echo $select['30']?> value="setResultsperpage(30)">30</option>

                            <option <?php echo $select['40']?> value="setResultsperpage(40)">40</option>

                            <option <?php echo $select['50']?> value="setResultsperpage(50)">50</option>

                        </select>

                        <input type="hidden" value="<?php echo strip_tags(urlencode(html_entity_decode(urldecode($searchTerm))));?>" name="query" />

                        <input type="hidden" value="<?php echo $fieldCode;?>"  name="fieldcode" />  

                    </form>

                    </li>

                </ul>

        </div>

     </div>

<div style="text-align: center">

    <div class="pagination"><?php echo paginate($results['recordCount'], $limit, $start, $encodedSearchTerm, $fieldCode, $backpath); ?></div>

</div>

<?php } ?>



<div class="results table">
   
   <div id="placard_container"></div>
   <?php foreach ($results['researchStarters'] as $result) {
      if ($result['ResultId'] == "1") {
// BEGIN RS

?>
   <div id="researchStarters">
            <div class="result table-row">
               

                 <?php if (!empty($result['pubType'])) { ?>

                <div class="pubtype table-cell" style="text-align: center">  

                    <?php if (!empty($result['ImageInfo'])) { ?>                    

                    <a href="record.php?db=<?php echo $result['DbId']; ?>&an=<?php echo $result['An']; ?>&<?php echo $encodedHighLigtTerm; ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']; ?>&<?php echo $encodedSearchTerm;?>&fieldcode=<?php echo $fieldCode; ?>&backpath=<?php echo $backpath; ?>">                         

                                <img class="researchStartersImage" src="<?php echo fixprotocol($result['ImageInfo']['thumb']); ?>" />                                                                       

                        </a> 

                    <?php }else{ 

                     $pubTypeId =  $result['PubTypeId'];                    

                     $pubTypeClass = "pt-".$pubTypeId;

                    ?>

                    <span class="pt-icon <?php echo $pubTypeClass?>"></span>

                    <?php } ?>

                </div>     

                <?php } ?>       

                <div class="info table-cell">

                    <div style="margin-left: 10px">

               <div class="rsheader">
                  <?php echo _("Research Starter");?>
               </div>

                        <?php if((!isset($_COOKIE['login']))&&$result['AccessLevel']==1){ ?>

                            <p><?php echo _("This record from ");?><b>[<?php echo $result['DbLabel'] ?>]</b><?php echo _(" cannot be displayed to guests.");?><a href="login.php?path=results&<?php echo $encodedSearchTerm;?>&fieldcode=<?php echo $fieldCode; ?>"><?php echo _("Login");?></a><?php echo _(" for full access.");?></p>

                       <?php }else{  ?>

                        <div class="title">                     

                            <?php if (!empty($result['RecordInfo']['BibEntity']['Titles'])){ ?>

                            <?php foreach($result['RecordInfo']['BibEntity']['Titles'] as $Ti){ ?> 

                            <a href="record.php?db=<?php echo $result['DbId']; ?>&an=<?php echo $result['An']; ?>&<?php echo $encodedHighLigtTerm; ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']; ?>&<?php echo $encodedSearchTerm; ?>&fieldcode=<?php echo $fieldCode; ?>&backpath=<?php echo $backpath; ?>"><?php echo $Ti['TitleFull']; ?></a>

                           <?php } }

                            else { ?> 

                            <a href="record.php?db=<?php echo $result['DbId']; ?>&an=<?php echo $result['An']; ?>&<?php echo $encodedHighLigtTerm; ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']; ?>&<?php echo $encodedSearchTerm; ?>&fieldcode=<?php echo $fieldCode; ?>&backpath=<?php echo $backpath; ?>"><?php echo "Title is not available"; ?></a>                   

                          <?php  } ?>                

                        </div>

                        <?php if(!empty($result['Items']['TiAtl'])){ ?>

                        <div>

                        <?php foreach($result['Items']['TiAtl'] as $TiAtl){ 

                              echo $TiAtl['Data']; 

                              } ?>

                        </div>

                        <?php } ?>

                        <div class="authors b">

                        <span style="font-style: italic; ">

                        <?php if(isset($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'])){?>                                                 

                             <?php foreach($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'] as $title){ ?>

                               <?php echo $title['TitleFull']; ?>,                                  

                        <?php                        }}?>

                        </span>

                        <?php if(!empty($result['RecordInfo']['BibEntity']['Identifiers'])){

                                 foreach($result['RecordInfo']['BibEntity']['Identifiers'] as $identifier){

                                     $pieces = explode('-',$identifier['Type']); 

                                     if(isset($pieces[1])){                                       

                                       echo strtoupper($pieces[0]).'-'.ucfirst( $pieces[1]);

                                       }else{ 

                                       echo strtoupper($pieces[0]);

                                       }?>: <?php echo $identifier['Value']?>,                                                                

                        <?php }} ?>

                        <?php if(isset($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'])){?>

                             <?php foreach($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'] as $identifier){

                                    $pieces = explode('-',$identifier['Type']);

                                    if(isset($pieces[1])){                                        

                                       }else{ 

                                       echo strtoupper($pieces[0]);

                                       }?>: <?php echo $identifier['Value']?>, 

                             <?php }?>  

                        <?php }?>

                        <?php if(isset($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'])){?>

                             <?php foreach($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'] as $date){ ?>

                                 <span class="hiddendata" style="display:none;"><?php echo $date['Y']; ?></span><?php echo _("Published: ");?><?php echo $date['M']?>/<?php echo $date['D']?>/<?php echo $date['Y']?>, 

                             <?php }?> 

                        <?php }?>

                        <?php if(isset($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'])){ 

                                foreach($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'] as $number){?>

                                  <?php $type = str_replace('volume','Vol',$number['Type']); $type = str_replace('issue','Issue',$type); ?>

                                    <?php echo $type;?>: <?php echo $number['Value']; ?>, 

                        <?php } } ?>

                        <?php if(!empty($result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage'])){?>

                                 <?php echo _("Start Page: ");?><?php echo $result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage']?>, 

                        <?php } ?>                        

                        <?php if(!empty($result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination'])){ ?>

                                 <?php echo _("Page Count: ");?><?php echo $result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination']?>, 

                        <?php } ?>

                        <?php if(!empty($result['RecordInfo']['BibEntity']['Languages'])){ ?>

                        <?php foreach($result['RecordInfo']['BibEntity']['Languages'] as $language){ ?> 

                                 <?php echo _("Language: ");?><?php echo $language['Text']?>

                        <?php } }?>

                        </div>

                        <?php if (isset($result['Items']['Ab'])) { ?>

             <script type="text/javascript">            

                 $(document).ready(function(){             

                 $("#rsabstract-plug<?php echo $result['ResultId']; ?>").click(function(){              

                     $("#rsfull-abstract<?php echo $result['ResultId']; ?>").show() ; 

                     $("#rsabstract<?php echo $result['ResultId']; ?>").hide() ; 

                 }); 

                 $("#rsfull-abstract-plug<?php echo $result['ResultId']; ?>").click(function(){              

                     $("#rsfull-abstract<?php echo $result['ResultId']; ?>").hide() ; 

                     $("#rsabstract<?php echo $result['ResultId']; ?>").show() ; 

                 });   

                });

             </script>

                        <div id="rsabstract<?php echo $result['ResultId'];?>" class="abstract">

                            <span>

                                      <?php foreach($result['Items']['Ab'] as $Abstract){ ?>                                            

                                                     <?php

                                                      $length = 300;

                                                      if($length == 'Full'){

                                                            echo $Abstract['Data'];

                                                      }else{

                                                            $data = str_replace(array('<span class="highlight">','</span>'), array('',''), $Abstract['Data']);

                                                            $data = substr($data, 0, $length).'...';

                                                            

                                                            echo $data;

                                                      }

                                                     ?>                                                

                                            <?php } ?>                                  

                                        

                                    <span id="rsabstract-plug<?php echo $result['ResultId'];?>">[+]</span>                                

                            </span>

                        </div>

                        <div id="rsfull-abstract<?php echo $result['ResultId'];?>" class="full-abstract">

                            <span>

                                          <?php foreach($result['Items']['Ab'] as $Abstract){ ?>                                          

                                               <?php echo $Abstract['Data']; ?>                                                                                                                                                   

                                          <?php } ?>                                        

                                    <span id="rsfull-abstract-plug<?php echo $result['ResultId'];?>">[-]</span>

                                </tr>

                            </span>

                        </div>

                      <?php } 

                      
                     $fulltextlinkfound = false;

                      ?>





                      <?php if((!empty($result['PDF']))) { ?>
                      <div class="links fulltextlink eric">

                         <?php if($result['HTML']==1){
                           $fulltextlinkfound = true;
                           ?> 
                          <?php if((!isset($_COOKIE['login']))&&$result['AccessLevel']==2){ ?> 

                        <a target="_blank" class="icon html fulltext" href="login.php?path=HTML&an=<?php echo $result['An']; ?>&db=<?php echo $result['DbId']; ?>&<?php echo $encodedHighLigtTerm ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']?>&<?php echo $encodedSearchTerm;?>&fieldcode=<?php echo $fieldCode; ?>"><?php echo _("Full Text");?></a>

                          <?php } else { ?>

                        <a target="_blank" class="icon html fulltext" href="record.php?an=<?php echo $result['An']; ?>&db=<?php echo $result['DbId']; ?>&<?php echo $encodedHighLigtTerm; ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']?>&<?php echo $encodedSearchTerm;?>&fieldcode=<?php echo $fieldCode; ?>&backpath=<?php echo $backpath; ?>#html"><?php echo _("Full Text");?></a>

                         <?php } ?>                          

                        <?php } ?>

                        <?php if(!empty($result['PDF'])){
                           $fulltextlinkfound = true;
                           ?> 

                          <a target="_blank" class="icon pdf fulltext" href="PDF.php?an=<?php echo $result['An']?>&db=<?php echo $result['DbId']?>"><?php echo _("Full Text");?></a>

                        <?php } ?>

                      </div>

                      <?php }
                      
                      if (!empty($result['CustomLinks'])){ ?>

                      <div class="custom-links">

                      <?php if (count($result['CustomLinks'])<=3){?> 

                    

                            <?php foreach ($result['CustomLinks'] as $customLink) {
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                              $fulltextlinkfound = true;
                              ?>

                                <div class="fulltextlink">

                                 <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php if (isset($customLink['Icon']) && (strlen($customLink['Icon']) > 0)) { ?><img src="<?php echo fixprotocol($customLink['Icon']); ?>" /><?php } else { echo "<img src='web/iconFTAccessSm.gif' />"; } ?> <?php echo $customLink['Text']; ?></a>

                                </div>

                            <?php }
                            } ?>

                    

                      <?php } else {?>

                    

                            <?php for($i=0; $i<3 ; $i++){
                                if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;

                                $customLink = $result['CustomLinks'][$i];

                                ?>

                                <div class="fulltextlink"> 

                                   <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php echo $customLink['Name']; ?></a>

                                </div>

                            <?php } ?>

                    

                      <?php }
                      } ?>                   

                      </div>                      

                      <?php } ?>

                      <?php if (!empty($result['FullTextCustomLinks'])){ ?>

                      <div class="custom-links">

                      <?php if (count($result['FullTextCustomLinks'])<=3){?>                     

                            <?php foreach ($result['FullTextCustomLinks'] as $customLink) {
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;
                              
                              ?>

                                <div class="fulltextlink">

                                 <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php if ((isset($customLink['Icon'])) && (strlen($customLink['Icon']))) { ?><img src="<?php echo fixprotocol($customLink['Icon']); ?>" /><?php }  else { echo "<img src='web/iconFTAccessSm.gif' />"; } ?> <?php echo $customLink['Name']; ?></a>

                                </div>

                            <?php } ?>                    

                      <?php }
                      } else {?>                    

                            <?php for($i=0; $i<3 ; $i++){
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;

                                $customLink = $result['FullTextCustomLinks'][$i];

                                ?>

                                <div class="fulltextlink"> 

                                   <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php echo $customLink['Name']; ?></a>

                                </div>

                            <?php }
                            
                            } ?>

                    

                      <?php } ?>                   

                      </div>                     

                      <?php } ?>
                      <?php if (count($result['Items'])) {
                        
                        foreach($result['Items'] as $item) {
                           if ($item[0]['Group'] == "URL") {
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;

                              ?>
                              
                              <div class="custom-links">
                                 <div class="fulltextlink">
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
                                             
                                             echo "<a target='_blank' href='".$customlinkURL."'><img src='web/iconFTAccessSm.gif' /> Online Access</a>";
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
                        
                      } ?>

                      <?php } ?>

<?php if (isInstructor()) { ?>                  
<!--
  
Foldering
   Display 'add to folder' link to allow logged in users to save items.
   If the user is not logged in, invite them to.
   Uses AJAX to automatically insert and remove items from folders without reloading page.
  
-->
<div class="folderblock">
<?php 
	if (isset($_COOKIE['login'])) {
?>                    
<!-- If the user is logged in -->
<!-- If the item is not in the folder -->
	<div id="notinfolder0" class="folderitem" style="font-size: 11px; display: <?php 
		if (itemInFolder($folderitemsarray,$result['An'],$result['DbId'])) {
  			echo "none";
		} else {
  			echo "inline";
		}
?>;">
<button class="addFolder" id="addbutton0" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $result['An']; ?>', '<?php echo $result['DbId']; ?>','none','none','<?php echo urlencode($Ti['TitleFull']); ?>',1,0,1,1)"><?php echo _("Add to Reading List");?></button>

</div>
<!-- END item in NOT in folder -->
<!-- If the item is in the folder... -->
<div id="infolder0" class="folder" style="font-size: 11px; display: <?php 

		if (itemInFolder($folderitemsarray,$result['An'],$result['DbId'])) {
  			echo "inline";
		} else {
  			echo "none";
		}
?>;">
<button class="removeFolder" id="removebutton0" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $result['An']; ?>', '<?php echo $result['DbId']; ?>','none','none','<?php echo urlencode($Ti['TitleFull']); ?>',2,0,1,1)"><?php echo _("Remove from Reading List");?></button>

</div>
<!-- END item is in the folder -->
<!-- END user is logged in -->

<?php } else {?>

<!-- If user is NOT logged in -->
<div id="notinfolder0" class="folderitem" style="font-size: 11px; display: inline;">
<?php
$params = array(
                          'path'=>'results',
                          'query'=>$searchTerm,
                          'fieldcode'=>$fieldCode
                      );
                      $params = http_build_query($params);
?>

</div>
<!-- END user is NOT logged in -->

<?php } ?>
</div>
<!-- END folders -->
<?php } ?>

                </div>

                </div>

            </div>
</div>
<?php

// END RS
      }
   }
   ?></div>

    <?php if (empty($results['records'])) { ?>

        <div class="result table-row">

            <div class="table-cell">

                <h2><i><?php echo _("No results were found.");?></i></h2>

            </div>

        </div>

    <?php } else { ?>

        <?php foreach ($results['records'] as $result) { 
          ?>

            <div class="result table-row">              

                 <?php if (!empty($result['pubType'])) { ?>

                <div class="pubtype table-cell" style="text-align: center">  

                    <?php if (!empty($result['ImageInfo'])) { ?>                    

                    <a href="record.php?db=<?php echo $result['DbId']; ?>&an=<?php echo $result['An']; ?>&<?php echo $encodedHighLigtTerm; ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']; ?>&<?php echo $encodedSearchTerm;?>&fieldcode=<?php echo $fieldCode; ?>&backpath=<?php echo $backpath; ?>">                         

                                <img src="<?php echo fixprotocol($result['ImageInfo']['thumb']); ?>" />                                                                       

                        </a> 

                    <?php }else{ 

                     $pubTypeId =  $result['PubTypeId'];                    

                     $pubTypeClass = "pt-".$pubTypeId;

                    ?>

                    <span class="pt-icon <?php echo $pubTypeClass?>"></span>

                    <?php } ?>

                    <div><?php echo _($result['pubType']); ?></div>

                </div>     

                <?php } ?>       

                <div class="info table-cell">

                    <div style="margin-left: 10px">

                        <div class="record-id table-cell">
        
                            <?php echo _("Result")."# ";?><?php echo $result['ResultId']; ?>.
        
                        </div> 

                        <?php if((!isset($_COOKIE['login']))&&$result['AccessLevel']==1){ ?>

                            <p>This record from <b>[<?php echo $result['DbLabel'] ?>]</b><?php echo _(" cannot be displayed to guests.");?><a href="login.php?path=results&<?php echo $encodedSearchTerm;?>&fieldcode=<?php echo $fieldCode; ?>"><?php echo _("Login");?></a><?php echo _(" for full access.");?></p>

                       <?php }else{  ?>

                        <div class="title">                     

                            <?php if (!empty($result['RecordInfo']['BibEntity']['Titles'])){ ?>

                            <?php foreach($result['RecordInfo']['BibEntity']['Titles'] as $Ti){ ?> 

                            <a href="record.php?db=<?php echo $result['DbId']; ?>&an=<?php echo $result['An']; ?>&<?php echo $encodedHighLigtTerm; ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']; ?>&<?php echo $encodedSearchTerm; ?>&fieldcode=<?php echo $fieldCode; ?>&backpath=<?php echo $backpath; ?>"><?php echo $Ti['TitleFull']; ?></a>

                           <?php } }

                            else { ?> 

                            <a href="record.php?db=<?php echo $result['DbId']; ?>&an=<?php echo $result['An']; ?>&<?php echo $encodedHighLigtTerm; ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']; ?>&<?php echo $encodedSearchTerm; ?>&fieldcode=<?php echo $fieldCode; ?>&backpath=<?php echo $backpath; ?>"><?php echo _("Title is not available"); ?></a>                   

                          <?php  } ?>                

                        </div>

                        <?php if(!empty($result['Items']['TiAtl'])){ ?>

                        <div>

                        <?php foreach($result['Items']['TiAtl'] as $TiAtl){ 

                              echo $TiAtl['Data']; 

                              } ?>

                        </div>

                        <?php } ?>

                        <?php if (!empty($result['Items']['Au'])) { ?>

                        <div class="authors a">

                            <span>

                                <span style="font-style: italic;"><?php echo _("By : ");?></span>                                            

                                 <?php
                                    $authorstring = '';
                                    foreach($result['Items']['Au'] as $Author){ ?>                                    

                                    <?php if (strlen($authorstring) <= 200) {
                                       $authorstring .= $Author['Data'];
                                       }                               
                                    }
                                    echo $authorstring;

                                 ?>

                            </span>                        

                        </div>                        

                        <?php } ?>

                        <div class="authors b">

                        <span style="font-style: italic; ">

                        <?php if(isset($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'])){?>                                                 

                             <?php foreach($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'] as $title){ ?>

                               <?php echo $title['TitleFull']; ?>,                                  

                        <?php                        }}?>

                        </span>

                        <?php if(!empty($result['RecordInfo']['BibEntity']['Identifiers'])){

                                 foreach($result['RecordInfo']['BibEntity']['Identifiers'] as $identifier){

                                     $pieces = explode('-',$identifier['Type']); 

                                     if(isset($pieces[1])){                                       

                                       echo strtoupper($pieces[0]).'-'.ucfirst( $pieces[1]);

                                       }else{ 

                                       echo strtoupper($pieces[0]);

                                       }?>: <?php echo $identifier['Value']?>,                                                                

                        <?php }} ?>

                        <?php if(isset($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'])){?>

                             <?php foreach($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'] as $identifier){

                                    $pieces = explode('-',$identifier['Type']);

                                    if(isset($pieces[1])){                                        

                                       }else{ 

                                       echo strtoupper($pieces[0]);

                                       }?>: <?php echo $identifier['Value']?>, 

                             <?php }?>  

                        <?php }?>

                        <?php if(isset($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'])){?>

                             <?php foreach($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'] as $date){ ?>

                                 <span class="hiddendata" style="display:none;"><?php echo $date['Y']; ?></span><?php echo _("Published: ");?><?php echo $date['M']?>/<?php echo $date['D']?>/<?php echo $date['Y']?>, 

                             <?php }?> 

                        <?php }?>

                        <?php if(isset($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'])){ 

                                foreach($result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'] as $number){?>

                                  <?php $type = str_replace('volume','Vol',$number['Type']); $type = str_replace('issue','Issue',$type); ?>

                                    <?php echo $type;?>: <?php echo $number['Value']; ?>, 

                        <?php } } ?>

                        <?php if(!empty($result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage'])){?>

                                 <?php echo _("Start Page: ");?><?php echo $result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage']?>, 

                        <?php } ?>                        

                        <?php if(!empty($result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination'])){ ?>

                                 <?php echo _("Page Count: ");?><?php echo $result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination']?>, 

                        <?php } ?>

                        <?php if(!empty($result['RecordInfo']['BibEntity']['Languages'])){ ?>

                        <?php foreach($result['RecordInfo']['BibEntity']['Languages'] as $language){ ?> 

                                 <?php echo _("Language: ");?><?php echo $language['Text']?>

                        <?php } }?>

                        </div>

                        <?php if (isset($result['Items']['Ab'])) { ?>

             <script type="text/javascript">            

                 $(document).ready(function(){             

                 $("#abstract-plug<?php echo $result['ResultId']; ?>").click(function(){              

                     $("#full-abstract<?php echo $result['ResultId']; ?>").show() ; 

                     $("#abstract<?php echo $result['ResultId']; ?>").hide() ; 

                 }); 

                 $("#full-abstract-plug<?php echo $result['ResultId']; ?>").click(function(){              

                     $("#full-abstract<?php echo $result['ResultId']; ?>").hide() ; 

                     $("#abstract<?php echo $result['ResultId']; ?>").show() ; 

                 });   

                });

             </script>

                        <div id="abstract<?php echo $result['ResultId'];?>" class="abstract">

                            <span>

                                <span style="font-style: italic;"><?php echo _("Abstract:");?> </span>                                    

                                      <?php foreach($result['Items']['Ab'] as $Abstract){ ?>                                            

                                                     <?php

                                                      $length = 300;

                                                      if($length == 'Full'){

                                                            echo $Abstract['Data'];

                                                      }else{

                                                            $data = str_replace(array('<span class="highlight">','</span>'), array('',''), $Abstract['Data']);

                                                            $data = substr($data, 0, $length).'...';

                                                            

                                                            echo $data;

                                                      }

                                                     ?>                                                

                                            <?php } ?>                                  

                                        

                                    <span id="abstract-plug<?php echo $result['ResultId'];?>">[+]</span>                                

                            </span>

                        </div>

                        <div id="full-abstract<?php echo $result['ResultId'];?>" class="full-abstract">

                            <span>

                                    <span style="font-style: italic;"><?php echo _("Abstract: ");?></span>

                                          <?php foreach($result['Items']['Ab'] as $Abstract){ ?>                                          

                                               <?php echo $Abstract['Data']; ?>                                                                                                                                                   

                                          <?php } ?>                                        

                                    <span id="full-abstract-plug<?php echo $result['ResultId'];?>">[-]</span>

                                </tr>

                            </span>

                        </div>

                      <?php } ?>

                      <?php if (!empty($result['Items']['Su'])) { ?>

                        <div class="subjects">

                            <span>

                                    <span style="font-style: italic;"><?php echo _("Subjects:");?></span>

                                             <?php foreach($result['Items']['Su'] as $Subject){ ?>

                                            <?php echo $Subject['Data']; ?>; 

                                             <?php } ?> 

                            </span>

                        </div>

                      <?php }
                      
                     $fulltextlinkfound = false;

                      ?>





                      <?php if(($result['HTML']==1) || (!empty($result['PDF']))) { ?>
                      <div class="links fulltextlink eric">

                         <?php if($result['HTML']==1){
                           $fulltextlinkfound = true;
                           ?> 
                          <?php if((!isset($_COOKIE['login']))&&$result['AccessLevel']==2){ ?> 

                        <a target="_blank" class="icon html fulltext" href="login.php?path=HTML&an=<?php echo $result['An']; ?>&db=<?php echo $result['DbId']; ?>&<?php echo $encodedHighLigtTerm ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']?>&<?php echo $encodedSearchTerm;?>&fieldcode=<?php echo $fieldCode; ?>"><?php echo _("Full Text");?></a>

                          <?php } else { ?>

                        <a target="_blank" class="icon html fulltext" href="record.php?an=<?php echo $result['An']; ?>&db=<?php echo $result['DbId']; ?>&<?php echo $encodedHighLigtTerm; ?>&resultId=<?php echo $result['ResultId'];?>&recordCount=<?php echo $results['recordCount']?>&<?php echo $encodedSearchTerm;?>&fieldcode=<?php echo $fieldCode; ?>&backpath=<?php echo $backpath; ?>#html"><?php echo _("Full Text");?></a>

                         <?php } ?>                          

                        <?php } ?>

                        <?php if(!empty($result['PDF'])){
                           $fulltextlinkfound = true;
                           ?> 

                          <a target="_blank" class="icon pdf fulltext" href="PDF.php?an=<?php echo $result['An']?>&db=<?php echo $result['DbId']?>"><?php echo _("Full Text");?></a>

                        <?php } ?>

                      </div>

                      <?php }
                      
                      if (!empty($result['CustomLinks'])){ ?>

                      <div class="custom-links">

                      <?php if (count($result['CustomLinks'])<=3){?> 

                    

                            <?php foreach ($result['CustomLinks'] as $customLink) {
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                              $fulltextlinkfound = true;
                              ?>

                                <div class="fulltextlink">

                                 <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php if (isset($customLink['Icon']) && (strlen($customLink['Icon']) > 0)) { ?><img src="<?php echo fixprotocol($customLink['Icon']); ?>" /><?php } else { echo "<img src='web/iconFTAccessSm.gif' />"; } ?> <?php echo $customLink['Text']; ?></a>

                                </div>

                            <?php }
                            } ?>

                    

                      <?php } else {?>

                    

                            <?php for($i=0; $i<3 ; $i++){
                                if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;

                                $customLink = $result['CustomLinks'][$i];

                                ?>

                                <div class="fulltextlink"> 

                                   <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php echo $customLink['Name']; ?></a>

                                </div>

                            <?php } ?>

                    

                      <?php }
                      } ?>                   

                      </div>                      

                      <?php } ?>

                      <?php if (!empty($result['FullTextCustomLinks'])){ ?>

                      <div class="custom-links">

                      <?php if (count($result['FullTextCustomLinks'])<=3){?>                     

                            <?php foreach ($result['FullTextCustomLinks'] as $customLink) {
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;
                              
                              ?>

                                <div class="fulltextlink">

                                 <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php if ((isset($customLink['Icon'])) && (strlen($customLink['Icon']))) { ?><img src="<?php echo fixprotocol($customLink['Icon']); ?>" /><?php }  else { echo "<img src='web/iconFTAccessSm.gif' />"; } ?> <?php echo $customLink['Name']; ?></a>

                                </div>

                            <?php } ?>                    

                      <?php }
                      } else {?>                    

                            <?php for($i=0; $i<3 ; $i++){
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;

                                $customLink = $result['FullTextCustomLinks'][$i];

                                ?>

                                <div class="fulltextlink"> 

                                   <a target="_blank" href="<?php echo processProxy($customLink['Url'],$customparams['proxyprefix'],$customparams['proxyencode']); ?>" title="<?php echo $customLink['MouseOverText']; ?>"><?php echo $customLink['Name']; ?></a>

                                </div>

                            <?php }
                            
                            } ?>

                    

                      <?php } ?>                   

                      </div>                     

                      <?php } ?>
                      <?php if (count($result['Items'])) {
                        
                        foreach($result['Items'] as $item) {
                           if ($item[0]['Group'] == "URL") {
                              if (!(($customparams['firstftonly'] == 'y') && ($fulltextlinkfound))) {
                                 $fulltextlinkfound = true;

                              ?>
                              
                              <div class="custom-links">
                                 <div class="fulltextlink">
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
                                             
                                             echo "<a target='_blank' href='".$customlinkURL."'><img src='web/iconFTAccessSm.gif' /> Online Access</a>";
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
                        
                      } ?>

                      <?php } ?>

<?php if (isInstructor()) { ?>                  
<!--
  
Foldering
   Display 'add to folder' link to allow logged in users to save items.
   If the user is not logged in, invite them to.
   Uses AJAX to automatically insert and remove items from folders without reloading page.
  
-->
<div class="folderblock" style="padding-top:10px; padding-bottom:20px;">
<?php 
	if (isset($_COOKIE['login'])) {
?>                    
<!-- If the user is logged in -->
<!-- If the item is not in the folder -->
	<div id="notinfolder<?php echo $result['ResultId']; ?>" class="folderitem" style="font-size: 11px; display: <?php 
		if (itemInFolder($folderitemsarray,$result['An'],$result['DbId'])) {
  			echo "none";
		} else {
  			echo "inline";
		}
?>;">
<button class="addFolder" id="addbutton<?php echo $result['ResultId'];?>" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $result['An']; ?>', '<?php echo $result['DbId']; ?>','none','none','<?php echo urlencode($Ti['TitleFull']); ?>',1,<?php echo $result['ResultId']; ?>,1,1)"><?php echo _("Add to Reading List");?></button>

</div>
<!-- END item in NOT in folder -->
<!-- If the item is in the folder... -->
<div id="infolder<?php echo $result['ResultId']; ?>" class="folder" style="font-size: 11px; display: <?php 

		if (itemInFolder($folderitemsarray,$result['An'],$result['DbId'])) {
  			echo "inline";
		} else {
  			echo "none";
		}
?>;">
<button class="removeFolder" id="removebutton<?php echo $result['ResultId'];?>" onclick="addToFolder(xmlhttp,<?php echo decryptCookie($_COOKIE['currentListId']); ?>,<?php echo decryptCookie($_COOKIE['currentAuthorId']); ?>,'<?php echo $result['An']; ?>', '<?php echo $result['DbId']; ?>','none','none','<?php echo urlencode($Ti['TitleFull']); ?>',2,<?php echo $result['ResultId']; ?>,1,1)"><?php echo _("Remove from Reading List");?></button>

</div>
<!-- END item is in the folder -->
<!-- END user is logged in -->

<?php } else {?>

<!-- If user is NOT logged in -->
<div id="notinfolder<?php echo $result['ResultId']; ?>" class="folderitem" style="font-size: 11px; display: inline;">
<?php
$params = array(
                          'path'=>'results',
                          'query'=>$searchTerm,
                          'fieldcode'=>$fieldCode
                      );
                      $params = http_build_query($params);
?>

</div>
<!-- END user is NOT logged in -->

<?php } ?>
</div>
<!-- END folders -->
<?php } ?>

                </div>

                </div>

            </div>

        <?php } ?>

    <?php } ?>

</div>

<?php if (!empty($results)) { ?>

    <div style="text-align: center">

        <div class="pagination"><?php echo paginate($results['recordCount'], $limit, $start, $encodedSearchTerm, $fieldCode, $backpath); ?></div>       

    </div>

<?php } ?>

        </div>

    </div>

</div>

</div>      

</div>

<script type="text/javascript">
   $('a.searchlinks').each(function () {
      var linktarget = $(this).attr("href");
      linktarget = linktarget + "<?php echo $emplimqs; ?>";
      $(this).attr("href",linktarget);
   });
</script>
