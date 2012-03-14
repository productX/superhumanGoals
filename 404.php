<?php
include("template/baseIncludes.php");

/*
DATA ACCESS STRUCTURE

dailytests_status
- key: id, user, day

dailytests
- key: id
- by: goal

goals_status
- key: user, goal

goals
- key: id
- by: user

stories
- key: id
- by: user
- by: goal

users
- key: id
*/

$url = $_SERVER['REQUEST_URI'];
$urlParts = explode("/",$url);

array_shift($urlParts); // shift off the empty space before the "/"
if(count($urlParts)>1) {
	// check for processors
	$foundProcessor = false;
	while(!$foundProcessor && count($urlParts)) {
		$processor = array_shift($urlParts);
		switch($processor) {
			case 'api':
				$foundProcessor=true;
				break;
			default:
				break;
		}
	}
	if($foundProcessor && $processor=="api") {
		$apiCategory = array_shift($urlParts);
		$returnData = null;
		switch($apiCategory) {
			case 'dailytests':
				if(!count($urlParts)) {
					// don't let them get a full list of all tests
					//$returnData = $db->doQueryArray("SELECT id FROM dailytests");
				}
				else {
					if(count($urlParts)==1) {
						$dailytestID = array_shift($urlParts); // shift off ID & save
						$rs = $db->doQuery("SELECT * FROM dailytests WHERE id = %s", $dailytestID);
						$returnData = mysql_fetch_assoc($rs);
					}
				}
				break;
			case 'dailytests_status':
				if(!count($urlParts)) {
					// don't let them get a full list of all tests
					//$returnData = $db->doQueryArray("SELECT id FROM dailytests");
				}
				else {
					if(count($urlParts)==3) {
						$dailytestID = array_shift($urlParts); // shift off & save
						$userID = array_shift($urlParts); // shift off & save
						$day = array_shift($urlParts); // shift off & save
						$rs = $db->doQuery("SELECT * FROM dailytests_status WHERE dailytest_id = %s AND user_id=%s AND entered_at_day=%s", $dailytestID, $userID, $day);
						$returnData = mysql_fetch_assoc($rs);
					}
				}
				break;
			case 'goals':
				if(!count($urlParts)) {
					$returnData = $db->doQueryArray("SELECT id FROM goals");
				}
				else {
					$goalID = array_shift($urlParts); // shift off ID & save
					if(!count($urlParts)) {
						$rs = $db->doQuery("SELECT * FROM goals WHERE id = %s", $goalID);
						$returnData = mysql_fetch_assoc($rs);
					}
					else {
						$attribute = array_shift($urlParts); // shift off attribute & save
						switch($attribute) {
							case 'stories':
								$returnData = $db->doQueryArray("SELECT id FROM stories WHERE goal_id=%s", $goalID);
								break;
							case 'dailytests':
								$returnData = $db->doQueryArray("SELECT id FROM dailytests WHERE goal_id=%s", $goalID);
								break;
							default:
								break;
						}
					}
				}
				break;
			case 'goals_status':
				if(!count($urlParts)) {
					// don't let them get a full list of all goals_status
					//$returnData = $db->doQueryArray("SELECT id FROM dailytests");
				}
				else {
					if(count($urlParts)==2) {
						$goalID = array_shift($urlParts); // shift off & save
						$userID = array_shift($urlParts); // shift off & save
						$rs = $db->doQuery("SELECT * FROM goals_status WHERE goal_id = %s AND user_id=%s", $goalID, $userID);
						$returnData = mysql_fetch_assoc($rs);
					}
				}
				break;
			case 'stories':
				if(!count($urlParts)) {
					$returnData = $db->doQueryArray("SELECT id FROM stories LIMIT 100");
				}
				else {
					if(count($urlParts)==1) {
						$storyID = array_shift($urlParts); // shift off & save
						$rs = $db->doQuery("SELECT * FROM stories WHERE id = %s", $storyID);
						$returnData = mysql_fetch_assoc($rs);
					}
				}
				break;
			case 'users':
				if(!count($urlParts)) {
					$returnData = $db->doQueryArray("SELECT id FROM users");
				}
				else {
					$userID = array_shift($urlParts); // shift off ID & save
					if(!count($urlParts)) {
						$rs = $db->doQuery("SELECT * FROM users WHERE id = %s", $userID);
						$returnData = mysql_fetch_assoc($rs);
					}
					else {
						$attribute = array_shift($urlParts); // shift off attribute & save
						switch($attribute) {
							case 'stories':
								$returnData = $db->doQueryArray("SELECT id FROM stories WHERE user_id=%s", $userID);
								break;
							case 'goals':
								$returnData = $db->doQueryArray("SELECT goal_id FROM goals_status WHERE user_id=%s", $userID);
								break;
							default:
								break;
						}
					}
				}
				break;
			default:
				break;
		}
		die(json_encode($returnData));
	}
}
die("404 not found!!");
?>