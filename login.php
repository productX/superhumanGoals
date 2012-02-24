<?php
include("template/userFacingBase.php");

$redirectTo = "";
if($userLoggedIn) {
	$redirectTo = PAGE_ACTIVITY;
}
else {
	$success = User::login();
	if($success) {
		$redirectTo = PAGE_ACTIVITY;
	}
	else {
		$redirectTo = PAGE_SIGNUP;
	}
}
redirect($redirectTo);
?>
