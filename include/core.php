<?php

const CLASSNAME_DATETIME='Date';
class Date {

	// private
	private $ut;
	private function __construct($ut) {
		$this->ut = $ut;
	}
	
	// protected
	
	// public
	public static function setTimezone() {
		date_default_timezone_set('America/Los_Angeles');
	}
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

class User {

	// private
	const ENUM_VISITS_DAILY = 1;
	const ENUM_VISITS_EVERYFEWDAYS = 2;
	const ENUM_VISITS_WEEKLY = 3;
	const ENUM_VISITS_EVERYFEWWKS = 4;
	const ENUM_VISITS_MONTHLY = 5;
	const NUM_VISITS_TO_TRACK = 20;
	private $id, $authID, $pictureURL, $lastDailyEntry, $visitHistory, $dailyEntryStoryPosted;
	private $firstName, $lastName, $email;
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

?>
					<!-- Case -->
					<div class="case">
						<!-- Users -->
						<div class="users">
<?php
		if(count($userListNonletters)>0) {
			echo "<p>?</p>";
			foreach($userListNonletters as $lUser) {
				User::printCard($lUser);
			}
		}
		$lastLetter = "?";
		foreach($userListLetters as $lUser) {
			$currentLetter = strtoupper(substr($lUser->lastName, 0, 1));
			if($currentLetter != $lastLetter) {
				$lastLetter = $currentLetter;
				echo "<p>$currentLetter</p>";
			}
			User::printCard($lUser);
		}
?>
							<div class="cl">&nbsp;</div>
						</div>
						<!-- End Users -->
					</div>
					<!-- End Case -->
<?php
	}
	private function save() {
		global $db;
		// currently cannot update intranet auth DB

		// update SG DB
		$visitHistoryStr = User::visitHistoryToStr($this->visitHistory);
		$db->doQuery("UPDATE users SET	picture_url=%s,
											visit_history=%s,
											last_daily_entry=%s,
											daily_entry_story_posted=%s
											WHERE id=%s",
											$this->pictureURL, $visitHistoryStr, $this->lastDailyEntry, $this->dailyEntryStoryPosted, $this->id);
	}
	
	// protected
	
	// public
	public static function getObjFromUserID($userID) {
		global $db, $intranetAuth;
		
		$user = null;
		//$db->debugMode(true);
		$sgObj = $db->doQueryRFR("SELECT * FROM users WHERE id=%s", $userID);
		if(!is_null($sgObj)) {
			$authObj = $intranetAuth->getUserVars($sgObj->auth_id);
			assert(!is_null($authObj) && is_object($authObj));
			$user = new User($authObj, $sgObj);
		}
		return $user;
	}

	public static function createNewForSignup($appData) {
		global $intranetAuth, $db;
		$pictureURL = $appData["pictureURL"];

		$authID = $intranetAuth->getUserID();
		$fullName = $intranetAuth->getUserFullName();
		$visitHistoryStr = User::visitHistoryToStr(array(Date::now()));
		$db->doQuery("INSERT INTO users (auth_id, picture_url, visit_history, full_name) VALUES (%s, %s, %s, %s)", $authID, $pictureURL, $visitHistoryStr, $fullName);
		$newID = mysql_insert_id();
		return $newID;

	}
	public static function printListAll() {
		global $db;
		
		$rs = $db->doQuery("SELECT id FROM users");
		$userIDList = array();
		while($obj = mysql_fetch_object($rs)) {
			$userIDList[] = $obj->id;
		}
		User::printUserListBase($userIDList);
	}
	public static function printListByGoal($goalID) {
		global $db;
		
		$rs = $db->doQuery("SELECT user_id FROM goals_status WHERE goal_id=%s", $goalID);
		$userIDList = array();
		while($obj = mysql_fetch_object($rs)) {
			$userIDList[] = $obj->user_id;
		}
		User::printUserListBase($userIDList);
	}
	public static function printCard($user) {
		assert(!is_null($user));
		$profLink = $user->getPagePath();
		$numGoals = GoalStatus::getNumUserGoals($user->id);
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
?>
							<!-- Card -->
							<div class="card">
								<div class="user-image">
						    		<a href="<?php echo $profLink;?>"><img src="<?php echo htmlspecialchars($user->pictureURL);?>" alt="<?php echo "$user->firstName $user->lastName";?>" /></a>
						    	</div>
						    	<div class="info">
						    		<a href="<?php echo $profLink;?>"><?php echo "$user->firstName <b>$user->lastName</b>";?></a>
						    		<span><?php echo $numGoals;?> goals</span>
						    		<span><?php echo $visitFreqText;?></span>
						    	</div>
						    	<div class="cl">&nbsp;</div>
							</div>
							<!-- End Card -->
<?php
	}

	public function adoptGoal($goalID) {
		GoalStatus::userAdoptGoal($this->id, $goalID);
	}
	public function updateLastDailyEntry() {
		$this->lastDailyEntry = Date::now();
		$this->dailyEntryStoryPosted = false;
		$this->save();
	}
	public function hasMadeDailyEntry() {
		return floor($this->lastDailyEntry->diffDays(Date::now()))==0;
	}
	public function trackVisit() {
		$needUpdate = true;
		if(!is_null($this->visitHistory) && (count($this->visitHistory)>0)) {
			$lastVisit = $this->visitHistory[0];
			$today = Date::now();
			if(floor($today->diffDays($lastVisit))==0) {
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
		$this->email = $authDBData->email;
	}
	public function __get($name) {
		static $publicGetVars = array("id","pictureURL","firstName","lastName","email","authID");
		
		$returnVal = null;
		if(in_array($name, $publicGetVars)) {
			$returnVal = $this->$name;
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
	private static $messages=null;
	private static $init=false;
	const SESSVAR_STATUSMESSAGES = 'superhumanGoals_statusMessages3';
	
	private static function save() {
		Session::setVar(StatusMessages::SESSVAR_STATUSMESSAGES, StatusMessages::$messages);
	}
	private function __construct() {} // static-only class
		
	// protected
	
	// public
	public static function init() {
		assert(Session::isStarted());
		$messages = array();
		if(Session::issetVar(StatusMessages::SESSVAR_STATUSMESSAGES)) {
			$messages = Session::getVar(StatusMessages::SESSVAR_STATUSMESSAGES);
		}
		StatusMessages::$messages = $messages;
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
?>
					<!-- Case -->
					<div class="case boxes">
<?php
		foreach(StatusMessages::$messages as $message) {
			$style = "";
			switch($message->type) {
				case StatusMessage::ENUM_GOOD:
					$style="good";
					break;
				default:
				case StatusMessage::ENUM_BAD:
					$style="bad";
					break;
			}
?>
						<!-- Box -->
						<div class="status-message-<?php echo $style;?>">
<?php
			echo htmlspecialchars($message->text);
?>
						</div>
						<!-- End Post -->
<?php
		}
?>
					</div>
					<!-- End Case -->
<?php
		Session::clearVar(StatusMessages::SESSVAR_STATUSMESSAGES);
	}

};

class Goal {

	// private
	private $id, $name, $description;
	
	// protected
	
	// public
	public static function createNew($name, $description) {
		global $db;
		
		$db->doQuery("INSERT INTO goals (name, description) VALUES (%s,%s)", $name, $description);
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getObjFromGoalID($goalID) {
		global $db;
		
		$goal = new Goal($db->doQueryRFR("SELECT * FROM goals WHERE id=%s", $goalID));
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
		global $db;
		
		$today = Date::now()->toDay();
		$db->doQuery("INSERT INTO stories (user_id, type, is_public, entered_at, event_goal_id, event_new_level, event_old_level, event_letter_score, event_description, entered_at_day)
							VALUES (%s, '".EventStory::STORY_TYPENAME."', %s, %s, %s, %s, %s, %s, %s, %s)",
							$userID, $isPublic, Date::Now(), $goalID, $newLevel, $oldLevel, $letterGrade, $description, $today);
	}
	public static function getTodayStory($userID, $goalID) {
		global $db;
		
		$today = Date::now()->toDay();
		$dbData = $db->doQueryRFR("SELECT * FROM stories WHERE user_id=%s AND event_goal_id=%s AND entered_at_day=%s", $userID, $goalID, $today);
		if(is_null($dbData)) {
			return null;
		}
		return EventStory::getObjFromDBData($dbData);
	}
	public static function createNewOrUpdate($userID, $goalID, $newLevel, $oldLevel, $letterGrade, $why) {
		global $db;
		
		if($why=="") {
			return;
		}

		$story = EventStory::getTodayStory($userID, $goalID);
		if(is_null($story)) {
			EventStory::createNew($userID, true, $goalID, $newLevel, $oldLevel, $letterGrade, $why);
		}
		else {
			$db->doQuery("UPDATE stories SET event_new_level=%s, event_letter_score=%s, event_description=%s, entered_at=%s WHERE id=%s", $newLevel, $letterGrade, $why, Date::Now(), $story->id);
		}
		
		$user = User::getObjFromUserID($userID);
		$user->updateLastDailyEntry();
	}
	public static function getObjFromDBData($dbData) {
		return new EventStory($dbData);
	}
	public static function getLevelHistory($userID, $goalID, $daysBack) {
		global $db;
		
		$rs = $db->doQuery("SELECT entered_at_day, event_new_level, event_old_level FROM stories WHERE user_id=%s AND event_goal_id=%s AND UNIX_TIMESTAMP(entered_at)>(".Date::Now()->toUT()."-%s*60*60*24) ORDER BY entered_at DESC", $userID, $goalID, $daysBack);

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
		$changeWord = "raised";
		if($this->newLevel<$this->oldLevel) {
			$changeWord = "lowered";
		}
		$timeSinceStr = $this->enteredAt->timeSince();
		$goodBad="bad";
		if(($this->letterGrade=="A") || ($this->letterGrade=="B")) {
			$goodBad="good";
		}
?>
					<!-- Case -->
					<div class="case">
						<!-- Post -->
						<div class="post">
							<div class="user-image">
								<a href="<?php echo $userPagePath; ?>"><img src="<?php echo htmlspecialchars($user->pictureURL); ?>" alt="<?php echo "$user->firstName $user->lastName"; ?>" />
							</div>
							<div class="cnt">
								<p class="post-title"><a href="<?php echo $userPagePath; ?>"><?php echo "$user->firstName $user->lastName"; ?></a> <?php echo $changeWord; ?> his level for <a href="<?php echo $goalPagePath; ?>"><?php echo htmlspecialchars($goal->name); ?></a> from <?php echo $this->oldLevel; ?> to <?php echo $this->newLevel; ?>.</p>
								<div class="quote-image-<?php echo $goodBad;?>">
									<span><?php echo $this->letterGrade; ?></span>
								</div>
								<div class="quote">
									<p><?php echo htmlspecialchars($this->description); ?></p>
									<span class="time"><?php echo $timeSinceStr; ?> ago</span>
								</div>
								<div class="cl">&nbsp;</div>
							</div>
							<div class="cl">&nbsp;</div>
						</div>
						<!-- End Post -->
					</div>
					<!-- End Case -->
<?php
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
		global $db;
		
		$progressStr = DailyscoreStory::progressToStr($goalsTouched);
		$db->doQuery("INSERT INTO stories (user_id, type, is_public, entered_at, dailyscore_progress) VALUES 
							(%s, '".DailyscoreStory::STORY_TYPENAME."', %s, %s, %s)",
							$userID, $isPublic, Date::Now(), $progressStr);
	}
	public function __construct($dbData) {
		parent::__construct($dbData);
		
		$this->progress = DailyscoreStory::progressFromStr($dbData->dailyscore_progress);
	}
	public function printStory() {
		global $db;
		
		$user = User::getObjFromUserID($this->userID);
		$userPagePath = $user->getPagePath();
		$totalGoals = $db->doQueryOne("SELECT COUNT(*) FROM goals_status WHERE user_id=%s", $user->id);
		$numGoalsTouched = count($this->progress);
		$score = floor(($numGoalsTouched/$totalGoals)*100);
		$timeSinceStr = $this->enteredAt->timeSince();
		$goodBad="bad";
		if($score>70) {
			$goodBad="good";
		}
?>
					<!-- Case -->
					<div class="case">
						<!-- Post -->
						<div class="post">
							<div class="user-image">
								<a href="<?php echo $userPagePath; ?>"><img src="<?php echo htmlspecialchars($user->pictureURL); ?>" alt="<?php echo "$user->firstName $user->lastName"; ?>" />
							</div>
							<div class="cnt">
								<p class="post-title"><a href="<?php echo $userPagePath; ?>"><?php echo "$user->firstName $user->lastName"; ?></a> just entered daily goal progress, touching <?php echo $numGoalsTouched; ?> out of <?php echo $totalGoals; ?> of their goals.</p>
								<div class="result-image-<?php echo $goodBad;?>">
									<span><?php echo $score; ?><span class="sub">%</span></span>
								</div>
								<div class="result">
									<p>
<?php
		$goalList=array();
		foreach($progress as $goalID) {
			$goal = Goal::getObjFromGoalID($goalID);
			$goalPagePath = $goal->getPagePath();
			$goalList[] = "<a href='$goalPagePath'>".htmlspecialchars($goal->name)."</a>";
		}
		echo implode(", ",$goalList);
?>
									</p>
									<span class="time"><?php echo $timeSinceStr; ?> ago</span>
								</div>
								<div class="cl">&nbsp;</div>
							</div>
							<div class="cl">&nbsp;</div>
						</div>
						<!-- End Post -->
					</div>
					<!-- End Case -->
<?php
	}
};

class Dailytest {
	
	// private
	private $id, $goalID, $name, $description;
	
	// protected
	
	// public
	public static function createNew($goalID, $name, $description) {
		global $db;
		
		$db->doQuery("INSERT INTO dailytests (goal_id, name, description) VALUES (%s, %s, %s)", $goalID, $name, $description);
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getListFromGoalID($goalID) {
		global $db;
		
		$rs = $db->doQuery("SELECT * FROM dailytests WHERE goal_id=%s", $goalID);
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
		global $db;
		
		$rs = $db->doQuery("SELECT goal_id FROM goals_status WHERE user_id=%s AND goal_id=%s", $userID, $goalID);
		$userHasGoal = mysql_num_rows($rs)>0;
		return $userHasGoal;
	}
	public static function getNumUserGoals($userID) {
		global $db;
	
		$numGoals = $db->doQueryOne("SELECT COUNT(goal_id) FROM goals_status WHERE user_id=%s", $userID);
		return $numGoals;
	}
	public static function getUserGoalLevel($userID, $goalID) {
		global $db;
		
		$level = $db->doQueryOne("SELECT level FROM goals_status WHERE user_id=%s AND goal_id=%s", $userID, $goalID);
		return $level;
	}
	public static function setUserGoalLevel($userID, $goalID, $newLevel) {
		global $db;
		
		$db->doQuery("UPDATE goals_status SET level=%s WHERE user_id=%s AND goal_id=%s", $newLevel, $userID, $goalID);
	}
	public static function getAverageGoalScore($goalID) {
		global $db;
		
		return $db->doQueryOne("SELECT AVG(level) FROM goals_status WHERE goal_id=%s", $goalID);
	}
	public static function userAdoptGoal($userID, $goalID) {
		global $db;
		
		$nextIndex = $db->doQueryOne("SELECT MAX(position_index)+1 FROM goals_status WHERE goal_id=%s AND user_id=%s", $goalID, $userID);
		if(is_null($nextIndex)) {
			$nextIndex=0;
		}
		// by default all goals are public until we put up goal adoption page
		$db->doQuery("INSERT INTO goals_status (goal_id, user_id, level, is_active, is_public, position_index) 
											VALUES (%s, %s, 5, TRUE, TRUE, %s)", $goalID, $userID, $nextIndex);
	}
	public static function getNumGoalAdopters($goalID) {
		global $db;
		
		$rs2 = $db->doQuery("SELECT user_id FROM goals_status WHERE goal_id=%s", $goalID);
		$numAdopters = mysql_num_rows($rs2);
		return $numAdopters;
	}
	public static function getObjFromDBData($dbData) {
		return new GoalStatus($dbData);
	}
	public static function printRowList($userID, $dayUT, $isEditable) {
		global $db;
		
		// ignore dayUT for now
?>
					<!-- Case -->
					<div class="case boxes">
<?php
		$rs = $db->doQuery("SELECT * FROM goals_status WHERE user_id=%s", $userID);
		while($obj = mysql_fetch_object($rs)) {
			$goalStatus = GoalStatus::getObjFromDBData($obj);
			$goalStatus->printRow($isEditable);
		}
?>
					</div>
					<!-- End Case -->
<?php
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
		
		$goal = Goal::getObjFromGoalID($this->goalID);
		$newLevelVal = "";
		$letterGradeVal = "A";
		$whyVal = "";
		$plusButtonDefaultDisplay = "block";
		$eventDivDefaultDisplay = "none";
		if($isEditable) {
			$eventStory = EventStory::getTodayStory($this->userID, $this->goalID);
			if(!is_null($eventStory)) {
				$newLevelVal = $eventStory->newLevel;
				$letterGradeVal = $eventStory->letterGrade;
				$whyVal = htmlspecialchars($eventStory->description);
				$plusButtonDefaultDisplay = "none";
				$eventDivDefaultDisplay = "block";
			}
		}
?>
						<!-- Box -->
						<div class="box">
							<!-- GOAL TITLE & LEVEL -->
							<div class="fitness" style="width:120px">
								<a href="<?php echo $goal->getPagePath();?>" class="title"><?php echo htmlspecialchars($goal->name);?></a>
								<span class="number" id="currentLevel<?php echo $rowID;?>"><?php echo $this->level;?> <a href="#" class="add" id="plusButton<?php echo $rowID;?>" onclick="expandEvent<?php echo $rowID;?>();" style="display:<?php echo $plusButtonDefaultDisplay;?>;">Add</a></span>
							</div>
<?php
		static $numDaysBack = 15;
		$dailytests = Dailytest::getListFromGoalID($this->goalID);
		if(count($dailytests)) {
?>
							<!-- ADHERENCE TESTS -->
							<div class="tests">
<?php
			foreach($dailytests as $dailytest) {
				$checkedVal = DailytestStatus::getTodayStatus($this->userID, $dailytest->id)?"checked":"";
				$ajaxSaveDailytestPath = PAGE_AJAX_SAVEDAILYTEST;
?>
								<div class="row">
<?php
				if($isEditable) {
?>
									<script type="text/javascript">										
										var timer=null;
										function onChangeCheck<?php echo $testID; ?>() {
											if(timer != null) {
												clearTimeout(timer);
											}
											timer=setTimeout("doSaveCheck<?php echo $testID; ?>()",200);
										}
										
										function doSaveCheck<?php echo $testID; ?>() {
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
											var isChecked = document.getElementById("testCheck<?php echo $testID; ?>").checked;
											xmlhttp.open("GET","<?php echo $ajaxSaveDailytestPath; ?>?userID=<?php echo $this->userID; ?>&dailytestID=<?php echo $dailytest->id; ?>&result="+(isChecked?"1":"0"),true);
											xmlhttp.send();
										}
									</script>
									<label for="testCheck<?php echo $testID; ?>"><input type="checkbox" value="Check" id="testCheck<?php echo $testID; ?>" <?php echo $checkedVal; ?> onchange="onChangeCheck<?php echo $testID; ?>();" /></label>
<?php
				}
?>									
									<div class="test-cnt">
										<p><?php echo htmlspecialchars($dailytest->name);?></p>
										<div class="scale">
											<ul>
<?php
				$dailytestStatuses = DailytestStatus::getListFromUserID($this->userID, $dailytest->id, $numDaysBack);
				$dailytestStatusDays = array();
				foreach($dailytestStatuses as $dailytestStatus) {
					$dailytestStatusDays[] = $dailytestStatus->enteredAt->toDay();
				}
				for($i=0; $i<$numDaysBack; ++$i) {
					$current = Date::fromUT(time()-($i+1)*60*60*24);
					$currentDay = $current->toDay();
					$style = "";
					if(in_array($currentDay,$dailytestStatusDays)) {
						$style="background: #7bc545;";
					}
?>
												<li><a href="#" style="<?php echo $style; ?>">&nbsp;</a></li>
<?php
				}
?>
											</ul>
											<div class="cl">&nbsp;</div>
										</div>
									</div>
									<div class="cl">&nbsp;</div>
								</div>
<?php
				++$testID;
			}
?>
							</div>
<?php
		}
?>
							<!-- LEVEL HISTORY GRAPH -->
							<div class="placeholder">
								<div class="image">
									<img src="<?php echo "template/createGraphLevelHistory.php?userID=$this->userID&goalID=$this->goalID";?>" id="graph<?php echo $rowID;?>" alt="Level History" />
								</div>
							</div>
							<div class="cl">&nbsp;</div>
<?php
		if($isEditable) {
			$ajaxSaveEventPath = PAGE_AJAX_SAVEEVENT;
			// other vars defined above
			$optionSelectedA = ($letterGradeVal=="A")?"selected":"";
			$optionSelectedB = ($letterGradeVal=="B")?"selected":"";
			$optionSelectedC = ($letterGradeVal=="C")?"selected":"";
			$optionSelectedD = ($letterGradeVal=="D")?"selected":"";
			$optionSelectedF = ($letterGradeVal=="F")?"selected":"";
?>
							<!-- EVENT ENTRY BOX -->
							<script type="text/javascript">
								function expandEvent<?php echo $rowID;?>() {
									document.all['eventDiv<?php echo $rowID;?>'].style="display:block;";
									document.all['plusButton<?php echo $rowID;?>'].style.display = 'none';
								}
								
								var timer=null;
								function onChangeEvent<?php echo $rowID;?>() {
									// validate
									if(parseFloat(document.all['eventNewScore<?php echo $rowID;?>'].value)==0) {
										return;
									}
								
									// trigger save timer
									if(timer != null) {
										clearTimeout(timer);
									}
									timer=setTimeout("doSaveEvent<?php echo $rowID;?>()",200);
								}
								
								function doSaveEvent<?php echo $rowID;?>() {
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
											document.all['currentLevel<?php echo $rowID;?>'].innerHTML = document.all['eventNewScore<?php echo $rowID;?>'].value;
											document.all['graph<?php echo $rowID;?>'].src = "template/createGraphLevelHistory.php?userID=<?php echo $this->userID;?>&goalID=<?php echo $goal->id;?>&r="+(Math.random()*1000000);
										}
									}
									xmlhttp.open("GET","<?php echo $ajaxSaveEventPath;?>?userID=<?php echo $this->userID;?>&goalID=<?php echo $goal->id;?>&oldLevel=<?php echo $this->level;?>&newLevel="+parseFloat(document.getElementById("eventNewScore<?php echo $rowID;?>").value)+"&letterGrade="+document.getElementById("eventLetterGrade<?php echo $rowID;?>").value+"&why="+escape(document.getElementById("eventWhy<?php echo $rowID;?>").value),true);
									xmlhttp.send();
								}
							</script>
							<div class="dd-row" id="eventDiv<?php echo $rowID;?>" style="display:<?php echo $eventDivDefaultDisplay;?>;">
								<div class="left">
									<div class="newscore-row">
										<label for="score-1">New Level:</label><input type="text" class="field" id="eventNewScore<?php echo $rowID;?>" onkeyup="onChangeEvent<?php echo $rowID;?>();" value="<?php echo $newLevelVal;?>" />
										<div class="cl">&nbsp;</div>
									</div>
									<div class="grade-row">
										<label>Letter grade:</label>
										<select name="grade" id="eventLetterGrade<?php echo $rowID;?>" onchange="onChangeEvent<?php echo $rowID;?>();" size="1">
											<option value="A" <?php echo $optionSelectedA;?>>A</option>
											<option value="B" <?php echo $optionSelectedB;?>>B</option>
											<option value="C" <?php echo $optionSelectedC;?>>C</option>
											<option value="D" <?php echo $optionSelectedD;?>>D</option>
											<option value="F" <?php echo $optionSelectedF;?>>F</option>
										</select>
										<div class="cl">&nbsp;</div>
									</div>
								</div>
								<div class="right-side">
									<label for="textarea-1">Why:</label>
									<textarea name="textarea" id="eventWhy<?php echo $rowID;?>" onkeyup="onChangeEvent<?php echo $rowID;?>();" class="field" rows="8" cols="40"><?php echo $whyVal;?></textarea>
								</div>
								<div class="cl">&nbsp;</div>
							</div>
							<!-- End Dd Row -->
<?php
		}
?>
						</div>
						<!-- End Box -->
<?php
		++$rowID;
	}
	
};

class DailytestStatus {

	// private
	private $dailytestID, $userID, $result, $enteredAt;
	
	// protected
	
	// public
	public static function createNew($dailytestID, $userID, $result) {
		global $db;
		
		$db->doQuery("INSERT INTO dailytests_status (dailytest_id, user_id, result, entered_at) VALUES (%s, %s, %s, %s)", $dailytestID, $userID, $result, Date::Now());
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getObjFromDBData($dbData) {
		$status = new DailytestStatus($dbData);
		return $status;
	}
	public static function getListFromUserID($userID, $dailytestID, $daysBack) {
		global $db;
		
		$rs = $db->doQuery("SELECT * FROM dailytests_status WHERE user_id=%s AND dailytest_id=%s AND UNIX_TIMESTAMP(entered_at)>(".Date::Now()->toUT()."-%s*60*60*24) ORDER BY entered_at DESC", $userID, $dailytestID, $daysBack);
		$obj = null;
		$list = array();
		while($obj = mysql_fetch_object($rs)) {
			$list[] = DailytestStatus::getObjFromDBData($obj);
		}
		return $list;
	}
	public static function getTodayStatus($userID, $dailytestID) {
		global $db;
		
		$today = Date::now()->toDay();
		$rs = $db->doQuery("SELECT result FROM dailytests_status WHERE user_id=%s AND dailytest_id=%s AND entered_at_day=%s", $userID, $dailytestID, $today);
		return mysql_num_rows($rs)>0;
	}
	public static function setTodayStatus($userID, $dailytestID, $newStatus) {
		global $db;
		
		$currentStatus = DailytestStatus::getTodayStatus($userID, $dailytestID);
		$today = Date::now()->toDay();
		if($currentStatus!=$newStatus) {
			if($currentStatus) {
				$db->doQuery("DELETE FROM dailytests_status WHERE user_id=%s AND dailytest_id=%s AND entered_at_day=%s", $userID, $dailytestID, $today);
			}
			else {
				$db->doQuery("INSERT INTO dailytests_status (dailytest_id, user_id, result, entered_at, entered_at_day) VALUES (%s, %s, 1, %s, %s)", $dailytestID, $userID, Date::Now(), $today);
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
