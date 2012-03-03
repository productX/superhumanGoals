<?php
require_once("auth/client/include/intranetAuthClient.php");

$intranetAuth = createIntranetAuth();
$intranetAuth->enforceLogin();
?>