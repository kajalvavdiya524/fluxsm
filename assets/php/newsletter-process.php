<?php
$myFile = "_subbies";
$fh = fopen($myFile, 'a') or die("Can't subscribe email address");
fwrite($fh, $_POST['email']."\n");
fclose($fh);
echo "success";
?>