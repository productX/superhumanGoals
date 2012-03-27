<?php
// enforce login with auth servers
// HACK: this should be done by AppAuth
include("enforceLogin.php");

// include basics
include("baseIncludes.php");

// initialize the $user obj
initUser();
// initialize the $view obj according to what view user is using (currently web or mobile)
initView();
?>