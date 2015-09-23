<?php
	header('Content-Type: text/html; charset=UTF-8');
	include("connect.php");

	function strip_tags_deep($value)
	{
		global $c;
		return is_array($value) ? array_map('strip_tags_deep', $value) : mysqli_real_escape_string($c,htmlentities(strip_tags($value),ENT_QUOTES,"UTF-8"));
	}
	
	$clean = strip_tags_deep($_GET);
	
	$listid = $clean['listid'];
	$authorID = $clean['authorid'];
	$an = $clean['an'];
	$db = $clean['db'];
	$action = $clean['action'];
	$title = $clean['title'];
	$priority = $clean['priority'];
	$url = $clean['url'];
	$type = $clean['type'];
	if (isset($clean['instruct'])) {
		$text = $clean['instruct'];
	} else {
		$text = 'none';
	}
	
	if ($action == "1") {
		$sql = 'INSERT INTO readings (listid, authorid, an, db, url, instruct, title, priority, type) VALUES ('. $listid .',' . $authorID . ',"' . $an . '", "' . $db . '","' . $url . '","' . $text . '","' . $title . '",'. $priority .',' . $type . ');';
		$results = mysqli_query($c,$sql);
		echo $sql;
	} else if ($action == "2") {
		if ($type == 1) {
		    $sql = 'DELETE FROM readings WHERE listid = ' . $listid . ' AND an = "' . $an . '" AND db = "' . $db . '";';
		    $results = mysqli_query($c,$sql);
		} else if ($type == 2) {
		    $sql = 'DELETE FROM readings WHERE listid = ' . $listid . ' AND url = "' . $url . '";';
		    $results = mysqli_query($c,$sql);		
		} else if ($type == 3) {
		    $sql = 'DELETE FROM readings WHERE listid = ' . $listid . ' AND instruct = "' . mysqli_real_escape_string($c,$text) . '";';
		    $results = mysqli_query($c,$sql);		
		}
	}
	    
	mysqli_close($c);
	
?>