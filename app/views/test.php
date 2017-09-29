<?php
$language = $language = (isset($_REQUEST["language"])) ? trim(strip_tags($_REQUEST["language"])) : 'es_CO.UTF-8' ;
putenv("LC_ALL=$language");
setlocale(LC_ALL, $language);
bindtextdomain("messages", "./locale");
textdomain("messages");
echo $language;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
<p><a href=<?php echo $_SERVER["PHP_SELF"]; ?>?language=en_US.UTF-8><input value="English" type="button" name="idioma" /></a> -
	<a href=<?php echo $_SERVER["PHP_SELF"]; ?>?language=es_CO.UTF-8><input value="Español" type="button" name="idioma" /></a> - 
	<a href=<?php echo $_SERVER["PHP_SELF"]; ?>?language=de_DE.UTF-8><input value="Aleman" type="button" name="idioma" /></a> - 
	<a href=<?php echo $_SERVER["PHP_SELF"]; ?>?language=pt_PT.UTF-8><input value="Portugues" type="button" name="idioma" /></a> - 
	<a href=<?php echo $_SERVER["PHP_SELF"]; ?>?language=it_IT.UTF-8><input value="Italiano" type="button" name="idioma" /></a></p>

<p><?php echo _("Author");?></p>
<p><?php echo _("CB Version 2.3");?></p>
<p><?php echo _("Keyword");?></p>
<p><?php echo _("Author");?></p>
<p><?php echo _("Title");?></p>

</body>
</html>
