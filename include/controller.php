<?php
// INCLUDE after userFacingForceLogin

class Controller {
	public static function processUserPage(&$viewUser) {
		global $user;
		
		// get the ID for whose user page we're viewing
		$viewUserID = $user->id;
		if(isset($_GET["id"])) {
			$viewUserID = GPC::strToInt($_GET["id"]);
		}
		// pull a user obj for this ID
		$viewUser = User::getObjFromUserID($viewUserID);
		
		// return $viewUser
	}
	public static function processGoalPage(&$goalID) {
		global $view;
		
		// if no ID is specified, redirect to the All Goals page
		if(!isset($_GET["id"])) {
			$view->handleNoGoalForGoalPage();
		}
		$goalID = GPC::strToInt($_GET["id"]);
	}
};

?>
