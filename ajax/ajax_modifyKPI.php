<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$kpiID = $_POST['kpiID'];
$testID = $_POST['testID'];
$goalID = $_POST['goalID'];
$type = $_POST['type'];
$newKPIName = $_POST['newKPIName'];
$newKPIDescription = $_POST['newKPIDescription']
$newKPITestName = $_POST['newKPITestName'];
$newKPITestDescription = $_POST['newKPITestDescription'];
$newKPITestFrequency = $_POST['newKPITestFrequency'];

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
}elseif($type == 'create'){
	$kpiInfo = KPI::createNew($goalID, $newKPIName, $newKPIDescription, $newKPITestDescription, $newKPITestName, $newKPITestFrequency,$userID);
	$kpiID = $kpiInfo[0];
	$testID = $kpiInfo[1];
	KPI::adoptKPI($userID, $kpiID, $goalID);
	if($kpiTestName != ''){
		KPI::adoptTest($userID, $kpiID, $goalID, $testID);
	}
}



?>