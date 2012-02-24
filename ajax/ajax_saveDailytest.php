<?php
include("template/userFacingBase.php");

if(	!isset($_GET["userID"]) ||
	!isset($_GET["dailytestID"]) ||
	!isset($_GET["checked"])) {
	exit;
}

$userID = GPC::strToInt($_GET["userID"]);
$dailytestID = GPC::strToInt($_GET["dailytestID"]);
$checked = GPC::strToInt($_GET["checked"]);

DailytestStatus::setTodayStatus($userID, $dailytestID, $checked);

?>