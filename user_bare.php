<?php
include("template/userFacingForceLogin.php");

// DO PROCESSING
$userID = $user->id;
if(isset($_GET["id"])) {
	$userID = GPC::strToInt($_GET["id"]);
}
$viewingSelf = $userID == $user->id;


// RENDER PAGE
require_once("include/chrome.php");
printHeader("User page");

$daysBack = 0;
if(isset($_GET["db"])) {
	$daysBack = GPC::strToInt($_GET["db"]);
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