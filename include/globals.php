<?php
require_once("core.php");
require_once("session.php");
require_once("constants.php");

// global vars
$user = null;
$userLoggedIn = false;
$userEmail = "";

// pertinent functions
function initGlobals() {
	global $user, $userLoggedIn, $userEmail;

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
	$userEmail = $userLoggedIn?$user->email:Session::getAuthEmail();
	
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