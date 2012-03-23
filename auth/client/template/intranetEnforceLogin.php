<?php
require_once(dirname(__FILE__)."/../include/intranetAuthClient.php");

// create the intranet auth client, then use it to check with the auth server that we're logged in. a token in the session will have to match up to an entry on the auth server to pass through.
$intranetAuth = createIntranetAuth();
$intranetAuth->enforceLogin();
?>