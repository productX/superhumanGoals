<?php
include("template/userFacingForceLogin.php");

/*****************************************************************
						DO PROCESSING
*****************************************************************/

// if no ID is specified, redirect to the All Goals page
if(!isset($_GET["id"])) {
	redirect(PAGE_GOALS);
}

// get the ID & determine if user has the goal
$goalID = GPC::strToInt($_GET["id"]);
$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goalID);

// check to see if user is adopting the goal
if(!$userHasGoal && isset($_GET["adopt"])) {
	$user->adoptGoal($goalID);
	$userHasGoal = true;
}


/*****************************************************************
						RENDER PAGE
*****************************************************************/

$view->printGoalPage($goalID);
?>