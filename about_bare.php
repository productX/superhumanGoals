<?php
include("template/userFacingForceLogin.php");

// RENDER PAGE
require_once("include/chrome.php");
printHeader(NAVNAME_NONE, array(new ChromeTitleElementHeader("About")));
?>

<div style="padding:10px 0 0 10px;">
By winners, for winners.
</div>

<?php
printFooter();
?>