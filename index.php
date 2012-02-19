<?php
require_once("include/auth.php");
authorize();

require_once("include/header.php");
printHeader("Main page");
?>

<ul>
	<li><a href="joke.php">Joke</a></li>
	<li><a href="user.php">User</a></li>
</ul>

<?php
printFooter();
?>