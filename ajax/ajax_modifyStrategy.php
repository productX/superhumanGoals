<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$goalID = $_POST['goalID'];
$strategyID = $_POST['strategyID'];
$type = $_POST['type'];
$newStrategyName = $_POST['newStrategyName'];
$strategyType  = $_POST['strategyType'];


if($type == 'adopt'){
	Dailytest::adoptStrategy($userID, $strategyID, $goalID);
	echo "Strategy Adopted!";
}elseif($type == 'remove'){
	Dailytest::removeStrategy($userID,$strategyID,$goalID);
	echo "Strategy Removed!";
}elseif($type == 'readopt'){
	Dailytest::reAdoptStrategy($userID,$strategyID,$goalID);
	echo "Strategy ReAdopted!";
}elseif($type == 'edit'){
	Dailytest::editStrategy($userID,$strategyID,$goalID,$newStrategyName,$strategyType);
	echo "Strategy Edited!";
}



?>