<?php
include("template/userFacingForceLogin.php");

// DO PROCESSING
if(!isset($_GET["id"])) {
	redirect(PAGE_GOALS);
}
$goalID = GPC::strToInt($_GET["id"]);
$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goalID);

if(!$userHasGoal && isset($_GET["adopt"])) {
	$user->adoptGoal($goalID);
	$userHasGoal = true;
}

// RENDER PAGE
$view->printGoalPage($goalID);
?>