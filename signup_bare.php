<?php
include("template/userFacingBase.php");

// DO PROCESSING
if(isset($_POST["submit"])) {
	$pictureURL = $_POST["pictureURL"];
	
	User::doSignup($pictureURL);
	StatusMessages::addMessage("Signed up.",StatusMessage::ENUM_GOOD);
	redirect(PAGE_INDEX);
}

// RENDER PAGE
require_once("include/chrome.php");
printHeader("Signup page");
?>

Signed in as: <?php echo $userEmail; ?><br/>
<form method="post" action="<?php echo PAGE_SIGNUP; ?>">
Picture URL: <input type="text" name="pictureURL" /><br/>
<input type="submit" name="submit" value="Sign up ->" />
</form><br/>
<a href="<?php echo PAGE_LOGIN; ?>">Log in</a><br/>

<?php
printFooter();
?>