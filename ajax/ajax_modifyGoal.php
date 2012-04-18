<?php
include("../template/userFacingBase.php");

$type = $_POST['type'];
$userID = $_POST['userID'];
$goalID = $_POST['goalID'];
if(isset($_POST['isPublic'])){
	$is_public = $_POST['isPublic'];
}else{
	$is_public = 0;
}

if($type == 'insert'){
	if($is_public > -1){
		GoalStatus::userAdoptGoalSimple($userID, $goalID,$is_public);
	}
}elseif($type == 'remove'){
	GoalStatus::userRemoveGoal($userID, $goalID);
	echo '';
}elseif($type == 'delete'){
	GoalStatus::userDeleteGoal($userID, $goalID);
	echo '';
}elseif($type == 'privacy'){
	GoalStatus::editPrivacy($userID, $goalID, $is_public);
	echo '';
}

//echo "Goal Adopted!";

?>