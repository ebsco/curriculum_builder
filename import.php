<?php
  if (isset($_POST['ris'])) {
    $text = $_POST['ris'];
    $numhits = 0;  
    while (strpos($text,"TI- ") > 0) {
      $text = substr($text,strpos($text,"TI- ")+4);
      $title[$numhits] = substr($text,0,strpos($text,"\n"));
      $text = substr($text,strpos($text,"UR- "));
      $text = substr($text,strpos($text,"&db=")+4);
      $db[$numhits] = substr($text,0,strpos($text,"&"));
      $text = substr($text,strpos($text,"&AN=")+4);
      $an[$numhits] = substr($text,0,strpos($text,"&"));
      $numhits += 1;
    }
    
    for ($i=0;$i<$numhits;$i++) {
      echo $db[$i] . "/" . $an[$i] . ":" . $title[$i] . "<br />";
    }
  }
?>
<form action="import.php" method="post">
    <textarea name="ris"></textarea>
    <input type="submit" />
</form>