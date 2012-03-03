<?php
require_once(dirname(__FILE__)."/../../../../common/framework/auth/client/include/authClient.php"); 

function createIntranetAuth() {
	$serverCheckUserURL = "http://localhost/intranet/auth/server/intranetCheckUser.php";
	$serverAuthPageURL = "http://localhost/intranet/auth/server/intranetAuthPage.php";
	$serverGetUserVarsURL = "http://localhost/intranet/auth/server/intranetGetUserVars.php";
	$auth = new AuthClient($serverCheckUserURL, $serverAuthPageURL, $serverGetUserVarsURL);
	$auth->setPageOverrides(array());
	return $auth;
}
?>