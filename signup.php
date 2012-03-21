<?php
include("template/userFacingBase.php");

// DO PROCESSING
if(isset($_POST["submit"])) {
	$pictureURL = $_POST["pictureURL"];
	
	if(!$appAuth->doSignup(array("pictureURL"=>$pictureURL))) {
		StatusMessages::addMessage("User already signed up.", StatusMessage::ENUM_BAD);
	}
	StatusMessages::addMessage("Signed up.",StatusMessage::ENUM_GOOD);
	redirect(PAGE_INDEX);
}


// RENDER PAGE
$view->printSignupPage();
?>
