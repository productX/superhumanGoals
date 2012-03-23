<?php
require_once(dirname(__FILE__)."/../../../../common/framework/auth/client/include/authClient.php"); 
require_once(dirname(__FILE__)."/../../../config/config.php"); 

// function to intialize the intranet auth client
function createIntranetAuth() {
	$serverCheckUserURL = CONFIG_AUTHCHECKUSERURL;
	$serverAuthPageURL = CONFIG_AUTHPAGEURL;
	$serverGetUserVarsURL = CONFIG_AUTHGETUSERVARSURL;
	
	// create auth client (login later)
	$auth = new AuthClient($serverCheckUserURL, $serverAuthPageURL, $serverGetUserVarsURL);
	$auth->setPageOverrides(array());
	return $auth;
}
?>