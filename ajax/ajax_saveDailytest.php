<?php
include("../template/userFacingForceLogin.php");

// verify & pull parameters
if(	!isset($_GET["userID"]) ||
	!isset($_GET["dailytestID"]) ||
	!isset($_GET["result"])) {
	exit;
}
$userID = GPC::strToInt($_GET["userID"]);
$dailytestID = GPC::strToInt($_GET["dailytestID"]);
$result = GPC::strToInt($_GET["result"]);

// create or modify a user's status today for a particular adherence test
DailytestStatus::setTodayStatus($userID, $dailytestID, $result);

// echo success (although this currently isn't captured)
echo "ok";
?>