<?php
require_once("core.php");
require_once("constants.php");
require_once("view.php");
require_once(dirname(__FILE__)."/../config/config.php");
require_once(dirname(__FILE__)."/../../common/include/functions.php"); 
require_once(dirname(__FILE__)."/../../common/framework/auth/include/appAuth.php"); 
require_once(dirname(__FILE__)."/../auth/client/include/sgAuthClient.php"); 

// global vars
$user = null;
$appAuth = null;
$db = null;
$view = null;

function initError() {
	// assert options
	assert_options(ASSERT_ACTIVE, 1);
	assert_options(ASSERT_BAIL, 1);
}

function initTime() {
	// timezone
	Date::setTimezone(/* everybody is on PST */);
}

function initDB() {
	global $db;
		
	// database
	$db = Database::init(CONFIG_DBSERVER, CONFIG_DBUSER, CONFIG_DBPASS, CONFIG_DBNAME, FUNCNAME_HANDLESQLARGOBJ);
}

function initSession() {
	// sessions
	Session::init();
}

function initView() {
	global $view;
	if(!is_null($view)) {
		return;
	}
	
	// do some magic to figure out if we're mobile or PC
	$viewmode = ViewSwitch::getViewmode(	VIEWSWITCH_MOBILEVIEWSERVER,
											VIEWSWITCH_MOBILEVIEWQS,
											VIEWSWITCH_WEBVIEWSERVER,
											VIEWSWITCH_WEBVIEWQS
										);

	// create view
	switch($viewmode) {
		case ViewSwitch::VIEWMODE_MOBILE:
			$view = new MobileView();
			break;
		case ViewSwitch::VIEWMODE_WEB:
			$view = new WebView();
			break;
		default:
			assert(false);
			break;
	}

	StatusMessages::init();
}

function initAuth() {
	global $appAuth;

	$authClients = array();
	$authClients[] = createSGAuth();
	$appAuth = AppAuth::init("appAuthGetUserForAuthID", "appAuthCreateNewUser", null /* no sign-up page*/, $authClients);
}

// returns false if fail, userID if pass
function appAuthGetUserForAuthID($lastAuthClientUserID) {
	global $db;
	
	$userID = null;
	$sgObj = $db->doQueryRFR("SELECT id FROM users WHERE auth_id=%s", $lastAuthClientUserID);
	if(!is_null($sgObj)) {
		$userID = $sgObj->id;
	}
	return $userID;
}

function appAuthCreateNewUser($lastAuthClientUserID, $authClientUserData) {
	User::createNewForSignup($lastAuthClientUserID, $authClientUserData);
}

const FUNCNAME_HANDLESQLARGOBJ='handleSQLArgObj';
function handleSQLArgObj($className, $arg) {
	$val="";
	// this function is left here as an example. the below cases are now built into the Database class in common
	/*if($className == CLASSNAME_DATETIME) {
		$val = $arg->toSQLStr();
	}
	elseif($className == CLASSNAME_SQLARGLIKE) {
		$val = $arg->toSQLStr();
	}*/
	return $val;
}

?>
