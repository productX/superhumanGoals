<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$kpiID = $_POST['kpiID'];
$goalID = $_POST['goalID'];
$testID = $_POST['testID'];
$newActiveStatus = $_POST['newActiveStatus'];

	KPI::modifyTest($userID, $kpiID, $goalID, $testID, $newActiveStatus);
	//echo "Test Updated!";

?>