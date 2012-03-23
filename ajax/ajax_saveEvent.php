<?php
include("../template/userFacingBase.php");

// verify & pull parameters
if(	!isset($_GET["userID"]) ||
	!isset($_GET["goalID"]) ||
	!isset($_GET["newLevel"]) ||
	!isset($_GET["oldLevel"]) ||
	!isset($_GET["letterGrade"]) ||
	!isset($_GET["why"])) {
	exit;
}
$userID = GPC::strToInt($_GET["userID"]);
$goalID = GPC::strToInt($_GET["goalID"]);
$newLevel = GPC::strToFloat($_GET["newLevel"]);
$oldLevel = GPC::strToFloat($_GET["oldLevel"]);
$letterGrade = GPC::strToLetterGrade($_GET["letterGrade"]);
$why = $_GET["why"];

// create a new event or update today's event for this user/goal
EventStory::createNewOrUpdate($userID, $goalID, $newLevel, $oldLevel, $letterGrade, $why);

// set the user's goal level to their new level
GoalStatus::setUserGoalLevel($userID, $goalID, $newLevel);

// return processing result (this currently isn't captured)
echo "ok";
?>