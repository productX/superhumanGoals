<?php
include("template/userFacingForceLogin.php");

// DO PROCESSING
$viewUserID = $user->id;
if(isset($_GET["id"])) {
	$viewUserID = GPC::strToInt($_GET["id"]);
}
$viewingSelf = ($viewUserID == $user->id);
$viewUser = User::getObjFromUserID($viewUserID);

// RENDER PAGE
require_once("include/chrome.php");

const PAGEMODE_ACTIVITY='activity';
const PAGEMODE_GOALS='goals';
$mode = PAGEMODE_GOALS;
if(isset($_GET["t"])) {
	$mode = $_GET["t"];
}
$tabIndex = 0;
switch($mode) {
	case PAGEMODE_ACTIVITY:
		$tabIndex = 0;
		break;
	case PAGEMODE_GOALS:
		$tabIndex = 1;
		break;
	default:
		assert(false);
		break;
}
printHeader($viewingSelf?NAVNAME_YOU:NAVNAME_USERS, 
			array(	new ChromeTitleElementUserPic($viewUser),
					new ChromeTitleElementHeader("Person: $viewUser->firstName $viewUser->lastName"),
					new ChromeTitleElementTabs(	array(	"Activity"=>PAGE_USER."?id=$viewUserID&t=".PAGEMODE_ACTIVITY,
														"Goals"=>PAGE_USER."?id=$viewUserID&t=".PAGEMODE_GOALS
												), $tabIndex)
			));

/*
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
*/

switch($mode) {
	case PAGEMODE_ACTIVITY:
		$rs = Database::doQuery("SELECT * FROM stories WHERE is_public=TRUE AND user_id=%s ORDER BY entered_at DESC LIMIT 100", $viewUserID);
		Story::printListForRS($rs);
		break;
	case PAGEMODE_GOALS:
		$currentTime=time();
		GoalStatus::printRowList($viewUserID, $currentTime, $viewingSelf);
		break;
	default:
		break;
}

printFooter();
?>