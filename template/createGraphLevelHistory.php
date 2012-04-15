<?php
include("userFacingForceLogin.php");

// verify & pull parameters
if(	!isset($_GET["userID"]) ||
	!isset($_GET["goalID"])) {
	exit;
}
$userID = GPC::strToInt($_GET["userID"]);
$goalID = GPC::strToInt($_GET["goalID"]);

// pull the user's level history for this goal as far back as we want to go
static $numDaysBack = 45;
$graphData = EventStory::getLevelHistory($userID, $goalID, $numDaysBack);

// define the size (different for diff views)
$width = 200;
$height = 40;
if(isset($_GET['big'])) {
	$width = 400;
	$height = 100;
}


?>
<span class="levelGraph"></span>
<script type="text/javascript">
	/* Inline sparklines take their values from the contents of the tag */
	var values = [<?php echo implode(",",$graphData); ?>];
	$('.levelGraph').sparkline(values, {width:'<?php echo $width;?>px',height:'<?php echo $height;?>px', chartRangeMin:0, chartRangeMax:10, lineWidth:4, spotRadius:8}); 
</script>
