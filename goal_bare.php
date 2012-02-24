<?php
include("template/userFacingForceLogin.php");

// DO PROCESSING
if(!isset($_GET["id"])) {
	redirect(PAGE_GOALS);
}
$goalID = $_GET["id"];
$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goalID);

if(!$userHasGoal && isset($_POST["adopt"])) {
	$user->adoptGoal($goalID);
	$userHasGoal = true;
}

// RENDER PAGE
require_once("include/header.php");
printHeader("Goal page");

const PAGEMODE_FACTS='facts';
const PAGEMODE_ACTIVITY='activity';
const PAGEMODE_PEOPLE='people';
$mode = PAGEMODE_FACTS;
if(isset($_GET["t"])) {
	$mode = $_GET["t"];
}
echo "<a href='".PAGE_GOAL."?id=$goalID&t=".PAGEMODE_FACTS."'>Facts</a> | ";
echo "<a href='".PAGE_GOAL."?id=$goalID&t=".PAGEMODE_ACTIVITY."'>Activity</a> | ";
echo "<a href='".PAGE_GOAL."?id=$goalID&t=".PAGEMODE_PEOPLE."'>People</a><br/><br/>";
switch($mode) {
	case PAGEMODE_FACTS:
		$goal = Goal::getObjFromGoalID($goalID);
		$numAdopters = $goal->getNumAdopters();
		$average = GoalStatus::getAverageGoalScore($goalID);
		echo "$numAdopters people have it.<br/>";
		echo "$average is the average score.<br/>";
		if(!$userHasGoal) {
			echo "<form method='post' action='".PAGE_GOAL."?id=$goalID'><input type='submit' name='adopt' value='Adopt Goal' /></form>";
		}
		break;
	case PAGEMODE_ACTIVITY:
		// only returns event type stories for this goal
		$rs = Database::doQuery("SELECT * FROM stories WHERE is_public=TRUE AND type='".EventStory::STORY_TYPENAME."' AND event_goal_id=$goalID ORDER BY entered_at DESC LIMIT 100");
		Story::printListForRS($rs);
		break;
	case PAGEMODE_PEOPLE:
		User::printListByGoal($goalID);
		break;
	default:
		assert(false);
		break;
}

printFooter();
?>