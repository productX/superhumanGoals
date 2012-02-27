<?php
require_once("intranetAuth.php");

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
	public static function strToEmail($str) {
		$isValid = validEmail($str);
		$email = validEmail($str)?$str:null;
		return $email;
	}
};

const CLASSNAME_DATETIME='Date';
class Date {

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
		return date("Y-m-d H:i:s", $this->ut);
	}
	public function toUT() {
		return $this->ut;
	}
	public function diffDays($otherDay) {
		return ($otherDay->ut-$this->ut)/(60*60*24);
	}
	public function shiftDays($numDays) {
		return new Date($this->ut+$numDays*60*60*24);
	}
	public function timeSince() {
		return timeSince($this->ut);
	}
	public static function fromDay($day) {
		return new Date(strtotime($day));
	}
	public static function fromSQLStr($str) {
		return new Date(strtotime($str));
	}
	public static function fromUT($ut) {
		return new Date($ut);
	}
	public static function now() {
		return new Date(time());
	}

};

const CLASSNAME_SQLARGLIKE='SQLArgLike';
class SQLArgLike {

	//private
	private $likeArg;
	
	//protected
	
	//public
	public function __construct($arg) {
		$this->likeArg=$arg;
	}
	public function toSQLStr() {
		return "%$this->likeArg%";
	}
	
};

class Database {

	// private
	const SERVER = 'localhost';
	const USERNAME = 'root';
	const PASSWORD = '';
	const DBNAME = 'superhuman_goals';
	private static $initialized = false;
	private static $conn;
	private static $db;
	private static $debugOn = false;
	private function __construct() {} // static-only class
	private static function escapeForSQL($str) {
		return mysql_real_escape_string($str);
	}
	
	// protected
	
	// public
	public static function init() {
		Database::$conn = @mysql_connect(Database::SERVER, Database::USERNAME, Database::PASSWORD) or die(mysql_error());
		Database::$db = @mysql_select_db(Database::DBNAME,Database::$conn)or die(mysql_error());
		Database::$initialized = true;
	}
	public static function reInit() {
		Database::init();
	}
	private static function doQueryBase($args) {
		assert(Database::$initialized && isset(Database::$db) && isset(Database::$conn) && (count($args)>0));

		if(Database::$debugOn) {
			echo "<hr/>QUERY: $args[0]<br/>";
			var_dump($args);
			var_dump(debug_backtrace());
		}
		
		$printArgs = array();
		for($i=1; $i<count($args); ++$i) {
			$val = null;
			$arg = $args[$i];
			if(is_null($arg)) {
				$val = "NULL";
			}
			else {
				switch(gettype($arg)) {
					case 'boolean':
						$val = $arg?"TRUE":"FALSE";
						break;
					case 'integer':
					case 'double':
						$val = strval($arg);
						break;
					case 'string':
						$val = "'".Database::escapeForSQL($arg)."'";
						break;
					case 'object':
						$className = get_class($arg);
						if($className == CLASSNAME_DATETIME) {
							$val = "'".Database::escapeForSQL($arg->toSQLStr())."'";
						}
						elseif($className == CLASSNAME_SQLARGLIKE) {
							$val = "'".Database::escapeForSQL($arg->toSQLStr())."'";
						}
						break;
					default:
						break;
				}
			}
			$printArgs[] = $val;
		}
		$sql = vsprintf($args[0],$printArgs);
		
		if(Database::$debugOn) {
			echo "processed query: $sql<br/><hr/>";
		}

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
	public static function debugMode($turnOn) {
		Database::$debugOn = $turnOn;
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
	private $id, $authID, $pictureURL, $lastDailyEntry, $visitHistory, $dailyEntryStoryPosted;
	private $firstName, $lastName, $password, $authGroups, $email, $verified, $lastLogin;
	// HACK: this should be done better
	private static function visitHistoryToStr($visitHistory) {
		return serialize($visitHistory);
	}
	private static function visitHistoryFromStr($blob) {
		return unserialize($blob);
	}
	private static function printUserListBase($userIDList) {
		static $firstCharCode = 65;
		static $lastCharCode = 90;

		$userListNonletters = array();
		$lnListNonletters = array();
		$userListLetters = array();
		$lnListLetters = array();
		foreach($userIDList as $lUserID) {
			$lUser = User::getObjFromUserID($lUserID);
			assert(!is_null($lUser));
			$charCode = ord(strtoupper($lUser->lastName));
			if(($charCode >= $firstCharCode) && ($charCode <= $lastCharCode)) {
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
		Database::doQuery("UPDATE users SET	picture_url=%s,
											visit_history=%s,
											last_daily_entry=%s,
											daily_entry_story_posted=%s
											WHERE id=%s",
											$this->pictureURL, $visitHistoryStr, $this->lastDailyEntry, $this->dailyEntryStoryPosted, $this->id);
	}
	
	// protected
	
	// public
	public static function getObjFromUserID($userID) {
		$user = null;
		//Database::debugMode(true);
		$sgObj = Database::doQueryRFR("SELECT * FROM users WHERE id=%s", $userID);
		if(!is_null($sgObj)) {
			$authObj = getAuthUserData($sgObj->auth_id);
			assert(!is_null($authObj));
			$user = new User($authObj, $sgObj);
		}
		return $user;
	}
	public static function doSignup($pictureURL) {
		$loggedIn = User::tryLogin();
		if($loggedIn) {
			StatusMessages::addMessage("User already signed up.", StatusMessage::ENUM_BAD);
		}
		else {
			$authID = Session::getAuthUserID();
			$authObj = getAuthUserData($authID);
			assert(!is_null($authObj));
			$visitHistoryStr = User::visitHistoryToStr(array(Date::now()));
			Database::doQuery("INSERT INTO users (auth_id, picture_url, visit_history, full_name) VALUES (%s, %s, %s, %s)", $authID, $pictureURL, $visitHistoryStr, "$authObj->firstname $authObj->lastname");
			$newID = mysql_insert_id();
			Session::setLoggedInUserID($newID);
		}
		return true;
	}
	public static function tryLogin() {
		$success = false;
		$authUserID = Session::getAuthUserID();
		$sgObj = Database::doQueryRFR("SELECT * FROM users WHERE auth_id=%s", $authUserID);
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
		$rs = Database::doQuery("SELECT user_id FROM goals_status WHERE goal_id=%s", $goalID);
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
		echo "<a href='$profLink'>$user->firstName <b>$user->lastName</b></a><br/>";
		$numGoals = GoalStatus::getNumUserGoals($user->id);
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
		$this->lastDailyEntry = Date::now();
		$this->dailyEntryStoryPosted = false;
		$this->save();
	}
	public function hasMadeDailyEntry() {
		return $this->lastDailyEntry->diffDays(Date::now())==0;
	}
	public function trackVisit() {
		$needUpdate = true;
		if(!is_null($this->visitHistory) && (count($this->visitHistory)>0)) {
			$lastVisit = $this->visitHistory[0];
			$today = Date::now();
			if($today->diffDays($lastVisit)==0) {
				$needUpdate = false;
			}
		}
		if($needUpdate) {
			if(!is_array($this->visitHistory)) {
				$this->visitHistory = array();
			}
			array_unshift($this->visitHistory, Date::now());
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
		$this->lastDailyEntry = Date::fromSQLStr($sgDBData->last_daily_entry);
		$this->dailyEntryStoryPosted = boolval($sgDBData->daily_entry_story_posted);
		
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
		$this->lastLogin = Date::fromSQLStr($authDBData->last_login);
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
		StatusMessages::save();
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
		$goal = new Goal(Database::doQueryRFR("SELECT * FROM goals WHERE id=%s", $goalID));
		return $goal;
	}

	public function getPagePath() {
		return PAGE_GOAL."?id=$this->id";
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

abstract class Story {

	// private
	private $id, $userID, $isPublic, $enteredAt;
	
	// protected
	protected function __construct($dbData) {
		$this->id = $dbData->id;
		$this->userID = $dbData->user_id;
		$this->isPublic = boolval($dbData->is_public);
		$this->enteredAt = Date::fromSQLStr($dbData->entered_at);
	}
	
	// public
	abstract public function printStory();
	
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
	public function __get($name) {
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

class EventStory extends Story {

	// private
	private $goalID, $newLevel, $oldLevel, $letterGrade, $description;
	
	// protected
	
	// public
	const STORY_TYPENAME = 'event';
	public static function createNew($userID, $isPublic, $goalID, $newLevel, $oldLevel, $letterGrade, $description) {
		$today = Date::now()->toDay();
		Database::doQuery("INSERT INTO stories (user_id, type, is_public, entered_at, event_goal_id, event_new_level, event_old_level, event_letter_score, event_description, entered_at_day)
							VALUES (%s, '".EventStory::STORY_TYPENAME."', %s, %s, %s, %s, %s, %s, %s, %s)",
							$userID, $isPublic, Date::Now(), $goalID, $newLevel, $oldLevel, $letterGrade, $description, $today);
	}
	public static function getTodayStory($userID, $goalID) {
		$today = Date::now()->toDay();
		$dbData = Database::doQueryRFR("SELECT * FROM stories WHERE user_id=%s AND event_goal_id=%s AND entered_at_day=%s", $userID, $goalID, $today);
		if(is_null($dbData)) {
			return null;
		}
		return EventStory::getObjFromDBData($dbData);
	}
	public static function createNewOrUpdate($userID, $goalID, $newLevel, $oldLevel, $letterGrade, $why) {
		if($why=="") {
			return;
		}

		$story = EventStory::getTodayStory($userID, $goalID);
		if(is_null($story)) {
			EventStory::createNew($userID, true, $goalID, $newLevel, $oldLevel, $letterGrade, $why);
		}
		else {
			Database::doQuery("UPDATE stories SET event_new_level=%s, event_letter_score=%s, event_description=%s, entered_at=%s WHERE id=%s", $newLevel, $letterGrade, $why, Date::Now(), $story->id);
		}
		
		$user = User::getObjFromUserID($userID);
		$user->updateLastDailyEntry();
	}
	public static function getObjFromDBData($dbData) {
		return new EventStory($dbData);
	}
	public static function getLevelHistory($userID, $goalID, $daysBack) {
		$rs = Database::doQuery("SELECT entered_at_day, event_new_level, event_old_level FROM stories WHERE user_id=%s AND event_goal_id=%s AND UNIX_TIMESTAMP(entered_at)>(".Date::Now()->toUT()."-%s*60*60*24) ORDER BY entered_at DESC", $userID, $goalID, $daysBack);

		$history = array();
		if(mysql_num_rows($rs)>0) {
			$obj=null;
			$lastDT = Date::now();
			$lastLevel = 0;
			$firstEntry = true;
			$daysSoFar = 0;
			while($obj=mysql_fetch_object($rs)) {
				$entryDT = Date::fromDay($obj->entered_at_day);
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
				//$history[date("M j",$lastDT->shiftDays(0-($i+1))->toUT())]=$level;
				$history[$i]=$level;
			}
		}
		else {
			for($i=0; $i<$daysBack; ++$i) {
				// HACK
				$level = GoalStatus::getUserGoalLevel($userID, $goalID);
				//$history[date("M j",$lastDT->shiftDays(0-($i+1))->toUT())]=$level;
				$history[$i]=$level;
			}
		}
		return array_reverse($history);
	}
	
	public function __construct($dbData) {
		parent::__construct($dbData);
		
		$this->goalID = $dbData->event_goal_id;
		$this->newLevel = $dbData->event_new_level;
		$this->oldLevel = $dbData->event_old_level;
		$this->letterGrade = $dbData->event_letter_score;
		$this->description = $dbData->event_description;
	}
	public function __get($name) {
		static $publicGetVars = array("enteredAt","id","newLevel","letterGrade","description","userID");
		
		$returnVal = null;
		if(in_array($name, $publicGetVars)) {
			if(isset($this->$name)) {
				$returnVal = $this->$name;
			}
			else {
				$returnVal = parent::__get($name);
			}
		}
		else {
			var_dump(debug_backtrace());
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
		echo "<a href='$userPagePath'>$user->firstName $user->lastName</a> $changeWord his score for 
				<a href='$goalPagePath'>".htmlspecialchars($goal->name)."</a> from $this->oldLevel to $this->newLevel.<br/>";
		echo "Letter: $this->letterGrade<br/>";
		echo "Description: '".htmlspecialchars($this->description)."'<br/>";
		$timeSinceStr = $this->enteredAt->timeSince();
		echo "Time: $timeSinceStr ago<br/>";
	}
};

class DailyscoreStory extends Story {

	// private
	private $progress;
	private static function progressFromStr($str) {
		return unserialize($str);
	}
	private static function progressToStr($progress) {
		return serialize($progress);
	}
	
	// protected
	
	// public
	const STORY_TYPENAME = 'dailyscore';
	public static function createNew($userID, $isPublic, $goalsTouched) {
		$progressStr = DailyscoreStory::progressToStr($goalsTouched);
		Database::doQuery("INSERT INTO stories (user_id, type, is_public, entered_at, dailyscore_progress) VALUES 
							(%s, '".DailyscoreStory::STORY_TYPENAME."', %s, %s, %s)",
							$userID, $isPublic, Date::Now(), $progressStr);
	}
	public function __construct($dbData) {
		parent::__construct($dbData);
		
		$this->progress = DailyscoreStory::progressFromStr($dbData->dailyscore_progress);
	}
	public function printStory() {
		$user = User::getObjFromUserID($this->userID);
		$userPagePath = $user->getPagePath();
		echo "<a href='$userPagePath'><img src='".htmlspecialchars($user->pictureURL)."' /></a><br/>";
		$totalGoals = Database::doQueryOne("SELECT COUNT(*) FROM goals_status WHERE user_id=%s", $user->id);
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
		Database::doQuery("INSERT INTO dailytests (goal_id, name, description) VALUES (%s, %s, %s)", $goalID, $name, $description);
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getListFromGoalID($goalID) {
		$rs = Database::doQuery("SELECT * FROM dailytests WHERE goal_id=%s", $goalID);
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
		$rs = Database::doQuery("SELECT goal_id FROM goals_status WHERE user_id=%s AND goal_id=%s", $userID, $goalID);
		$userHasGoal = mysql_num_rows($rs)>0;
		return $userHasGoal;
	}
	public static function getNumUserGoals($userID) {
		$numGoals = Database::doQueryOne("SELECT COUNT(goal_id) FROM goals_status WHERE user_id=%s", $userID);
		return $numGoals;
	}
	public static function getUserGoalLevel($userID, $goalID) {
		$level = Database::doQueryOne("SELECT level FROM goals_status WHERE user_id=%s AND goal_id=%s", $userID, $goalID);
		return $level;
	}
	public static function setUserGoalLevel($userID, $goalID, $newLevel) {
		Database::doQuery("UPDATE goals_status SET level=%s WHERE user_id=%s AND goal_id=%s", $newLevel, $userID, $goalID);
	}
	public static function getAverageGoalScore($goalID) {
		return Database::doQueryOne("SELECT AVG(level) FROM goals_status WHERE goal_id=%s", $goalID);
	}
	public static function userAdoptGoal($userID, $goalID) {
		$nextIndex = Database::doQueryOne("SELECT MAX(position_index)+1 FROM goals_status WHERE goal_id=%s AND user_id=%s", $goalID, $userID);
		if(is_null($nextIndex)) {
			$nextIndex=0;
		}
		// by default all goals are public until we put up goal adoption page
		Database::doQuery("INSERT INTO goals_status (goal_id, user_id, level, is_active, is_public, position_index) 
											VALUES (%s, %s, 5, TRUE, TRUE, %s)", $goalID, $userID, $nextIndex);
	}
	public static function getNumGoalAdopters($goalID) {
		$rs2 = Database::doQuery("SELECT user_id FROM goals_status WHERE goal_id=%s", $goalID);
		$numAdopters = mysql_num_rows($rs2);
		return $numAdopters;
	}
	public static function getObjFromDBData($dbData) {
		return new GoalStatus($dbData);
	}
	public static function printRowList($userID, $dayUT, $isEditable) {
		// ignore dayUT for now
		$rs = Database::doQuery("SELECT * FROM goals_status WHERE user_id=%s", $userID);
		while($obj = mysql_fetch_object($rs)) {
			$goalStatus = GoalStatus::getObjFromDBData($obj);
			$goalStatus->printRow($isEditable);
		}
	}

	public function __construct($dbData) {
		$this->goalID = $dbData->goal_id;
		$this->userID = $dbData->user_id;
		$this->level = $dbData->level;
		$this->isActive = boolval($dbData->is_active);
		$this->isPublic = boolval($dbData->is_public);
		$this->positionIndex = $dbData->position_index;
	}
	public function printRow($isEditable) {
		static $rowID = 1;
		static $testID = 1;
		
		if(!$this->isActive) {
			return;
		}
		
		echo "<hr/>";
		// overall level
		$goal = Goal::getObjFromGoalID($this->goalID);
		echo "<a href='".$goal->getPagePath()."'>".htmlspecialchars($goal->name)."</a><br/>";
		echo "Level: <div id='currentLevel$rowID'>$this->level</div><br/>";
		if($isEditable) {
			$ajaxSaveEventPath = PAGE_AJAX_SAVEEVENT;
			$newLevelVal = "";
			$letterGradeVal = "A";
			$whyVal = "";
			$plusButtonDefaultDisplay = "block";
			$eventDivDefaultDisplay = "none";
			$eventStory = EventStory::getTodayStory($this->userID, $this->goalID);
			if(!is_null($eventStory)) {
				$newLevelVal = $eventStory->newLevel;
				$letterGradeVal = $eventStory->letterGrade;
				$whyVal = htmlspecialchars($eventStory->description);
				$plusButtonDefaultDisplay = "none";
				$eventDivDefaultDisplay = "block";
			}
			$optionSelectedA = ($letterGradeVal=="A")?"selected":"";
			$optionSelectedB = ($letterGradeVal=="B")?"selected":"";
			$optionSelectedC = ($letterGradeVal=="C")?"selected":"";
			$optionSelectedD = ($letterGradeVal=="D")?"selected":"";
			$optionSelectedF = ($letterGradeVal=="F")?"selected":"";
			$eventDivStr = <<< EOT
<script type="text/javascript">
	function expandEvent$rowID() {
		document.all['eventDiv$rowID'].style.display = 'block';
		document.all['plusButton$rowID'].style.display = 'none';
	}
	
	var timer=null;
	function onChangeEvent$rowID() {
		// validate
		if(parseFloat(document.all['eventNewScore$rowID'].value)==0) {
			return;
		}
	
		// trigger save timer
		if(timer != null) {
			clearTimeout(timer);
		}
		timer=setTimeout("doSaveEvent$rowID()",200);
	}
	
	function doSaveEvent$rowID() {
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
				document.all['currentLevel$rowID'].innerHTML = document.all['eventNewScore$rowID'].value;
				document.all['graph$rowID'].src = "template/createGraphLevelHistory.php?userID=$this->userID&goalID=$goal->id&r="+(Math.random()*1000000);
			}
		}
		xmlhttp.open("GET","$ajaxSaveEventPath?userID=$this->userID&goalID=$goal->id&oldLevel=$this->level&newLevel="+parseFloat(document.getElementById("eventNewScore$rowID").value)+"&letterGrade="+document.getElementById("eventLetterGrade$rowID").value+"&why="+escape(document.getElementById("eventWhy$rowID").value),true);
		xmlhttp.send();
	}
</script>
<input type='button' value='+' id="plusButton$rowID" onclick="expandEvent$rowID();" style="display:$plusButtonDefaultDisplay;" />
<div id="eventDiv$rowID" style="display:$eventDivDefaultDisplay;">
	New level: <input type="text" id="eventNewScore$rowID" onkeyup="onChangeEvent$rowID();" value="$newLevelVal"/><br/>
	Letter grade:
	<select id="eventLetterGrade$rowID" onchange="onChangeEvent$rowID();">
		<option value="A" $optionSelectedA>A</option>
		<option value="B" $optionSelectedB>B</option>
		<option value="C" $optionSelectedC>C</option>
		<option value="D" $optionSelectedD>D</option>
		<option value="F" $optionSelectedF>F</option>
	</select><br/>
	Why: <input type="text" id="eventWhy$rowID" onkeyup="onChangeEvent$rowID();" value="$whyVal" /><br/>
</div>
EOT;
			echo $eventDivStr;
		}
		echo "<br/>";
		// daily tests
		//const NUM_DAYS_BACK = 10;
		static $numDaysBack = 10;
		$dailytests = Dailytest::getListFromGoalID($this->goalID);
		foreach($dailytests as $dailytest) {
			$checkedVal = DailytestStatus::getTodayStatus($this->userID, $dailytest->id)?"checked":"";
			$ajaxSaveDailytestPath = PAGE_AJAX_SAVEDAILYTEST;
			$htmlStr = <<< EOT
<script type="text/javascript">
	
	var timer=null;
	function onChangeCheck$testID() {
		if(timer != null) {
			clearTimeout(timer);
		}
		timer=setTimeout("doSaveCheck$testID()",200);
	}
	
	function doSaveCheck$testID() {
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
		var isChecked = document.getElementById("testCheck$testID").checked;
		xmlhttp.open("GET","$ajaxSaveDailytestPath?userID=$this->userID&dailytestID=$dailytest->id&result="+(isChecked?"1":"0"),true);
		xmlhttp.send();
	}
</script>
<input type='checkbox' id="testCheck$testID" $checkedVal onchange="onChangeCheck$testID();" />
EOT;
			echo $htmlStr;
			echo htmlspecialchars($dailytest->name)."<br/>";

			$dailytestStatuses = DailytestStatus::getListFromUserID($this->userID, $dailytest->id, $numDaysBack);
			$dailytestStatusDays = array();
			foreach($dailytestStatuses as $dailytestStatus) {
				$dailytestStatusDays[] = $dailytestStatus->enteredAt->toDay();
			}
			for($i=0; $i<$numDaysBack; ++$i) {
				$char = " ";
				$current = Date::fromUT(time()-($i+1)*60*60*24);
				$currentDay = $current->toDay();
				if(in_array($currentDay,$dailytestStatusDays)) {
					$char="X";
				}
				echo "[$char]";
			}
			echo "<br/>";

			++$testID;
		}
		echo "<br/>";

		// level history graph
		echo "<img src='template/createGraphLevelHistory.php?userID=$this->userID&goalID=$this->goalID' id='graph$rowID' /><br/>";
		echo "<hr/>";
		++$rowID;
	}
	
};

class DailytestStatus {

	// private
	private $dailytestID, $userID, $result, $enteredAt;
	
	// protected
	
	// public
	public static function createNew($dailytestID, $userID, $result) {
		Database::doQuery("INSERT INTO dailytests_status (dailytest_id, user_id, result, entered_at) VALUES (%s, %s, %s, %s)", $dailytestID, $userID, $result, Date::Now());
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getObjFromDBData($dbData) {
		$status = new DailytestStatus($dbData);
		return $status;
	}
	public static function getListFromUserID($userID, $dailytestID, $daysBack) {
		$rs = Database::doQuery("SELECT * FROM dailytests_status WHERE user_id=%s AND dailytest_id=%s AND UNIX_TIMESTAMP(entered_at)>(".Date::Now()->toUT()."-%s*60*60*24) ORDER BY entered_at DESC", $userID, $dailytestID, $daysBack);
		$obj = null;
		$list = array();
		while($obj = mysql_fetch_object($rs)) {
			$list[] = DailytestStatus::getObjFromDBData($obj);
		}
		return $list;
	}
	public static function getTodayStatus($userID, $dailytestID) {
		$today = Date::now()->toDay();
		$rs = Database::doQuery("SELECT result FROM dailytests_status WHERE user_id=%s AND dailytest_id=%s AND entered_at_day=%s", $userID, $dailytestID, $today);
		return mysql_num_rows($rs)>0;
	}
	public static function setTodayStatus($userID, $dailytestID, $newStatus) {
		$currentStatus = DailytestStatus::getTodayStatus($userID, $dailytestID);
		$today = Date::now()->toDay();
		if($currentStatus!=$newStatus) {
			if($currentStatus) {
				Database::doQuery("DELETE FROM dailytests_status WHERE user_id=%s AND dailytest_id=%s AND entered_at_day=%s", $userID, $dailytestID, $today);
			}
			else {
				Database::doQuery("INSERT INTO dailytests_status (dailytest_id, user_id, result, entered_at, entered_at_day) VALUES (%s, %s, 1, %s, %s)", $dailytestID, $userID, Date::Now(), $today);
			}
		}
		
		User::getObjFromUserID($userID)->updateLastDailyEntry();
	}

	public function __construct($dbData) {
		$this->dailytestID = $dbData->dailytest_id;
		$this->userID = $dbData->user_id;
		$this->result = $dbData->result;
		$this->enteredAt = Date::fromSQLStr($dbData->entered_at);
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