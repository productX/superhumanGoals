<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$goalID = $_POST['goalID'];
$displayStyle = $_POST['displayStyle'];

	GoalStatus::setTracking($userID, $goalID, $displayStyle);

?>