<?php
include("template/userFacingForceLogin.php");

$appAuth->doLogout();
redirect(PAGE_SIGNUP);
?>
