<?php
include("../template/userFacingForceLogin.php");
require_once("../include/controller.php");

// verify & pull parameters
if(	!isset($_GET["page"])) {
	exit;
}
$page = GPC::strToStr($_GET["page"]);

// HACK: print statements below will error if view isn't mobile

echo "<div style='height:135px'>&nbsp</div>";
switch($page) {
	case NAVNAME_USERS:
		$view->printAllUsersPageMainDiv();
		break;
	case NAVNAME_ACTIVITY:
		$view->printActivityPageMainDiv();
		break;
	case NAVNAME_GOAL:
		$goalID = null;
		Controller::processGoalPage($goalID);
		$view->printGoalPageMainDiv($goalID);
		break;
	case NAVNAME_MYHABITS:
		$viewUser = null;
		Controller::processUserPage($viewUser);
		$view->printUserPageMainDiv($viewUser, USERPAGEMODE_MYHABITS);
		break;
	case NAVNAME_MYGOALS:
		$viewUser = null;
		Controller::processUserPage($viewUser);
		$view->printUserPageMainDiv($viewUser, USERPAGEMODE_MYGOALS);
		break;
	default:
		die("ERROR: invalid page requested");
		break;
}
echo "<div style='height:160px'>&nbsp</div>";
?>
