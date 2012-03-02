<?php
include("template/userFacingBase.php");

$redirectTo = "";
if($appAuth->isLoggedIn()) {
	$redirectTo = PAGE_ACTIVITY;
}
else {
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
