<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<?php
include("template/userFacingForceLogin.php");

// if no ID is specified, redirect to the All Goals page
if(!isset($_GET["id"])) {
	redirect(PAGE_GOALS);
}

$goalID = GPC::strToInt($_GET["id"]);


// MOVED OUT OF VIEW
const PAGEMODE_EDIT='edit';
const PAGEMODE_ACTIVITY='activity';
const PAGEMODE_PEOPLE='people';
const PAGEMODE_FACTS='facts';



/*****************************************************************
						RENDER PAGE
*****************************************************************/

$view->printGoalPage($goalID);

?>