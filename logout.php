<?php
include ('app/app.php');
if(isset($_COOKIE['login'])){
    setcookie('login','',time()-3600);
}

session_destroy();
header("location: index.php");
?>
