<?php
/*
CRON INSTRUCTIONS:
- run every 5 minutes
*/

include("../template/baseIncludes.php");

$rs = $db->doQuery("SELECT user_id FROM users WHERE (".Date::Now()->toUT()."-UNIX_TIMESTAMP(last_daily_entry))>(60*30) AND daily_entry_story_posted=FALSE");
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$userID = $obj->user_id;
	$touchedGoalIDs = array();
	$today = Date::now()->toDay();
	$rs2 = $db->doQuery("SELECT event_goal_id FROM stories WHERE user_id=%s AND is_public=TRUE AND type='event' AND entered_at_day=%s", $userID, $today);
	$obj2 = null;
	while($obj2 = mysql_fetch_object($rs2)) {
		$touchedGoalIDs[$obj2->event_goal_id]=true;
	}
	$rs2 = $db->doQuery("SELECT strategies.goal_id FROM strategies_log INNER JOIN strategies ON strategies.strategy_id=strategies_log.strategy_id WHERE strategies_log.user_id=%s AND strategies_log.entered_at_day=%s", $userID, $today);
	$obj2 = null;
	while($obj2 = mysql_fetch_object($rs2)) {
		$touchedGoalIDs[$obj2->goal_id]=true;
	}
	$goals = array_keys($touchedGoalIDs);
	DailyscoreStory::createNew($userID, true, $goals);
	
	$db->doQuery("UPDATE users SET daily_entry_story_posted=TRUE WHERE id=%s", $userID);
}
?>