<?php
include("userFacingBase.php");

// ensure we're logged in, then track visit
$appAuth->enforceLogin(PAGE_SIGNUP);
assert(!is_null($user));
$user->trackVisit();
?>