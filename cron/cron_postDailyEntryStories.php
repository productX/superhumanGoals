<?php
/*
CRON INSTRUCTIONS:
- run every 5 minutes
*/

include("../template/baseIncludes.php");

$rs = $db->doQuery("SELECT id FROM users WHERE (".Date::Now()->toUT()."-UNIX_TIMESTAMP(last_daily_entry))>(60*30) AND daily_entry_story_posted=FALSE");
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$userID = $obj->id;
	$touchedGoalIDs = array();
	$today = Date::now()->toDay();
	$rs2 = $db->doQuery("SELECT event_goal_id FROM stories WHERE user_id=%s AND is_public=TRUE AND type='event' AND entered_at_day=%s", $userID, $today);
	$obj2 = null;
	while($obj2 = mysql_fetch_object($rs2)) {
		$touchedGoalIDs[$obj2->event_goal_id]=true;
	}
	$rs2 = $db->doQuery("SELECT dailytests.goal_id FROM dailytests_status INNER JOIN dailytests ON dailytests.id=dailytests_status.dailytest_id WHERE dailytests_status.user_id=%s AND dailytests_status.entered_at_day=%s", $userID, $today);
	$obj2 = null;
	while($obj2 = mysql_fetch_object($rs2)) {
		$touchedGoalIDs[$obj2->goal_id]=true;
	}
	$goals = array_keys($touchedGoalIDs);
	DailyscoreStory::createNew($userID, true, $goals);
	
	$db->doQuery("UPDATE users SET daily_entry_story_posted=TRUE WHERE id=%s", $userID);
}
?>