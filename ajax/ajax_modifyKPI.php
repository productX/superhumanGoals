<?php
include("../template/userFacingBase.php");

$userID = $_POST['userID'];
$kpiID = $_POST['kpiID'];
$goalID = $_POST['goalID'];
$type = $_POST['type'];

if($type == 'adopt'){
	KPI::adoptKPI($userID, $kpiID, $goalID);
	//echo "KPI Adopted!";
}elseif($type == 'remove'){
	KPI::removeKPI($userID,$kpiID,$goalID);
	//echo "KPI Removed!";
}elseif($type == 'readopt'){
	KPI::reAdoptKPI($userID,$kpiID,$goalID);
	//echo "KPI ReAdopted!";
}


?>