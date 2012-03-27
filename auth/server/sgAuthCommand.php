<?php
require_once("include/sgAuthServer.php");

$authServer = createSGAuthServer();
$authServer->doCommand(/* params read later via GET */);
?>