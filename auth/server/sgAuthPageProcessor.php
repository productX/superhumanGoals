<?php
require_once("include/sgAuthServer.php");

$authServer = createSGAuthServer();
$returnIncludePath = $authServer->doAuthPageProcessor(/* params read later via GET */);
include $returnIncludePath;
?>