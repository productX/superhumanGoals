<?php
require_once(dirname(__FILE__)."/../include/intranetAuthClient.php");

$intranetAuth = createIntranetAuth();
$intranetAuth->enforceLogin();
?>