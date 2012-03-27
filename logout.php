<?php
include("template/userFacingForceLogin.php");

// log out of the app, not out of any auth servers
// HACK: this should log you out of the app & all auth servers
$appAuth->doLogout();
redirect(PAGE_SIGNUP);
?>
