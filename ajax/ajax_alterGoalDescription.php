<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$goalID = $_POST['goalID'];
$description = $_POST['description'];


GoalStatus::alterDescription($userID, $goalID, $description);

//echo "Description Changed!";

?>