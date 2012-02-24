<?php
include("template/userFacingBase.php");

if(	!isset($_GET["userID"]) ||
	!isset($_GET["dailytestID"]) ||
	!isset($_GET["checked"])) {
	exit;
}

$userID = $_GET["userID"];
$dailytestID = $_GET["dailytestID"];
$checked = $_GET["checked"];

DailytestStatus::setTodayStatus($userID, $dailytestID, $checked);

?>