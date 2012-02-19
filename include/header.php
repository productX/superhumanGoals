<?php
require_once(dirname(__FILE__)."/../../common/includes/functions.php"); 
require_once("constants.php");

function printHeader($pageTitle) {
?>
<html>
	<head>
		<title><?php echo $pageTitle; ?></title>
	</head>
	<body>
		<font size="6"><?php echo $pageTitle; ?></font><br/>
<?php
}

function printFooter() {
?>
		<br/><br/>
		<a href="index.php">BACK to main page</a> | <a href="../auth/logout.php">Log out</a>
	</body>
</html>
<?php
}

?>