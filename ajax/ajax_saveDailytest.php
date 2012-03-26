<?php
include("../template/userFacingBase.php");

if(	!isset($_GET["userID"]) ||
	!isset($_GET["dailytestID"]) ||
	!isset($_GET["result"])) {
	exit;
}

$userID = GPC::strToInt($_GET["userID"]);
$dailytestID = GPC::strToInt($_GET["dailytestID"]);
$result = GPC::strToInt($_GET["result"]);

DailytestStatus::setTodayStatus($userID, $dailytestID, $result);

echo "ok";
?>