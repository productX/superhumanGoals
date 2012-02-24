<?php
include("template/userFacingForceLogin.php");

// DO PROCESSING
if(!isset($_GET["id"])) {
	redirect(PAGE_USERS);
}
$userID = $_GET["id"];
$viewingSelf = $userID == $user->id;


// RENDER PAGE
require_once("include/header.php");
printHeader("User page");

$daysBack = 0;
if(isset($_GET["db"])) {
	$daysBack = $_GET["db"];
}
$daysBack = min(0,$daysBack);
$currentTime = $daysBack*60*60*24;
$currentDate = date("M j",time()-$currentTime);
echo "<a href='".PAGE_USER."?id=$userID&db=".($daysBack+1)."'>&lt;</a>";
echo $currentDate;
if($daysBack>0) {
	echo "<a href='".PAGE_USER."?id=$userID&db=".($daysBack-1)."'>&gt;</a>";
}
echo "<br/>";

GoalStatus::printRowList($userID, $currentTime, $viewingSelf);

printFooter();
?>