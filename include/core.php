<?php

class GPC {

	// private
	
	// protected
	
	// public
	public static function strToInt($str) {
		return intval($str);
	}
	public static function strToFloat($str) {
		return floatval($str);
	}
	public static function strToLetterGrade($str) {
		$str = strtoupper(substr($str,0,1));
		if(!in_array($str,array('A','B','C','D','F'))) {
			$str = 'F';
		}
		return $str;
	}
};

const CLASSNAME_DATETIME='Datetime';
class Datetime {

	// private
	private $ut;
	private function __construct($ut) {
		$this->ut = $ut;
	}
	
	// protected
	
	// public
	public function toDay() {
		return date("Y-m-d", $this->ut);
	}
	public function toSQLStr() {
		return date("m/d/y g:i A", $this->ut);
	}
	public function toUT() {
		return $this->ut;
	}
	public function diffDays($otherDay) {
		return ($otherDay->ut-$this->ut)/(60*60*24);
	}
	public function shiftDays($numDays) {
		return new Datetime($this->ut+$numDays*60*60*24);
	}
	public function timeSince() {
		return timeSince($this->ut);
	}
	public static function fromDay($day) {
		return new Datetime(strtotime($day));
	}
	public static function fromSQLStr($str) {
		return new Datetime(strtotime($str));
	}
	public static function fromUT($ut) {
		return new Datetime($ut);
	}
	public static function now() {
		return new Datetime(time());
	}

};

class Database {

	// private
	const SERVER = 'localhost';
	const USERNAME = 'root';
	const PASSWORD = '';
	const NAME = 'superhuman_goals';
	private static $initialized = false;
	private static $conn;
	private static $db;
	private function __construct() {} // static-only class
	
	// protected
	
	// public
	public static function init() {
		Database::$conn = @mysql_connect(Database::SERVER, Database::USERNAME, Database::PASSWORD) or die(mysql_error());
		Database::$db = @mysql_select_db(Database::NAME,Database::$conn)or die(mysql_error());
		Database::$initialized = true;
	}
	public static function doQueryBase($args) {
		assert(Database::$initialized && isset(Database::$db) && isset(Database::$conn) && (count($args)>0));
		$printArgs = array();
		for($i=1; $i<count($args) ++$i) {
			$val = null;
			$arg = $args[0];
			switch(gettype($arg)) {
				case 'boolean':
					$val = $arg?"TRUE":"FALSE";
					break;
				case 'integer':
				case 'double':
					$val = strval($arg);
					break;
				case 'string':
					$val = "'".mysql_real_escape_string($val)."'";
					break;
				case 'object':
					$className = get_class($arg);
					if($className == CLASSNAME_DATETIME) {
						$val = "'".$arg->toSQLStr()."'";
					}
					break;
				default:
					break;
			}
			$printArgs[] = $val;
		}
		$sql = vsprintf($arg[0],$printArgs);
		
		$rs = @mysql_query($sql,Database::$conn) or die(mysql_error());
		return $rs;
	}
	public static function doQuery() {
		$args = func_get_args();
		return Database::doQueryBase($args);
	}
	public static function doQueryRFR() { // do query & Return First Row or null
		$args = func_get_args();
		$rs = Database::doQueryBase($args);
		$obj = null;
		if(mysql_num_rows($rs)>0) {
			$obj = mysql_fetch_object($rs);
		}
		return $obj;
	}
	public static function doQueryOne() { // do query & Return First Row or null
		$args = func_get_args();
		$rs = Database::doQueryBase($args);
		$result = null;
		if(mysql_num_rows($rs)>0) {
			$result = mysql_result($rs,0);
		}
		return $result;
	}

};


class User {

	// private
	const ENUM_VISITS_DAILY = 1;
	const ENUM_VISITS_EVERYFEWDAYS = 2;
	const ENUM_VISITS_WEEKLY = 3;
	const ENUM_VISITS_EVERYFEWWKS = 4;
	const ENUM_VISITS_MONTHLY = 5;
	const NUM_VISITS_TO_TRACK = 20;
	private $id, $authID, $pictureURL;
	private $firstName, $lastName, $password, $authGroups, $email, $verified, $lastLogin, $visitHistory;
	private static function visitHistoryToStr($visitHistory) {
		return serialize($visitHistory);
	}
	private static function visitHistoryFromStr($blob) {
		return deserialize($blob);
	}
	private static function printUserListBase($userIDList) {
		const FIRST_CHAR_CODE=65;
		const LAST_CHAR_CODE=90;

		$userListNonletters = array();
		$lnListNonletters = array();
		$userListLetters = array();
		$lnListLetters = array();
		while($userIDList as $lUserID) {
			$lUser = User::getObjFromUserID($lUserID);
			assert(!is_null($lUser));
			$charCode = ord(strtoupper($lUser->lastName));
			if(($charCode >= FIRST_CHAR_CODE) && ($charCode <= LAST_CHAR_CODE)) {
				$userListLetters[] = $lUser;
				$lnListLetters[] = $lUser->lastName;
			}
			else {
				$userListNonletters[] = $lUser;
				$lnListNonletters[] = $lUser->lastName;
			}
		}
		array_multisort($lnListNonletters, $userListNonletters);
		array_multisort($lnListLetters, $userListLetters);

		if(count($userListNonletters)>0) {
			echo "<b>?</b><br/><hr/>";
			foreach($userListNonletters as $lUser) {
				User::printCard($lUser);
			}
		}
		$lastLetter = "?";
		foreach($userListLetters as $lUser) {
			$currentLetter = strtoupper(substr($lUser->lastName, 0, 1));
			if($currentLetter != $lastLetter) {
				$lastLetter = $currentLetter;
				echo "<b>$currentLetter</b><br/><hr/>";
			}
			User::printCard($lUser);
		}
	}
	private function save() {
		// currently cannot update intranet auth DB

		// update SG DB
		$visitHistoryStr = User::visitHistoryToStr($this->visitHistory);
		Database::doQuery("UPDATE users SET	picture_url=%s, _
											visit_history=%s, _
											last_daily_entry=%s, _
											daily_entry_story_posted=%s _
											WHERE id=%d",
											$this->pictureURL, $visitHistoryStr, $this->lastDailyEntry, $this->dailyEntryStoryPosted, $this->id);
	}
	
	// protected
	
	// public
	public static function doSignup($pictureURL) {
		$authID = Session::getAuthUserID();
		$authObj = Database::doQueryRFR("SELECT * FROM auth_users WHERE id=%d", $authID);
		assert(!is_null($authObj));
		$visitHistoryStr = User::visitHistoryToStr(array(Datetime::now()));
		Database::doQuery("INSERT INTO users (auth_id, picture_url, visit_history, full_name) VALUES (%d, %s, %s, %s)", $authID, $pictureURL, $visitHistoryStr, "$authObj->firstname $authObj->lastname");
		$newID = mysql_insert_id();
		setLoggedInUserID($newID);
		$success = true;
		return $success;
	}
	public static function getObjFromUserID($userID) {
		$user = null;
		$sgObj = Database::doQueryRFR("SELECT * FROM users WHERE id=%d", $userID);
		if(!is_null($sgObj)) {
			$authObj = Database::doQueryRFR("SELECT * FROM auth_users WHERE id=%d", $sgObj->auth_id);
			assert(!is_null($authObj));
			$user = new User($authObj, $sgObj);
		}
		return $user;
	}
	public static function login() {
		$success = false;
		$authUserID = Session::getAuthUserID();
		$sgObj = Database::doQueryRFR("SELECT * FROM users WHERE auth_id=%d", $authUserID);
		if(!is_null($sgObj)) {
			$success = true;
			$userID = $sgObj->id;
			Session::setLoggedInUserID($userID);
		}
		return $success;
	}
	public static function getLoggedInUser() {
		$user = null;
		$loggedInUserID = Session::getLoggedInUserID();
		if(!is_null($loggedInUserID)) {
			$user = User::getObjFromUserID($loggedInUserID);
			assert(!is_null($user));
			$authUserID = Session::getAuthUserID();
			assert($authUserID == $user->authID);
		}
		return $user;
	}
	public static function printListAll() {
		$rs = Database::doQuery("SELECT id FROM users");
		$userIDList = array();
		while($obj = mysql_fetch_object($rs)) {
			$userIDList[] = $obj->id;
		}
		User::printUserListBase($userIDList);
	}
	public static function printListByGoal($goalID) {
		$rs = Database::doQuery("SELECT user_id FROM goal_status WHERE goal_id=%d", $goalID);
		$userIDList = array();
		while($obj = mysql_fetch_object($rs)) {
			$userIDList[] = $obj->user_id;
		}
		User::printUserListBase($userIDList);
	}
	public static function printCard($user) {
		assert(!is_null($user));
		echo "<hr/>";
		$profLink = $user->getPagePath();
		echo "<a href='$profLink'><img src='".htmlspecialchars($user->pictureURL)."' /></a><br/>";
		echo "<a href='$profLink'>$user->firstName $user->lastName</a><br/>";
		$rs = Database::doQuery("SELECT goal_id FROM goals WHERE user_id=%d", $user->id);
		$numGoals = mysql_num_rows($rs);
		echo "$numGoals goals<br/>";
		$visitFrequency = $user->getVisitFrequency();
		$visitFreqText = "";
		switch($visitFrequency) {
			case User::ENUM_VISITS_DAILY:
				$visitFreqText = "Visits daily";
				break;
			case User::ENUM_VISITS_EVERYFEWDAYS:
				$visitFreqText = "Visits most days";
				break;
			case User::ENUM_VISITS_WEEKLY:
				$visitFreqText = "Visits weekly";
				break;
			case User::ENUM_VISITS_EVERYFEWWEEKS:
				$visitFreqText = "Visits most weeks";
				break;
			default:
			case User::ENUM_VISITS_MONTHLY:
				$visitFreqText = "Visits monthly";
				break;
		}
		echo "$visitFreqText<br/>";
		echo "<hr/>";
	}

	public function adoptGoal($goalID) {
		GoalStatus::userAdoptGoal($this->id, $goalID);
	}
	public function logout() {
		Session::clearLoggedInUserID();
	}
	public function updateLastDailyEntry() {
		$this->lastDailyEntry = Datetime::now();
		$this->dailyEntryStoryPosted = false;
		$this->save();
	}
	public function hasMadeDailyEntry() {
		return $this->lastDailyEntry->diffDays(Datetime::now())==0;
	}
	public function trackVisit() {
		$needUpdate = true;
		if(!is_null($this->visitHistory) && (count($this->visitHistory)>0)) {
			$lastVisit = $this->visitHistory[0];
			$today = Datetime::now();
			if($today->diffDays($lastVisit)==0) {
				$needUpdate = false;
			}
		}
		if($needUpdate) {
			if(!is_array($this->visitHistory)) {
				$this->visitHistory = array();
			}
			array_unshift($this->visitHistory, Datetime::now());
			if(count($this->visitHistory)>User::NUM_VISITS_TO_TRACK) {
				array_splice($this->visitHistory, User::NUM_VISITS_TO_TRACK);
			}
			$this->save();
		}
	}
	public function getVisitFrequency() {
		$frequency = User::ENUM_VISITS_MONTHLY;
		if(is_array($this->visitHistory) && (count($this->visitHistory)>1)) {
			$diffs = array();
			$numPeriods = count($this->visitHistory)-1;
			for($i=0; $i<$numPeriods; ++$i) {
				$diffs[] = $this->visitHistory[$i+1]->diffDays($this->visitHistory[$i]);
			}
			$sum = array_sum($diffs);
			$avgGap = $sum/$numPeriods;
			if($avgGap<1.5) {
				$frequency = User::ENUM_VISITS_DAILY;
			}
			elseif($avgGap<4) {
				$frequency = User::ENUM_VISITS_EVERYFEWDAYS;
			}
			elseif($avgGap<10) {
				$frequency = User::ENUM_VISITS_WEEKLY;
			}
			elseif($avgGap<20) {
				$frequency = User::ENUM_VISITS_EVERYFEWWEEKS;
			}
		}
		return $frequency;
	}
	public function getPagePath() {
		return PAGE_USER."?id=$this->id";
	}
	public function __construct($authDBData, $sgDBData) {
		// data from user DB
		$this->id = $sgDBData->id;
		$this->authID = $sgDBData->auth_id;
		$this->pictureURL = $sgDBData->picture_url;
		$this->visitHistory = User::visitHistoryFromStr($sgDBData->visit_history);
		$this->lastDailyEntry = Datetime::fromSQLStr($sgDBData->last_daily_entry);
		$this->dailyEntryStoryPosted = $sgDBData->daily_entry_story_posted;
		
		// data from auth DB
		$this->firstName = $authDBData->firstname;
		$this->lastName = $authDBData->lastname;
		$this->password = $authDBData->password;
		$this->authGroups = array();
		$this->authGroups[] = $authDBData->group1;
		$this->authGroups[] = $authDBData->group2;
		$this->authGroups[] = $authDBData->group3;
		$this->email = $authDBData->email;
		$this->verified = $authDBData->verified;
		$this->lastLogin = Datetime::fromSQLStr($authDBData->last_login);
	}
	public function __get($name) {
		static $publicGetVars = array("id","pictureURL","firstName","lastName","email","lastLogin","authID");
		
		$returnVal = null;
		if(in_array($name, $publicGetVars)) {
			$returnVal = $this->$name;
		}
		else {
			assert(false);
		}
		return $returnVal;
	}
	public function __set($name, $val) {
		static $publicSetVars = array();
		
		if(in_array($name, $publicSetVars)) {
			$this->$name = $val;
		}
		else {
			assert(false);
		}
	}

};

class StatusMessage {
	
	// private
	
	// protected
	
	// public
	const ENUM_GOOD=1;
	const ENUM_BAD=2;
	public $text;
	public $type;
	public function __construct($text, $type) {
		$this->text = $text;
		$this->type = $type;
	}
	
};

class StatusMessages {

	// private
	private static $messages=array();
	private static $init=false;
	
	private static function save() {
		Session::setStatusMessages(StatusMessages::$messages);
	}
	private function __construct() {} // static-only class
		
	// protected
	
	// public
	public static function init() {
		assert(Session::isStarted());
		StatusMessages::$messages = Session::getStatusMessages();
		StatusMessages::$init=true;
	}
	public static function isInit() {
		return StatusMessages::$init;
	}
	public static function addMessage($text, $type) {
		assert(StatusMessages::isInit());

		$message = new StatusMessage($text, $type);
		StatusMessages::$messages[] = $message;
		save();
	}
	public static function printMessages() {
		assert(StatusMessages::isInit());

		if(count(StatusMessages::$messages)==0) {
			return;
		}
		echo "<hr/>";
		foreach(StatusMessages::$messages as $message) {
			$style = "";
			switch($message->type) {
				case StatusMessage::ENUM_GOOD:
					$style="Good: ";
					break;
				default:
				case StatusMessage::ENUM_BAD:
					$style="Bad: ";
					break;
			}
			echo $style.htmlspecialchars($message->text)."<br/>";
		}
		echo "<hr/>";
	}

};

class Goal {

	// private
	private $id, $name, $description;
	
	// protected
	
	// public
	public static function createNew($name, $description) {
		Database::doQuery("INSERT INTO goals (name, description) VALUES (%s,%s)", $name, $description);
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getObjFromGoalID($goalID) {
		$goal = new Goal(Database::doQueryRFR("SELECT * FROM goals WHERE id=%d", $goalID));
		return $goal;
	}

	public function getPagePath() {
		return PATH_GOAL."?id=$this->id";
	}
	public function getNumAdopters() {
		return GoalStatus::getNumGoalAdopters($this->id);
	}
	public function __construct($dbData) {
		$this->id = $dbData->id;
		$this->name = $dbData->name;
		$this->description = $dbData->description;
	}
	public function __get($name) {
		static $publicGetVars = array("id","name","description");
		
		$returnVal = null;
		if(in_array($name, $publicGetVars)) {
			$returnVal = $this->$name;
		}
		else {
			assert(false);
		}
		return $returnVal;
	}
	public function __set($name, $val) {
		static $publicSetVars = array();
		
		if(in_array($name, $publicSetVars)) {
			$this->$name = $val;
		}
		else {
			assert(false);
		}
	}

};

class Story {

	// private
	private $id, $userID, $isPublic, $enteredAt;
	
	// protected
	protected function __construct($dbData) {
		$this->id = $dbData->id;
		$this->userID = $dbData->user_id;
		$this->isPublic = $dbData->is_public;
		$this->enteredAt = Datetime::fromSQLStr($dbData->entered_at);
	}
	protected function __get($name) {
		static $publicGetVars = array("userID","isPublic","enteredAt","id");
		
		$returnVal = null;
		if(in_array($name, $publicGetVars)) {
			$returnVal = $this->$name;
		}
		else {
			assert(false);
		}
		return $returnVal;
	}
	protected function __set($name, $val) {
		static $publicSetVars = array();
		
		if(in_array($name, $publicSetVars)) {
			$this->$name = $val;
		}
		else {
			assert(false);
		}
	}
	
	// public
	public function printStory();
	
	public static function getObjFromDBData($dbData) {
		$newStory = null;
		switch($dbData->type) {
			case EventStory::STORY_TYPENAME:
				$newStory = new EventStory($dbData);
				break;
			case DailyscoreStory::STORY_TYPENAME:
				$newStory = new DailyscoreStory($dbData);
				break;
			default:
				break;
		}
		return $newStory;
	}
	public static function printListForRS($rs) {
		$obj=null;
		while($obj=mysql_fetch_object($rs)) {
			$story = Story::getObjFromDBData($obj);
			assert(!is_null($story));
			$story->printStory();
			echo "<hr/>";
		}
	}
	
};

class EventStory extends Story {

	// private
	private $goalID, $newLevel, $oldLevel, $letterScore, $description;
	
	// protected
	
	// public
	const STORY_TYPENAME = 'event';
	public static function createNew($userID, $isPublic, $goalID, $newLevel, $oldLevel, $letterGrade, $description) {
		$today = Datetime::now()->toDay();
		Database::doQuery("INSERT INTO stories (user_id, type, is_public, entered_at, event_goal_id, event_new_level, event_old_level, event_score_snapshot, event_description, entered_at_day) _
							VALUES (%d, '".EventStory::STORY_TYPENAME."', %s, NOW(), %d, %f, %f, %s, %s, %s)",
							$userID, $isPublic, $goalID, $newLevel, $oldLevel, $letterGrade, $description, $today);
	}
	public static function getTodayStory($userID, $goalID) {
		$today = Datetime::now()->toDay();
		$dbData = Database::doQueryRFR("SELECT * FROM stories WHERE user_id=%d AND event_goal_id=%d AND entered_at_day=%s", $userID, $goalID, $today);
		if(is_null($dbData)) {
			return null;
		}
		return EventStory::getObjFromDBData($dbData);
	}
	public static function createNewOrUpdate($userID, $goalID, $newLevel, $oldLevel, $letterGrade, $why, $pageSession) {
		if($why=="") {
			return;
		}

		$story = EventStory::getTodayStory($userID, $goalID);
		if(is_null($story)) {
			EventStory::createNew($userID, true, $goalID, $newLevel, $oldLevel, $letterGrade, $why, $pageSession);
		}
		else {
			Database::doQuery("UPDATE stories SET event_new_level=%f, event_score_snapshot=%s, event_description=%s WHERE id=%d", $newLevel, $letterGrade, $why, $story->id);
		}
		
		User::getObjFromUserID($userID)->updateLastDailyEntry();
	}
	public static function getObjFromDBData($dbData) {
		return new EventStory($dbData);
	}
	public static function getLevelHistory($userID, $goalID, $daysBack) {
		$rs = Database::doQuery("SELECT entered_at_day, event_new_level, event_old_level FROM stories WHERE user_id=%d AND event_goal_id=%d AND UNIX_TIMESTAMP(entered_at)>(NOW()-%d*60*60*24) ORDER BY entered_at DESC", $userID, $goalID, $daysBack);

		$history = array();
		if(mysql_num_rows($rs)>0) {
			$obj=null;
			$lastDT = Datetime::now();
			$lastLevel = 0;
			$firstEntry = true;
			$daysSoFar = 0;
			while($obj=mysql_fetch_object($rs)) {
				$entryDT = Datetime::fromDay($obj->entered_at_day);
				$newLevel = $obj->event_new_level;
				$oldLevel = $obj->event_old_level;
				$dayDiff = round($entryDT->diffDays($lastDT));
				if($firstEntry) {
					$lastLevel = $newLevel;
					$firstEntry = false;
				}
				for($i=0; $i<$dayDiff; ++$i) {
					$level = ($lastLevel*($dayDiff-$i-1)/$dayDiff)+($newLevel*($i+1)/$dayDiff);
					$history[date("M j",$lastDT->shiftDays($i+1)->toUT())]=$level;
					++$daysSoFar;
				}
				$lastLevel = $newLevel;
				$lastDT = $entryDT;
			}
			$dayDiff = $daysBack-$daysSoFar;
			for($i=0; $i<$dayDiff; ++$i) {
				// HACK
				$level = ($newLevel*($dayDiff-$i-1)/$dayDiff)+($oldLevel*($i+1)/$dayDiff);
				$history[date("M j",$lastDT->shiftDays($i+1)->toUT())]=$level;
			}
		}
		return $history;
	}
	
	public function __construct($dbData) {
		parent::__construct($dbData);
		
		$this->goalID = $dbData->event_goal_id;
		$this->newLevel = $dbData->event_new_level;
		$this->oldLevel = $dbData->event_old_level;
		$this->letterScore = $dbData->event_score_snapshot;
		$this->description = $dbData->event_description;
	}
	protected function __get($name) {
		static $publicGetVars = array("enteredAt","id","newLevel","letterScore","description");
		
		$returnVal = null;
		if(in_array($name, $publicGetVars)) {
			$returnVal = $this->$name;
		}
		else {
			assert(false);
		}
		return $returnVal;
	}
	protected function __set($name, $val) {
		static $publicSetVars = array();
		
		if(in_array($name, $publicSetVars)) {
			$this->$name = $val;
		}
		else {
			assert(false);
		}
	}
	public function printStory() {
		$user = User::getObjFromUserID($this->userID);
		$goal = Goal::getObjFromGoalID($this->goalID);
		$userPagePath = $user->getPagePath();
		$goalPagePath = $goal->getPagePath();
		echo "<a href='$userPagePath'><img src='".htmlspecialchars($user->pictureURL)."' /></a><br/>";
		$changeWord = "raised";
		if($this->newLevel<$this->oldLevel) {
			$changeWord = "lowered";
		}
		echo "<a href='$userPagePath'>$user->firstName $user->lastName</a> $changeWord his score for _
				<a href='$goalPagePath'>".htmlspecialchars($goal->name)."</a> from $this->oldLevel to $this->newLevel.<br/>";
		echo "Letter: $this->letterScore<br/>";
		echo "Description: '".htmlspecialchars($this->description)."'<br/>";
		$timeSinceStr = $this->enteredAt->timeSince();
		echo "Time: $timeSinceStr ago<br/>";
	}
};

class DailyscoreStory extends Story {

	// private
	private $progress;
	private static function progressFromStr($str) {
		return deserialize($str);
	}
	private static function progressToStr($progress) {
		return serialize($progress);
	}
	
	// protected
	
	// public
	const STORY_TYPENAME = 'dailyscore';
	public static function createNew($userID, $isPublic, $goalsTouched) {
		$progressStr = DailyscoreStory::progressToStr($goalsTouched);
		Database::doQuery("INSERT INTO stories (user_id, type, is_public, entered_at, dailyscore_progress) VALUES _
							(%d, '".DailyscoreStory::STORY_TYPENAME."', %s, NOW(), %s)",
							$userID, $isPublic, $progressStr);
	}
	public function __construct($dbData) {
		parent::__construct($dbData);
		
		$this->progress = DailyscoreStory::progressFromStr($dbData->dailyscore_progress);
	}
	public function printStory() {
		$user = User::getObjFromUserID($this->userID);
		$userPagePath = $user->getPagePath();
		echo "<a href='$userPagePath'><img src='".htmlspecialchars($user->pictureURL)."' /></a><br/>";
		$totalGoals = Database::doQueryOne("SELECT COUNT(*) FROM goals_status WHERE user_id=%d", $user->id);
		$numGoalsTouched = count($this->progress);
		echo "<a href='$userPagePath'>$user->firstName $user->lastName</a> just entered his daily goal progress, touching $numGoalsTouched out of $totalGoals of his goals.<br/>";
		$score = floor(($numGoalsTouched/$totalGoals)*100);
		echo "Score: $score<br/>";
		foreach($progress as $goalID) {
			$goal = Goal::getObjFromGoalID($goalID);
			$goalPagePath = $goal->getPagePath();
			echo "<a href='$goalPagePath'>".htmlspecialchars($goal->name)."</a>, ";
		}
		echo "<br/>";
		$timeSinceStr = $this->enteredAt->timeSince();
		echo "Time: $timeSinceStr ago<br/>";
	}
};

class Dailytest {
	
	// private
	private $id, $goalID, $name, $description;
	
	// protected
	
	// public
	public static function createNew($goalID, $name, $description) {
		Database::doQuery("INSERT INTO dailytests (goal_id, name, description) VALUES (%d, %s, %s)", $goalID, $name, $description);
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getListFromGoalID($goalID) {
		$rs = Database::doQuery("SELECT id FROM dailytests WHERE goal_id=%d", $goalID);
		$list = array();
		$obj = null;
		while($obj = mysql_fetch_object($rs)) {
			$list[] = new Dailytest($obj);
		}
		return $list;
	}
	
	public function __construct($dbData) {
		$this->id = $dbData->id;
		$this->goalID = $dbData->goal_id;
		$this->name = $dbData->name;
		$this->description = $dbData->description;
	}
	public function __get($name) {
		static $publicGetVars = array("id","goalID","name","description");
		
		$returnVal = null;
		if(in_array($name, $publicGetVars)) {
			$returnVal = $this->$name;
		}
		else {
			assert(false);
		}
		return $returnVal;
	}
	public function __set($name, $val) {
		static $publicSetVars = array();
		
		if(in_array($name, $publicSetVars)) {
			$this->$name = $val;
		}
		else {
			assert(false);
		}
	}
	
};

class GoalStatus {
	
	// private
	private $goalID, $userID, $level, $isActive, $isPublic, $positionIndex;

	// protected
	
	// public
	public static function doesUserHaveGoal($userID, $goalID) {
		$rs = Database::doQuery("SELECT goal_id FROM goals_status WHERE user_id=%d AND goal_id=%d", $userID, $goalID);
		$userHasGoal = mysql_num_rows($rs)>0;
		return $userHasGoal;
	}
	public static function getAverageGoalScore($goalID) {
		return Database::doQueryOne("SELECT AVERAGE(level) FROM goals_status WHERE goal_id=%d", $goalID);
	}
	public static function userAdoptGoal($userID, $goalID) {
		$nextIndex = Database::doQueryOne("SELECT MAX(position_index)+1 FROM goals_status WHERE goal_id=%d AND user_id=%d", $goalID, $userID);
		// by default all goals are public until we put up goal adoption page
		Database::doQuery("INSERT INTO goals_status (goal_id, user_id, level, is_active, is_public, position_index) _
											VALUES (%d, %d, 0, TRUE, TRUE, %d)", $goalID, $userID, $nextIndex);
	}
	public static function getNumGoalAdopters($goalID) {
		$rs2 = Database::doQuery("SELECT user_id FROM goals_status WHERE goal_id=%d", $goalID);
		$numAdopters = mysql_num_rows($rs2);
		return $numAdopters;
	}
	public static function getObjFromDBData($dbData) {
		return new GoalStatus($dbData);
	}
	public static function printRowList($userID, $dayUT, $isEditable) {
		// ignore dayUT for now
		$rs = Database::doQuery("SELECT * FROM goals_status WHERE user_id=%d", $userID);
		while($obj = mysql_fetch_object($rs)) {
			$goalStatus = GoalStatus::getObjFromDBData($obj);
			$goalStatus->printRow($isEditable);
		}
	}

	public function __construct($dbData) {
		$this->goalID = $dbData->goal_id;
		$this->userID = $dbData->user_id;
		$this->level = $dbData->level;
		$this->isActive = $dbData->is_active;
		$this->isPublic = $dbData->is_public;
		$this->positionIndex = $dbData->position_index;
	}
	public function printRow($isEditable) {
		static $divID = 1;
		
		if(!$this->isActive) {
			return;
		}
		
		echo "<hr/>";
		// overall level
		$goal = Goal::getObjFromGoalID($this->goalID);
		echo "<a href='".$goal->getPagePath()."'>".htmlspecialchars($goal->name)."</a><br/>";
		echo "$this->level<br/>";
		if($isEditable) {
			$ajaxSaveEventPath = PAGE_AJAX_SAVEEVENT;
			$newScoreVal = "";
			$letterGradeVal = "";
			$whyVal = "";
			$eventStory = EventStory::getTodayStory($this->userID, $this->goalID);
			if(!is_null($eventStory)) {
				$newScoreVal = $eventStory->newScore;
				$letterGradeVal = $eventStory->letterGrade;
				$whyVal = htmlspecialchars($eventStory->description);
			}
			$eventDivStr = <<< EOT
<script language="text/javascript">
	function expandEvent$divID() {
		document.all['eventDiv$divID'].style.display = 'block';
	}
	
	var timer=null;
	function onChangeEvent$divID() {
		if(timer != null) {
			clearTimeout(timer);
		}
		timer=setTimeout("doSaveEvent$divID()",1000);
	}
	
	function doSaveEvent$divID() {
		// make request
		var xmlhttp;
		if (window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else {
			// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				response = xmlhttp.responseText;
				// DONE
				//document.getElementById("ratingBox").innerHTML="<center>Thanks :)</center>";
			}
		}
		xmlhttp.open("GET","$ajaxSaveEventPath?userID=$this->userID&goalID=$goal->id&oldLevel=$this->level&newLevel="+document.getElementById("eventNewScore$divID").value+"&letterGrade="+document.getElementById("eventLetterGrade$divID").value+"&why="+escape(document.getElementById("eventWhy$divID").value),true);
		xmlhttp.send();
	}
</script>
<input type='button' value='+' onclick="expandEvent$divID();" />
<div id="eventDiv$divID" style="display:none;">
	New level: <input type="text" id="eventNewScore$divID" onkeyup="onChangeEvent$divID();" value="$newLevelVal"/><br/>
	Letter grade:
	<select id="eventLetterGrade$divID" onchange="onChangeEvent$divID();" onload="document.all['eventLetterGrade$divID'].value='$letterGradeVal';">
		<option value="A">A</option>
		<option value="B">B</option>
		<option value="C">C</option>
		<option value="D">D</option>
		<option value="F">F</option>
	</select><br/>
	Why: <input type="text" id="eventWhy$divID" onkeyup="onChangeEvent$divID();" value="$whyVal" /><br/>
</div>
EOT;
			echo $eventDivStr;
			++$divID;
		}
		// daily tests
		const NUM_DAYS_BACK = 10;
		$dailytests = Dailytest::getListFromGoalID($this->goalID);
		foreach($dailytests as $dailytest) {
			$checkedVal = DailytestStatus::getTodayStatus($this->userID, $dailytest->id)?"checked":"";
			$ajaxSaveDailytestPath = PAGE_AJAX_SAVEDAILYTEST;
			$htmlStr = <<< EOT
<script language="text/javascript">
	
	var timer=null;
	function onChangeEvent$divID() {
		if(timer != null) {
			clearTimeout(timer);
		}
		timer=setTimeout("doSaveCheck$divID()",1000);
	}
	
	function doSaveCheck$divID() {
		// make request
		var xmlhttp;
		if (window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else {
			// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				response = xmlhttp.responseText;
				// DONE
				//document.getElementById("ratingBox").innerHTML="<center>Thanks :)</center>";
			}
		}
		var isChecked = document.getElementById("testCheck$divID").checked;
		xmlhttp.open("GET","$ajaxSaveDailytestPath?userID=$this->userID&dailytestID=$dailytest->id&checked="+isChecked,true);
		xmlhttp.send();
	}
</script>
<input type='checkbox' id="testCheck$divID" $checkedVal onchange="onChangeEvent$divID();" />
EOT;
			echo $htmlStr;
			++$divID;
		
			echo htmlspecialchars($dailytest->name)."<br/>";
			$dailytestStatuses = DailytestStatus::getListFromUserID($this->userID, $dailytest->id, NUM_DAYS_BACK);
			$dailytestStatusDays = array();
			foreach($dailytestStatuses as $dailytestStatus) {
				$dailytestStatusDays[] = $dailytestStatus->enteredAt->toDay();
			}
			for($i=0; $i<NUM_DAYS_BACK; ++$i) {
				$char = " ";
				$currentDay = (new Datetime(time()-($i+1)*60*60*24))->toDay();
				if(in_array($currentDay,$dailytestStatusDays)) {
					$char="X";
				}
				echo "[$char]";
			}
			echo "<br/>";
		}

		// level history graph
		echo "<img src='template/createGraphLevelHistory.php?userID=$this->userID&goalID=$this->goalID' /><br/>";
		echo "<hr/>";
	}
	
};

class DailytestStatus {

	// private
	private $dailytestID, $userID, $result, $enteredAt;
	
	// protected
	
	// public
	public static function createNew($dailytestID, $userID, $result) {
		Database::doQuery("INSERT INTO dailytests_status (dailytest_id, user_id, result, entered_at) VALUES (%d, %d, %d, NOW())", $dailytestID, $userID, $result);
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getObjFromDBData($dbData) {
		$status = new DailytestStatus($dbData);
		return $status;
	}
	public static function getListFromUserID($userID, $dailytestID, $daysBack) {
		$rs = Database::doQuery("SELECT * FROM dailytests_status WHERE user_id=%d AND dailytest_id=%d AND UNIX_TIMESTAMP(entered_at)>(NOW()-%d*60*60*24) ORDER BY entered_at DESC", $userID, $dailytestID, $daysBack);
		$obj = null;
		$list = array();
		while($obj = mysql_fetch_object($rs)) {
			$list[] = DailytestStatus::getObjFromDBData($obj);
		}
		return $list;
	}
	public static function getTodayStatus($userID, $dailytestID) {
		$today = Datetime::now()->toDay();
		$rs = Database::doQuery("SELECT result FROM dailytests_status WHERE user_id=%d AND dailytest_id=%d AND entered_at_day=%s", $userID, $dailytestID, $today);
		return mysql_num_rows($rs)>0;
	}
	public static function setTodayStatus($userID, $dailytestID, $newStatus) {
		$currentStatus = DailytestStatus::getTodayStatus($userID, $dailytestID);
		$today = Datetime::now()->toDay();
		if($currentStatus!=$newStatus) {
			if($currentStatus) {
				Database::doQuery("DELETE FROM dailytests_status WHERE user_id=%d AND dailytest_id=%d AND entered_at_day=%s", $userID, $dailytestID, $today);
			}
			else {
				Database::doQuery("INSERT INTO dailytests_status (dailytest_id, user_id, result, entered_at, entered_at_day) VALUES (%d, %d, 1, NOW(), %s)", $dailytestID, $userID, $today);
			}
		}
		
		User::getObjFromUserID($userID)->updateLastDailyEntry();
	}

	public function __construct($dbData) {
		$this->dailytestID = $dbData->dailytest_id;
		$this->userID = $dbData->user_id;
		$this->result = $dbData->result;
		$this->enteredAt = Datetime::fromSQLStr($dbData->entered_at);
	}
	public function __get($name) {
		static $publicGetVars = array("result","enteredAt");
		
		$returnVal = null;
		if(in_array($name, $publicGetVars)) {
			$returnVal = $this->$name;
		}
		else {
			assert(false);
		}
		return $returnVal;
	}
	public function __set($name, $val) {
		static $publicSetVars = array();
		
		if(in_array($name, $publicSetVars)) {
			$this->$name = $val;
		}
		else {
			assert(false);
		}
	}

};

?>