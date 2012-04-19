<?php
include("../template/userFacingBase.php");

$userID = $_REQUEST['userID'];
$goalID = $_REQUEST['goalID'];
$strategyID = $_REQUEST['strategyID'];
$type = $_REQUEST['type'];
$newStrategyName = $_REQUEST['newStrategyName'];
$strategyType  = $_REQUEST['strategyType'];
$newStrategyDescription  = $_REQUEST['newStrategyDescription'];
$page = $_REQUEST['page'];
if(isset($_REQUEST['isPublic'])){
	$is_public = $_REQUEST['isPublic'];
}else{
	$is_public = 0;
}

if($type == 'adopt'){
	Dailytest::adoptStrategy($userID, $strategyID, $goalID, $is_public);
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
}elseif($type == 'privacy'){
	Dailytest::editPrivacy($userID,$strategyID,$goalID,$is_public);
	//echo "Strategy Edited!";
}elseif($type == 'create'){
	//$db->debugMode(true);
	if($is_public > -1){
		// HACK: should be updated to "habit" everywhere
		if($strategyType=="habit") {
			$strategyType="adherence";
		}
		$strategyID = Dailytest::createNew($goalID, $newStrategyName, $newStrategyDescription, $strategyType, $userID);
		if($strategyID > 0){
			Dailytest::adoptStrategy($userID, $strategyID, $goalID, $is_public);
		}
	}
		if($page == 1 ){
		    header ("location: ../user.php?id=$userID&t=habits#.php");
		}
	echo $strategyID;
}





?>