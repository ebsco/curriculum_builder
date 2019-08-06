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
  $oauth_consumer_key = decryptCookie($_COOKIE['oauth_consumer_key']);
?>
    <p><strong>Total Lists</strong>: 
    <?php
	  //Find the number of lists (and the number of private lists)

		$sql = $c->prepare("SELECT id, private FROM lists WHERE oauth_consumer_key = ?;");
		$sql->bind_param('s', $oauth_consumer_key);
	  $sql->execute();
	  $sql->store_result();
	  $sql->bind_result($results_id, $results_private);
	  $num_rows = $sql->num_rows;
		$privateLists = 0;
		while ($sql->fetch()) {
			if ($results_private == 1) {
				$privateLists++;
			}
		}

		echo $num_rows . " <em>(" . $privateLists . " marked as private)</em>";
	  
    ?></p>
    <p><strong><?php echo _("Total Readings");?></strong>: 
    <?php
    $numReadings = 0;
    $sql = $c->prepare("SELECT readings.id, lists.id FROM readings, lists WHERE readings.listid = lists.id AND lists.oauth_consumer_key = ?");
	  $sql->bind_param('s', $oauth_consumer_key);
	  $sql->execute();
	  $sql->store_result();
	  
		$numReadings += $sql->num_rows;
 	  
    echo $numReadings;
	?></p>
    <p><strong><?php echo _("Total People Using Tool");?></strong>: 
    <?php
      // Find the number of authors (instructors) using the tool at the institution.
	  	$ids = array(); //used in next stat
                     
			$sql = $c->prepare("SELECT DISTINCT authors.id FROM authors, authorlists, lists WHERE authors.id = authorlists.authorid AND authorlists.listid = lists.id AND lists.oauth_consumer_key = ?;");
			$sql->bind_param('s', $oauth_consumer_key);
			$sql->execute();
			$sql->store_result();
			$sql->bind_result($authorsId);
	  
			while($sql->fetch()){ //populate the array
				$ids[] = $authorsId;
			}
	  
			$numPeople = $sql->num_rows;
      	  
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
		$sql = $c->prepare("SELECT DISTINCT course FROM lists WHERE oauth_consumer_key = ?;");
		$sql->bind_param('s',$oauth_consumer_key);
		$sql->execute();
		$sql->store_result();
		$sql->bind_result($course);//will be used to populate the courses array.
		while($sql->fetch()){ //populate the array
			$courses[] = $course;
		}
		$numCourses += $sql->num_rows;
		unset($course);

		echo $numCourses;         
      ?>
      </p>
    <p><strong><?php echo _("Courses");?></strong>:<span style="font-size:smaller;">
      <?php
        
            
        foreach ($courses as $course) {
            
            
          echo "<br />" . $course;
          $sql = $c->prepare("SELECT id FROM lists WHERE course = ? AND oauth_consumer_key = ?;");
		  $sql->bind_param('ss', $course, $oauth_consumer_key);
		  $sql->execute();
		  $sql->store_result();
		  
          $numListsInCourse = $sql->num_rows;
          echo " <em>(" . $numListsInCourse . " list";
          if ($numListsInCourse != 1) {
            echo "s";
          }
          echo ")</em>";
        }
		mysqli_close($c);
      ?>
      </span></p>       
</div>