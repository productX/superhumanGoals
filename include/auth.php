<?php
$baseDir = "http://intranet.productx.co/goals";
if(true) { // Windows Debug
	$baseDir = "http://localhost/intranet/goals";
}

$authCodePath="../intranet/auth";
$authWebPath="../auth"; // it would be same as code path, except for symlink to "goals" inside intranet dir
require_once(dirname(__FILE__)."/../$authCodePath/config.php");
require_once(dirname(__FILE__)."/../$authCodePath/functions.php"); 

function authorize() {
	global $authCodePath, $authWebPath;
	startAuthSession();

	//this is group name or username of the group or person that you wish to allow access to
	// - please be advise that the Administrators Groups has access to all pages.
	if (!allow_access($requiredGroup = "Users")) {                       
		//this should the the absolute path to the no_access.html file - see above                                     
		$_SESSION["redirect"] = $_SERVER['SCRIPT_NAME'];
		//die($_SESSION["redirect"]);
		redirect("$authWebPath/errorlogin.php?restricted");
	}
}
?>