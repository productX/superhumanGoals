<?php
require_once("core.php");
require_once("session.php");
require_once("constants.php");

// global vars
$user = null;
$userLoggedIn = false;

// pertinent functions
function initGlobals() {
	global $user, $userLoggedIn;
	
	// set up assert options
	assert_options(ASSERT_ACTIVE, 1);
	assert_options(ASSERT_BAIL, 1);
	
	// database
	Database::init();
	
	// sessions
	Session::init();
	StatusMessages::init();

	// user
	$user = User::getLoggedInUser();
	$userLoggedIn = !is_null($user);
}
function verifyLogin() {
	global $user, $userLoggedIn;
	
	if(!$userLoggedIn) {
		redirect(PAGE_SIGNUP);
	}
	assert(!is_null($user));
	$user->trackVisit();
}

?>