<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$kpiID = $_POST['kpiID'];
$testID = $_POST['testID'];
$goalID = $_POST['goalID'];
$type = $_POST['type'];
$newKPIName = $_POST['newKPIName'];
$newKPITestName = $_POST['newKPITestName'];


if($type == 'adopt'){
	KPI::adoptKPI($userID, $kpiID, $goalID);
	//echo "KPI Adopted!";
}elseif($type == 'remove'){
	KPI::removeKPI($userID,$kpiID,$goalID);
	//echo "KPI Removed!";
}elseif($type == 'readopt'){
	KPI::reAdoptKPI($userID,$kpiID,$goalID);
	//echo "KPI ReAdopted!";
}elseif($type == 'edit'){
	KPI::editKPI($userID,$kpiID,$goalID,$newKPIName,$newKPITestName, $testID);
	echo "KPI Edited!";
}

?>