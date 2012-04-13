<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<?php
include("template/userFacingForceLogin.php");
require_once("include/controller.php");

/*****************************************************************
						DO PROCESSING
*****************************************************************/

$goalID = null;
Controller::processGoalPage($goalID);

/*****************************************************************
						RENDER PAGE
*****************************************************************/

// HACK: should be in WebView::printGoalPage, but const defs can't be in functions...
const PAGEMODE_EDIT='edit';
const PAGEMODE_ACTIVITY='activity';
const PAGEMODE_PEOPLE='people';
const PAGEMODE_FACTS='facts';

$view->printGoalPage($goalID);
?>