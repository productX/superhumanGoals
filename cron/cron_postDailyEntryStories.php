<?php
/*
CRON

DESCRIPTION:
- post "daily entry made" stories for all users that have completed an un-posted daily entry & not made further adjustmentsin the last 30 mins

SETUP INSTRUCTIONS:
- run every 5 minutes
*/

include("../template/baseIncludes.php");

// pull id's for all users who have made an un-posted daily entry at least 30 minutes ago (this allows time for the entry to settle if they want to make a few changes)
$rs = $db->doQuery("SELECT id FROM users WHERE (".Date::Now()->toUT()."-UNIX_TIMESTAMP(last_daily_entry))>(60*30) AND daily_entry_story_posted=FALSE");

// go through all of these users
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$userID = $obj->id;
	$touchedGoalIDs = array();
	$today = Date::now()->toDay();
	
	// pull a list of all goals this user had events for
	$rs2 = $db->doQuery("SELECT event_goal_id FROM stories WHERE user_id=%s AND is_public=TRUE AND type='event' AND entered_at_day=%s", $userID, $today);
	$obj2 = null;
	while($obj2 = mysql_fetch_object($rs2)) {
		$touchedGoalIDs[$obj2->event_goal_id]=true;
	}
	
	// pull a list of all goals this user completed adherence tests for
	$rs2 = $db->doQuery("SELECT dailytests.goal_id FROM dailytests_status INNER JOIN dailytests ON dailytests.id=dailytests_status.dailytest_id WHERE dailytests_status.user_id=%s AND dailytests_status.entered_at_day=%s", $userID, $today);
	$obj2 = null;
	while($obj2 = mysql_fetch_object($rs2)) {
		$touchedGoalIDs[$obj2->goal_id]=true;
	}
	
	// create a new dailyscore story using the de-duplicated list of touched goals
	$goals = array_keys($touchedGoalIDs);
	DailyscoreStory::createNew($userID, true, $goals);
	
	// mark that we've posted the daily entry so it doesn't re-post for this user
	$db->doQuery("UPDATE users SET daily_entry_story_posted=TRUE WHERE id=%s", $userID);
}
?>