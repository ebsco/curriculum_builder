<!DOCTYPE html>
<html lang="en-US">
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
            ?>
            <div><a id="logo2" href="index.php"></a></div>
            <h1><strong><?php
            if (isset($_COOKIE['context_title'])) {
                echo strip_tags(html_entity_decode(decryptCookie($_COOKIE['context_title']))) . ": ";
            }
            if (isset($_COOKIE['resource_link_title'])) {
                echo strip_tags(html_entity_decode(decryptCookie($_COOKIE['resource_link_title'])));
            } else {
                echo "Reading List";
            }
            ?></strong></h1><span style="color:#333333;"><?php
            if (isset($customparams['libname'])) {
                echo $customparams['libname'];
            } ?></span>
			<?php
			if ((isset($_COOKIE['logged_in_cust_id'])) && (!(isset($_REQUEST['logout'])))){
			
			echo "<br /><a href=\"admin2.php?logout=YES\" title=\"Staff Login\">Log Out</a>";
			
			}

			?>
        </div>

        <div class="content">
	<?php if (isInstructor() || ((isset($_COOKIE['launch_presentation_return_url'])) && isset($customparams) && ($customparams['courselink'] == 'y'))) { ?>
	<div class="readingListLink" id="currentList"><?php
	        if ((isset($_COOKIE['launch_presentation_return_url'])) && (isset($customparams)) && ($customparams['courselink'] == 'y')) {
		    echo '<a target="_top" href="'.htmlspecialchars_decode(decryptCookie($_COOKIE['launch_presentation_return_url'])).'">Return to Course</a>';    
		}
		if ((isset($_COOKIE['launch_presentation_return_url'])) && (isInstructor()) && (isset($customparams)) && ($customparams['courselink'] == 'y')) {
		    echo ' | ';
		}
		if (isInstructor()) {
                    echo '<a href="reading_list.php';
		    if (isset($clean['folderid'])) { echo "?folderid=" . $clean['folderid']; }
		    echo '">See Current Reading List</a>';
                }
	    ?></div>
	<?php } ?>
	    
            <?php echo $content; ?>
        </div>
        <div class="footer">        
            <div class="span-5" style="width:100%;">
               <div style="color: #666666; font-size:small;">
                <?php if ((isset($customparams['libemail'])) && ($customparams['libemail'] != '')) {
                  echo "Contact <a target='_blank' href='mailto:" . $customparams['libemail'] . "'>" . $customparams['libemail'] . "</a> for assistance with this tool.";    
                }
                if ((isset($customparams['liblink'])) && ($customparams['liblink'] != '')) {
                    echo "<br /><a target='_blank' href='" . strip_tags_for_display($customparams['liblink']) . "'>Library Home</a>";
                }
		if ((isset($customparams['helppages'])) && ($customparams['helppages'] != '')) {
                    echo "<br /><a target='_blank' href='" . strip_tags_for_display($customparams['helppages']) . "'>Help Using this Tool</a>";		    
		}
		if ((isset($customparams['copyright'])) && ($customparams['copyright'] != '')) {
		  echo "<br /><br /><a name='copyright'><span style='color:#666666'>".$customparams['copyright']."</span>";
		}
		if (isInstructor()) {
                ?>
                <span style="float:right; font-size:smaller; color:#999999;"><strong>List ID</strong>: <?php echo decryptCookie($_COOKIE['currentLinkId']); ?></span>
		<?php
		}
		?>
               </div>
           </div>
        </div>
        </div>
	<div class="debug" style="display:none;"><?php if (isset($_SESSION['debug'])) { echo $_SESSION['debug']; } ?></div>
    </body>
    <script type="text/javascript" src="//widgets.ebscohost.com/prod/common/branding/curriculumbuilder.js"></script>
</html>