<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$goalID = $_POST['goalID'];
$strategyName = $_POST['strategyName'];
$strategyDescription = $_POST['strategyDescription'];
$strategyType = $_POST['strategyType'];


	$strategyID = Dailytest::createNew($goalID, $strategyName, $strategyDescription, $strategyType, $userID);
	Dailytest::adoptStrategy($userID, $strategyID, $goalID);

	echo $strategyID;


?>