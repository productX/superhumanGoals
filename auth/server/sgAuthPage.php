<?php
require_once("include/sgAuthServer.php");

$authServer = createSGAuthServer();
$returnIncludePath = $authServer->doAuthPage(/* params read later via GET */);
include $returnIncludePath;
?>