<?php
include("template/userFacingBase.php");

if(	!isset($_GET["userID"]) ||
	!isset($_GET["goalID"]) ||
	!isset($_GET["pageSession"]) ||
	!isset($_GET["newLevel"]) ||
	!isset($_GET["oldLevel"]) ||
	!isset($_GET["letterGrade"]) ||
	!isset($_GET["why"])) {
	exit;
}

$userID = $_GET["userID"];
$goalID = $_GET["goalID"];
$pageSession = $_GET["pageSession"];
$newLevel = $_GET["newLevel"];
$oldLevel = $_GET["oldLevel"];
$letterGrade = $_GET["letterGrade"];
$why = $_GET["why"];

EventStory::createNewOrUpdate($userID, $goalID, $newLevel, $oldLevel, $letterGrade, $why, $pageSession);

?>