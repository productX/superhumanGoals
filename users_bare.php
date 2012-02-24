<?php
include("template/userFacingForceLogin.php");

// RENDER PAGE
require_once("include/header.php");
printHeader("Users page");

User::printListAll();

printFooter();
?>