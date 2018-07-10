<?php
	// save current language to cookie by default
	if (!isset($_SESSION["language"])){
		if(!isset($customparams)){
			$customparams['language']= "en_US.UTF-8";
	}		
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
	
	//define direction of the page RTL support
	$langRTL = array (
			"he_HE.UTF-8",
			"he_IL.UTF-8"
		);
	
	$pageDir="ltr";
	if (in_array($language, $langRTL)) {
		$pageDir="rtl";
	}

?>
<!DOCTYPE html>
<html lang="en-US" dir="<?php echo $pageDir; ?>">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Search</title>
        <link rel="stylesheet" href="web/styles.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="web/pubtype-icons.css" />
	<?php
	  if ((isset($customparams['css'])) && (strlen($customparams['css']) > 0)) {
	    echo '<link rel="stylesheet" href="'.fixprotocol($customparams['css']).'" type="text/css" media="screen" />';
	  }
	?>
        <link rel="shortcut icon" href="web/favicon.ico" />
        <script type="text/javascript" src="web/jquery.js" ></script>
        <script type="text/javascript" src="folders.js" ></script>
        <script type="text/javascript" src="functions.js" ></script>
		<link rel="stylesheet" href="web/jquery-ui.css">
		<script src="web/jquery-1.10.2.js"></script>
		<script src="web/jquery-ui.js"></script>
    </head>

    <body>
        <div id="hiddenXMLresponse" style="display:none;"></div>
        <div class="container">
        <div class="header">
            <?php
                if ((isset($customparams['liblogo'])) && ($customparams['liblogo'] != '')) {
					?>
					<div><a id="logo" width="120" target="_blank" href="<?php
					
					if ((isset($customparams['liblink'])) && ($customparams['liblink'] != '')) {
						echo strip_tags_for_display($customparams['liblink']);
					} else {
						echo "#";
					}
					
					?>" style='background: url("<?php echo strip_tags_for_display(fixprotocol($customparams['liblogo'])); ?>") no-repeat scroll left center transparent; background-size:contain;'></a></div>
					<?php
                }
				
				$urlWithoutparameters=$_SERVER["PHP_SELF"];
				$queryString=$_SERVER["QUERY_STRING"];
				
				// check if language exists and remove it , because its already set on the cookie
				$a1=array (
					"language=en_US.UTF-8&",
					"language=es_CO.UTF-8&",
					"language=de_DE.UTF-8&",
					"language=pt_PT.UTF-8&",
					"language=pt_BR.UTF-8&",
					"language=it_IT.UTF-8&",
					"language=tr_TR.UTF-8&",
					"language=fr_FR.UTF-8&",					
					"language=zh_TW.UTF-8&",
					"language=zh_CN.UTF-8&",
					"language=ja_JP.UTF-8&",
					"language=th_TH.UTF-8&",
					"language=pl_PL.UTF-8&",
					"language=sv_SE.UTF-8&",
					"language=he_IL.UTF-8&",
                    "language=ko_KR.UTF-8&",
                    "language=nl_NL.UTF-8&",
				);
				$queryString=str_replace($a1,"",$queryString);
				
				
            ?>
            <div><a id="logo2" href="index.php"></a></div>
			<div id="languageSwitcher">
				<select onchange="" id="changeLanguageMenu" name="changeLanguageMenu">
				
					<option value="<?php echo $urlWithoutparameters."?language=en_US.UTF-8&".$queryString; ?>"  <?php echo ($language=='en_US.UTF-8'?' selected ':''); ?> >English</option> 
					<option value="<?php echo $urlWithoutparameters."?language=es_CO.UTF-8&".$queryString; ?>"  <?php echo ($language=='es_CO.UTF-8'?' selected ':''); ?> >Español</option> 
					<option value="<?php echo $urlWithoutparameters."?language=de_DE.UTF-8&".$queryString; ?>"  <?php echo ($language=='de_DE.UTF-8'?' selected ':''); ?> >Deutsch</option> 
					<option value="<?php echo $urlWithoutparameters."?language=pt_PT.UTF-8&".$queryString; ?>"  <?php echo ($language=='pt_PT.UTF-8'?' selected ':''); ?> >Português (Portugal)</option> 
					<option value="<?php echo $urlWithoutparameters."?language=pt_BR.UTF-8&".$queryString; ?>"  <?php echo ($language=='pt_BR.UTF-8'?' selected ':''); ?> >Português (Brasil)</option> 
					<option value="<?php echo $urlWithoutparameters."?language=it_IT.UTF-8&".$queryString; ?>"  <?php echo ($language=='it_IT.UTF-8'?' selected ':''); ?> >Italiano</option>
					<option value="<?php echo $urlWithoutparameters."?language=fr_FR.UTF-8&".$queryString; ?>"  <?php echo ($language=='fr_FR.UTF-8'?' selected ':''); ?> >Français</option>
					
					<option value="<?php echo $urlWithoutparameters."?language=zh_TW.UTF-8&".$queryString; ?>"  <?php echo ($language=='zh_TW.UTF-8'?' selected ':''); ?> >繁體中文</option> 
					<option value="<?php echo $urlWithoutparameters."?language=zh_CN.UTF-8&".$queryString; ?>"  <?php echo ($language=='zh_CN.UTF-8'?' selected ':''); ?> >简体中文</option> 
					<option value="<?php echo $urlWithoutparameters."?language=ja_JP.UTF-8&".$queryString; ?>"  <?php echo ($language=='ja_JP.UTF-8'?' selected ':''); ?> >日本語</option> 
					<option value="<?php echo $urlWithoutparameters."?language=th_TH.UTF-8&".$queryString; ?>"  <?php echo ($language=='th_TH.UTF-8'?' selected ':''); ?> >ไทย</option> 
					<option value="<?php echo $urlWithoutparameters."?language=pl_PL.UTF-8&".$queryString; ?>"  <?php echo ($language=='pl_PL.UTF-8'?' selected ':''); ?> >Polski</option> 
					<option value="<?php echo $urlWithoutparameters."?language=sv_SE.UTF-8&".$queryString; ?>"  <?php echo ($language=='sv_SE.UTF-8'?' selected ':''); ?> >Svenska</option> 
					<option value="<?php echo $urlWithoutparameters."?language=he_IL.UTF-8&".$queryString; ?>"  <?php echo ($language=='he_IL.UTF-8'?' selected ':''); ?> >עברית</option>	
                    <option value="<?php echo $urlWithoutparameters."?language=ko_KR.UTF-8&".$queryString; ?>"  <?php echo ($language=='ko_KR.UTF-8'?' selected ':''); ?> >한국어</option>	
                    <option value="<?php echo $urlWithoutparameters."?language=nl_NL.UTF-8&".$queryString; ?>"  <?php echo ($language=='nl_NL.UTF-8'?' selected ':''); ?> >Nederlands</option>	
				</select>
				<script>
					jQuery(function(){
					  // bind change event to select
					  jQuery('#changeLanguageMenu').on('change', function () {
						  var url = $(this).val(); // get selected value
						  if (url) { // require a URL
							  window.location = url; // redirect
						  }
						  return false;
					  });
					});
				</script>				
			</div>
            <h1><strong><?php
            if (isset($_COOKIE['context_title'])) {
                echo strip_tags(html_entity_decode(decryptCookie($_COOKIE['context_title']))) . ": ";
            }
            if (isset($_COOKIE['resource_link_title'])) {
                echo strip_tags(html_entity_decode(decryptCookie($_COOKIE['resource_link_title'])));
            } else {
                echo _("Reading List");
            }
            ?></strong></h1><span style="color:#333333;"><?php
            if (isset($customparams['libname'])) {
				if (substr_count($customparams['libname'],"http")) {
					$closeTag = "</a>";
					$startURL = strpos($customparams['libname'],"http");
					$endURL = strpos($customparams['libname']," ",$startURL+1);
					
					if (!($endURL)) {
						$opentag = '<a target="_blank" href="'.substr($customparams['libname'],$startURL).'">';
						$final = substr($customparams['libname'],0,$startURL).$opentag.substr($customparams['libname'],$startURL).$closeTag;
					} else {
						$urllength = $endURL - $startURL;
						$opentag = '<a target="_blank" href="'.substr($customparams['libname'],$startURL,$urllength).'">';
						$final = substr($customparams['libname'],0,$startURL).$opentag.substr($customparams['libname'],$startURL,$urllength).$closeTag.substr($customparams['libname'],$endURL);						
					}
					echo $final;
				} else {
	                echo $customparams['libname'];
				}
            } ?></span>
			<?php
			if ((isset($_COOKIE['logged_in_cust_id'])) && (!(isset($_REQUEST['logout'])))){
			
			echo "<a href=\"admin2.php?logout=YES\" title=\"Staff Login\">"._("Log Out")."</a>";
			
			}

			?>
        </div>

        <div class="content">
	<?php if (isInstructor() || ((isset($_COOKIE['launch_presentation_return_url'])) && isset($customparams) && ($customparams['courselink'] == 'y'))) { ?>
	<div class="readingListLink" id="currentList"><?php
	        if ((isset($_COOKIE['launch_presentation_return_url'])) && (isset($customparams)) && ($customparams['courselink'] == 'y')) {
		    echo '<a target="_top" href="'.htmlspecialchars_decode(decryptCookie($_COOKIE['launch_presentation_return_url'])).'">'._("Return to Course").'</a>';    
		}
		if ((isset($_COOKIE['launch_presentation_return_url'])) && (isInstructor()) && (isset($customparams)) && ($customparams['courselink'] == 'y')) {
		    echo ' | ';
		}
		if (isInstructor()) {
                    echo '<a href="reading_list.php';
		    if (isset($clean['folderid'])) { echo "?folderid=" . $clean['folderid']; }
		    echo '">'._("See Current Reading List").'</a>';
                }
	    ?></div>
	<?php } ?>
	    
            <?php echo $content; ?>
        </div>
        <div class="footer">        
            <div class="span-5" style="width:100%;">
               <div style="color: #666666; font-size:small;">
                <?php if ((isset($customparams['libemail'])) && ($customparams['libemail'] != '')) {
                  echo _("Contact")." "."<a target='_blank' href='mailto:" . $customparams['libemail'] . "'>" . $customparams['libemail'] . "</a>"." "._("for assistance with this tool.");    
                }
                if ((isset($customparams['liblink'])) && ($customparams['liblink'] != '')) {
                    echo "<br /><a target='_blank' href='" . strip_tags_for_display($customparams['liblink']) . "'>"._("Library Home")."</a>";
                }
		if ((isset($customparams['helppages'])) && ($customparams['helppages'] != '')) {
                    echo "<br /><a target='_blank' href='" . strip_tags_for_display($customparams['helppages']) . "'>"._("Help Using this Tool")."</a>";		    
		}
		if ((isset($customparams['copyright'])) && ($customparams['copyright'] != '')) {
		  echo "<br /><br /><a name='copyright'><span style='color:#666666'>".$customparams['copyright']."</span>";
		}
		if (isInstructor()) {
                ?>
                <span style="float:right; font-size:smaller; color:#999999;"><strong><?php echo _("CB Version 2.3");?></strong> - <strong><?php echo _("List ID");?></strong>: <?php echo decryptCookie($_COOKIE['currentLinkId']); ?></span>
		<?php
		}
		?>
               </div>
           </div>
        </div>
        </div>
	<div class="debug" style="display:none;"><?php if (isset($_SESSION['debug'])) { echo $_SESSION['debug']; } ?></div>
    </body>
    <script type="text/javascript" id="custom_script" data-consumerkey="<?php echo decryptCookie($_COOKIE['oauth_consumer_key']); ?>" src="//widgets.ebscohost.com/prod/common/branding/curriculumbuilder.js"></script>
</html>
