<?php
include("../template/userFacingBase.php");

$type = $_POST['type'];
$userID = $_POST['userID'];
$goalID = $_POST['goalID'];

if($type == 'insert'){
	GoalStatus::userAdoptGoalSimple($userID, $goalID);
}elseif($type == 'remove'){
	GoalStatus::userRemoveGoal($userID, $goalID);

}


//echo "Goal Adopted!";

?>