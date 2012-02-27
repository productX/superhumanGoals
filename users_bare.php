<?php
include("template/userFacingForceLogin.php");

// RENDER PAGE
require_once("include/chrome.php");
printHeader("Users page");

User::printListAll();

printFooter();
?>