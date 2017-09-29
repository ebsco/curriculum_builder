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

?>
<?php
  include_once("app/app.php");
?>
<style type="text/css">
  #currentList { display: none; }
</style>
<div class="readingListLink">
  <h3><a href="admin2.php"><?php echo _("Administration");?></a></h3>
</div>
<div class="readingListLink">

<?php

  if (decryptCookie($_COOKIE['logged_in_cust_id']) != "none") {
?>
    <p><strong>Total Lists</strong>: 
    <?php
	  //Find the number of lists (and the number of private lists)
      $privateLists = 0;
      $num_rows = 0;
      if (!(isset($_COOKIE['consumeridsArray']))) {
	echo " 0";
      } else {

      $consumerids = decryptCookie($_COOKIE['consumeridsArray']);
      
      foreach ($consumerids['logged_in_consumerid'] as $consumerid) {
          $querystring = 'SELECT id FROM credentialconsumers WHERE credentialid = ' . decryptCookie($_COOKIE['logged_in_cust_id']) . ' AND consumerid = "' . $consumerid . '";';
          $credconsumresults = mysqli_query($c,$querystring);
          $credconsumrow = mysqli_fetch_array($credconsumresults);
	  $credconsumer = $credconsumrow['id'];

          $sql = $c->prepare("SELECT id, private FROM lists WHERE credentialconsumerid = ?;");
	  $sql->bind_param('i', $credconsumer);
	  $sql->execute();
	  $sql->store_result();
	  $sql->bind_result($results_id, $results_private);
	  $num_rows += $sql->num_rows;
          
      while ($sql->fetch()) {
        if ($results_private == 1) {
          $privateLists++;
        }
	  }
	  
	  if ($c->more_results()) {
	  $c->next_result();
	  }
      }
      echo $num_rows . " <em>(" . $privateLists . " marked as private)";
	  
    ?></p>
    <p><strong><?php echo _("Total Readings");?></strong>: 
    <?php

    $numReadings = 0;
    foreach($consumerids['logged_in_consumerid'] as $consumerid) {
          $querystring = 'SELECT id FROM credentialconsumers WHERE credentialid = ' . decryptCookie($_COOKIE['logged_in_cust_id']) . ' AND consumerid = "' . $consumerid . '";';
          $credconsumresults = mysqli_query($c,$querystring);
          $credconsumrow = mysqli_fetch_array($credconsumresults);
	  $credconsumer = $credconsumrow['id'];

	  //Find the number of readings for the given institution
	  $sql = $c->prepare("SELECT readings.id, lists.id FROM readings, lists WHERE readings.listid = lists.id AND lists.credentialconsumerid = ?");
	  $sql->bind_param('i', $credconsumer);
	  $sql->execute();
	  $sql->store_result();
	  
          $numReadings += $sql->num_rows;
 	  
	  if ($c->more_results()) {
	  $c->next_result();
	  }
    }
    echo $numReadings;

	?></p>
    <p><strong><?php echo _("Total People Using Tool");?></strong>: 
    <?php
      // Find the number of authors (instructors) using the tool at the institution.
     $numPeople = 0;
	  $ids = array(); //used in next stat

		foreach($consumerids['logged_in_consumerid'] as $consumerid) {
            
			$querystring = 'SELECT id FROM credentialconsumers WHERE credentialid = ' . decryptCookie($_COOKIE['logged_in_cust_id']) . ' AND consumerid = "' . $consumerid . '";';
			$credconsumresults = mysqli_query($c,$querystring);
			$credconsumrow = mysqli_fetch_array($credconsumresults);
			$credconsumer = $credconsumrow['id'];
         
			$sql = $c->prepare("SELECT DISTINCT authors.id FROM authors, authorlists, lists WHERE authors.id = authorlists.authorid AND authorlists.listid = lists.id AND lists.credentialconsumerid = ?;");
			$sql->bind_param('i', $credconsumer);
			$sql->execute();
			$sql->store_result();
			$sql->bind_result($authorsId);
	  
			while($sql->fetch()){ //populate the array
				$ids[] = $authorsId;
			}
	  
			$numPeople += $sql->num_rows;
      	  
			if ($c->more_results()) {
			$c->next_result();
			}
        }
		echo $numPeople;
    ?></p>
    <p><strong><?php echo _("Users");?></strong>:<span style="font-size:smaller;">
      <?php
        
		//for each author find their name, email and the number of lists they authored. 
		foreach($ids as $id) {
			$sql = $c->prepare("SELECT authors.fullname, authors.email FROM authors, authorlists WHERE authorlists.authorid = ? AND authors.id = ?;");
			$sql->bind_param('ii', $id, $id);
			$sql->execute();
			$sql->store_result();
			$sql->bind_result($fullname, $email);
			$numListsByAuthor = $sql->num_rows; //gives us the number of lists they created
			
			//these 2 arrays are a simple way of reducing the table returned by our last query into a single line. This is okay because every line of that table will include the same information.
			$authorsFullname = array (); 
			$authorsEmail = array ();
			while ($sql->fetch()){
				$authorsFullname[0] = $fullname;
				$authorsEmail[0] = $email;
			}
			//echo each author's name, email, and the number of lists they created.
			echo "<br />" . $authorsFullname[0] . " - " . $authorsEmail[0] . " <em>(" . $numListsByAuthor . " list";
			if ($numListsByAuthor != 1) {
				echo "s";
			}
			echo " authored)</em>";
		}
		
      ?>
      </span></p>
    <p><strong><?php echo _("Total Courses Using Tool");?></strong>: 
      <?php
      $numCourses = 0;
		$courses=array(); //will be used in next stat.

         foreach($consumerids['logged_in_consumerid'] as $consumerid) {
           $courses[$consumerid] = array();

          $querystring = 'SELECT id FROM credentialconsumers WHERE credentialid = ' . decryptCookie($_COOKIE['logged_in_cust_id']) . ' AND consumerid = "' . $consumerid . '";';
          $credconsumresults = mysqli_query($c,$querystring);
          $credconsumrow = mysqli_fetch_array($credconsumresults);
	  $credconsumer = $credconsumrow['id'];
          
        $sql = $c->prepare("SELECT DISTINCT course FROM lists WHERE credentialconsumerid = ?;");
		$sql->bind_param('i',$credconsumer);
		$sql->execute();
		$sql->store_result();
		$sql->bind_result($course);//will be used to populate the courses array.
		while($sql->fetch()){ //populate the array
			$courses[$consumerid][] = $course;
		}
		$numCourses += $sql->num_rows;
		unset($course);
		if ($c->more_results()) {
		$c->next_result();
		}
         }
         echo $numCourses;         
      ?>
      </p>
    <p><strong><?php echo _("Courses");?></strong>:<span style="font-size:smaller;">
      <?php
        
        foreach ($consumerids['logged_in_consumerid'] as $consumerid) {
            
          $querystring = 'SELECT id FROM credentialconsumers WHERE credentialid = ' . decryptCookie($_COOKIE['logged_in_cust_id']) . ' AND consumerid = "' . $consumerid . '";';
          $credconsumresults = mysqli_query($c,$querystring);
          $credconsumrow = mysqli_fetch_array($credconsumresults);
	  $credconsumer = $credconsumrow['id'];
          
        foreach ($courses[$consumerid] as $course) {
            
            
          echo "<br />" . $course;
          $sql = $c->prepare("SELECT id FROM lists WHERE course = ? AND credentialconsumerid = ?;");
		  $sql->bind_param('si', $course, $credconsumer);
		  $sql->execute();
		  $sql->store_result();
		  
          $numListsInCourse = $sql->num_rows;
          echo " <em>(" . $numListsInCourse . " list";
          if ($numListsInCourse != 1) {
            echo "s";
          }
          echo ")</em>";
        }
        }
		mysqli_close($c);
      ?>
      </span></p>
<?php
      }
  } else {
    echo "No statistics to report yet.";
  }
?>        
</div>
