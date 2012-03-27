<?php
require_once(dirname(__FILE__)."/../../../../common/framework/auth/server/include/authServer.php"); 
require_once(dirname(__FILE__)."/../../../config/config.php"); 

function createSGAuthServer() {
	$dbServer = CONFIG_AS_DBSERVER;
	$dbUsername = CONFIG_AS_DBUSER;
	$dbPassword = CONFIG_AS_DBPASS;
	$dbName = CONFIG_AS_DBNAME;
	$logActivity = CONFIG_AS_LOGACTIVITY;
	$sessionDuration = CONFIG_AS_SESSIONDURATION;
	$adminEmail = CONFIG_AS_ADMINEMAIL;
	$confirmEmail = CONFIG_AS_CONFIRMEMAIL;
	$passRuleMinLength = CONFIG_AS_PASSRULEMAXLEN;
	$passRuleMaxLength = CONFIG_AS_PASSRULEMINLEN;
	$namespace = CONFIG_AS_NAMESPACE;
	$authPagePath = CONFIG_AS_PAGEABSURL;
	$authPageProcessorPath = CONFIG_AS_PAGEPROCESSORABSURL;
	$funcPrintHeader = CONFIG_AS_FUNCPRINTHEADER;
	$funcPrintFooter = CONFIG_AS_FUNCPRINTFOOTER;
	global $CONFIG_AS_EXTRADATAPARAMS;
	$extraDataParams = $CONFIG_AS_EXTRADATAPARAMS;
	$authServer = new AuthServer(	$dbServer, $dbUsername, $dbPassword, $dbName, $logActivity, $sessionDuration,
									$adminEmail, $confirmEmail, $passRuleMinLength, $passRuleMaxLength,
									$namespace, $authPagePath, $authPageProcessorPath,
									$funcPrintHeader, $funcPrintFooter, $extraDataParams);
	return $authServer;
}
?>