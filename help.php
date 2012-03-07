<?php
include("template/userFacingForceLogin.php");

// RENDER PAGE
require_once("include/chrome.php");
printHeader(NAVNAME_NONE, array(new ChromeTitleElementHeader("Help")));
?>

<div style="padding:10px 0 0 10px;">
Is it really that hard to figure out? :P
</div>

<?php
printFooter();
?>