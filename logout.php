<?php
include("template/userFacingForceLogin.php");

User::logout();
redirect(PAGE_SIGNUP);
?>
