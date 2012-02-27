<?php
include("template/userFacingForceLogin.php");

// DO PROCESSING
if(isset($_POST["newGoalName"])) {
	$name = $_POST["newGoalName"];
	$description = $_POST["newGoalDescription"];
	$numDailytests = GPC::strToInt($_POST["numDailytests"]);

	$newID = Goal::createNew($name, $description);
	if($numDailytests>0) {
		for($i=0; $i<$numDailytests; ++$i) {
			$name = $_POST["dailytestName".($i+1)];
			$description = $_POST["dailytestDescription".($i+1)];
			if($name!="") {
				Dailytest::createNew($newID, $name, $description);
			}
		}
	}
	
	StatusMessages::addMessage("New goal '$name' successfully added.",StatusMessage::ENUM_GOOD);
}

// RENDER PAGE
require_once("include/chrome.php");
printHeader("Goals page");

$rs = Database::doQuery("SELECT id FROM goals");
$numGoals = mysql_num_rows($rs);
const NUM_COLS = 5;
$numPerColumn = max($numGoals/NUM_COLS,4);
$colContents = array();
$obj=null;
$currentCol=0;
$i=0;
while($obj = mysql_fetch_object($rs)) {
	$goal = Goal::getObjFromGoalID($obj->id);
	if($i==0) {
		$colContents[$currentCol] = array();
	}
	$colContents[$currentCol][] = $goal;
	++$i;
	if($i>=$numPerColumn) {
		++$currentCol;
		$i=0;
	}
}
echo "All Goals<br/>";
for($i=0; $i<NUM_COLS; ++$i) {
	if(isset($colContents[$i])) {
		echo "Column $i<br/>";
		foreach($colContents[$i] as $goal) {
			$pagePath = $goal->getPagePath();
			$numAdopters = $goal->getNumAdopters();
			echo "<a href='$pagePath'>".htmlspecialchars($goal->name)."</a> ($numAdopters)<br/>";
		}
	}
}
echo "<br/>";
?>

Don't see your goal? Add here:<br/>
<form method="post" action="<?php echo PAGE_GOALS;?>" name="goalForm">
Goal name: <input type="text" name="newGoalName" /><br/>
Description: <input type="text" name="newGoalDescription" /><br/>

<script type="text/javascript">
	var numDailytests = 0;
	
	function addDailytest(postedTo) {
		document.getElementById("dailytests").innerHTML=document.getElementById("dailytests").innerHTML+"Name: <input type='text' name='dailytestName"+(numDailytests+1)+"' /><br/>Description: <input type='text' name='dailytestDescription"+(numDailytests+1)+"' /><br/>";
		document.goalForm.numDailytests=++numDailytests;
		document.getElementById("numDailytests").value=numDailytests;
	}
</script>
<div id="dailytests"></div>
<input type="button" value="Add daily test" onclick="addDailytest();"/><br/>
<input type="hidden" name="numDailytests" id="numDailytests" value="0" />

<input type="submit" value="Submit" />
</form>

<?php
printFooter();
?>