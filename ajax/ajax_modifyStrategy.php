<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$goalID = $_POST['goalID'];
$strategyID = $_POST['strategyID'];
$type = $_POST['type'];
$newStrategyName = $_POST['newStrategyName'];
$strategyType  = $_POST['strategyType'];
$newStrategyDescription  = $_POST['newStrategyDescription'];


if($type == 'adopt'){
	Dailytest::adoptStrategy($userID, $strategyID, $goalID);
	//echo "Strategy Adopted!";
}elseif($type == 'remove'){
	Dailytest::removeStrategy($userID,$strategyID,$goalID);
	//echo "Strategy Removed!";
}elseif($type == 'readopt'){
	Dailytest::reAdoptStrategy($userID,$strategyID,$goalID);
	//echo "Strategy ReAdopted!";
}elseif($type == 'edit'){
	Dailytest::editStrategy($userID,$strategyID,$goalID,$newStrategyName,$strategyType);
	//echo "Strategy Edited!";
}elseif($type == 'completed'){
	Dailytest::editTodo($userID,$strategyID,$goalID);
	//echo "Strategy Edited!";
}elseif($type == 'create'){
	$strategyID = Dailytest::createNew($goalID, $newStrategyName, $newStrategyDescription, $strategyType, $userID);
	Dailytest::adoptStrategy($userID, $strategyID, $goalID);
    header ("location: ../user.php?id=$userID&t=habits#.php");
}

?>