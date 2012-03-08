<?php
require_once("core.php");
require_once("constants.php");

// global vars
$user = null;
$appAuth = null;
$db = null;

const FUNCNAME_HANDLESQLARGOBJ='handleSQLArgObj';
function handleSQLArgObj($className, $arg) {
	$val="";
	if($className == CLASSNAME_DATETIME) {
		$val = $arg->toSQLStr();
	}
	elseif($className == CLASSNAME_SQLARGLIKE) {
		$val = $arg->toSQLStr();
	}
	return $val;
}

function initGlobals() {
	global $user, $appAuth, $db;
	
	// assert options
	assert_options(ASSERT_ACTIVE, 1);
	assert_options(ASSERT_BAIL, 1);
	
	// timezone
	Date::setTimezone(/* everybody is on PST */);
	
	// database
	$db = Database::init("localhost", "root", "", "superhuman_goals", FUNCNAME_HANDLESQLARGOBJ);
	
	// sessions
	Session::init();
	StatusMessages::init();

	// app auth
	$appAuth = AppAuth::init("appTryAutoLogin", "User::createNewForSignup");
	if($appAuth->isLoggedIn()) {
		$userID = $appAuth->getUserID();
		$user = User::getObjFromUserID($userID);
	}
}

/* HACK: this should move into a common place at some point. requirements for doing this: more documentation, common session class
perhaps should also find a way to have AppAuth manage all AuthClient sessions & serve as single point for all auth?
*/ 
class AppAuth {

	// private
	const SESSVAR_USERID = 'userID';
	private $userID=null;
	private $autoLoginFunc=null, $createNewUserFunc=null;
	private function __construct($autoLoginFunc, $createNewUserFunc) {
		$this->autoLoginFunc = $autoLoginFunc;
		$this->createNewUserFunc = $createNewUserFunc;
		$this->userID = null;
		// HACK: certainly could put this somewhere better
		if(isset($_GET['logout'])) {
			$this->doLogout();
			return;
		}
		if(Session::issetVar(AppAuth::SESSVAR_USERID)) {
			$this->userID = Session::getVar(AppAuth::SESSVAR_USERID);
		}
	}
	
	// protected
	
	// public
	public static function init($autoLoginFunc, $createNewUserFunc) {
		return new AppAuth($autoLoginFunc, $createNewUserFunc);
	}
	public function isLoggedIn() {
		return !is_null($this->userID);
	}
	public function getUserID() {
		return $this->userID;
	}
	public function enforceLogin($redirectURL) {
		if(!$this->isLoggedIn()) {
			redirect($redirectURL);
		}
	}
	public function tryAutoLogin() {
		$autoLoginFunc = $this->autoLoginFunc;
		$success = $autoLoginFunc();
		if(!$success) {
			return false;
		}
		$userID = $success;
		Session::setVar(AppAuth::SESSVAR_USERID, $userID);
		return true;
	}
	public function doSignup($appData) {
		if($this->isLoggedIn()) {
			// already logged in
			return false;
		}
		if($this->tryAutoLogin()) {
			// user obj already exists; were able to "automatically" log them in
			return false;
		}
		$createNewUserFunc = $this->createNewUserFunc;
		$newID = $createNewUserFunc($appData);
		Session::setVar(AppAuth::SESSVAR_USERID, $newID);
		return true;
	}
	public function doLogout() {
		Session::clearVar(AppAuth::SESSVAR_USERID);
	}
	
};

// returns false if fail, userID if pass
function appTryAutoLogin() {
	global $intranetAuth, $db;
	
	$success = false;
	$authUserID = $intranetAuth->getUserID();
	$sgObj = $db->doQueryRFR("SELECT * FROM users WHERE auth_id=%s", $authUserID);
	if(!is_null($sgObj)) {
		$userID = $sgObj->id;
		$success = $userID;
	}
	return $success;
}

?>
