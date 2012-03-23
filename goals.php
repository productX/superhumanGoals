<?php
include("template/userFacingForceLogin.php");

/*****************************************************************
						DO PROCESSING
*****************************************************************/

// check to see if the user wants to create a new goal
if(isset($_POST["newGoalName"])) {
	// pull the goal's parameters
	$name = $_POST["newGoalName"];
	$description = $_POST["newGoalDescription"];
	$numDailytests = GPC::strToInt($_POST["numDailytests"]);

	// create the goal  & pull back the new ID
	$newID = Goal::createNew($name, $description);
	// check to see if the user created daily tests
	if($numDailytests>0) {
		// for each daily test
		for($i=0; $i<$numDailytests; ++$i) {
			// pull the parameters & create it, tied to the new goal's ID
			$name = $_POST["dailytestName".($i+1)];
			$description = $_POST["dailytestDescription".($i+1)];
			if($name!="") {
				Dailytest::createNew($newID, $name, $description);
			}
		}
	}
	
	// create a status message informing the user of success
	$goalName = $name;
	StatusMessages::addMessage("New goal '$goalName' successfully added.",StatusMessage::ENUM_GOOD);
}


/*****************************************************************
						RENDER PAGE
*****************************************************************/

$view->printAllGoalsPage();
?>