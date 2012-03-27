<?php
include("template/userFacingBase.php");

/*****************************************************************
						DO PROCESSING
*****************************************************************/

// check to see if user submitted sign-up form
// HACK: this should be moved into the auth server
if(isset($_POST["submit"])) {
	// pull the sign-up parameters
	$pictureURL = $_POST["pictureURL"];
	
	// try the sign-up
	if(!$appAuth->doSignup(array("pictureURL"=>$pictureURL))) {
		// if it fails this is likely because they've already signed up. report it.
		StatusMessages::addMessage("User already signed up.", StatusMessage::ENUM_BAD);
	}
	else {
		// report success in status
		StatusMessages::addMessage("Signed up.",StatusMessage::ENUM_GOOD);
	}
	redirect(PAGE_INDEX);
}


/*****************************************************************
						RENDER PAGE
*****************************************************************/

$view->printSignupPage();
?>
