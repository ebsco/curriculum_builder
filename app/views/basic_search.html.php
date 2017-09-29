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



$api =  new EBSCOAPI($c,$customparams);
$Info = $api->getInfo();
?>
<div id="toptabcontent"> 
<div class="searchHomeContent">
<div class="searchHomeForm">
    <div class="searchform">
<h1><?php echo $customparams['searchlabel']; ?></h1>
<form action="results.php">
    <p>
        <input type="text" name="query" style="width: 350px;" id="lookfor" />
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
        <input type="submit" value="<?php echo _('Search');?>" />
    </p>
    <table>
        <tr>
            <td>
                <input type="radio" id="type-keyword" name="fieldcode" value="keyword" checked="checked"/>
                <label for="type-keyword"><?php echo _("Keyword");?></label>
            </td>
      <?php if(!empty($Info['search'])){ ?>
      <?php foreach($Info['search'] as $searchField){
          if($searchField['Label']=='Author'){
              $fieldCode = $searchField['Code']; ?>
      
            <td>
                <input type="radio" id="type-author" name="fieldcode" value="<?php echo $fieldCode; ?>" />
                <label for="type-author"><?php echo _("Author");?></label>
            </td>
      <?php   
          }
          if($searchField['Label']=='Title'){
              $fieldCode = $searchField['Code']; ?>
            <td>
                <input type="radio" id="type-title" name="fieldcode" value="<?php echo $fieldCode; ?>" />
                <label for="type-title"><?php echo _("Title");?></label>                             
            </td>          
      <?php
          }
      } ?>
      <?php } ?>          
        </tr>
    </table>
</form>
</div>
</div>
    <?php if (isset($customparams['helppages']) && ($customparams['helppages'] != '')) {
        ?>
        <div class="helppages" style="margin:20px auto;width:500px;"><img src="web/help.png" style="padding-right:5px;float:left;"><?php echo _(" What does this tool do?  Check");?><a target="_blank" href="<?php echo $customparams['helppages']; ?>"><?php echo _("our help pages on using it")."</a>"._("for more information.");?></div>
        <?php
    } ?>
</div>
</div>
