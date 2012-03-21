<?php
include("template/userFacingForceLogin.php");

/*****************************************************************
						DO PROCESSING
*****************************************************************/

$viewUserID = $user->id;
if(isset($_GET["id"])) {
	$viewUserID = GPC::strToInt($_GET["id"]);
}
$viewUser = User::getObjFromUserID($viewUserID);

/*****************************************************************
						RENDER PAGE
*****************************************************************/

$view->printUserPage($viewUser);
?>