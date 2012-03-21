<?php
include("template/userFacingForceLogin.php");

// DO PROCESSING
if(isset($_POST["newGoalName"])) {
	$name = $_POST["newGoalName"];
	$description = $_POST["newGoalDescription"];
	$numDailytests = GPC::strToInt($_POST["numDailytests"]);

	$newID = Goal::createNew($name, $description);
	$goalName = $name;
	if($numDailytests>0) {
		for($i=0; $i<$numDailytests; ++$i) {
			$name = $_POST["dailytestName".($i+1)];
			$description = $_POST["dailytestDescription".($i+1)];
			if($name!="") {
				Dailytest::createNew($newID, $name, $description);
			}
		}
	}
	
	StatusMessages::addMessage("New goal '$goalName' successfully added.",StatusMessage::ENUM_GOOD);
}

// RENDER PAGE
$view->printAllGoalsPage();
?>