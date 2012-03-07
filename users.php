<?php
include("template/userFacingForceLogin.php");

// RENDER PAGE
require_once("include/chrome.php");
printHeader(NAVNAME_USERS, array(new ChromeTitleElementHeader("All People")));

User::printListAll();

printFooter();
?>