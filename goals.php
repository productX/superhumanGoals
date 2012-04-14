<?php
include("template/userFacingForceLogin.php");

// DO PROCESSING
if(isset($_POST["newGoalName"])) {
	$goalName = $_POST["newGoalName"];
	$goalDescription = $_POST["newGoalDescription"];
	$numDailytests = GPC::strToInt($_POST["numDailytests"]);
	$numkpis = $_POST["numkpis"];

	// create the goal  & pull back the new ID
	$newID = Goal::createNew($goalName, $goalDescription, $user->id);

	// check to see if the user created daily tests
	if($numDailytests>0) {
		// for each daily test
		for($i=0; $i<$numDailytests; ++$i) {
			$strategyName = $_POST["dailytestName".($i+1)];
			$strategyDescription = $_POST["dailytestDescription".($i+1)];
			$strategyType = $_POST["dailytestType".($i+1)];
			
			if($strategyName != ''){
				Dailytest::createNew($newID, $strategyName, $strategyDescription, $strategyType, $user->id);
			}
		}
	}

	if($numkpis>0) {
		for($i=0; $i<$numkpis; ++$i) {
			$kpiName = $_POST["kpiName".($i+1)];
			$kpiDescription = $_POST["kpiDescription".($i+1)];
			$kpiTestDescription = $_POST["kpiTestDescription".($i+1)];
			$kpiTestName = $_POST["kpiTestName".($i+1)];
			$kpiTestFrequency = $_POST["kpiTestFrequency".($i+1)];
			if($kpiName!="") {

				//KPI::createNew($newID, $kpiName, $kpiDescription, $kpiTestDescription, $kpiTestName, $kpiTestFrequency, $user->id);
			}
		}
	}
				
	StatusMessages::addMessage("'$goalName' successfully added.",StatusMessage::ENUM_GOOD);
}

// MOVED OUT OF VIEW
const NUM_COLS = 5;


/*****************************************************************
						RENDER PAGE
*****************************************************************/

$view->printAllGoalsPage();
?>
