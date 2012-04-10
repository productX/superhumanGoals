<?php
// include everything we need to render pages
include("userFacingBase.php");

//var_dump($_SESSION);
//die();

// create auth
initAuth();
PerformanceMeter::addTimestamp("Auth init done");
// enforce login
$userID = $appAuth->enforceLogin();
PerformanceMeter::addTimestamp("Enforce login done");
// create user
$user = User::getObjFromUserID($userID);

$user->trackVisit();
PerformanceMeter::addTimestamp("User create & track visit done");
?>