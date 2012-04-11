<?php
include("../template/userFacingForceLogin.php");

// verify & pull parameters
if(	!isset($_GET["userID"]) ||
	!isset($_GET["dailytestID"]) ||
	!isset($_GET["result"])) {
	exit;
}
if(!isset($_GET['date'])){
	$today = date("Y-m-d"); 	
	$today = (string)$today;
}else{
	$today = $_GET['date'];
}


$userID = GPC::strToInt($_GET["userID"]);
$dailytestID = GPC::strToInt($_GET["dailytestID"]);
$result = GPC::strToInt($_GET["result"]);

// create or modify a user's status today for a particular adherence test
DailytestStatus::setTodayStatus($userID, $dailytestID, $result, $today);

// echo success (although this currently isn't captured)
echo $today;
?>