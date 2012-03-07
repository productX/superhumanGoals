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
require_once("include/chrome.php");

$goal = Goal::getObjFromGoalID($goalID);
const PAGEMODE_FACTS='facts';
const PAGEMODE_ACTIVITY='activity';
const PAGEMODE_PEOPLE='people';
$mode = PAGEMODE_FACTS;
if(isset($_GET["t"])) {
	$mode = $_GET["t"];
}
$tabIndex = 0;
switch($mode) {
	case PAGEMODE_FACTS:
		$tabIndex = 0;
		break;
	case PAGEMODE_ACTIVITY:
		$tabIndex = 1;
		break;
	case PAGEMODE_PEOPLE:
		$tabIndex = 2;
		break;
	default:
		assert(false);
		break;
}
printHeader(NAVNAME_GOALS, array(
					new ChromeTitleElementHeader("Goal: $goal->name"),
					new ChromeTitleElementTabs(	array(	"Facts"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_FACTS,
														"Activity"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_ACTIVITY,
														"People"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_PEOPLE
												), $tabIndex)
			));

switch($mode) {
	case PAGEMODE_FACTS:
		$numAdopters = $goal->getNumAdopters();
		$average = GoalStatus::getAverageGoalScore($goalID);
		if(is_null($average)) {
			$average=0;
		}
?>
					<!-- Case -->
					<div class="case">
						<!-- Score -->
						<div class="score">
							<div class="text">
								<p><strong>What it's all about:</strong> <?php echo $goal->description; ?></p>
<?php
		if(!$userHasGoal) {
?>
								<a href="<?php echo PAGE_GOAL."?id=$goalID&adopt";?>" class="btn">Adopt Goal &raquo;</a>
<?php
		}
?>
								<div class="cl">&nbsp;</div>
							</div>
							<div class="results">
								<ul>
								    <li><p class="res-box"><span><?php echo $numAdopters; ?></span></p><p class="label">People have this goal</p></li>
								    <li class="last"><p class="res-box"><span><?php echo sprintf("%1.1f",$average); ?></span></p><p class="label">Average level</p></li>
								</ul>
							</div>
							<div class="cl">&nbsp;</div>
						</div>
						<!-- End Score -->
					</div>
					<!-- End Case -->
<?php
		break;
	case PAGEMODE_ACTIVITY:
		// only returns event type stories for this goal
		$rs = $db->doQuery("SELECT * FROM stories WHERE is_public=TRUE AND type='".EventStory::STORY_TYPENAME."' AND event_goal_id=%s ORDER BY entered_at DESC LIMIT 100", $goalID);
		Story::printListForRS($rs);
		break;
	case PAGEMODE_PEOPLE:
		User::printListByGoal($goalID);
		break;
	default:
		break;
}

printFooter();
?>