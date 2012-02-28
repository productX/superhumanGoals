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
printHeader(NAVNAME_GOALS, array(new ChromeTitleElementHeader("All Goals")));

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
?>
					<!-- Case -->
					<div class="case goals">
						<!-- Cols -->
						<div class="cols">
							<p>Goals</p>
<?php
for($i=0; $i<NUM_COLS; ++$i) {
	if(isset($colContents[$i])) {
		echo "<div class='col'><ul>";
		foreach($colContents[$i] as $goal) {
			$pagePath = $goal->getPagePath();
			$numAdopters = $goal->getNumAdopters();
			echo "<li><a href='$pagePath'>".htmlspecialchars($goal->name)."</a> ($numAdopters)</li>";
		}
		echo "</ul></div>";
	}
}
?>
							<div class="cl">&nbsp;</div>
						</div>
						<!-- End Cols -->

						<div class="form">
							<p>Don't see your goal? Add one here:</p>
							<form action="<?php echo PAGE_GOALS;?>" method="post" name="goalForm">
								<label for="name">Goal Name:</label>
								<input type="text" class="field" value="" id="newGoalName" name="newGoalName" />
								<div class="cl">&nbsp;</div>
								<label for="description">Description:</label>
								<textarea id="newGoalDescription" name="newGoalDescription" rows="2" cols="40"></textarea>
								<div class="cl">&nbsp;</div>

								<script type="text/javascript">
									var numDailytests = 0;
									
									function addDailytest(postedTo) {
										document.goalForm.numDailytests=++numDailytests;
										document.getElementById("dailytests").innerHTML=document.getElementById("dailytests").innerHTML+
											"<label class='small-label'>Test "+numDailytests+" Name:</label><input type='text' class='small-field' name='dailytestName"+numDailytests+"' /><label class='small-label'>&nbsp;&nbsp;Description:</label><input type='text' class='small-field' name='dailytestDescription"+numDailytests+"' /><div class='cl'>&nbsp;</div>";
										document.getElementById("numDailytests").value=numDailytests;
									}
								</script>
								<div id="dailytests"></div>

								<input type="button" value="+" onclick="addDailytest();" class="small-add-btn"/>
								<input type="hidden" name="numDailytests" id="numDailytests" value="0" />
								<div class="cl" style="height:5px;">&nbsp;</div>
								<input type="submit" value="Add Goal &raquo;" class="add-btn" />
							</form>
						</div>
					</div>
					<!-- End Case -->

<?php
printFooter();
?>