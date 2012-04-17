<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$goalID = $_POST['goalID'];
$strategyID = $_POST['strategyID'];
$type = $_POST['type'];
$newStrategyName = $_POST['newStrategyName'];
$strategyType  = $_POST['strategyType'];
$newStrategyDescription  = $_POST['newStrategyDescription'];
$page = $_POST['page'];
if(isset($_POST['isPublic'])){
	$is_public = $_POST['isPublic'];
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
}elseif($type == 'create'){

echo "<pre>";
print_r($_POST);
echo "</pre>";
echo $is_public;

	if($is_public > -1){
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