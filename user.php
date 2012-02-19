<?php
require_once("include/auth.php");
require_once("include/header.php");
$authConfig[]="";
$authConfig[]="";
authorize($authConfig);
printHeader("User page");
?>

<?php
echo "$_SESSION[email], $_SESSION[password]<br/>";
?>

<?php
printFooter();
?>
