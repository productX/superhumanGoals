<?php
// include everything we need to render pages
include("userFacingBase.php");

// create auth
initAuth();
// enforce login
$userID = $appAuth->enforceLogin();
// create user
$user = User::getObjFromUserID($userID);
$user->trackVisit();
?>