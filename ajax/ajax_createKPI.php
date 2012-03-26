<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$goalID = $_POST['goalID'];
$kpiName = $_POST['kpiName'];
$kpiDescription = $_POST['kpiDescription'];
$kpiTestDescription = $_POST['kpiTestDescription'];
$kpiTestName = $_POST['kpiTestName'];
$kpiTestFrequency = $_POST['kpiTestFrequency'];
$adopt = $_POST['adopt'];

if($adopt == 'true'){

	$kpiInfo = KPI::createNew($goalID, $kpiName, $kpiDescription, $kpiTestDescription, $kpiTestName, $kpiTestFrequency,$userID);

	$kpiID = $kpiInfo[0];
	$testID = $kpiInfo[1];
	
	
	KPI::adoptKPI($userID, $kpiID, $goalID);
	KPI::adoptTest($userID, $kpiID, $goalID, $testID);

	echo json_encode($kpiInfo);

}elseif($adopt == 'false'){
	//echo "KPI/s Added but not Adopted!";
}


?>