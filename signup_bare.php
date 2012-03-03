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
require_once("include/chrome.php");
printHeader(NAVNAME_NONE, array(), true);
?>

			<div class="signup-box">
				<h2>Be Amazing.</h2>
				<a href="#" class="signup-btn">Sign up &raquo;</a>
				<div class="upload-box">
					<p>Signed in as: <strong><?php echo $intranetAuth->getUserEmail(); ?></strong></p>
					<p>Profile pic URL (50x50):</p>
					<form action="<?php echo PAGE_SIGNUP; ?>" method="post">
						<!--<input type="file" name="file" id="file" value="" />-->
						<input type="text" name="pictureURL" />
						<input name="submit" type="submit" value="Sign up &raquo;" class="submit-btn" />
						<div class="cl">&nbsp;</div>
					</form>
				</div>
			</div>

<?php
printFooter(true);
?>
