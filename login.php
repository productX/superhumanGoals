<?php
// include everything we need to render a page + enforce login with auth servers
include("template/userFacingBase.php");

$redirectTo = "";
// redirect to the main page if logged in
if($appAuth->isLoggedIn()) {
	$redirectTo = PAGE_ACTIVITY;
}
// if not logged in
else {
	// try to "auto login", eg look for anything in session that could log us into the app
	// HACK: eventually this should be a pass-through login that just verifies that we're logged in to all requisite auth servers
	$success = $appAuth->tryAutoLogin();
	if($success) {
		$redirectTo = PAGE_ACTIVITY;
	}
	else {
		$redirectTo = PAGE_SIGNUP;
	}
}
redirect($redirectTo);
?>
