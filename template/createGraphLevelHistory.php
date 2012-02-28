<?php
include("userFacingForceLogin.php");

require_once("../../common/lib/phpgraphlib/phpgraphlib.php");

if(	!isset($_GET["userID"]) ||
	!isset($_GET["goalID"])) {
	exit;
}
$userID = GPC::strToInt($_GET["userID"]);
$goalID = GPC::strToInt($_GET["goalID"]);

static $numDaysBack = 45;
$graphData = EventStory::getLevelHistory($userID, $goalID, $numDaysBack);

//var_dump($graphData);
//die();

$graph = new PHPGraphLib(290,88);
/*$data = array("1" => .0032, "2" => .0028, "3" => .0021, "4" => .0033, 
"5" => .0034, "6" => .0031, "7" => .0036, "8" => .0027, "9" => .0024, 
"10" => .0021, "11" => .0026, "12" => .0024, "13" => .0036, 
"14" => .0028, "15" => .0025);*/
$graph->addData($graphData);
$graph->setTitle("Progress: Last ".$numDaysBack." days");
$graph->setBars(false);
$graph->setLine(true);
$graph->setRange(10,0);
$graph->setXValues(false);
/*$graph->setDataPoints(true);
$graph->setDataPointColor('maroon');*/
//$graph->setDataValues(true);
$graph->setDataValueColor('maroon');
/*$graph->setGoalLine(.0025);
$graph->setGoalLineColor('red');*/
$graph->createGraph();
?>