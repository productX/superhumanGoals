<?php
include("template/userFacingForceLogin.php");

/*****************************************************************
						DO PROCESSING
*****************************************************************/

// get the ID for whose user page we're viewing
$viewUserID = $user->id;
if(isset($_GET["id"])) {
	$viewUserID = GPC::strToInt($_GET["id"]);
}
// pull a user obj for this ID
$viewUser = User::getObjFromUserID($viewUserID);

/*****************************************************************
						RENDER PAGE
*****************************************************************/

$view->printUserPage($viewUser);
?>