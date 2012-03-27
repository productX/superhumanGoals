<?php
require_once(dirname(__FILE__)."/../../../../common/framework/auth/client/include/authClient.php"); 
require_once(dirname(__FILE__)."/../../../config/config.php"); 

function createSGAuth() {
	$serverAuthPageURL = CONFIG_AUTHPAGEURL;
	$serverAuthCommandURL = CONFIG_AUTHCOMMANDURL;
	$auth = new AuthClient($serverAuthCommandURL, $serverAuthPageURL);
	$auth->setPageOverrides(array());
	return $auth;
}
?>