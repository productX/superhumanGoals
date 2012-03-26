<?php
include("template/userFacingForceLogin.php");


// DO PROCESSING
if(isset($_POST["newGoalName"])) {
	$goalName = $_POST["newGoalName"];
	$goalDescription = $_POST["newGoalDescription"];
	$numDailytests = GPC::strToInt($_POST["numDailytests"]);
	$numkpis = $_POST["numkpis"];

	$newID = Goal::createNew($goalName, $goalDescription, $user->id);

	if($numDailytests>0) {
		for($i=0; $i<$numDailytests; ++$i) {
			$strategyName = $_POST["dailytestName".($i+1)];
			$strategyDescription = $_POST["dailytestDescription".($i+1)];
			$strategyType = $_POST["dailytestType".($i+1)];
			if($strategyName!="") {
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

				KPI::createNew($newID, $kpiName, $kpiDescription, $kpiTestDescription, $kpiTestName, $kpiTestFrequency, $user->id);
			}
		}
	}
				
	StatusMessages::addMessage("New goal '$goalName' successfully added.",StatusMessage::ENUM_GOOD);
}






// RENDER PAGE
require_once("include/chrome.php");
printHeader(NAVNAME_GOALS, array(new ChromeTitleElementHeader("All Goals")));

$rs = $db->doQuery("SELECT id FROM goals");
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
									var numkpis = 0;
																		
									function addKPI(postedTo) {
										document.goalForm.numkpis=++numkpis;
										document.getElementById("kpis").innerHTML=document.getElementById("kpis").innerHTML+
											"<label class='small-label'>KPI "+numkpis+":</label><input type='text' class='small-field' name='kpiName"+numkpis+"' /><label class='small-label'>&nbsp;&nbsp;Description:</label><input type='text' class='small-field' name='kpiDescription"+numkpis+"' /><br/><br/><label class='small-label'>&nbsp;&nbsp;Test Name:</label><input type='text' class='small-field' name='kpiTestName"+numkpis+"' /><label class='small-label'>&nbsp;&nbsp;Test Description:</label><input type='text' class='small-field' name='kpiTestDescription"+numkpis+"' /><br/><br/><label class='small-label'>&nbsp;&nbsp;Test Frequency (in days):</label><input type='text' class='small-field' name='kpiTestFrequency"+numkpis+"' /><div class='cl'>&nbsp;</div><br/>";
										document.getElementById("numkpis").value=numkpis;
									}
									
									function addDailytest(postedTo) {
										document.goalForm.numDailytests=++numDailytests;
										document.getElementById("dailytests").innerHTML=document.getElementById("dailytests").innerHTML+
											"<label class='small-label'>Strategy "+numDailytests+":</label><input type='text' class='small-field' name='dailytestName"+numDailytests+"' /><label class='small-label'>&nbsp;&nbsp;Description:</label><input type='text' class='small-field' name='dailytestDescription"+numDailytests+"' /><label class='small-label'>&nbsp;&nbsp;Type:</label><select name='dailytestType"+numDailytests+"'><option value='adherence'>Adherence</option><option value='todo'>ToDo</option><option value='tactic'>Tactic</option></select><div class='cl'>&nbsp;</div>";
										document.getElementById("numDailytests").value=numDailytests;
									}
								</script>
								<div id="kpis"></div>								
								<div id="dailytests"></div>

								<input type="button" value="Add KPI" onclick="addKPI();" class="small-add-btn"/>
								<input type="hidden" name="numkpis" id="numkpis" value="0" />

								<input type="button" value="Add Strategy" onclick="addDailytest();" class="small-add-btn"/>
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