<?php
/*
USAGE INSTRUCTIONS:

users
- get all: http://www.superhumangoals.com/api/users
- get by id: http://www.superhumangoals.com/api/users/#

stories
- get by user: http://www.superhumangoals.com/api/users/#/stories
- get by goal: http://www.superhumangoals.com/api/goals/#/stories
- get by ID: http://www.superhumangoals.com/api/stories/#

goals
- get by ID: http://www.superhumangoals.com/api/goals/#
- get by user: http://www.superhumangoals.com/api/users/#/goals

goals_status
- get by user & goal: http://www.superhumangoals.com/api/goals_status/goal#/user#

dailytests
- get by goal: http://www.superhumangoals.com/api/goals/#/dailytests
- get by id: http://www.superhumangoals.com/api/dailytests/#

dailytests_status
- get by dailytest, user, and day: http://www.superhumangoals.com/api/dailytests_status/dailytest#/user#/day#
- set by dailytest, user, and day: http://www.superhumangoals.com/api/dailytests_status/dailytest#/user#/day#
	- use POST to set
	- send JSON with 1 variable, the integer result you want to set
- notes
	- format for day: YYYY-MM-DD


NOTES - DATA ACCESS STRUCTURE:

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

// called from 404.php, this function checks the URL to see if it's an API call & if so completes the API call
function tryURLForAPICall() {
	global $db;
	
	// pull the URL
	$url = $_SERVER['REQUEST_URI'];
	
	// pull it apart
	$urlParts = explode("/",$url);
	array_shift($urlParts); // shift off the empty space before the "/"
	
	// if there's something there
	if(count($urlParts)>1) {
		// check for URL processors, right now just the API URL processor that we're in right now
		// HACK: really this check & the code above should happen at a higher level. if we're inside this function we should only be handling API calls
		$foundProcessor = false;
		// loop through until a processor is found or we run out of URL parts
		while(!$foundProcessor && count($urlParts)) {
			$processor = array_shift($urlParts);
			// is it a recognized processor?
			switch($processor) {
				case 'api':
					$foundProcessor=true;
					break;
				default:
					break;
			}
		}
		// if we found a processor & it's the API processor
		if($foundProcessor && $processor=="api") {
			// the next URL piece would be an API top-level category, eg "user"
			$apiCategory = array_shift($urlParts);
			$returnData = null;
			
			// switch on the API category
			switch($apiCategory) {
				// all API calls pertaining to dailytests
				case 'dailytests':
					// the base in this category, eg "/dailytests"
					if(!count($urlParts)) {
						// don't let them get a full list of all tests
						//$returnData = $db->doQueryArray("SELECT id FROM dailytests");
					}
					// do further processing as there are more pieces
					else {
						if(count($urlParts)==1) {
							// if there's a 2nd piece we assume it's a dailytest ID
							$dailytestID = array_shift($urlParts); // shift off ID & save
							$rs = $db->doQuery("SELECT * FROM dailytests WHERE id = %s", $dailytestID);
							
							// return this dailytest's data. there's currently no way to set ie change a dailytest (no POST handler)
							$returnData = mysql_fetch_assoc($rs);
						}
					}
					break;
				case 'dailytests_status':
					// the base in this category, eg "/dailytests_status"
					if(!count($urlParts)) {
						// don't let them get a full list of all tests
						//$returnData = $db->doQueryArray("SELECT id FROM dailytests");
					}
					// do further processing as there are more pieces
					else {
						// there must be exactly 3 more pieces, as there are 3 keys to define a dailytest_status
						if(count($urlParts)==3) {
							$dailytestID = array_shift($urlParts); // shift off & save
							$userID = array_shift($urlParts); // shift off & save
							$day = array_shift($urlParts); // shift off & save
							// is this a GET (to read data) or a POST (to update data)
							switch($_SERVER['REDIRECT_REQUEST_METHOD']) {
								case "GET":
									// we are reading, so return this dailytest_status's data
									$rs = $db->doQuery("SELECT * FROM dailytests_status WHERE dailytest_id = %s AND user_id=%s AND entered_at_day=%s", $dailytestID, $userID, $day);
									$returnData = mysql_fetch_assoc($rs);
									break;
								case "POST":
									// this would set the dailytest_status's data
									// HACK: not finished yet
									//$rs = $db->doQuery("SELECT * FROM dailytests_status WHERE dailytest_id = %s AND user_id=%s AND entered_at_day=%s", $dailytestID, $userID, $day);
									$returnData = serialize($_SERVER);
									break;
								default:
									break;
							}
						}
					}
					break;
				case 'goals':
					// the base in this category, eg "/goals"
					if(!count($urlParts)) {
						// allow them to grab a full list of goals
						$returnData = $db->doQueryArray("SELECT id FROM goals");
					}
					// do further processing if there are more pieces
					else {
						// grab the goal's ID & proceed
						$goalID = array_shift($urlParts);
						// if this is it, return everything about this goal
						if(!count($urlParts)) {
							$rs = $db->doQuery("SELECT * FROM goals WHERE id = %s", $goalID);
							$returnData = mysql_fetch_assoc($rs);
						}
						else {
							// if the request is for an attribute of this goal, process that
							$attribute = array_shift($urlParts); // shift off attribute & save
							switch($attribute) {
								case 'stories':
									// return all stories associated with this goal
									$returnData = $db->doQueryArray("SELECT id FROM stories WHERE goal_id=%s", $goalID);
									break;
								case 'dailytests':
									// return all dailytests associated with this goal
									$returnData = $db->doQueryArray("SELECT id FROM dailytests WHERE goal_id=%s", $goalID);
									break;
								default:
									break;
							}
						}
					}
					break;
				case 'goals_status':
					// the base in this category, eg "goals_status"
					if(!count($urlParts)) {
						// don't let them get a full list of all goals_status
						//$returnData = $db->doQueryArray("SELECT id FROM dailytests");
					}
					// do further processing if there are more pieces
					else {
						// there must be 2 more pieces to specify which goals_status to grab
						if(count($urlParts)==2) {
							$goalID = array_shift($urlParts); // shift off & save
							$userID = array_shift($urlParts); // shift off & save
							
							// pull the goal_status & return
							$rs = $db->doQuery("SELECT * FROM goals_status WHERE goal_id = %s AND user_id=%s", $goalID, $userID);
							$returnData = mysql_fetch_assoc($rs);
						}
					}
					break;
				case 'stories':
					// the base in this category, eg "/stories"
					if(!count($urlParts)) {
						// return a list of all recent stories
						// HACK: should ORDER BY for recency
						$returnData = $db->doQueryArray("SELECT id FROM stories LIMIT 100");
					}
					// do further processing if there are more pieces
					else {
						// grab the story ID
						if(count($urlParts)==1) {
							$storyID = array_shift($urlParts);
							// return story data
							$rs = $db->doQuery("SELECT * FROM stories WHERE id = %s", $storyID);
							$returnData = mysql_fetch_assoc($rs);
						}
					}
					break;
				case 'users':
					// base in this category, eg "/users"
					if(!count($urlParts)) {
						// return a list of all users (eventually this should be turned off when big enough)
						$returnData = $db->doQueryArray("SELECT id FROM users");
					}
					// do further processing if there are more pieces
					else {
						$userID = array_shift($urlParts); // shift off ID & save
						// if we just have a user ID, return the user data
						if(!count($urlParts)) {
							$rs = $db->doQuery("SELECT * FROM users WHERE id = %s", $userID);
							$returnData = mysql_fetch_assoc($rs);
						}
						// if we have more, check for the attribute
						else {
							$attribute = array_shift($urlParts); // shift off attribute & save
							switch($attribute) {
								case 'stories':
									// return all stories associated with this user
									$returnData = $db->doQueryArray("SELECT id FROM stories WHERE user_id=%s", $userID);
									break;
								case 'goals':
									// return all goals associated with this user
									$returnData = $db->doQueryArray("SELECT goal_id FROM goals_status WHERE user_id=%s AND is_active = 1", $userID);
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
			
			// end script & return the data in JSON format
			die(json_encode($returnData));
		}
	}
	
	// if we get here & didn't return JSON, the API call was invalid. exiting this handler will fall through to the 404 error on 404 page.
}
?>