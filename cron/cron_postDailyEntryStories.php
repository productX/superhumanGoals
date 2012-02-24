<?php
/*
CRON INSTRUCTIONS:
- run every 5 minutes
*/

include("template/baseIncludes.php");

$rs = Database::doQuery("SELECT id FROM users WHERE (NOW()-UNIX_TIMESTAMP(last_daily_entry))>(60*30) AND daily_entry_story_posted=FALSE");
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$userID = $obj->id;
	$touchedGoalIDs = array();
	$today = Datetime::now()->toDay();
	$rs2 = Database::doQuery("SELECT event_goal_id FROM stories WHERE user_id=%d AND is_public=TRUE AND type='event' AND entered_at_day=%s", $userID, $today);
	$obj2 = null;
	while($obj2 = mysql_fetch_object($rs2)) {
		$touchedGoalIDs[$obj2->event_goal_id]=true;
	}
	$rs2 = Database::doQuery("SELECT dailytests.goal_id FROM dailytests_status INNER JOIN dailytests ON dailytests.id=dailytests_status.dailytest_id WHERE dailytests_status.user_id=%d AND dailytests_status.entered_at_day=%s", $userID, $today);
	$obj2 = null;
	while($obj2 = mysql_fetch_object($rs2)) {
		$touchedGoalIDs[$obj2->goal_id]=true;
	}
	$goals = array_keys($touchedGoalIDs);
	DailyscoreStory::createNew($userID, true, $goals);
	
	Database::doQuery("UPDATE users SET daily_entry_story_posted=TRUE WHERE id=%d", $userID);
}
?>