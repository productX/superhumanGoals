<?php
include("template/userFacingForceLogin.php");
require_once("include/controller.php");

/*****************************************************************
						DO PROCESSING
*****************************************************************/

$viewUser = null;
Controller::processUserPage($viewUser);

/*****************************************************************
						RENDER PAGE
*****************************************************************/

$view->printUserPage($viewUser);
?>