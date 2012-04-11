<?php
require_once(dirname(__FILE__)."/../../common/include/functions.php"); 

class User {
	// private
	const ENUM_VISITS_DAILY = 1;
	const ENUM_VISITS_EVERYFEWDAYS = 2;
	const ENUM_VISITS_WEEKLY = 3;
	const ENUM_VISITS_EVERYFEWWKS = 4;
	const ENUM_VISITS_MONTHLY = 5;
	const NUM_VISITS_TO_TRACK = 20;
	private $id, $authID, $pictureURL, $lastDailyEntry, $visitHistory, $dailyEntryStoryPosted;
	private $firstName, $lastName, $email, $permissions;
	// HACK: this should be done better
	private static function visitHistoryToStr($visitHistory) {
		return serialize($visitHistory);
	}
	private static function visitHistoryFromStr($blob) {
		return unserialize($blob);
	}
	private function save() {
		global $db;
		// currently cannot update intranet auth DB

		// update SG DB
		$visitHistoryStr = User::visitHistoryToStr($this->visitHistory);
		$db->doQuery("UPDATE users SET	visit_history=%s,
										last_daily_entry=%s,
										daily_entry_story_posted=%s
										WHERE id=%s",
										$visitHistoryStr, $this->lastDailyEntry, $this->dailyEntryStoryPosted, $this->id);
	}
	
	// protected
	
	// public
	public static function getObjFromUserID($userID) {
		global $db, $appAuth;
		
		$user = null;
		//$db->debugMode(true);
		$sgObj = $db->doQueryRFR("SELECT * FROM users WHERE id=%s", $userID);
				
		if(!is_null($sgObj)) {
			// HACK: would need to pass in multiple auth ID's in the scenario where there are several auth servers to connect to
			$authArr = $appAuth->getAuthUserDataAgg($sgObj->auth_id);
			$authObj = (object)$authArr;			
			
			$user = new User($authObj, $sgObj);
			
		}
		return $user;
	}
	
	
	public static function getObjFromUserIDAuthData($userID, $authClientData) {
		global $db;
		
		$user = null;
		//$db->debugMode(true);
		$sgObj = $db->doQueryRFR("SELECT * FROM users WHERE id=%s", $userID);
		if(!is_null($sgObj)) {
			$authObj = (object)$authClientData;
			assert(!is_null($authObj) && is_object($authObj));
			$user = new User($authObj, $sgObj);
		}
		return $user;
	}
	public static function createNewForSignup($lastAuthClientUserID, $authClientUserData) {
		global $db;
		$authID = $lastAuthClientUserID;
		$pictureURL = $authClientUserData["pictureURL"];
		$visitHistoryStr = User::visitHistoryToStr(array(Date::now()));
		$fullName = $authClientUserData['firstName']." ".$authClientUserData['lastName'];
		$db->doQuery("INSERT INTO users (auth_id, picture_url, visit_history, full_name) VALUES (%s, %s, %s, %s)", $authID, $pictureURL, $visitHistoryStr, $fullName);
		$newID = mysql_insert_id();
		return $newID;
	}


	// Legacy for the goal page but may perhaps be useful for other pages
	public function adoptGoal($goalID) {
		GoalStatus::userAdoptGoalSimple($this->id, $goalID);
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
	public function __construct($authUserData, $sgDBData) {	
		// data from user DB

		$this->id = $sgDBData->id;
		$this->authID = $sgDBData->auth_id;
		$this->visitHistory = User::visitHistoryFromStr($sgDBData->visit_history);
		$this->lastDailyEntry = Date::fromSQLStr($sgDBData->last_daily_entry);
		$this->dailyEntryStoryPosted = boolval($sgDBData->daily_entry_story_posted);
		$this->permissions = $sgDBData->permissions;
		
		// data from auth DB
		$this->firstName = $authUserData->firstName;
		$this->lastName = $authUserData->lastName;
		$this->email = $authUserData->email;
		$this->pictureURL = $authUserData->pictureURL;
	}
	
		# Stubs with whitelisting for some fields
	public function __get($name) {
		static $publicGetVars = array("id","pictureURL","firstName","lastName","email","authID","permissions");
		
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
	
	# Stub for setting values
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
			echo GPC::strToPrintable($message->text);
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

	// public
	public $id, $name, $description, $userID, $created_by, $date_created;
	
	// protected
	
	// public
	public static function createNew($name, $description, $userID) {
		global $db;
		
		$db->doQuery("INSERT INTO goals (name, description, created_by, date_created) VALUES (%s,%s,%s, NOW())", $name, $description, $userID);
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getObjFromGoalID($goalID) {
		global $db;
							
		$goal = new Goal($db->doQueryRFR("SELECT * FROM goals WHERE id=%s", $goalID));
		
		return $goal;
	}


	public static function getFullObjFromGoalID($goalID,$userID) {
		global $db;
	
		$rs = $db->doQuery("SELECT * FROM goals WHERE id=%s", $goalID);

		while($obj = mysql_fetch_object($rs)) {
			
			$goal_status_rs = $db->doQuery("SELECT display_style, description FROM goals_status WHERE user_id=%s AND goal_id = %s", $userID, $goalID);
			$goal = new Goal($obj);

			while($obj_two = mysql_fetch_object($goal_status_rs)) {

				$goal_display_style = $obj_two->display_style;
				$goal_desc = $obj_two->description;

			}

			if($goal_display_style == 1){
				$display_style = '1';
			}else{
				$display_style = '0';
			}

			if(empty($goal_desc)){
				$sub_description = 'none';
			}else{
				$sub_description = $goal_desc;
			}
			
			$goal_obj = new stdClass;
			$goal_obj->goal = $goal;
			$goal_obj->display_style = $display_style;
			$goal_obj->sub_description = $sub_description;

		}
				
		
		return $goal_obj;
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
		$this->created_by = $dbData->created_by;
		// &&& HAVE SOMETHING TO ADD CREATED_BY ID
	}
	
	
		# Stubs with whitelisting for some fields
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
	
	# Stub for setting values
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
	const VIEWFORMAT_EVENT = "svf_event";
	const VIEWFORMAT_DAILYSCORE = "svf_dailyscore";

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
	abstract public function getViewFormat();
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
	
	# Stub for setting values
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
	
	// public
	const STORY_TYPENAME = 'event';
	public function getViewFormat() {
		return Story::VIEWFORMAT_EVENT;
	}
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
	
		# Stubs with whitelisting for some fields
	public function __get($name) {
		static $publicGetVars = array("enteredAt","id","newLevel","oldLevel","letterGrade","description","userID", "goalID");
		
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
	
	# Stub for setting values
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

class DailyscoreStory extends Story {
	// private
	private $progress;
	private static function progressFromStr($str) {
		return unserialize($str);
	}
	private static function progressToStr($progress) {
		return serialize($progress);
	}
	
	// public
	public function getViewFormat() {
		return Story::VIEWFORMAT_DAILYSCORE;
	}
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
};

class Dailytest {
	
	// &&&&&&
	// public
	public $id, $goalID, $name, $description, $strategy_type, $userID, $strategyID, $strategy_active, $created_by, $stashedStyleArray;

	// private
	
	// protected
	
	// public
	public static function createNew($goalID, $name, $description, $type, $userID) {
		global $db;
		
		if(empty($description)){ $description = ''; }		
		$db->doQuery("INSERT INTO strategies (goal_id, name, description, strategy_type, created_by, date_created) VALUES (%s, %s, %s, %s, %s, NOW())", $goalID, $name, $description, $type, $userID);
		
		$newID = mysql_insert_id();
		return $newID;
	}


	public static function adoptStrategy($userID, $strategyID, $goalID) {
		global $db;
		
		$db->doQuery("INSERT INTO user_strategies (user_id, strategy_id, goal_id, is_active, date_created) VALUES (%s, %s, %s, 1, NOW()) ON DUPLICATE KEY UPDATE is_active =1, date_created = NOW()", $userID, $strategyID, $goalID);
				
	}	

	public static function editStrategy($userID, $strategyID, $goalID, $newStrategyName, $strategyType) {
		global $db;
		
		// Change the is_active value of the current strategy in user_strategies to 0  
		$db->doQuery("UPDATE user_strategies SET is_active = 0 WHERE strategy_id = %s", $strategyID);

		// Check if there is already a strategy by the new name in the DB
		$isNewStrategy = $db->doQuery("SELECT * FROM strategies WHERE name = %s", $newStrategyName);
		if(is_null($isNewStrategy)) {
		
			// If no, create a new strategy in the strategies table and then 
			if(empty($description)){ $description = ''; }		
			$db->doQuery("INSERT INTO strategies (goal_id, name, description, strategy_type, created_by, date_created) VALUES (%s, %s, %s, %s, %s, NOW())", $goalID, $newStrategyName, $description, $strategyType, $userID);
			$newStrategyID = mysql_insert_id();
			// INSERT into the user_strategies row with the new strategy information
			$db->doQuery("INSERT INTO user_strategies (user_id, strategy_id, goal_id, is_active, date_created) VALUES (%s, %s, %s, 1, NOW()) ON DUPLICATE KEY UPDATE is_active =1, date_created = NOW()", $userID, $newStrategyID, $goalID);

		}else{
			while($obj = mysql_fetch_object($isNewStrategy)) {
				$newStrategyID = $obj->id;
			}
		
		// If yes INSERT or ON DUPLICATE UPDATE the user_strategies row with the new strategy information
			$db->doQuery("INSERT INTO user_strategies (user_id, strategy_id, goal_id, is_active, date_created) VALUES (%s, %s, %s, 1, NOW()) ON DUPLICATE KEY UPDATE is_active =1, date_created = NOW()", $userID, $newStrategyID, $goalID);
		
		}
		
				
	}	

	public static function removeStrategy($userID, $strategyID, $goalID) {
		global $db;

		$db->doQuery("UPDATE user_strategies SET is_active = 0, date_created = NOW() WHERE user_id = %s AND goal_id = %s AND strategy_id = %s", $userID, $goalID, $strategyID);
				
	}	

	public static function reAdoptStrategy($userID, $strategyID, $goalID) {
		global $db;
		
		$db->doQuery("UPDATE user_strategies SET is_active = 1, date_created = NOW() WHERE user_id = %s AND goal_id = %s AND strategy_id = %s", $userID, $goalID, $strategyID);
		
	}	

	public static function getListFromGoalID($goalID,$userID) {
		global $db;

		// This is actually getting the list of strategies that are active for a user
		$rs = $db->doQuery("SELECT strategies.id, strategies.goal_id, strategies.name, strategies.description, strategies.strategy_type, strategies.created_by, strategies.date_created FROM strategies, user_strategies WHERE strategies.goal_id=%s AND strategies.goal_id = user_strategies.goal_id AND user_strategies.user_id = %s AND strategies.id = user_strategies.strategy_id AND user_strategies.is_active = 1", $goalID, $userID );
		$list = array();
		$obj = null;
		
		while($obj = mysql_fetch_object($rs)) {

				$strategy_is_active = $db->doQueryOne("SELECT is_active FROM user_strategies WHERE user_id=%s AND strategy_id = %s AND goal_id = %s", $userID, $obj->id, $goalID);
				if(!empty($strategy_is_active)){
					$strategy_active = '1';
				}else{
					$strategy_active = '0';
				}
				
				$obj->strategy_active = $strategy_active;						


			$list[] = new Dailytest($obj);
		}

		return $list;
	}
	
	
	// &&&&&&
	public function __construct($dbData) {
		$this->id = $dbData->id;
		$this->goalID = $dbData->goal_id;
		$this->name = $dbData->name;
		$this->description = $dbData->description;
		$this->strategy_type = $dbData->strategy_type;
		$this->strategy_active = $dbData->strategy_active;
		$this->stashedStyleArray = null;
	}
		
	// HACK: there has GOT to be a better way to do this...
	public function getStashedStyleArray() {
		return $this->stashedStyleArray;
	}
	public function setStashedStyleArray($newStyleArray) {
		$this->stashedStyleArray = $newStyleArray;
	}
	
	
	# Stubs with whitelisting for some fields
	public function __get($name) {
		static $publicGetVars = array("id","goalID","name","description","strategy_type");
		
		$returnVal = null;
		if(in_array($name, $publicGetVars)) {
			$returnVal = $this->$name;
		}
		else {
			assert(false);
		}
		return $returnVal;
	}
	

	# Stub for setting values

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

class KPI {
	
	// private
	public $kpi_name, $kpi_desc, $kpi_active, $id, $kpi_tests;
	
	
	// protected
	
	private $goalID, $name, $description, $testDescription, $testName, $testFrequency, $kpi_id, $newID, $kpiID, $userID, $newTest;
	
	// public
	public static function createNew($goalID, $name, $description, $testDescription, $testName, $testFrequency, $userID) {
		global $db;
		
		$info = array();
		
		if(empty($description)){ $description = ''; }
		$db->doQuery("INSERT INTO kpis (kpi_name, kpi_desc, created_by, date_created) VALUES (%s, %s, %s, NOW())", $name, $description, $userID);
		$kpiID = mysql_insert_id();
		
		$info[] = $kpiID;

		if(empty($testDescription)){ $testDescription = ''; }
		if(empty($testFrequency)){ $testFrequency = 30; }
		$db->doQuery("INSERT INTO kpi_tests (kpi_id, test_description, test_name, test_frequency, created_by, date_created) VALUES (%s, %s, %s, %s, %s, NOW())", $kpiID, $testDescription, $testName, $testFrequency, $userID);
		$testID = mysql_insert_id();
	
		$info[] = $testID;
			
		$db->doQuery("INSERT INTO goals_to_kpis (goal_id, kpi_id, associated_by, date_created) VALUES (%s, %s, %s, NOW())", $goalID, $kpiID, $userID);
		
		return $info;
	}	

	public static function adoptTest($userID, $kpiID, $goalID, $testID) {
		global $db;

		$db->doQuery("INSERT INTO user_tests (user_id, goal_id, kpi_id, test_id, is_active, date_created) VALUES (%s, %s, %s, %s, 1, NOW())", $userID, $goalID, $kpiID, $testID);
		
	}	

	public static function adoptKPI($userID, $kpiID, $goalID) {
		global $db;
				
		$db->doQuery("INSERT INTO user_kpis (user_id, kpi_id, goal_id, is_active, date_created) VALUES (%s, %s, %s, 1, NOW()) ON DUPLICATE KEY UPDATE is_active = %s, date_created = NOW()", $userID, $kpiID, $goalID, 1);
				
	}	

	public static function removeKPI($userID, $kpiID, $goalID) {
		global $db;

		$db->doQuery("UPDATE user_kpis SET is_active = 0, date_created = NOW() WHERE user_id = %s AND kpi_id = %s AND goal_id = %s", $userID, $kpiID, $goalID);
		
		$db->doQuery("UPDATE user_tests SET is_active = 0 , date_created = NOW() WHERE user_id = %s AND kpi_id = %s AND goal_id = %s", $userID, $kpiID, $goalID);
		
	}	

	public static function reAdoptKPI($userID, $kpiID, $goalID) {
		global $db;
		
		$db->doQuery("UPDATE user_kpis SET is_active = 1, date_created = NOW() WHERE user_id = %s AND kpi_id = %s AND goal_id = %s", $userID, $kpiID, $goalID);
		
		$db->doQuery("UPDATE user_tests SET is_active = 1 , date_created = NOW() WHERE user_id = %s AND kpi_id = %s AND goal_id = %s", $userID, $kpiID, $goalID);
		
	}	

	public static function modifyTest($userID, $kpiID, $goalID, $testID, $newActiveStatus) {
		global $db;
				
		$db->doQuery("INSERT INTO user_tests (user_id, goal_id, kpi_id, test_id, is_active, date_created) VALUES (%s, %s, %s, %s, 1, NOW()) ON DUPLICATE KEY UPDATE is_active = %s , date_created = NOW()", $userID, $goalID, $kpiID, $testID, $newActiveStatus);

	}	
	
	public static function getListFromGoalID($goalID,$userID) {
		global $db;
		
		# select all kpi_ids that are associated with the goal
		$rs = $db->doQuery("SELECT kpi_id FROM goals_to_kpis WHERE goal_id=%s", $goalID);
		$list = array();
		$tests = array();
		$obj = null;
		$kpi_active = null;

		while($obj = mysql_fetch_object($rs)) {
			# for each kpi_id, get the kpi_name and description
			$tests = array();
			$kpi_id = $obj->kpi_id;
			
			$res = $db->doQuery("SELECT * FROM kpis WHERE id=%s", $obj->kpi_id);
					while($obj_two = mysql_fetch_object($res)) {
						$kpi_name = $obj_two->kpi_name;
						$kpi_desc = $obj_two->kpi_desc;
						
						$kpi_is_active = $db->doQueryOne("SELECT is_active FROM user_kpis WHERE user_id=%s AND kpi_id = %s AND goal_id = %s", $userID, $obj->kpi_id, $goalID);
						if(!empty($kpi_is_active)){
							$kpi_active = '1';
						}else{
							$kpi_active = '0';
						}
					}
					
			# for each kpi_id, get all kpi_test data and put it in an array
			$rds = $db->doQuery("SELECT * FROM kpi_tests WHERE kpi_id=%s", $obj->kpi_id);
					while($obj_three = mysql_fetch_object($rds)) {

						$test_is_active = $db->doQueryOne("SELECT is_active FROM user_tests WHERE user_id=%s AND kpi_id = %s AND goal_id = %s AND test_id = %s", $userID, $obj->kpi_id, $goalID, $obj_three->id);
						if(!empty($test_is_active)){
							$test_active = '1';
						}else{
							$test_active = '0';
						}
						
						$obj_three->active = $test_active;						
						$tests[] = $obj_three;
					
					}
			$kpi_obj = new stdClass;
			$kpi_obj->id = $kpi_id;
			$kpi_obj->kpi_name = $kpi_name;
			$kpi_obj->kpi_desc = $kpi_desc;
			$kpi_obj->kpi_active = $kpi_active;
			$kpi_obj->kpi_tests = $tests;
		
			$list[] = new KPI($kpi_obj);

		}
	
		return $list;
	}
	
	public function __construct($dbData) {
		$this->id = $dbData->id;
		$this->kpi_name = $dbData->kpi_name;
		$this->kpi_desc = $dbData->kpi_desc;
		$this->kpi_active = $dbData->kpi_active;
		$this->kpi_tests = $dbData->kpi_tests;
	}
	
	# Stubs with whitelisting for some fields
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
	
	# Stub for setting values
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
	private $goalID, $userID, $kpiID, $testID, $level, $isActive, $isPublic, $positionIndex;

	// public
	public static function doesUserHaveGoal($userID, $goalID) {
		global $db;
		
		$rs = $db->doQuery("SELECT goal_id FROM goals_status WHERE user_id=%s AND goal_id=%s AND is_active = 1", $userID, $goalID);
		$userHasGoal = mysql_num_rows($rs)>0;
		
		return $userHasGoal;
	}
	
	public static function setTracking($userID, $goalID, $displayStyle) {
		global $db;
		
		$db->doQuery("UPDATE goals_status SET display_style = %s, latest_change = NOW() WHERE user_id=%s AND goal_id=%s", $displayStyle, $userID, $goalID);		
	}

	
	public static function alterDescription($userID, $goalID, $description) {
		global $db;
		
		
		$db->doQuery("UPDATE goals_status SET description = %s, latest_change = NOW() WHERE user_id=%s AND goal_id=%s", $description, $userID, $goalID);		
	}
	
	public static function getNumUserGoals($userID) {
		global $db;
	
		$numGoals = $db->doQueryOne("SELECT COUNT(goal_id) FROM goals_status WHERE user_id=%s AND is_active = 1", $userID);
		return $numGoals;
	}
	public static function getUserGoalLevel($userID, $goalID) {
		global $db;
		
		$level = $db->doQueryOne("SELECT level FROM goals_status WHERE user_id=%s AND goal_id=%s", $userID, $goalID);
		return $level;
	}
	public static function setUserGoalLevel($userID, $goalID, $newLevel) {
		global $db;
		
		$db->doQuery("UPDATE goals_status SET level=%s, latest_change = NOW() WHERE user_id=%s AND goal_id=%s", $newLevel, $userID, $goalID);
	}
	public static function getAverageGoalScore($goalID) {
		global $db;
		
		return $db->doQueryOne("SELECT AVG(level) FROM goals_status WHERE goal_id=%s", $goalID);
	}
	
	public static function userAdoptGoalSimple($userID, $goalID) {
		global $db;
		
		$nextIndex = $db->doQueryOne("SELECT MAX(position_index)+1 FROM goals_status WHERE goal_id=%s AND user_id=%s", $goalID, $userID);
		if(is_null($nextIndex)) {
			$nextIndex=0;
		}
		 
		// by default all goals are public until we put up goal adoption page
		$reportingStyle = 0;
		$goalDesc = '';

		$db->doQuery("INSERT INTO goals_status (goal_id, user_id, level, description, is_active, is_public, position_index, display_style, date_created, latest_change) VALUES (%s, %s, 5, %s, TRUE, TRUE, %s, %s, NOW(), NOW()) ON DUPLICATE KEY UPDATE is_active = 1, latest_change = NOW()", $goalID, $userID, $goalDesc, $nextIndex, $reportingStyle);
	}
	
	public static function userRemoveGoal($userID, $goalID) {
		global $db;
		
		$db->doQuery("UPDATE goals_status SET is_active = 0, latest_change = NOW() WHERE user_id = %s AND goal_id = %s", $userID, $goalID);
	}
	
	public static function userDeleteGoal($userID, $goalID) {
		global $db;
		
		$db->doQuery("UPDATE goals SET is_active = 0 WHERE id = %s", $goalID);
	}
	
	
	public static function getNumGoalAdopters($goalID) {
		global $db;
		
		$rs2 = $db->doQuery("SELECT user_id FROM goals_status WHERE goal_id=%s AND is_active = 1", $goalID);
		$numAdopters = mysql_num_rows($rs2);
		return $numAdopters;
	}
	public static function getObjFromDBData($dbData) {
		return new GoalStatus($dbData);
	}
	public function __construct($dbData) {
		$this->goalID = $dbData->goal_id;
		$this->userID = $dbData->user_id;
		$this->level = $dbData->level;
		$this->isActive = boolval($dbData->is_active);
		$this->isPublic = boolval($dbData->is_public);
		$this->positionIndex = $dbData->position_index;
	}
	public function __get($name) {
		static $publicGetVars = array("isActive", "goalID", "userID", "level");
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
};

class DailytestStatus {

	// private
	private $dailytestID, $userID, $result, $enteredAt;
	
	// protected
	
	// public
	public static function createNew($dailytestID, $userID, $result) {
		global $db;
		
		$db->doQuery("INSERT INTO strategies_log (strategy_id, user_id, input, entered_at) VALUES (%s, %s, %s, %s)", $dailytestID, $userID, $result, Date::Now());
		$newID = mysql_insert_id();
		return $newID;
	}
	public static function getObjFromDBData($dbData) {
		$status = new DailytestStatus($dbData);
		return $status;
	}
	
	public static function getListFromUserID($userID, $dailytestID, $daysBack) {
		global $db;
		
		$rs = $db->doQuery("SELECT * FROM strategies_log WHERE user_id=%s AND strategy_id=%s AND UNIX_TIMESTAMP(entered_at)>(".Date::Now()->toUT()."-%s*60*60*24) ORDER BY entered_at DESC", $userID, $dailytestID, $daysBack);
		
		$obj = null;
		$list = array();
		while($obj = mysql_fetch_object($rs)) {
		
			$list[] = DailytestStatus::getObjFromDBData($obj);
		}
		return $list;
	}
	
	public static function getTodayStatus($userID, $dailytestID, $date) {
		global $db;
		
		//$today = Date::now()->toDay();
		$rs = $db->doQuery("SELECT input FROM strategies_log WHERE user_id=%s AND strategy_id=%s AND entered_at_day=%s", $userID, $dailytestID, $date);
		return mysql_num_rows($rs)>0;
	}
	public static function setTodayStatus($userID, $dailytestID, $newStatus, $date) {
		global $db;
		
		$currentStatus = DailytestStatus::getTodayStatus($userID, $dailytestID, $date);
		$today = $date;
		//Date::now()->toDay();
		if($currentStatus!=$newStatus) {
			if($currentStatus) {
				$db->doQuery("DELETE FROM strategies_log WHERE user_id=%s AND strategy_id=%s AND entered_at_day=%s", $userID, $dailytestID, $today);
			}
			else {
				$db->doQuery("INSERT INTO strategies_log (strategy_id, user_id, input, entered_at, entered_at_day) VALUES (%s, %s, 1, %s, %s)", $dailytestID, $userID, Date::Now(), $today);
			}
		}
		
		User::getObjFromUserID($userID)->updateLastDailyEntry();
	}

	public function __construct($dbData) {
		$this->dailytestID = $dbData->strategy_id;
		$this->userID = $dbData->user_id;
		$this->result = $dbData->input;
		$this->enteredAt = Date::fromSQLStr($dbData->entered_at);
	}
	
	# Stub with whitelisting for some fields
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
	
	# Stub for setting values
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
