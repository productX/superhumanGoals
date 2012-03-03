<?php

require_once("constants.php");
require_once(dirname(__FILE__)."/../../common/include/functions.php"); 

$authCodePath="intranet";
$authWebPath="intranet"; // it would be same as code path, except for symlink to "goals" inside intranet dir
require_once(dirname(__FILE__)."/../$authCodePath/auth/config.php");
require_once(dirname(__FILE__)."/../$authCodePath/auth/functions.php"); 

function verifyIntranetLogin() {
	global $authCodePath, $authWebPath;
	startAuthSession();

	//this is group name or username of the group or person that you wish to allow access to
	// - please be advise that the Administrators Groups has access to all pages.
	if (!allow_access($requiredGroup = "Users")) {                       
		//this should the the absolute path to the no_access.html file - see above                                     
		$_SESSION["redirect"] = $_SERVER['SCRIPT_NAME'];
		//die($_SESSION["redirect"]);
		redirect("$authWebPath/auth/errorlogin.php?restricted");
	}
}

function getIntranetLogoutPath() {
	global $authWebPath;
	return $authWebPath."/auth/logout.php";
}

// HACK: probably best to do another class for this, but not worth the bother right now to untangle how to mesh this with the global static "Database" class
function getAuthUserData($authID) {
	static $server = "localhost";
	static $username = "root";
	static $password = "faramir";
	static $dbName = "intranet_user_auth";

	$conn = @mysql_connect($server, $username, $password) or die(mysql_error());
	@mysql_select_db($dbName,$conn)or die(mysql_error());
	$rs = @mysql_query("SELECT * FROM auth_users WHERE id=$authID",$conn) or die(mysql_error());
	$obj = null;
	if(mysql_num_rows($rs)>0) {
		$obj = mysql_fetch_object($rs);
	}
	Database::reInit();
	return $obj;
}

?>