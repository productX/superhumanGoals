<?php
include("template/userFacingForceLogin.php");

// RENDER PAGE
require_once("include/chrome.php");
printHeader(NAVNAME_ACTIVITY, array(new ChromeTitleElementHeader("Activity")));

$rs = Database::doQuery("SELECT * FROM stories WHERE is_public=TRUE ORDER BY entered_at DESC LIMIT 100");
Story::printListForRS($rs);

printFooter();
?>