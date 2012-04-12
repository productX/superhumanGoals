<?php
require_once(dirname(__FILE__)."/../../common/include/functions.php"); 
require_once("constants.php");
require_once("core.php");
require_once("globals.php");

abstract class BaseView {
	// protected
	protected function storyPrintListForRS($rs) {
		$this->storyPrintListPre();
		$obj=null;
		while($obj=mysql_fetch_object($rs)) {
			$story = Story::getObjFromDBData($obj);
			assert(!is_null($story));
			$this->storyPrintStory($story);
		}
		$this->storyPrintListPost();
	}
	abstract protected function storyPrintListPre();
	abstract protected function storyPrintListPost();
	protected function storyPrintStory($story) {
		$viewFormat = $story->getViewFormat();
				
		switch($viewFormat) {
			case Story::VIEWFORMAT_EVENT:
				$this->storyPrintEventStory(/* dcast to EventStory */$story);
				break;
			case Story::VIEWFORMAT_DAILYSCORE:
				$this->storyPrintDailyscoreStory(/* dcast to DailyscoreStory */$story);
				break;
			default:
				assert(false);
		}
	}
	protected function storyPrintEventStory($eventStory) {
		$user = User::getObjFromUserID($eventStory->userID);
		$goal = Goal::getObjFromGoalID($eventStory->goalID);
		$changeWord = "raised";
		if($eventStory->newLevel<$eventStory->oldLevel) {
			$changeWord = "lowered";
		}
		
		$timeSinceStr = $eventStory->enteredAt->timeSince();
		
		$goodBad="bad";
		
		if(($eventStory->letterGrade=="A") || ($eventStory->letterGrade=="B")) {
			$goodBad="good";
		}
		
		$this->storyPrintEventStoryPrint($user, $goal, $eventStory, $changeWord, $goodBad, $timeSinceStr);
	}
	abstract protected function storyPrintEventStoryPrint($user, $goal, $eventStory, $changeWord, $goodBad, $timeSinceStr);
	protected function storyPrintDailyscoreStory($dailyscoreStory) {
		global $db;
		
		$user = User::getObjFromUserID($dailyscoreStory->userID);
		$totalGoals = $db->doQueryOne("SELECT COUNT(*) FROM goals_status WHERE user_id=%s AND is_active = 1", $user->id);
		$numGoalsTouched = count($dailyscoreStory->progress);
		$score = floor(($numGoalsTouched/$totalGoals)*100);
		$timeSinceStr = $dailyscoreStory->enteredAt->timeSince();
		$goodBad="bad";
		if($score>70) {
			$goodBad="good";
		}
		$goalList=array();
		foreach($progress as $goalID) {
			$goal = Goal::getObjFromGoalID($goalID);
			$goalPagePath = $goal->getPagePath();
			$goalList[] = "<a href='$goalPagePath'>".GPC::strToPrintable($goal->name)."</a>";
		}
		$goalLinkList = implode(", ",$goalList);
		
		$this->storyPrintDailyscoreStoryPrint($user, $numGoalsTouched, $totalGoals, $goodBad, $score, $goalLinkList, $timeSinceStr);
	}
	abstract protected function storyPrintDailyscoreStoryPrint($user, $numGoalsTouched, $totalGoals, $goodBad, $score, $goalLinkList, $timeSinceStr);
	
	// &&&&&&
	protected function goalstatusPrintList($userID, $dayUT, $isEditable, $type) {
		global $db;

		$this->goalstatusPrintPre();
		// ignore dayUT for now
		$rs = $db->doQuery("SELECT * FROM goals_status WHERE user_id=%s AND is_active = 1", $userID);
		while($obj = mysql_fetch_object($rs)) {
			$goalstatus = GoalStatus::getObjFromDBData($obj);
			
			//&&&&&& Gets objects for each adopted goal
			$this->goalstatusPrintGoalstatus($goalstatus, $isEditable, $type);
		}
		$this->goalstatusPrintPost();
	}
	
	
	abstract protected function goalstatusPrintPre();
	abstract protected function goalstatusPrintPost();
	
	
	//&&&&&&
	protected function goalstatusPrintGoalstatus($goalstatus, $isEditable, $type) {
		static $rowID = 1;
		if(!$goalstatus->isActive) {
			return;
		}
		//&&&&&& Get all the information for a particular goal
		$goal = Goal::getObjFromGoalID($goalstatus->goalID);
		$newLevelVal = $goalstatus->level;
		$letterGradeVal = "A";
		$whyVal = "";
		$plusButtonDefaultDisplay = "block";
		$eventDivDefaultDisplay = "none";
		if($isEditable) {
			$eventStory = EventStory::getTodayStory($goalstatus->userID, $goalstatus->goalID);
			if(!is_null($eventStory)) {
				$newLevelVal = $eventStory->newLevel;
				$letterGradeVal = $eventStory->letterGrade;
				$whyVal = GPC::strToPrintable($eventStory->description);
				$plusButtonDefaultDisplay = "none";
				$eventDivDefaultDisplay = "block";
			}
		}
		static $numDaysBack = 6;
		
		//&&&&&& Get all the strategies from the DB
		$dailytests = Dailytest::getListFromGoalID($goalstatus->goalID,$goalstatus->userID);
		
		// HACK: stash styleArray's in the dailytest objects
		foreach($dailytests as $dailytest) {
			//&&&&&& Get all the strategy_log data for each strategy
			$dailytestStatuses = DailytestStatus::getListFromUserID($goalstatus->userID, $dailytest->id, $numDaysBack);
			$dailytestStatusDays = array();
			foreach($dailytestStatuses as $dailytestStatus) {
				$dailytestStatusDays[] = $dailytestStatus->enteredAt->toDay();
			}
			$styleArray = array();
			for($i=0; $i<$numDaysBack; ++$i) {
				$current = Date::fromUT(time()-($i+1)*60*60*24);
				$currentDay = $current->toDay();
				$style = "";
				if(in_array($currentDay,$dailytestStatusDays)) {
					$style="background: red;";
				}
				$styleArray[] = $style;
			}
			$dailytest->setStashedStyleArray($styleArray);
		}
		
		$this->goalstatusPrintGoalstatusPrint($goal, $rowID, $goalstatus, $plusButtonDefaultDisplay, $eventDivDefaultDisplay, $dailytests, $letterGradeVal, $newLevelVal, $whyVal, $isEditable, $type);
		++$rowID;
	}
	abstract protected function goalstatusPrintGoalstatusPrint($goal, $rowID, $goalstatus, $plusButtonDefaultDisplay, $eventDivDefaultDisplay, $dailytests, $letterGradeVal, $newLevelVal, $whyVal, $isEditable, $type);
	protected function goalstatusPrintAjaxEventSave($rowID, $levelEntryID, $changeFuncName, $ajaxSaveEventPath, $goalstatus, $goal, $whyID, $letterGradeID, $levelDisplayID) {
?>
							<script type="text/javascript">								
								var timer=null;
								function <?php echo $changeFuncName.$rowID;?>() {
									// validate
									if(parseFloat(document.all['<?php echo $levelEntryID.$rowID;?>'].value)==0) {
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
<?php
		if($levelDisplayID!="") {
?>
											document.all['<?php echo $levelDisplayID.$rowID;?>'].innerHTML = document.all['<?php echo $levelEntryID.$rowID;?>'].value;
<?php
		}
?>
										}
									}
									xmlhttp.open("GET","<?php echo $ajaxSaveEventPath;?>?userID=<?php echo $goalstatus->userID;?>&goalID=<?php echo $goal->id;?>&oldLevel=<?php echo $goalstatus->level;?>&newLevel="+parseFloat(document.getElementById("<?php echo $levelEntryID.$rowID;?>").value)+"&letterGrade="+document.getElementById("<?php echo $letterGradeID.$rowID;?>").value+"&why="+escape(document.getElementById("<?php echo $whyID.$rowID;?>").value),true);
									xmlhttp.send();
								}
							</script>
<?php
	}
	protected function goalstatusPrintAjaxCheckSave($goalstatus, $dailytest, $testID, $ajaxSaveDailytestPath, $changeFuncName, $checkDivID) {
?>
									<script type="text/javascript">										
										var timer=null;
										function <?php echo $changeFuncName.$testID; ?>() {
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
											var isChecked = document.getElementById("<?php echo $checkDivID.$testID; ?>").checked;
											xmlhttp.open("GET","<?php echo $ajaxSaveDailytestPath; ?>?userID=<?php echo $goalstatus->userID; ?>&dailytestID=<?php echo $dailytest->id; ?>&result="+(isChecked?"1":"0"),true);
											xmlhttp.send();
										}
									</script>
<?php
	}
	abstract protected function userPrintListPre();
	abstract protected function userPrintListPost();
	abstract protected function userPrintListSectionPre($letter);
	abstract protected function userPrintListSectionPost();
	protected function userPrintListBase($userIDList) {
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

		$this->userPrintListPre();
		
		if(count($userListNonletters)>0) {
			$this->userPrintListSectionPre("?");
			foreach($userListNonletters as $lUser) {
				$this->userPrintCard($lUser);
			}
			$this->userPrintListSectionPost();
		}
		$lastLetter = "?";
		foreach($userListLetters as $lUser) {
			$currentLetter = strtoupper(substr($lUser->lastName, 0, 1));
			if($currentLetter != $lastLetter) {
				$lastLetter = $currentLetter;
				$this->userPrintListSectionPost();
				$this->userPrintListSectionPre($currentLetter);
			}
			$this->userPrintCard($lUser);
		}
		
		$this->userPrintListSectionPost();
		$this->userPrintListPost();
	}
	protected function userPrintListAll() {
		global $db;
		
		$rs = $db->doQuery("SELECT id FROM users");
		$userIDList = array();
		while($obj = mysql_fetch_object($rs)) {
			$userIDList[] = $obj->id;
		}

		$this->userPrintListBase($userIDList);

	}
	protected function userPrintListByGoal($goalID) {
		global $db;
		
		$rs = $db->doQuery("SELECT user_id FROM goals_status WHERE goal_id=%s AND is_active = 1", $goalID);
		$userIDList = array();
		while($obj = mysql_fetch_object($rs)) {
			$userIDList[] = $obj->user_id;
		}
		$this->userPrintListBase($userIDList);
	}
	protected function userPrintCard($user) {
		assert(!is_null($user));
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

		$this->userPrintCardPrint($user, $numGoals, $visitFreqText);
	}
	abstract protected function userPrintCardPrint($user, $numGoals, $visitFreqText);

	// public
	abstract public function printHeader($navSelect, $chromeTitleElements, $justOuterChrome);
	abstract public function printFooter($navSelect, $justOuterChrome);
	abstract public function printAboutPage();
	abstract public function printHelpPage();
	abstract public function printAllGoalsPage();
	public function printActivityPage() {
		global $db, $viewSwitch;
		
		$this->printHeader(NAVNAME_ACTIVITY, array(new ChromeTitleElementHeader("Activity")));
		// TEST: bare page
		if($viewSwitch->issetViewFlag("bare")) {
			echo "<p><b><font color='white'>ACTIVITY PAGE</font></b></p>";
			$this->printFooter(NAVNAME_ACTIVITY);
			return;
		}
	
		$this->printActivityPagePre();

		$rs = $db->doQuery("SELECT * FROM stories WHERE is_public=TRUE ORDER BY entered_at DESC LIMIT 100");
		$this->storyPrintListForRS($rs);
		$this->printActivityPagePost();

		$this->printFooter(NAVNAME_ACTIVITY);
	}
	protected function printActivityPagePre() {
	}
	protected function printActivityPagePost() {
	}
	abstract public function printUserPage($viewUser);
	abstract public function printGoalPage($goalID);
	public function printSignupPage() {		
		$this->printHeader(NAVNAME_NONE, array(), true);
		
		$this->printSignupPagePrint();

		$this->printFooter(NAVNAME_NONE, true);
	}
	protected function printSignupPagePrint() {
		global $appAuth;
		?>
					<div class="signup-box">
						<h2>Be Amazing.</h2>
						<a href="#" class="signup-btn">Sign up &raquo;</a>
						<div class="upload-box">
							<p>Signed in as: <strong><?php echo $appAuth->getUserEmail(); ?></strong></p>
							<p>Profile pic URL (50x50):</p>
							<form action="<?php echo PAGE_INDEX; ?>" method="post">
								<!--<input type="file" name="file" id="file" value="" />-->
								<input type="text" name="pictureURL" />
								<input name="submit" type="submit" value="Sign up &raquo;" class="submit-btn" />
								<div class="cl">&nbsp;</div>
							</form>
						</div>
					</div>

		<?php
	}
	abstract public function printAllUsersPage();
};

class WebView extends BaseView {
	// public
	public function printHeader($navSelect, $chromeTitleElements, $justOuterChrome=false) {
		global $user, $appAuth;

	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<title>superhuman</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />

		<link rel="shortcut icon" href="<?php echo BASEPATH_UI;?>/web/css/images/favicon.ico" />
		<link rel="stylesheet" href="<?php echo BASEPATH_UI;?>/web/css/style.css" type="text/css" media="all" />
		<link rel="stylesheet" href="<?php echo BASEPATH_UI;?>/web/css/enhanced.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo BASEPATH_UI;?>/web/css/jquery.jscrollpane.css" type="text/css" media="all" />


		<script src="<?php echo BASEPATH_UI;?>/web/js/jquery-1.7.1.min.js" type="text/javascript"></script>
		<script src="<?php echo BASEPATH_UI;?>/web/js/jquery.jscrollpane.min.js" type="text/javascript"></script>
		<script src="<?php echo BASEPATH_UI;?>/web/js/jquery.mousewheel.js" type="text/javascript"></script>
		<script src="<?php echo BASEPATH_UI;?>/web/js/jquery.fileinput.js" type="text/javascript"></script>
		<script src="<?php echo BASEPATH_UI;?>/web/js/functions.js" type="text/javascript"></script>
		<script type="text/javascript"> $(document).ready(function() { autoHeightContainer(); }) </script>
	</head>

	<body>
		<!-- Wrapper -->
		<div id="wrapper">
			<!-- Header -->
			<div id="header">
				<!-- Shell -->
				<div class="shell">
					<h1 id="logo"><a href="<?php echo PAGE_INDEX; ?>" class="notext">Superhuman</a></h1>
	<?php

		if(isset($appAuth) && $appAuth->isLoggedIn()) {
	?>
					<div class="user-image">
						<a href="<?php echo $user->getPagePath(); ?>"><img src="<?php echo $user->pictureURL; ?>" alt="<?php echo "$user->firstName $user->lastName";?>" /></a>
						<span class="anchor"><img src="<?php echo BASEPATH_UI;?>/web/css/images/anchor.png" alt="Anchor" /></span>
						<div class="dd">
							<ul>
								<!--<li><a href="#">Change Password</a></li>-->
								<li><a href="<?php echo $appAuth->getLogoutPageURL(); ?>">Log Out</a></li>
							</ul>
						</div>
					</div>
	<?php
		}
		else {
			if(!$justOuterChrome) {
	?>
					<p class="right"><a href="<?php echo PAGE_INDEX; ?>" class="login-btn">Log In &raquo;</a><a href="<?php echo PAGE_INDEX; ?>" class="signup-btn">Sign Up &raquo;</a></p>
	<?php
			}
		}
	?>
					<div class="cl">&nbsp;</div>
				</div>
				<!-- End Shell -->
			</div>
			<!-- End Header -->
			<!-- Content -->
			<div id="container">
	<?php
		if(!$justOuterChrome) {
	?>
				<!-- Search -->
				<script type="text/javascript">
					var timer=null;
					function onSearchBoxChange() {
						var inputText = document.getElementById("searchBox").value;
						if(inputText=="") {
							document.getElementById("searchRecs").innerHTML = "";
						}
						else {
							if(timer != null) {
								clearTimeout(timer);
							}
							timer=setTimeout("updateSearchResults()",200);
						}
					}
					function updateSearchResults() {
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
								//response="<!-- ProductX Rox Your Sox --><results><result><type>People</type><name>roger dickey</name><link>user_bare.php?id=4</link></result></results>";
								//console.log(response);
								// DONE
								
								xmlDoc = null;
								if (window.DOMParser) {
									parser=new DOMParser();
									xmlDoc=parser.parseFromString(response,"text/xml");
								}
								else { // Internet Explorer
									xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
									xmlDoc.async=false;
									xmlDoc.loadXML(response); 
								}
								resultList = xmlDoc.getElementsByTagName("result");
								var namesByType = new Array();
								var linksByType = new Array();
								var types = new Array();
								//console.log(resultList);
								for(i=0; i<resultList.length; ++i) {
									resultName = resultList[i].getElementsByTagName("name")[0].firstChild.nodeValue;
									resultLink = resultList[i].getElementsByTagName("link")[0].firstChild.nodeValue;
									resultType = resultList[i].getElementsByTagName("type")[0].firstChild.nodeValue;
									typeIndex = types.indexOf(resultType);
									if(typeIndex == -1) {
										typeIndex = types.length;
										types.push(resultType);
										namesByType.push(new Array());
										linksByType.push(new Array());
									}
									namesByType[typeIndex].push(resultName);
									linksByType[typeIndex].push(resultLink);
								}
								//console.log(namesByType);
								//console.log(linksByType);
								//console.log(types);
								newHTML = "";
								for(i=0; i<types.length; ++i) {
									newHTML = newHTML + "<ul><li><span>"+types[i]+"</span></li>";
									for(j=0; j<namesByType[i].length; ++j) {
										newHTML = newHTML + "<li><a href='"+linksByType[i][j]+"'>"+namesByType[i][j]+"</a></li>";
									}
									newHTML = newHTML + "</ul>";
								}
								var inputText = document.getElementById("searchBox").value;
								if(inputText!="") {
									document.getElementById("searchRecs").innerHTML = newHTML;
								}
							}
						}
						var inputText = document.getElementById("searchBox").value;
						xmlhttp.open("GET","<?php echo PAGE_AJAX_GETSEARCHOPTIONS; ?>?inputText="+escape(inputText),true);
						xmlhttp.send();
					}
				</script>
				<div id="search">
					<form action="" method="post">
						<input type="text" class="field" title="Search" id="searchBox" value="Search"  onkeyup="onSearchBoxChange();" />
						<div class="complete-dd" id="searchRecs">
						</div>
					</form>
				</div>
				<!-- End Search -->
				<!-- Sidebar -->
				<div id="sidebar">
					<!-- Navigation -->
					<div id="navigation">
						<ul>
							<li><a href="<?php echo PAGE_ACTIVITY; ?>" class="<?php echo ($navSelect==NAVNAME_ACTIVITY)?"active":"";?> activity-link">Activity</a></li>
							<li><a href="<?php echo PAGE_USER; ?>" class="<?php echo ($navSelect==NAVNAME_YOU)?"active":"";?> you-link">You</a></li>
							<li><a href="<?php echo PAGE_GOALS; ?>" class="<?php echo ($navSelect==NAVNAME_GOALS)?"active":"";?> goals-link">All Goals</a></li>
							<li><a href="<?php echo PAGE_USERS; ?>" class="<?php echo ($navSelect==NAVNAME_USERS)?"active":"";?> all-link">All People</a></li>
						</ul>
					</div>
					<!-- End Navigation -->
				</div>
				<!-- End Sidebar -->
				<!-- Content -->
				<div id="content">
					<!-- Title bar -->
					<div class="head">
						<!--<p class="date"><a href="#">&laquo;</a> Dec 19 <a href="#">&raquo;</a></p>-->
	<?php
			foreach($chromeTitleElements as $element) {
				$element->printElement();
			}
	?>
						<div class="cl">&nbsp;</div>
					</div>
					<!-- End Title bar -->
					<!-- Scollarea -->
					<div class="scrollarea">
	<?php
	
			StatusMessages::printMessages();
			PerformanceMeter::addTimestamp("Header render done");
		}
	}
	public function printFooter($navSelect, $justOuterChrome=false) {
		global $user, $appAuth;
		if(!$justOuterChrome) {
			PerformanceMeter::addTimestamp("Page render done");
			PerformanceMeter::printRenderReport();
		}
	?>

	<?php
		if(!$justOuterChrome) {
	?>
					</div>
					<!-- End Scollarea -->
				</div>
				<!-- End Content -->
	<?php
		}
	?>
			</div>
			<!-- End Container -->
			<!-- Footer -->
			<div id="footer">
				<!-- Shell -->
				<div class="shell">
	<?php
		if(!$justOuterChrome) {
	?>
					<p class="nav"><a href="<?php echo PAGE_ABOUT; ?>">About</a><span>|</span><a href="<?php echo PAGE_HELP; ?>">Help</a></p>
	<?php
		}
		if(isset($appAuth) && $appAuth->isLoggedIn() && !$user->hasMadeDailyEntry()) {
	?>
					<a href="<?php echo PAGE_USER; ?>" class="entry-btn">Make your daily entry &raquo;</a>
	<?php
		}
	?>
					<div class="cl">&nbsp;</div>
				</div>
				<!-- End Shell -->
			</div>
			<!-- End Footer -->
		</div>
		<!-- End Wrapper -->
	</body>
	</html>
	<?php
		PerformanceMeter::addTimestamp("Footer render done");
	}
	protected function storyPrintListPre() {
	}
	protected function storyPrintListPost() {
	}
	protected function storyPrintEventStoryPrint($user, $goal, $eventStory, $changeWord, $goodBad, $timeSinceStr) {
?>
					<!-- Case -->
					<div class="case">
						<!-- Post -->
						<div class="post">
							<div class="user-image">
								<a href="<?php echo $user->getPagePath(); ?>"><img src="<?php echo GPC::strToPrintable($user->pictureURL); ?>" alt="<?php echo "$user->firstName $user->lastName"; ?>" />
							</div>
							<div class="cnt">
								<p class="post-title"><a href="<?php echo $user->getPagePath(); ?>"><?php echo "$user->firstName $user->lastName"; ?></a> <?php echo $changeWord; ?> his level for <a href="<?php echo $goal->getPagePath(); ?>"><?php echo GPC::strToPrintable($goal->name); ?></a> from <?php echo $eventStory->oldLevel; ?> to <?php echo $eventStory->newLevel; ?>.</p>
								<div class="quote-image-<?php echo $goodBad;?>">
									<span><?php echo $eventStory->letterGrade; ?></span>
								</div>
								<div class="quote">
									<p><?php echo GPC::strToPrintable($eventStory->description); ?></p>
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
	protected function storyPrintDailyscoreStoryPrint($user, $numGoalsTouched, $totalGoals, $goodBad, $score, $goalLinkList, $timeSinceStr) {
?>
					<!-- Case -->
					<div class="case">
						<!-- Post -->
						<div class="post">
							<div class="user-image">
								<a href="<?php echo $user->getPagePath(); ?>"><img src="<?php echo GPC::strToPrintable($user->pictureURL); ?>" alt="<?php echo "$user->firstName $user->lastName"; ?>" />
							</div>
							<div class="cnt">
								<p class="post-title"><a href="<?php echo $user->getPagePath(); ?>"><?php echo "$user->firstName $user->lastName"; ?></a> just entered daily goal progress, touching <?php echo $numGoalsTouched; ?> out of <?php echo $totalGoals; ?> of their goals.</p>
								<div class="result-image-<?php echo $goodBad;?>">
									<span><?php echo $score; ?><span class="sub">%</span></span>
								</div>
								<div class="result">
									<p><?php echo $goalLinkList; ?>
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
	public function printAllGoalsPage() {
		global $db, $user;
		$ajaxModifyGoal = PAGE_AJAX_MODIFY_GOAL;
				
		// RENDER PAGE
		$this->printHeader(NAVNAME_GOALS, array(new ChromeTitleElementHeader("All Goals")));

		$rs = $db->doQuery("SELECT id FROM goals WHERE is_active = 1");
		$numGoals = mysql_num_rows($rs);
		$numPerColumn = max($numGoals/NUM_COLS,7);
		$colContents = array();
		$obj=null;
		$currentCol=0;
		$i=0;
		
		while($obj = mysql_fetch_object($rs)) {
		
			$goal = Goal::getObjFromGoalID($obj->id);
		
			if($i==0) {
				$colContents[$currentCol] = array();
			}
			$colContents[$currentCol][] = $goal;
			++$i;
			if($i>=$numPerColumn) {
				++$currentCol;
				$i=0;
			}
		}
		
		
		/////////////////////////////////////////
		// AJAX for adopting/removing a Goal //
		///////////////////////////////////////
		?>
		<script>
			function modifySpecificGoal(type, goalID, numAdopters, goalDivNum, goalName){
                var lessAdopters = numAdopters - 1;
			    var newNumAdopters = "numAdopters" + goalDivNum;
			    var newDeactivateDiv = "deactivate" + goalDivNum;
			    var newDeleteDiv = "deleteGoal" + goalDivNum;
			    var goalEntry = "goalEntry" + goalDivNum;
			    
			    if(type == 'delete'){
			    var Action = 'Delete';
			    }else if (type == 'remove'){
			    var Action = 'Remove';
			    }
			    
			    var answer = confirm(Action + " " + goalName + "?");

    			if (answer){
			        $.ajax({  
			            type: "POST", 
			            url: '<?php echo $ajaxModifyGoal; ?>', 
			            data: "userID="+<?php echo $user->id; ?>+"&goalID="+goalID+"&type="+type,
			            dataType: "html",
			            complete: function(data){
			            	if(type == 'remove'){
			        	        $("#"+newNumAdopters).html(lessAdopters);
			            	    $("#"+newDeactivateDiv).html(data.responseText);
							}else if(type == 'delete')
							{
			            	    $("#"+goalEntry).html('');
							}
							
			            }  
			        }); 
		        }
		    }
        </script>
		
		<!-- Case -->
		<div class="case goals">
			<!-- Cols -->
			<div class="cols">
				<p>Goals</p>
		<?php
		$k = 0;
		for($i=0; $i<NUM_COLS; ++$i) {
			if(isset($colContents[$i])) {
				echo "<div class='col'><ul>";
				foreach($colContents[$i] as $goal) {
					
					$pagePath = $goal->getPagePath();
					$numAdopters = $goal->getNumAdopters();					
					
					?>
					<li id="goalEntry<?php echo $k; ?>"><a href=<?php echo $pagePath;?>><?php echo htmlspecialchars($goal->name); ?></a> (<span id="numAdopters<?php echo $k;?>"><?php echo $numAdopters;?></span>)
					
					<?php 		
					$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goal->id);
					if($userHasGoal){?>
					<a style="color: #999; text-decoration:none;" class="deactivate" id="deactivate<?php echo $k;?>" onclick="modifySpecificGoal('remove', <?php echo $goal->id; ?>, <?php echo $numAdopters; ?>, <?php echo $k; ?>, '<?php echo $goal->name; ?>')">remove</a>
					
					<?php
					}
					if($user->permissions == 1){?>
					<a style="color: red; text-decoration:none;" class="delete" id="deleteGoal<?php echo $k; ?>" onclick="modifySpecificGoal('delete', <?php echo $goal->id; ?>, <?php echo $numAdopters; ?>, <?php echo $k; ?>, '<?php echo $goal->name; ?>')"> delete</a>
					<?php 
					}
					
					?>
					</li>

					<?php
					$k = $k+1;
				}
				echo "</ul></div>";
				
			}
		}
		?>
								<div class="cl">&nbsp;</div>
								</div>
								<!-- End Cols -->
								<?php if($user->permissions == 1){?>
								<div class="form">
									<p> Add new goals:</p>
									<form action="<?php echo PAGE_GOALS;?>" method="post" name="goalForm">
										<label for="name">Goal Name:</label>
										<input type="text" class="field" value="" id="newGoalName" name="newGoalName" />
										<div class="cl">&nbsp;</div>
										<label for="description">Description:</label>
										<textarea id="newGoalDescription" name="newGoalDescription" rows="2" cols="40"></textarea>
										<div class="cl">&nbsp;</div>
		
										<script type="text/javascript">
											var numDailytests = 0;
											var numkpis = 0;
																				
											function addKPI(postedTo) {
												document.goalForm.numkpis=++numkpis;
												var newKPI = "<label class='small-label'>KPI "+numkpis+":</label><input type='text' class='small-field' name='kpiName"+numkpis+"' /><label class='small-label'>&nbsp;&nbsp;Description:</label><input type='text' class='small-field' name='kpiDescription"+numkpis+"' /><br/><br/><label class='small-label'>&nbsp;&nbsp;Test Name:</label><input type='text' class='small-field' name='kpiTestName"+numkpis+"' /><label class='small-label'>&nbsp;&nbsp;Test Description:</label><input type='text' class='small-field' name='kpiTestDescription"+numkpis+"' /><br/><br/><label class='small-label'>&nbsp;&nbsp;Test Frequency (in days):</label><input type='text' class='small-field' name='kpiTestFrequency"+numkpis+"' /><div class='cl'>&nbsp;</div><br/>";
												$("#kpis").append(newKPI);
												$("#numkpis").attr('value',numkpis);
											}
											
											function addDailytest(postedTo) {
												document.goalForm.numDailytests=++numDailytests;
												var newStrategy = "<label class='small-label'>Strategy "+numDailytests+":</label><input type='text' class='small-field' name='dailytestName"+numDailytests+"' /><label class='small-label'>&nbsp;&nbsp;Description:</label><input type='text' class='small-field' name='dailytestDescription"+numDailytests+"' /><label class='small-label'>&nbsp;&nbsp;Type:</label><select name='dailytestType"+numDailytests+"'><option value='adherence'>Adherence</option><option value='todo'>ToDo</option><option value='tactic'>Tactic</option></select><div class='cl'>&nbsp;</div>";

												$("#dailytests").append(newStrategy);
												$("#numDailytests").attr('value',numDailytests);
											}
										</script>
										<div id="kpis"></div>								
										<div id="dailytests"></div>
		
										<input type="button" value="Add KPI" onclick="addKPI();" class="small-add-btn"/>
										<input type="hidden" name="numkpis" id="numkpis" value="0" />
		
										<input type="button" value="Add Strategy" onclick="addDailytest();" class="small-add-btn"/>
										<input type="hidden" name="numDailytests" id="numDailytests" value="0" />
										<div class="cl" style="height:5px;">&nbsp;</div>
										<input type="submit" value="Add Goal &raquo;" class="add-btn" />
									</form>
									<?php } ?>
								</div>
							</div>
							<!-- End Case -->

		<?php
		$this->printFooter(NAVNAME_GOALS);
	}	
	public function printAboutPage() {
		$this->printHeader(NAVNAME_NONE, array(new ChromeTitleElementHeader("About")));
?>
<div style="padding:10px 0 0 10px;">
By winners, for winners.
</div>
<?php
		$this->printFooter(NAVNAME_NONE);
	}
	public function printHelpPage() {
		$this->printHeader(NAVNAME_NONE, array(new ChromeTitleElementHeader("Help")));
?>
<div style="padding:10px 0 0 10px;">
Is it really that hard to figure out? :P
</div>
<?php
		$this->printFooter(NAVNAME_NONE);
	}
	public function printAllUsersPage() {
		$this->printHeader(NAVNAME_USERS, array(new ChromeTitleElementHeader("All People")));
		$this->userPrintListAll();
		$this->printFooter(NAVNAME_USERS);
	}
	protected function userPrintListPre() {
?>
					<!-- Case -->
					<div class="case">
						<!-- Users -->
						<div class="users">
<?php
	}
	protected function userPrintListPost() {
?>
						</div>
						<!-- End Users -->
					</div>
					<!-- End Case -->
<?php
	}
	protected function userPrintListSectionPre($letter) {
?>
						<p><?php echo $letter;?></p>
<?php
	}
	protected function userPrintListSectionPost() {
?>
							<div class="cl">&nbsp;</div>
<?php
	}
	protected function userPrintCardPrint($user, $numGoals, $visitFreqText) {
?>
							<!-- Card -->
							<div class="card">
								<div class="user-image">
						    		<a href="<?php echo $user->getPagePath();?>"><img src="<?php echo GPC::strToPrintable($user->pictureURL);?>" alt="<?php echo "$user->firstName $user->lastName";?>" /></a>
						    	</div>
						    	<div class="info">
						    		<a href="<?php echo $user->getPagePath();?>"><?php echo "$user->firstName <b>$user->lastName</b>";?></a>
						    		<span><?php echo $numGoals;?> goals</span>
						    		<span><?php echo $visitFreqText;?></span>
						    	</div>
						    	<div class="cl">&nbsp;</div>
							</div>
							<!-- End Card -->
<?php
	}
	
	
	
	
	
	
	
	// &&&&&&
	public function printUserPage($viewUser) {
		global $user, $db;
		$viewUserID = $viewUser->id;
		$viewingSelf = ($viewUserID == $user->id);
		
		define('PAGEMODE_HABITS','habits');
		define('PAGEMODE_GOALS','goals');
		define('PAGEMODE_ACTIVITY','activity');

		$mode = PAGEMODE_HABITS;
		if(isset($_GET["t"])) {
			$mode = $_GET["t"];
		}
		$tabIndex = 0;
		switch($mode) {
			case PAGEMODE_HABITS:
				$tabIndex = 0;
				break;
			case PAGEMODE_GOALS:
				$tabIndex = 1;
				break;
			case PAGEMODE_ACTIVITY:
				$tabIndex = 2;
				break;
			default:
				assert(false);
				break;
		}

		$navName = $viewingSelf?NAVNAME_YOU:NAVNAME_USERS;
		$this->printHeader($navName, 
					array(	new ChromeTitleElementUserPic($viewUser),
							new ChromeTitleElementHeader("Person: $viewUser->firstName $viewUser->lastName"),
							new ChromeTitleElementTabs(	array(	"Habits"=>PAGE_USER."?id=$viewUserID&t=".PAGEMODE_HABITS,
																"Goals"=>PAGE_USER."?id=$viewUserID&t=".PAGEMODE_GOALS,
																"Activity"=>PAGE_USER."?id=$viewUserID&t=".PAGEMODE_ACTIVITY
																
														), $tabIndex)
					));

		/*
		// HACK: if used, this should be implemented as a ChromeTitleElement

		$daysBack = 0;
		if(isset($_GET["db"])) {
			$daysBack = GPC::strToInt($_GET["db"]);
		}
		$daysBack = min(0,$daysBack);
		$currentTime = $daysBack*60*60*24;
		$currentDate = date("M j",time()-$currentTime);
		echo "<a href='".PAGE_USER."?id=$userID&db=".($daysBack+1)."'>&lt;</a>";
		echo $currentDate;
		if($daysBack>0) {
			echo "<a href='".PAGE_USER."?id=$userID&db=".($daysBack-1)."'>&gt;</a>";
		}
		echo "<br/>";
		*/

		switch($mode) {
			case PAGEMODE_ACTIVITY:
				$rs = $db->doQuery("SELECT * FROM stories WHERE is_public=TRUE AND user_id=%s ORDER BY entered_at DESC LIMIT 100", $viewUserID);
				$this->storyPrintListForRS($rs);
				break;
			case PAGEMODE_HABITS:
				$currentTime=time();
				$type = 'habits';
				$this->goalstatusPrintList($viewUserID, $currentTime, $viewingSelf, $type);
				break;
			case PAGEMODE_GOALS:
				$currentTime=time();
				$type = 'goals';
				$this->goalstatusPrintList($viewUserID, $currentTime, $viewingSelf, $type);
				break;				
			default:
				break;
		}

		$this->printFooter($navName);
	}
	
	
	
	
	
	
	
	
	
	
	
	protected function goalstatusPrintPre() {
?>
					<!-- Case -->
					<div class="case boxes">
<?php
	}
	protected function goalstatusPrintPost() {
?>
					</div>
					<!-- End Case -->
<?php
	}
	



	// &&&&&&
	protected function goalstatusPrintGoalstatusPrint($goal, $rowID, $goalstatus, $plusButtonDefaultDisplay, $eventDivDefaultDisplay, $dailytests, $letterGradeVal, $newLevelVal, $whyVal, $isEditable, $type) {
		global $user;
		static $testID = 1;
		$dayID = 1;
		
		$ajaxSaveDailytestPath = PAGE_AJAX_SAVEDAILYTEST;
		$ajaxSaveEventPath = PAGE_AJAX_SAVEEVENT;
		$ajaxModifyStrategy = PAGE_AJAX_MODIFY_STRATEGY;
		
		
		
		
		$noHabitStrategies = 0;

//		echo "<pre>";
//		print_r($goalstatus);
//		echo "</pre>";
//		echo "<pre>";
//		print_r($dailytests);
//		echo "</pre>";		
		
		if(	(count($dailytests) == 1) && ( $dailytests[0]->strategy_type != 'adherence')){		
			$noHabitStrategies = 1;
		}
		
?>

<script>
		//////////////////////////////////////////////////////////////////////////
		// AJAX for modifying (adding/removing/readopting, not creating) a KPI //
		////////////////////////////////////////////////////////////////////////

		function modifyDailyStrategy(user_id, strategy_id, div_id, date){
			// Get the status of the particular div that is being called
			var divID = "#testCheck" + div_id; 
			if($(divID).prop('checked') == true){			
				var result = 1;
			}else{
				var result = 0;
			}

		    $.ajax({  
		        type: "GET", 
		        url: '<?php echo $ajaxSaveDailytestPath; ?>', 
		        data: "userID="+user_id+"&dailytestID="+strategy_id+"&result="+ result+"&date="+ date,
		        dataType: "html",
		        complete: function(data){
		            $("#ratingBox").html(data.responseText);  
		        }  
		    });  
		    
		}
			
		function modify_lightbox(display){
			if(display == 1){
			     $("#lightbox-panel").show();
			}else{
			     $("#lightbox-panel").hide();
			}
		}

		function issueGoalEvent(user_id, goal_id, old_level){
			// Get the status of the particular div that is being called
			
			var new_level = $("#eventNewScore" + goal_id).attr("value");
			var letter_grade = $("#eventLetterGrade" + goal_id).attr("value");
			var why = $("#eventWhy" + goal_id).attr("value");

		    $.ajax({  
		        type: "GET", 
		        url: '<?php echo $ajaxSaveEventPath; ?>', 
		        data: "userID="+user_id+"&goalID="+goal_id+"&newLevel="+ new_level+"&oldLevel="+ old_level+"&letterGrade="+ letter_grade+"&why="+ why,
		        dataType: "html",
		        complete: function(data){
		            //$("#ratingBox").html(data.responseText);  
		        }  
		    });  

			$("#eventNewScore" + goal_id).attr("value","");
			$("#eventWhy" + goal_id).attr("value","");
			$("#goalLevel" + goal_id).html(new_level);
		    $("#lightbox-panel").fadeOut();
		    
		}

</script>
<?php
				if( ( $type == 'habits') && ( !empty($dailytests)) && ($noHabitStrategies != 1) ) {
?>						
				<!-- Box -->
				<div class="box">
					<!-- GOAL TITLE & CATEGORY(?) -->
					<div class="habit_box" >
						<div class="habit_title"><span class="goal_level" style="margin-right:4px;" id="goalLevel<?php echo $goal->id;?>"> <?php echo $goalstatus->level; ?></span><a href="<?php echo $goal->getPagePath();?>" class="title"><?php echo GPC::strToPrintable($goal->name);?></a><a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1)" href="#">+</a></div>
						
					<!-- Lightbox for issuing Goal Events -->
					<div id="lightbox-panel">
						<div class="newscore-row">
							<label for="score-1">New Level:</label><input type="text" class="field" id="eventNewScore<?php echo $goal->id;?>"  />
							<div class="cl">&nbsp;</div>
						</div>
						<div class="grade-row">
							<label>Letter grade:</label>
							<select name="grade" id="eventLetterGrade<?php echo $goal->id;?>" size="1">
								<option value="A" >A</option>
								<option value="B" >B</option>
								<option value="C" >C</option>
								<option value="D" >D</option>
								<option value="F" >F</option>
							</select>
						</div>
						<div class="cl">&nbsp;</div>
						<label for="textarea-1">Why:</label><br/>
						<textarea name="textarea" id="eventWhy<?php echo $goal->id;?>" class="field" rows="8" cols="40"></textarea>
						<button type="submit" value="submit" onclick="issueGoalEvent(<?php echo $user->id; ?>, <?php echo $goal->id; ?>, <?php echo $goalstatus->level; ?>)">submit</button>
					    <p align="center">
					        <a id="close-panel" href="#" onclick="modify_lightbox(0)">Close this window</a>
					    </p>
					</div><!-- /lightbox-panel -->						
					<div id="lightbox"> </div><!-- /lightbox -->
						
					<!-- HABITS -->
					<div class="tests">
<?php
						for($t=0;$t<7;$t++){
							$today = date("D", strtotime("-".$t." day")); 	
							$today = (string)$today;
							if($t == 0){ $margin = '270px; font-size:12px; font-weight:bold';}elseif( $t == 1 ){ $margin = '32px; font-size:16px'; }else{ $margin = '29px; font-size:16px'; }
							?>
							
							<div style="float:left; margin-left:<?php echo $margin; ?>;"><?php if($t == 0){ echo 'Today';}else{echo $today;}?></div>	
<?php									
						}
						?><?php

					foreach($dailytests as $dailytest) {
					
						if($dailytest->strategy_type == 'adherence'){
							$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $dailytest->id, date("Y-m-d"))?"checked":"";
							
	?>
							<div class="row">
								<div class="habit_label"><?php echo GPC::strToPrintable($dailytest->name);?></div>
	<?php
								if($isEditable) {
					?>
														<label for="testCheck<?php echo $dayID; echo $dailytest->id;?>"><input type="checkbox" value="Check" id="testCheck<?php echo $dayID; echo $dailytest->id;?>" <?php echo $checkedVal; ?> onclick="modifyDailyStrategy(<?php echo $user->id; ?>, <?php echo $dailytest->id;?>, <?php echo $dayID; echo $dailytest->id;?>, '<?php echo date("Y-m-d");?>');" /></label>
					<?php
									++$dayID;
								}
	?>
								<div class="test-cnt">
									<div>
	<?php
										$r = 1;
										foreach($dailytest->getStashedStyleArray() as $style) {
											$date = date("Y-m-d", strtotime("-".$r." day")); 	
											$date = (string)$date;		
											$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $dailytest->id, $date)?"checked":"";
							?>
											<label for="testCheck<?php echo $dayID; echo $dailytest->id;?>"><input type="checkbox" value="Check" id="testCheck<?php echo $dayID; echo $dailytest->id;?>" <?php echo $checkedVal; ?> onclick="modifyDailyStrategy(<?php echo $user->id; ?>, <?php echo $dailytest->id;?>, <?php echo $dayID; echo $dailytest->id;?>, '<?php echo $date;?>');" /></label>
						<?php				++$dayID;
											++$r;
										}?>
										<div class="cl">&nbsp;</div>
									</div>
								</div>
								<div class="cl">&nbsp;</div>
							</div>
<?php					}
					}?>
					</div>
				</div>
					<div class="cl">&nbsp;</div>
				</div>
				<!-- End Box -->
<?php
		}elseif($type == 'goals'){
		
		
			$kpis = KPI::getListFromGoalID($goal->id, $user->id);
/*			echo "<pre>";
			print_r($kpis);
			echo "</pre>";
*/
		
		?>
						<!-- Box -->
						<div class="box">


							<!-- GOAL TITLE & LEVEL -->
							<div class="habit_title"><span class="goal_level" style="margin-right:4px;" id="goalLevel<?php echo $goal->id;?>" onclick="modify_lightbox(1)"> <?php echo $goalstatus->level; ?></span><a href="<?php echo $goal->getPagePath();?>" class="title"><?php echo GPC::strToPrintable($goal->name);?></a><a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1)" href="#">+</a></div>
							
							<!-- Lightbox for issuing Goal Events -->
							<div id="lightbox-panel">
								<div class="newscore-row">
									<label for="score-1">New Level:</label><input type="text" class="field" id="eventNewScore<?php echo $goal->id;?>"  />
									<div class="cl">&nbsp;</div>
								</div>
								<div class="grade-row">
									<label>Letter grade:</label>
									<select name="grade" id="eventLetterGrade<?php echo $goal->id;?>" size="1">
										<option value="A" >A</option>
										<option value="B" >B</option>
										<option value="C" >C</option>
										<option value="D" >D</option>
										<option value="F" >F</option>
									</select>
								</div>
								<div class="cl">&nbsp;</div>
								<label for="textarea-1">Why:</label><br/>
								<textarea name="textarea" id="eventWhy<?php echo $goal->id;?>" class="field" rows="8" cols="40"></textarea>
								<button type="submit" value="submit" onclick="issueGoalEvent(<?php echo $user->id; ?>, <?php echo $goal->id; ?>, <?php echo $goalstatus->level; ?>)">submit</button>
							    <p align="center">
							        <a id="close-panel" href="#" onclick="modify_lightbox(0)">Close this window</a>
							    </p>
							</div><!-- /lightbox-panel -->						
							<div id="lightbox"> </div><!-- /lightbox -->


		<?php if(!empty($dailytests)){?>
					<div class="todos">
						<span class="todo_title"> ToDos </span><br/>
<?php				$isToDo = 0;
					foreach($dailytests as $dailytest) {
					
						if($dailytest->strategy_type == 'todo'){
							$isToDo = 1;
							$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $dailytest->id, date("Y-m-d"))?"checked":"";
							
	?>
<?php
							if($isEditable) {
				?>
									<label for="testCheck<?php echo $dayID; echo $dailytest->id;?>" style="float:left;">
										<input type="checkbox" value="Check" id="testCheck<?php echo $dayID; echo $dailytest->id;?>" <?php echo $checkedVal; ?> onclick="modifyDailyStrategy(<?php echo $user->id; ?>, <?php echo $dailytest->id;?>, <?php echo $dayID; echo $dailytest->id;?>, '<?php echo date("Y-m-d");?>');" />
									</label>
				<?php
								++$dayID;
							}
?>

							<div class="todo_label"><?php echo GPC::strToPrintable($dailytest->name);?></div>

							<div class="cl">&nbsp;</div>
<?php					}
					}
					if($isToDo == 0){
						echo "<span class='no_todos'> Adopt some ToDos here.</span>";
					}
				
				
					?>
					</div>		
			<?php }?>
			
			
		<?php if(!empty($dailytests)){?>
					<div class="todos">
						<span class="todo_title"> Tactics </span><br/>
						<ul style="list-style-type:square;">
<?php				$isToDo = 0;
					foreach($dailytests as $dailytest) {
					
						if($dailytest->strategy_type == 'tactic'){
							$isToDo = 1;
							$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $dailytest->id, date("Y-m-d"))?"checked":"";
	?>
	
							<script>
								////////////////////////////////////
								// AJAX for modifying a Strategy //
								//////////////////////////////////
								
								function modifyStrategy(strategy_id, type, strategy_type){
								
									var new_strategy_name = $("#newStrategyName" + strategy_id).attr("value");
									
								    $.ajax({  
								        type: "POST", 
								        url: '<?php echo $ajaxModifyStrategy; ?>', 
								        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goal->id; ?>+"&strategyID="+ strategy_id+"&newStrategyName="+ new_strategy_name+"&type="+ type+"&strategyType="+ strategy_type,
								        dataType: "html",
								        complete: function(data){
								            $("#ratingBox").html(data.responseText);  
								        }  
								    });
								    
								 
								 
									$("#element"+strategy_id).hide();	
									$("#editButton"+strategy_id).show();
									$("#curElementText"+strategy_id).html(new_strategy_name );	
									$("#curElementText"+strategy_id).show();	
								      
								}
													
								function editElement(element_id, status){
									if(status == 1){
										$("#editButton"+element_id).hide();	
										$("#curElementText"+element_id).hide();
										$("#element"+element_id).fadeIn();	
									}else{
										$("#element"+element_id).hide();	
										$("#editButton"+element_id).show();	
										$("#curElementText"+element_id).show();	
									}
								}

							</script>
	
							<div class="tactic_label">
								<li>
									<div style="display:none;" id="element<?php echo $dailytest->id;?>"> 
										<input id="newStrategyName<?php echo $dailytest->id;?>" type="text" value="<?php echo GPC::strToPrintable($dailytest->name);?>" style="width:375px; font-size:13px; color:#666;"/>
										<button onclick="modifyStrategy(<?php echo $dailytest->id;?>,'edit', '<?php echo $dailytest->strategy_type;?>')">submit</button><button  onclick="editElement(<?php echo $dailytest->id;?>,0)">cancel</button>
									</div> 
									<span id="curElementText<?php echo $dailytest->id;?>"><?php echo GPC::strToPrintable($dailytest->name);?></span>
									<span class="editLink" id="editButton<?php echo $dailytest->id;?>" onclick="editElement(<?php echo $dailytest->id;?>,1)">edit</span>
								</li>
							</div>
							<div class="cl">&nbsp;</div>
<?php					}
					}
					?></ul> <?php
					if($isToDo == 0){
						echo "<span class='no_todos'> Adopt some Tactics here.</span>";
					}
				
				
					?>
					</div>		
			<?php }?>

		<?php if(!empty($kpis)){?>
					<div class="todos">
						<span class="todo_title"> Measurements and Milestones </span><br/>
<?php				
					foreach($kpis as $kpi) {							

							if($isEditable) {
				?>
									<label for="testCheck<?php echo $dayID; echo $dailytest->id;?>" style="float:left;">
										<input type="checkbox" value="Check" id="testCheck<?php echo $dayID; echo $dailytest->id;?>" <?php echo $checkedVal; ?> onclick="modifyDailyStrategy(<?php echo $user->id; ?>, <?php echo $dailytest->id;?>, <?php echo $dayID; echo $dailytest->id;?>, '<?php echo date("Y-m-d");?>');" />
									</label>
				<?php
								++$dayID;
							}
?>
							<div class="todo_label"><?php echo GPC::strToPrintable($kpi->kpi_name);?>
							<?php if(!empty($kpi->kpi_tests[0]->test_name)){
									echo "(". $kpi->kpi_tests[0]->test_name . ")";
							}?>
							</div>
							<div class="cl">&nbsp;</div>
<?php					
					}
					?>
					</div>		
			<?php }?>


							
							
							
							
							
							
						</div>

						<!-- End Box -->
<?php
		
		
		}

	}
		
		
		
		
		
		
		
	public function printGoalPage($goalID) {
		global $db, $user;

		$ajaxModifyGoal = PAGE_AJAX_MODIFY_GOAL;
		$ajaxModifyKPI = PAGE_AJAX_MODIFY_KPI;
		$ajaxCreateKPI = PAGE_AJAX_CREATE_KPI;
		$ajaxModifyTestStatus = PAGE_AJAX_MODIFY_TEST_STATUS;
		$ajaxModifyStrategy = PAGE_AJAX_MODIFY_STRATEGY;
		$ajaxCreateStrategy = PAGE_AJAX_CREATE_STRATEGY;
		$ajaxSetTracking = PAGE_AJAX_SET_TRACKING;
		$ajaxAlterGoalDescription = PAGE_AJAX_ALTER_GOAL_DESCRIPTION;

		// get the ID & determine if user has the goal
		$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goalID);


	
		$goal = Goal::getFullObjFromGoalID($goalID,$user->id);
		$goal_name = $goal->goal->name;
		$goal_description = $goal->goal->description;
		
		
		// Show your description if in DB
		if ($goal->sub_description == 'none'){ 
		}else{ 
		?><script>$(document).ready(function(){addDescription("<?php echo $goal->sub_description; ?>");});</script><?php
		}
		
		// Show your display style if in DB
		if ($goal->display_style == '1'){ 
		 	$self_checked = "unchecked";
		 	$adherence_checked = "checked";
		}else{ 
		 	$self_checked = "checked";
			$adherence_checked = "unchecked" ;
		}
		
		$mode = PAGEMODE_EDIT;
		
		# Get all the KPIs and the strategies for the goal being viewed
		$kpis = KPI::getListFromGoalID($goalID, $user->id);
		$strategies = Dailytest::getListFromGoalID($goalID, $user->id);
		
		
		if(isset($_GET["t"])) {
			$mode = $_GET["t"];
		}
		$tabIndex = 0;
		switch($mode) {
			case PAGEMODE_EDIT:
				$tabIndex = 0;
				break;
			case PAGEMODE_ACTIVITY:
				$tabIndex = 1;
				break;
			case PAGEMODE_PEOPLE:
				$tabIndex = 2;
				break;
			case PAGEMODE_FACTS:
				$tabIndex = 3;
				break;
			default:
				assert(false);
				break;
		}
				
		$this->printHeader(NAVNAME_GOALS, array(
							new ChromeTitleElementHeader("Goal: $goal_name"),
							new ChromeTitleElementTabs(	array(	"Edit"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_EDIT,
																"Activity"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_ACTIVITY,
																"People"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_PEOPLE,
																"Facts"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_FACTS
														), $tabIndex)
					));
					
		switch($mode) {
		
			case PAGEMODE_EDIT:
				$numAdopters = $goal->goal->getNumAdopters();
				$average = GoalStatus::getAverageGoalScore($goalID);
				if(is_null($average)) {
					$average=0;
				}


		?>
		
		<script>
		
		
		//////////////////////////////////////////////////////////////////////////
		// AJAX for modifying (adding/removing/readopting, not creating) a KPI //
		////////////////////////////////////////////////////////////////////////
		function modifyKPI(kpi_id, type){
		    $.ajax({  
		        type: "POST", 
		        url: '<?php echo $ajaxModifyKPI; ?>', 
		        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&kpiID="+ kpi_id+"&type="+ type,
		        dataType: "html",
		        complete: function(data){
		            $("#ratingBox").html(data.responseText);  
		        }  
		    });  
		}
		
		//////////////////////////////
		// AJAX for creating a KPI //
		////////////////////////////
		
		function createKPI(userID, goalID, kpiName, kpiDescription, kpiTestDescription, kpiTestName, kpiTestFrequency, adopt, newKPINum){
		    $.ajax({  
		        type: "POST", 
		        url: '<?php echo $ajaxCreateKPI; ?>', 
		        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&kpiName="+ kpiName+"&kpiDescription="+ kpiDescription+"&kpiTestDescription="+ kpiTestDescription+"&kpiTestName="+ kpiTestName+"&kpiTestFrequency="+ kpiTestFrequency+"&adopt="+ adopt,
		        dataType: "html",
		        complete: function(data){
		        
					var val = jQuery.parseJSON(data.responseText);       	
		        	
		        	// Get the new KPI id and set the value into the checkbox
		        	$("#adopted_kpi_checkbox_" + newKPINum).attr("value",val[0]);
		        	
		        	// Get the new Test id and set the value into the checkbox
		        	$("#adopted_test_checkbox_id_" + newKPINum + "_0").attr("value",val[1]);
		
					// Modify the onclick event for the checkbox to include the kpiID, testID, as well as the newKPINum (included on top) and testNUM (0)
		        	$("#adopted_test_checkbox_id_" + newKPINum + "_0").attr("onclick","modifyTestStatus("+ val[0] + ","+val[1]+"," +newKPINum+ ",0);");
		        	
		            $("#ratingBox").html(val[0]);  
		
		        }  
		    });  
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// AJAX for modifying a KPI Test (including creating it in certain cases if it doesn't exist) //
		///////////////////////////////////////////////////////////////////////////////////////////////
		
		function modifyTestStatus(kpiID, testID, kpiNum, testNum){
		
			if ($("#adopted_test_checkbox_id_" + kpiNum + "_" + testNum).attr("checked") == 'checked' ){	
				var newActiveStatus = '1';
			}else{
				var newActiveStatus = '0';	
			}
		
		    $.ajax({  
		        type: "POST", 
		        url: '<?php echo $ajaxModifyTestStatus; ?>', 
		        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&kpiID="+ kpiID+"&testID="+ testID+"&newActiveStatus="+ newActiveStatus,
		        dataType: "html",
		        complete: function(data){
		        	//$("#adopted_kpi_checkbox_" + newKPINum).attr("value",data.responseText);
		            $("#ratingBox").html(data.responseText);  
		        }  
		    });	
		
		}
		
		
		////////////////////////////////////
		// AJAX for modifying a Strategy //
		//////////////////////////////////
		
		function modifyStrategy(strategy_id, type){
		    $.ajax({  
		        type: "POST", 
		        url: '<?php echo $ajaxModifyStrategy; ?>', 
		        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&strategyID="+ strategy_id+"&type="+ type,
		        dataType: "html",
		        complete: function(data){
		            $("#ratingBox").html(data.responseText);  
		        }  
		    });  
		}
		
		
		//////////////////////////////////
		// AJAX for creating a Strategy //
		/////////////////////////////////
		
		function createStrategy(strategyName, strategyDescription, strategyType, newStrategyNum){
		    $.ajax({  
		        type: "POST", 
		        url: '<?php echo $ajaxCreateStrategy; ?>', 
		        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&strategyName="+ strategyName+"&strategyDescription="+ strategyDescription+"&strategyType="+ strategyType,
		        dataType: "html",
		        complete: function(data){
		
					var val = data.responseText;       	
		        	
		        	// Get the new Strategy id and set the value into the checkbox
		        	$("#adopted_strategy_checkbox_" + newStrategyNum).attr("value",val);
		        	
					// Modify the onclick event for the checkbox to include the kpiID, testID, as well as the newKPINum (included on top) and testNUM (0)
		        	//$("#adopted_test_checkbox_id_" + newKPINum + "_0").attr("onclick","modifyTestStatus("+ val[0] + ","+val[1]+"," +newKPINum+ ",0);");
		        	
		            $("#ratingBox").html(val[0]);  
		
		        }  
		    });  
		}
		
		
		
		/////////////////////////////////////////
		// AJAX for adopting/removing a Goal //
		///////////////////////////////////////
		
			function modifyGoal(type){
		        $.ajax({  
		            type: "POST", 
		            url: '<?php echo $ajaxModifyGoal; ?>', 
		            data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&type="+type,
		            dataType: "html",
		            complete: function(data){
		                $("#ratingBox").html(data.responseText);  
		            }  
		        });  
		    }
				
		
		////////////////////////////////////////
		// AJAX for inserting Tracking Style //
		//////////////////////////////////////
		
			function setTracking(displayStyle){
				
				if(displayStyle == '0'){
					$("#self_reported").prop('checked', true);
					$("#adherence_based").prop('checked', false);		
				}else{
					$("#self_reported").prop('checked', false);
					$("#adherence_based").prop('checked', true);		
				}
				
		        $.ajax({  
		            type: "POST", 
		            url: '<?php echo $ajaxSetTracking; ?>', 
		            data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&displayStyle="+ displayStyle,
		            dataType: "html",
		            complete: function(data){
		                $("#ratingBox").html(data.responseText);  
		            }  
		        });  
		    }
		
		
		////////////////////////////////////////////
		// AJAX for inserting a Goal description //
		//////////////////////////////////////////
		
			function alterGoalDescription(description){
		        $.ajax({  
		            type: "POST", 
		            url: '<?php echo $ajaxAlterGoalDescription; ?>', 
		            data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&description="+ description,
		            dataType: "html",
		            complete: function(data){
		                $("#ratingBox").html(data.responseText);  
		            }  
		        });  
		    }
				
		
		// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%   //
		// %%%%%%%%%%%%%%%%%%%   END OF AJAX  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%  //
		// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% //
		
		
		////////////////////////////////////////////
		//   %%% Adding and removing KPIs %%%%   //
		//////////////////////////////////////////
		
		
		////////////////////////////////////////////////////////////////	
		////// Adding a KPI to the Adopted column the first time //////
		//////////////////////////////////////////////////////////////
		
			function change_kpi_tests(kpi_box_id){
		
				// DB Insert Variables
				var kpi_db_id = $("#kpi_checkbox_" + kpi_box_id).attr("value");
				
				// *** DB ENTRY *** //
				modifyKPI(kpi_db_id,'adopt');
				
				// DOM variables needed to move the data to the adopted list
				var kpi_box = "#kpi_box_" + kpi_box_id;		
				var kpi_checkbox_id = "#kpi_checkbox_" + kpi_box_id;
				var kpi_tests_box = "#kpi_test_box_" + kpi_box_id;
				var test_display_status = $(kpi_tests_box).css("display");
				var adopted_kpi = ".kpi_results #adopted_kpi"; 
		
				// Displays the tests after the KPI has been chosen
				if(test_display_status == 'none'){
					$(kpi_tests_box).css("display", "");
				} /*else{
					$(kpi_tests_box).css("display", "none");
				}*/
		
				// Hide the KPI in Suggested and make it appear in Adopted
				$(kpi_box).fadeOut();
				$(adopted_kpi).prepend($(kpi_box).html());
		
		
				// Change the new div names under Adopted to have the "adopted" prefix
				var temp_kpi_internals_all = ".kpi_results #kpi_internals_" + kpi_box_id;		
				var adopted_internals = "adopted_internals_" + kpi_box_id;
				var adopted_internals_id = "#adopted_internals_" + kpi_box_id;
		
				$(temp_kpi_internals_all).attr("name",adopted_internals);
				$(temp_kpi_internals_all).attr("id",adopted_internals);
			
				// Rename checkbox names under Adopted to have the "adopted" prefix
				var temp_kpi_checkbox = ".kpi_results #kpi_checkbox_" + kpi_box_id;
				var adopted_kpi_checkbox = "adopted_kpi_checkbox_" + kpi_box_id;
				
				// Remove the KPI checkbox
				$(temp_kpi_checkbox).attr("id",adopted_kpi_checkbox);
				$(temp_kpi_checkbox).attr("name",adopted_kpi_checkbox);		
				$("#" + adopted_kpi_checkbox).css("display","none");	
					
				// Rename Main Test Div and then Each Test Checkbox 
				var test_count = $("#num_tests_" + kpi_box_id).attr("value");
				var kpi_test_box = ".kpi_results #kpi_test_box_" + kpi_box_id;
				var adopted_test_box = "adopted_test_box_" + kpi_box_id;
				$(kpi_test_box).attr("name",adopted_test_box)
				$(kpi_test_box).attr("id",adopted_test_box)
		
				for(i=0; i<test_count; i++) { 
					var adopted_test_box_id = "adopted_test_checkbox_id_" + kpi_box_id + "_" + i;
					var kpi_test_checkbox_id = ".kpi_results #kpi_test_checkbox_id_" + kpi_box_id + "_" + i;			
					
					$(kpi_test_checkbox_id).attr("id",adopted_test_box_id);	
					$("#" + adopted_test_box_id).attr("name",adopted_test_box_id);	
				}
		
				// Add new "remove" button
				var new_remove = "<input id='removeKPIButton' type='button' value='X' onclick='removeKPI(" + kpi_box_id + ");' class='small-add-btn up-down'/>";
				$(adopted_internals_id).append(new_remove);
		
				$(".kpi_start_prompt").hide();
				
			}
			
		
		/////////////////////////////////////////////////////
		////// Removing a KPI from the Adopted column //////
		///////////////////////////////////////////////////
		
			function removeKPI(adopted_box_id){
				var kpi_db_id = $("#adopted_kpi_checkbox_" + adopted_box_id).attr("value");
				var new_test_db_id = $("#adopted_test_checkbox_id_"+adopted_box_id+"_0").attr("value");
			
				var kpi_internals_id = "#kpi_box_" + adopted_box_id;
				var adopted_internals_id = "#adopted_internals_" + adopted_box_id;
				var kpi_internals_input = "#kpi_checkbox_" + adopted_box_id;
				var new_checkbox = "<input type='checkbox' unchecked name='kpi_" + adopted_box_id + "' onclick='reAdoptKPI(" + adopted_box_id + ");' id='kpi_checkbox_" + adopted_box_id + "'  value='"+ kpi_db_id +"' />";
				var newKPINum = adopted_box_id;
				//var newTestID = $("#adopted_internals_" + adopted_box_id + " .kpi_tests").attr("id");
				var kpiName = $("#adopted_internals_" + adopted_box_id + " .newKPIName").html();
				var kpiDescription = $("#adopted_internals_" + adopted_box_id + " .newKPIDescription").html();
				var kpiTestNameFrequency = $("#adopted_internals_" + adopted_box_id + " .newTestNameFrequency").html();
				var kpiTestDescription = $("#adopted_internals_" + adopted_box_id + " .newTestDescription").html();
					
								
				var newKPI = "<div id='kpi_box_" +newKPINum+ "' class='kpi' style='display: block;'><div id='kpi_internals_" +newKPINum+ "' name='kpi_internals_" +newKPINum+ "' class='kpi_internals'><input type='checkbox' id='kpi_checkbox_" +newKPINum+ "' onclick='reAdoptKPI(" +newKPINum+ ");' name='kpi_" +newKPINum+ "' unchecked='' value='"+ kpi_db_id +"'>" + kpiName + "<br><subtitle style='font-size:11px'>"+ kpiDescription + "</subtitle><br><input type='hidden' value='1' name='num_tests_" +newKPINum+ "' id='num_tests_" +newKPINum+ "'><div id='" +newKPINum+ "' name='" +newKPINum+ "' style='' class='kpi_tests'>Test 1 <br><input type='checkbox' value='"+ new_test_db_id + "' name='kpi_test_checkbox_id_"+ newKPINum + "_0' onclick='modifyTestStatus(" +kpi_db_id+ ","+new_test_db_id+"," +newKPINum+ ",0);' id='kpi_test_checkbox_id_"+ newKPINum + "_0'> "+ kpiTestNameFrequency + " <br><subtitle style='font-size:11px'>"+ kpiTestDescription +"</subtitle><br></div></div></div>";
				
				
				// Deactivate the KPI and its Tests
				modifyKPI(kpi_db_id,'remove');
				
						
				// This is only for KPIs that were not newly created
				$(kpi_internals_input).replaceWith(new_checkbox);
		
				// Checks if the KPI is new or old, if new it inserts the new value into the PRE block
				if (typeof $(kpi_internals_id).attr("class") === 'undefined'){					
					$(".kpi_box").append(newKPI);
					
					// Hide the test checkboxes in the Suggested column -> They should not be used and can be removed down the line
					   if(typeof $("#adopted_test_checkbox_id_" + adopted_box_id + "_0").attr("checked") === 'undefined'){
					   		$("#kpi_test_checkbox_id_" + adopted_box_id + "_0").css("display","none");
						}else{
					   		$("#kpi_test_checkbox_id_" + adopted_box_id + "_0").css("display","none");
						}
							
				}else{
					$(kpi_internals_id).fadeIn();
				
					// Hide the test checkboxes in the Suggested column -> They should not be used and can be removed down the line
					var test_count = $("#num_tests_" + adopted_box_id).attr("value");
						// Create JS array including all of the checked test_ids and update Checked/Unchecked status of Suggested KPI tests
						var test_id_array = [];
						for(i=0; i<test_count; i++) { 
						   test_id_array.push(i);
						   if(typeof $("#adopted_test_checkbox_id_" + adopted_box_id + "_" + i).attr("checked") === 'undefined'){
						   		$("#kpi_test_checkbox_id_" + adopted_box_id + "_" + i).css("display","none");
							}else{
						   		$("#kpi_test_checkbox_id_" + adopted_box_id + "_" + i).css("display","none");
							}
						}					
				}
				
				$(adopted_internals_id).fadeOut();
					
			}
		
			
		////////////////////////////////////////////////////////////////////////////////	
		////// Adopting a KPI after it has already been adopted and removed once //////
		//////////////////////////////////////////////////////////////////////////////
		
			function reAdoptKPI(kpi_id){
				var kpi_db_id = $("#kpi_checkbox_" + kpi_id).attr("value");
				var test_count = $("#num_tests_" + kpi_id).attr("value");
		
			    modifyKPI(kpi_db_id,'readopt');
		
		
				var kpi_internals_id = "#kpi_box_" + kpi_id;
				var adopted_internals_id = "#adopted_internals_" + kpi_id;
			
				$(kpi_internals_id).fadeOut();
				$(adopted_internals_id).fadeIn();
			}
		
		
		///////////////////////////////////////////
		////// Adopting a newly created KPI //////
		/////////////////////////////////////////
		
			function addAndAdoptKPI(){
		
				var kpiName = $("#kpiName").attr("value");
				var kpiDescription = $("#kpiDescription").attr("value");
				var kpiTestName = $("#kpiTestName").attr("value");
				var kpiTestDescription = $("#kpiTestDescription").attr("value");
				var kpiTestFrequency = $("#kpiTestFrequency").attr("value");
				var currentKPINum = $("#numkpis").attr("value");
				var newKPINum = Number(currentKPINum) + 1;
				var currentTestID = $("#numtests").attr("value");
				//var newTestID = Number(currentTestID) +1;
				var adopted_kpi = ".kpi_results #adopted_kpi";	
				
				createKPI(<?php echo $user->id; ?>, <?php echo $goalID;?>, kpiName, kpiDescription, kpiTestDescription, kpiTestName, kpiTestFrequency, 'true', newKPINum);
				
				var newKPI = "<div id='adopted_internals_"+ newKPINum + "' name='adopted_internals_0' class='kpi_internals'><input id='adopted_kpi_checkbox_"+ newKPINum +"' type='checkbox' value='a' name='kpi_"+ newKPINum +"' style='display: none;'><span class='newKPIName'>" + kpiName + "</span><br><subtitle style='font-size:11px'><span class='newKPIDescription'>"+ kpiDescription + " </span></subtitle><br><input type='hidden' value='1' name='num_tests_" +newKPINum+ "' id='num_tests_" +newKPINum+ "'><div id='" +newKPINum+ " ' name='" +newKPINum+ " ' style='' class='kpi_tests'> Test 1 <br><input type='checkbox' checked='true' value='na' name='adopted_test_checkbox_id_"+ newKPINum + "_0'  onclick='modifyTestStatus(" +newKPINum+ ",'0');'  id='adopted_test_checkbox_id_"+ newKPINum + "_0''> <span class='newTestNameFrequency'>"+ kpiTestName +" (every " + kpiTestFrequency + " days)</span> <br><subtitle style='font-size:11px'><span class='newTestDescription'>"+ kpiTestDescription +"</span></subtitle><br></div><input type='button' class='small-add-btn up-down' onclick='removeKPI("+ newKPINum +");' value='X' id='removeKPIButton'></div>";
		
				$(adopted_kpi).prepend(newKPI);
				$("#numkpis").attr("value", newKPINum);
		
				$("#if_kpi_input").fadeOut();
				$("#remove_kpi_input").hide();
				$("#show_kpi_input").fadeIn();
				$(".kpi_start_prompt").hide();
				
		
			}
		
		
		// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%   //
		// %%%%%%%%%%%%%%%%%%%   END OF KPIs  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%  //
		// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% //
		
		
		//////////////////////////////////////////////////
		//   %%% Adding and removing Strategies %%%%   //
		////////////////////////////////////////////////
		
		
			  //////////////////////////////////////////////////////////////////////////
			 // Move contents of strategy_internals divs to Adopted/"results" column, /
			//////////////////////////////////////////////////////////////////////////
			
			function adopt_strategy(strategy_num){
				var strategy_id = $("#strategy_checkbox_" + strategy_num).attr("value");
		
				var strategy_internals = "#strategy_internals_" + strategy_num;		
				var strategy_box = "#strategy_box_" + strategy_num;
				var adopted_strategy = ".strategy_results #adopted_strategy"; 
				var strategy_checkbox_id = "#adopted_strategy_checkbox_" + strategy_num;
				var new_remove_strategy = "<input id='removeStrategyButton' type='button' value='X' onclick='removeStrategy(" + strategy_num + ");' class='small-add-btn up-down'/>";
		
				modifyStrategy(strategy_id, 'adopt');
		
		
				$(strategy_box).fadeOut();
				$(adopted_strategy).prepend($(strategy_box).html());
				
				var strategy_internals_new = ".strategy_results " + strategy_internals;
				var adopted_internal_strategies = "adopted_strategy_internals_" + strategy_num;		
				var adopted_internal_strategies_id = "#" + adopted_internal_strategies;
				var adopted_strategy_checkbox = "adopted_strategy_checkbox_" + strategy_num;
				var temp_strategy_checkbox = ".strategy_results #strategy_checkbox_" + strategy_num;
								
				$(strategy_internals_new).attr("name",adopted_internal_strategies);
				$(strategy_internals_new).attr("id",adopted_internal_strategies);
		
				$(temp_strategy_checkbox).attr("id", adopted_strategy_checkbox);
				$(adopted_internal_strategies_id).append(new_remove_strategy);
				$(adopted_internal_strategies_id + " "+strategy_checkbox_id ).hide();
				$(".strategy_start_prompt").hide();
					
			}
		
			  ///////////////////////////////////////////////////
			 // This removes a Strategy from the Adopted column/
			///////////////////////////////////////////////////
			
			function removeStrategy(strategy_num){
				var strategy_id = $("#adopted_strategy_checkbox_" + strategy_num).attr("value");
				var strategyType = $("#newStrategyType_" + strategy_num).attr("value");;
		
		
				var adopted_internal_strategies_id = "#adopted_strategy_internals_" + strategy_num;		
				var strategy_box = "#strategy_box_" + strategy_num;
				var strategy_internals_input = "#strategy_checkbox_" + strategy_num;
				var new_checkbox = "<input type='checkbox' name='strategy_" + strategy_num + "' onclick='reAdoptStrategy(" + strategy_num + ");' id='strategy_checkbox_" + strategy_num + "' value='"+ strategy_id +"' />";
				var strategyName = $("#adopted_strategy_internals_" + strategy_num + " .newStrategyName").html();
				var strategyDescription = $("#adopted_strategy_internals_" + strategy_num + " .newStrategyDescription").html();
				
				var newStrategy = "<div id='strategy_box_"+ strategy_num +"' name='strategy_box_"+ strategy_num +"' class='strategy_boxes'><div id='strategy_internals_"+ strategy_num +"' name='strategy_internals_"+ strategy_num +"'><input type='checkbox' id='strategy_checkbox_"+ strategy_num +"' onclick='reAdoptStrategy("+ strategy_num +");' value='"+strategy_id+"' name='strategy_"+ strategy_num +"'> "+ strategyName +"<br><subtitle style='font-size:11px'> "+ strategyDescription +"</subtitle><br><span class='newStrategyType' value='"+strategyType+"' style='display:none;'></span></div></div>";
		
		
				modifyStrategy(strategy_id, 'remove');
		
				// This is only for Strategies that were not newly created
				$(strategy_internals_input).replaceWith(new_checkbox);
						
				// Checks if the KPI is new or old, if new it inserts the new value into the PRE block
				if (typeof $(strategy_box).attr("class") === 'undefined'){
					$(".strategies").append(newStrategy);		
				}else{
					$(strategy_box).fadeIn();
				}		
				
				
				$(adopted_internal_strategies_id).fadeOut();
			}
			
			
		     //////////////////////////////////////////////////////////////////////////
			// Adopting a Strategy after it has already been adopted and removed once/
		   //////////////////////////////////////////////////////////////////////////
		   
			function reAdoptStrategy(strategy_num){
				var strategy_id = $("#strategy_checkbox_" + strategy_num).attr("value");
			
				var strategy_internals_id = "#strategy_box_" + strategy_num;
				var adopted_internals_id = "#adopted_strategy_internals_" + strategy_num;
			
				modifyStrategy(strategy_id, 'readopt');
			
				$(strategy_internals_id + " input").attr("checked",false);
				$(strategy_internals_id).fadeOut();
				$(adopted_internals_id).fadeIn();
			}
		
		
			 //////////////////////////////////////////////////////////////
			// Creating and Adopting a new Strategy that you just created/
		   //////////////////////////////////////////////////////////////
		   
			function addAndAdoptStrategy(){
		
				var strategyName = $("#strategyName").attr("value");
				var strategyDescription = $("#strategyDescription").attr("value");
				var strategyType = $("#strategyType").attr("value");
				var currentStrategyNum = $("#numstrategies").attr("value");
				var newStrategyNum = Number(currentStrategyNum) + 1;
				var adopted_strategy = ".strategy_results #adopted_strategy";
				
				createStrategy(strategyName, strategyDescription, strategyType, newStrategyNum);
				
				var newStrategy = "<div id='adopted_strategy_internals_"+newStrategyNum+"' name='adopted_strategy_internals_"+newStrategyNum+"'><input id='adopted_strategy_checkbox_"+newStrategyNum+"' type='checkbox' onclick='adopt_strategy("+newStrategyNum + ");' value='na' name='strategy_"+newStrategyNum+"' style='display: none;'><span class='newStrategyName'>"+ strategyName +"</span><br><subtitle style='font-size:11px'><span class='newStrategyDescription'>"+ strategyDescription +"</span></subtitle><br><span class='newStrategyType' id='newStrategyType_"+ newStrategyNum +"'  value='"+strategyType+"' style='display:none;'></span><input type='button' class='small-add-btn up-down' onclick='removeStrategy("+newStrategyNum+");' value='X' id='removeStrategyButton'></div>";
		
		
				$(adopted_strategy).prepend(newStrategy);
				$("#numstrategies").attr("value", newStrategyNum);
		
				$("#if_strategy_input").fadeOut();
				$("#remove_strategy_input").hide();
				$("#show_strategy_input").fadeIn();
				$(".strategy_start_prompt").hide();
				
		
			}
		
		
		// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%   //
		// %%%%%%%%%%%%%%%%%%%   END OF Strategies  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%  //
		// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% //
		
		
		//////////////////////////////////////////////////////////
		//   %%% Adding and removing custom Description %%%%   //
		////////////////////////////////////////////////////////
		
			function addDescription(existing_desc){
		
				if(existing_desc == "none"){
					var new_desc = $("#goal_desc").attr("value");		
				}else{
					var new_desc = existing_desc;
				}
		
				$(".enter_desc_prompt").hide();
				$(".adopted_desc p").html(new_desc);
				$("#removeDescButton").fadeIn();
				alterGoalDescription(new_desc);		
			}
			function removeDescription(){
				var new_desc = "";
				$(".enter_desc_prompt").fadeIn();
				$(".adopted_desc p").html("").fadeIn();
				$("#removeDescButton").hide();
				alterGoalDescription(new_desc);		
			}
		
		//////////////////////////////////////////////////////////////////
		// Transition to edit mode when somebody elects to Adopt a goal	/
		////////////////////////////////////////////////////////////////
			
			function removeShowAdopt(){				
					$("#if_adopt").show();
					$("#suggested_goal_params").show();
					$(".pre_adopt").hide();
					$("#who_else_adopted").hide();
					$(".reporting_select").show();
					modifyGoal('insert');	
			}
		
			function removeGoal(){
					$(".pre_adopt").show();
					$(".pre_adopt").css("margin-top","305px");
					modifyGoal('remove');	
					$("#if_adopt").css("display","none");
			}
		
		
			function onEdit(){
					$("#if_adopt").show();
					$(".pre_adopt").hide();
					$("#suggested_goal_params").show();
					$("#who_else_adopted").hide();
					$(".reporting_select").show();
			}
		
		
			$(document).ready(function(){ 
		
				 /////////////////////////////////////////////////////////////////////
				// Grey text that disappears onclick in the goal description field //
			   /////////////////////////////////////////////////////////////////////
				$("#goal_desc").one("click", function(){
					$("#goal_desc").css("color","black");
					$("#goal_desc").attr("value","");
				});
				
				 ////////////////////////////////////////////////////////
				// Exposing the input fields for creating your own KPI//
			   ////////////////////////////////////////////////////////
				$("#show_kpi_input").click(function(){
					$("#if_kpi_input #kpiName").attr("value", '');
					$("#if_kpi_input #kpiDescription").attr("value", '');
					$("#if_kpi_input #kpiTestName").attr("value", '');
					$("#if_kpi_input #kpiTestDescription").attr("value", '');
					$("#if_kpi_input #kpiTestFrequency").attr("value", '');
					$("#if_kpi_input").show();
					$("#show_kpi_input").hide();
					$("#remove_kpi_input").fadeIn();
				});	
		
			     /////////////////////////////////////////////////////////////
				// Exposing the input fields for creating your own Strategy//
			   /////////////////////////////////////////////////////////////	
				$("#show_strategy_input").click(function(){
					$("#if_strategy_input #strategyName").attr("value", '');
					$("#if_strategy_input #strategyDescription").attr("value", '');
					$("#if_strategy_input").show();
					$("#show_strategy_input").hide();
					$("#remove_strategy_input").fadeIn();
				});	
		
				 ////////////////////////////////////////////////////////
				// Removing the input fields for creating your own KPI//
			   ////////////////////////////////////////////////////////
				$("#remove_kpi_input").click(function(){
					$("#if_kpi_input").fadeOut();
					$("#show_kpi_input").fadeIn();
					$("#remove_kpi_input").hide();
				});	
		
			     /////////////////////////////////////////////////////////////
				// Removing the input fields for creating your own Strategy//
			   /////////////////////////////////////////////////////////////	
				$("#remove_strategy_input").click(function(){
					$("#if_strategy_input").fadeOut();
					$("#show_strategy_input").fadeIn();
					$("#remove_strategy_input").hide();			
				});	
		
		
			});  											
		
		</script>
		
		
		
		<!-- AJAX TESTING GROUNDS -->		
		<div name="ratingBox" id="ratingBox" style="display:none;"></div>
		
		<!-- Case -->
		<div class="case">
		
			<!-- Score -->
			
			
			<div class="score">
				<div class="text">
		<?php if(!$userHasGoal){?>
				<div class="pre_adopt">
					<p id="suggested_description"><strong> Description:</strong> <?php echo $goal_description; ?></p>
					<button class="adopt-goal-btn" id="show_adopt_options" onclick="removeShowAdopt();">Adopt this goal</button>
				</div>
		<?php } ?>
					<div id="if_adopt" name="if_adopt" style="display:none;">
					<div class="edit_goal_params" id="suggested_goal_params"> Edit Goal Parameters </div>
		
						<!-- START REPORTING STYLE -->
						<div class="reporting_select">
							<label class='small-label' style="font-weight:bold"> Your Progress Indicator: </label>
							<input type="radio" id="self_reported" name="self_reported" <?php echo $self_checked; ?> value="0" onclick="setTracking('0')" /> Self Reported
							<input type="radio" id="adherence_based" name="adherence_based" <?php echo $adherence_checked; ?> value="1" onclick="setTracking('1')" /> Adherence Based
						</div>
						
						<!-- START YOUR DESCRIPTION --> 
						<div class="goal_desc_box" id="goal_desc_box" name="goal_desc_box">
							<label class='small-label' style="font-weight:bold;">Your own description (optional):</label><br/>	
							<div class="enter_desc_prompt" id="enter_desc_prompt" name="enter_desc_prompt">
								<input type="text" name="goal_desc" id="goal_desc" value="feel free to enter your own description" style="color:#999; width:280px; margin: 10px 10px 0px 0px; float:left;" />	
								<input type="button" value="Add" onclick="addDescription('none');" class="small-add-btn up-down"/>
							</div>									
			
							<div class="adopted_desc">
								<p></p>
								<input id="removeDescButton" type="button" value="X" onclick="removeDescription();" class="small-add-btn up-down" style="display:none;"/>
							</div>
						</div>
						
						<!-- START KPIS -->
						<div class="kpi_headings">
							<div class="chosen_kpis" id="show_adopted_kpis"> My KPIs </div>
							<div class="choose_kpi_heading"> 
								Choose KPIs:
								<a class="show_kpi_input" id="show_kpi_input" name="show_kpi_input" style="color:white; float:right;">
									Add Your Own
								</a>
								<a class="remove_kpi_input" id="remove_kpi_input" name="remove_kpi_input" style="color:white; float:right; display:none;">
									Close KPI Creator
								</a>
		
							</div>
						</div>
						<div style="clear:both;"/>
		
		
						<div class="all_kpis">
							<div class="kpi_results">
								<div class="adopted_kpi" id="adopted_kpi" name="adopted_kpi">
								
									<!-- ADOPTED KPIs GO HERE -->
									<?php 
									$active_kpis = 0;
									for($i=0; $i<count($kpis);$i++){
										$test_count = count($kpis[$i]->kpi_tests);
										if($kpis[$i]->kpi_active == 1){
											$active_kpis++;
									?>
											<div class="kpi_internals" name="<?php echo "adopted_internals" . "_" . $i ;?>" id="<?php echo "adopted_internals" . "_" . $i ;?>">									
											<input type="checkbox" value="<?php echo $kpis[$i]->id;?>" onclick="change_kpi_tests('<?php echo $i;?>');" id="<?php echo "adopted_kpi_checkbox_" . $i ;?>" style="display:none;" />
		
													<?php echo $kpis[$i]->kpi_name;?><br/>
													<subtitle style="font-size:11px"><?php echo $kpis[$i]->kpi_desc;?></subtitle>
													<br/>
											
												<input type="hidden" name="<?php echo "num_tests_" . $i?>" id="<?php echo "num_tests_" . $i?>" value="<?php echo $test_count; ?>" />
												<div class="kpi_tests" name="<?php echo "adopted_test_box_" . $i;?>" id="<?php echo "adopted_test_box_" .  $i;?>">
									<?php
																	for($k=0; $k<$test_count;$k++){
																		if ($kpis[$i]->kpi_tests[$k]->active == '1'){ 
																		 	$test_checked = "checked";
																		}else{ 
																		 	$test_checked = "unchecked";
																		}
																?>
																		KPI Test <?php echo $k+1; ?> <br/>
					
																		<input type="checkbox" name="<?php echo "adopted_test_checkbox_id_". $i ."_". $k;?>" id="<?php echo "adopted_test_checkbox_id_". $i ."_". $k;?>" value="<?php echo $kpis[$i]->kpi_tests[$k]->id;?>" <?php echo $test_checked;?> onclick="modifyTestStatus(<?php echo $kpis[$i]->id;?>, <?php echo $kpis[$i]->kpi_tests[$k]->id;?>, <?php echo $i;?>, <?php echo $k;?> );"  /> <?php echo $kpis[$i]->kpi_tests[$k]->test_name;?> (every <?php echo $kpis[$i]->kpi_tests[$k]->test_frequency;?> days) <br />
																		<subtitle style="font-size:11px"><?php echo $kpis[$i]->kpi_tests[$k]->test_description;?></subtitle>
																		<br/>
														<?php  }?>
												</div>
												<input id='removeKPIButton' type='button' value='X' onclick='removeKPI(<?php echo $i; ?>);' class='small-add-btn up-down'/>
											</div>
								<?php	}
								}  
								if($active_kpis == 0){
									echo "<div class='kpi_start_prompt'>Choose some KPIs to get started ----></div>";
								}
		
								
								?>
		
								</div>
							</div>
		
							<div class="kpi_box">
								<div id="if_kpi_input" style="margin-top:10px; display:none; color:white;"><br/>
									<label class='small-label'>  KPI: </label>
									<input type='text' class='small-field' name='kpiName' id='kpiName' /><br/><br/>
									<label class='small-label'> Description: </label>
									<input type='text' class='small-field' name='kpiDescription' id='kpiDescription' /><br/><br/>
									<label class='small-label'>  Test Name: </label>
									<input type='text' class='small-field' name='kpiTestName' id='kpiTestName' /><br/><br/>
									<label class='small-label'>  Test Description: </label>
									<input type='text' class='small-field' name='kpiTestDescription' id='kpiTestDescription' /><br/><br/>
									<label class='small-label'>  Test Freq. (days): </label>
									<input type='text' class='small-field' name='kpiTestFrequency' id='kpiTestFrequency' />
									<div class='cl'></div>
									<br/>					
									<center><input type="button" value="Add KPI" onclick="addAndAdoptKPI();" class="small-add-btn up-down"/></center>
								</div>
								<?php 
								for($i=0; $i<count($kpis);$i++){
									$test_count = count($kpis[$i]->kpi_tests);
									if($kpis[$i]->kpi_active == 0){
									$show_kpi = "display:'';";
									}else{
									$show_kpi = "display:none;";
									}
								?>
								<div class="kpi" id="<?php echo "kpi_box_" . $i;?>" style="<?php echo $show_kpi; ?>">
									<div class="kpi_internals" name="<?php echo "kpi_internals" . "_" . $i ;?>" id="<?php echo "kpi_internals" . "_" . $i ;?>">
											<input type="checkbox" value="<?php echo $kpis[$i]->id;?>" onclick="change_kpi_tests('<?php echo $i;?>');" id="<?php echo "kpi_checkbox_" . $i ;?>" /> <?php echo $kpis[$i]->kpi_name;?><br/>
											<subtitle style="font-size:11px"><?php echo $kpis[$i]->kpi_desc;?></subtitle>
											<br/>
									
										<input type="hidden" name="<?php echo "num_tests_" . $i?>" id="<?php echo "num_tests_" . $i?>" value="<?php echo $test_count; ?>" />
										<div class="kpi_tests" style="display:none;" name="<?php echo "kpi_test_box_" . $i;?>" id="<?php echo "kpi_test_box_" .  $i;?>">
							<?php
															for($k=0; $k<$test_count;$k++){
														?>
																KPI Test <?php echo $k+1; ?> <br/>
			
																<input type="checkbox" name="<?php echo "kpi_test_checkbox_id_". $i ."_". $k;?>" id="<?php echo "kpi_test_checkbox_id_". $i ."_". $k;?>" value="<?php echo $kpis[$i]->kpi_tests[$k]->id;?>"  onclick="modifyTestStatus(<?php echo $kpis[$i]->id;?>, <?php echo $kpis[$i]->kpi_tests[$k]->id;?>, <?php echo $i;?>, <?php echo $k;?> );"  /> <?php echo $kpis[$i]->kpi_tests[$k]->test_name;?> (every <?php echo $kpis[$i]->kpi_tests[$k]->test_frequency;?> days) <br />
																<subtitle style="font-size:11px"><?php echo $kpis[$i]->kpi_tests[$k]->test_description;?></subtitle>
																<br/>
												<?php  }?>
										</div>
									</div>
								</div>			
								<input type="hidden" name="numtests" id="numtests" value="<?php echo count($kpis[$i]->kpi_tests); ?> " />
								<input type="hidden" name="numkpis" id="numkpis" value="<?php echo count($kpis); ?> " />
					
							<?php
								}  
								?>
							</div>
						</div>
						<div style="clear:both;"/>
		
		
						<!-- START STRATEGIES -->
						<div class="strategy_headings">
							<div class="chosen_strategies" id="show_adopted_strategies"> My Strategies </div>
							<div class="choose_strategy_heading"> 
								Choose Strategies:
								<a class="show_strategy_input" id="show_strategy_input" name="show_strategy_input" style="color:white; float:right;">
									Add Your Own
								</a>
								<a class="remove_strategy_input" id="remove_strategy_input" name="remove_strategy_input" style="color:white; float:right; display:none;">
									Close Strategy Creator
								</a>
							</div>
						</div>
						
						<div style="clear:both;"/>
						<div class="all_strategies">
							<div class="strategy_results">
								<div class="adopted_strategy" id="adopted_strategy" name="adopted_strategy">
								
									<!-- ADOPTED Strategies GO HERE -->
								
									<?php 
									$active_strategies = 0;
									for($j=0; $j<count($strategies);$j++){
										if($strategies[$j]->strategy_active == 1){
											$active_strategies++;
									?>
										<div class="strategy_boxes" name="<?php echo "adopted_strategy_box_" . $j ; ?>" id="<?php echo "adopted_strategy_box_" . $j ; ?>">				
											<div name="<?php echo "adopted_strategy_internals_" . $j ;?>" id="<?php echo "adopted_strategy_internals" . "_" . $j ;?>">
											<!-- Strategy <?php echo $j; ?> <br/> -->
												<input type="checkbox" name="<?php echo "adopted_strategy" . "_" . $j ;?>" value="<?php echo $strategies[$j]->id;?>" onclick="adopt_strategy('<?php echo $j;?>');" id="<?php echo "adopted_strategy_checkbox_" . $j ;?>" style="display:none;"/> <?php echo $strategies[$j]->name;?><br/>
												<subtitle style="font-size:11px"><?php echo $strategies[$j]->description;?></subtitle><span><?php echo $strategies[$j]->strategy_type;?></span>
												<br/>
											<input id='removeStrategyButton' type='button' value='X' onclick='removeStrategy(<?php echo $j; ?>);' class='small-add-btn up-down'/>
											</div>
										</div>
								<?php }
									}
									if($active_strategies == 0){
										echo "<div class='strategy_start_prompt'>Choose some Strategies to get started ----></div>";
									}
								?>
								
								</div>
							</div>
												
							<div class="strategies" >						
								<div id="if_strategy_input" style="margin-top:10px; display:none; color:white;"><br/>
									<label class='small-label'> Strategy: </label>
									<input type='text' class='small-field' name='strategyName' id='strategyName'/>
									<br/><br/>
									<label class='small-label'> Description: </label>
									<input type='text' class='small-field' name='strategyDescription' id='strategyDescription'/>
									<br/><br/>
									<label class='small-label'> Type: </label>
									<select name='strategyType' id='strategyType'>
										<option value='adherence'>Adherence</option>
										<option value='todo'>ToDo</option>
										<option value='tactic'>Tactic</option>
									</select>
									<div class='cl'> </div>					
									<center><input type="button" value="Add Strategy" onclick="addAndAdoptStrategy();" class="small-add-btn up-down" style="margin-bottom:14px;"/></center>
								</div>
							<?php 
							for($j=0; $j<count($strategies);$j++){
								if($strategies[$j]->strategy_active == 0){
									$show_strategy = "display:'';";
									}else{
									$show_strategy = "display:none;";
								}
								
							?>
								<div class="strategy_boxes" name="<?php echo "strategy_box_" . $j ; ?>" id="<?php echo "strategy_box_" . $j;?>" style="<?php echo $show_strategy; ?>" >				
									<div name="<?php echo "strategy_internals_" . $j ;?>" id="<?php echo "strategy_internals" . "_" . $j ;?>">
									<!-- Strategy <?php echo $j; ?> <br/> -->
										<input type="checkbox" name="<?php echo "strategy" . "_" . $j ;?>" value="<?php echo $strategies[$j]->id;?>" onclick="adopt_strategy('<?php echo $j;?>');" id="<?php echo "strategy_checkbox_" . $j ;?>" /> <?php echo $strategies[$j]->name;?><br/>
										<subtitle style="font-size:11px"><?php echo $strategies[$j]->description;?></subtitle><span><?php echo $strategies[$j]->strategy_type;?></span>
										<br/>
									</div>
								</div>
						<?php
							}
						?>
			
								<div id="dailytests" style="margin-top:10px;"></div>
								<input type="hidden" name="numstrategies" id="numstrategies" value="<?php echo count($strategies); ?>" />
							</div>
						</div>		
						<input type="hidden" name="num_strategies" value="<?php echo count($strategies);?>"/>
						<input type="hidden" name="num_kpis" value="<?php echo count($kpis);?>"/>
						<input type="hidden" name="goal_id" value="<?php echo $goalID;?>"/>
					</div>
									
					<!-- END EDIT GOAL PARAMETERS -->
					<div class="cl">&nbsp;</div>
				</div>
		
				<!-- START DISPLAY TO USERS WHO HAVE NOT ADDED GOAL YET -->		
				<div class="results">
					<div id="who_else_adopted">		
						<ul>
						    <li><p class="res-box"><span><?php echo $numAdopters; ?></span></p><p class="label">People have this goal</p></li>
						    <li class="last"><p class="res-box"><span><?php echo sprintf("%1.1f",$average); ?></span></p><p class="label">Average level</p></li>
						</ul>
						
						<div class="five_friends"> 
							<!-- Insert 5 images of people who are have this goal here prioritizing friends -->
						</div>
					</div>
				</div>
				<div class="cl">&nbsp;</div> 
				<!-- END DISPLAY TO USERS WHO HAVE NOT ADDED GOAL YET -->		
		
			</div>
			
			<div class="remove_goal">
					<button class="remove-goal-btn" id="remove_goal" onclick="removeGoal();">Deactivate goal</button>
			</div>
			
			
			<div style="min-height:100px; min-width:700px;"></div>
			<!-- End Score -->	
		</div>
		<?php if($userHasGoal){?>
		<script> onEdit(); </script>			
		<?php } ?>
		
		<!-- End Case -->
		
		<?php
				break;
			case PAGEMODE_ACTIVITY:
				// only returns event type stories for this goal
				$rs = $db->doQuery("SELECT * FROM stories WHERE is_public=TRUE AND type='".EventStory::STORY_TYPENAME."' AND event_goal_id=%s ORDER BY entered_at DESC LIMIT 100", $goalID);
				
				//while ($obj = mysql_fetch_object($rs)){
				//	print_r($obj);				
				//}

				//print($rs);

				//REMOVED, need to use Baseview::storyPrintListForRS($rs)
				Baseview::storyPrintListForRS($rs);
				
				break;
				
				
				
			case PAGEMODE_PEOPLE:
				BaseView::userPrintListByGoal($goalID);


				break;
			case PAGEMODE_FACTS:
		
		?>
		<div class="facts">
			<div class="adopted_desc">
				<p>
				</p>
				<input id="removeDescButton" type="button" value="X" onclick="removeDescription();" class="small-add-btn up-down" style="display:none;"/>
			</div>
		
		
			<div id="who_else_adopted">		
				<ul>
				    <li><p class="res-box"><span><?php echo $numAdopters; ?></span></p><p class="label">People have this goal</p></li>
				    <li class="last"><p class="res-box"><span><?php echo sprintf("%1.1f",$average); ?></span></p><p class="label">Average level</p></li>
				</ul>
				
				<div class="five_friends"> 
					<!-- Insert 5 images of people who are have this goal here prioritizing friends -->
				</div>
			</div>
			
		</div>
		<?php
				break;
				
			default:
				break;
		}

		$this->printFooter(NAVNAME_GOALS);
	}
};




class MobileView extends BaseView {
	// public
	public function printHeader($navSelect, $chromeTitleElements, $justOuterChrome=false) {
		global $user, $appAuth;
?>
<!DOCTYPE html>
<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Superhuman</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=640"/>  
	<link rel="shortcut icon" href="<?php echo BASEPATH_UI;?>/mobile/css/images/favicon.ico" />
	<link rel="stylesheet" href="<?php echo BASEPATH_UI;?>/mobile/css/jquery.mobile.structure-1.1.0-rc.1.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo BASEPATH_UI;?>/mobile/css/style.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo BASEPATH_UI;?>/mobile/css/jqtransform.css" type="text/css" media="all" />
	<script src="<?php echo BASEPATH_UI;?>/mobile/js/jquery-1.7.1.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?php echo BASEPATH_UI;?>/mobile/js/jquery.jqtransform.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?php echo BASEPATH_UI;?>/mobile/js/functions.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?php echo BASEPATH_UI;?>/mobile/js/jquery.mobile-1.1.0-rc.1.min.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript">
		jQuery('div').live('pagehide', function(event, ui) {
			var page = jQuery(event.target);

			if(page.attr('data-cache') == 'never') {
				page.remove();
			};
		});
	</script>
</head>
<body>
<div data-role="page" data-cache="never">
	<!-- Shell -->
	<div class="shell">
		<!-- Header -->
		<header>
			<h1 id="logo" ><a href="<?php echo PAGE_INDEX; ?>">Superhuman</a></h1>
			
	<?php
		if(isset($appAuth) && $appAuth->isLoggedIn()) {
	?>
			<div class="profile">
				<a href="#" class="link" >
					<img src="<?php echo $user->pictureURL; ?>" alt="" style="width:87px;height:87px" />
					<span class="arrow" >&nbsp;</span>
				</a>
				<div class="dropdown">
					<ul>
						<!--<li><a href="#">Change Password</a></li>-->
						<li><a href="<?php echo $appAuth->getLogoutPageURL(); ?>">Log Out</a></li>
					</ul>
				</div>
			</div>
	<?php
		}
	?>
			<div class="cl">&nbsp;</div>
		</header>
		<!-- END Header -->
		
		<!-- Main -->
		<div id="main">
<?php
		if(!$justOuterChrome) {
			StatusMessages::printMessages();
			PerformanceMeter::addTimestamp("Header render done");
		}
	}
	public function printFooter($navSelect, $justOuterChrome=false) {
		if(!$justOuterChrome) {
			PerformanceMeter::addTimestamp("Page render done");
			PerformanceMeter::printRenderReport();
		}
?>
		</div>
		<!-- END Main -->
<?php
		if(!$justOuterChrome) {
?>
		<!--  Navigation -->
		<nav>
			<ul>
				<li><a href="<?php echo PAGE_USER; ?>" data-transition="none" class="<?php echo ($navSelect==NAVNAME_YOU)?"active":"";?>"><span class="icon icon-1" >&nbsp;</span>Daily Entry</a></li>
				<li><a href="<?php echo PAGE_ACTIVITY; ?>" data-transition="none" class="<?php echo ($navSelect==NAVNAME_ACTIVITY)?"active":"";?>" ><span class="icon icon-2" >&nbsp;</span>Activity</a></li>
				<li><a href="<?php echo PAGE_USERS; ?>" data-transition="none" class="<?php echo ($navSelect==NAVNAME_USERS)?"active":"";?>"><span class="icon icon-3" >&nbsp;</span>Friends</a></li>
			</ul>
		</nav>
		<!-- END Navigation -->
<?php
		}
?>
	</div>
	<!-- END Shell -->
</div>
</body>
</html>
<?php
		PerformanceMeter::addTimestamp("Footer render done");
	}
	protected function storyPrintEventStoryPrint($user, $goal, $eventStory, $changeWord, $goodBad, $timeSinceStr) {
		static $divID = 1;
?>
					<li>
						<div class="text">
							<a href="<?php echo $user->getPagePath(); ?>" id="<?php echo "imgholder_$divID";?>">
								<img class='img' src="<?php echo GPC::strToPrintable($user->pictureURL); ?>" />
								<!-- HACK: image is inserted later. if specified literally here, safari will load the page 2x. no other fix could be found. -->
							</a>
							<!--<script type="text/javascript">
								setTimeout("loadUserImage('<?php echo "imgholder_$divID"; ?>', '<?php echo GPC::strToPrintable($user->pictureURL); ?>')", 4000);
								function loadUserImage(divID, imgPath) {
									document.getElementById(divID).innerHTML="<img class='img' src='"+imgPath+"' />";
								}
							</script>-->
							<h4><a href="<?php echo $user->getPagePath(); ?>"><?php echo "$user->firstName $user->lastName"; ?></a> <?php echo $changeWord; ?> his level for <a href="<?php echo $goal->getPagePath(); ?>"><?php echo GPC::strToPrintable($goal->name); ?></a> from <?php echo $eventStory->oldLevel; ?> to <?php echo $eventStory->newLevel; ?>.</h4>
							
							<p class="letter" ><?php echo $eventStory->letterGrade; ?></p>
							<div class="quote">
								<p><?php echo GPC::strToPrintable($eventStory->description); ?></p>
								<span class="quote-top" >&ldquo;</span>
								<span class="quote-bottom" >&rdquo;</span>
							</div>
						</div>
						<div class="cl">&nbsp;</div>
						<p class="time" ><?php echo $timeSinceStr; ?> ago</p>
					</li>
<?php
		++$divID;
	}
	protected function storyPrintListPre() {
		echo "<ul>";
	}
	protected function storyPrintListPost() {
		echo "</ul>";
	}
	protected function storyPrintDailyscoreStoryPrint($user, $numGoalsTouched, $totalGoals, $goodBad, $score, $goalLinkList, $timeSinceStr) {
?>
					<li>
<!--						<div class="img" style="background:url();">-->
							<a href="<?php echo $user->getPagePath(); ?>"><img class="img" src="<?php echo GPC::strToPrintable($user->pictureURL); ?>" alt="<?php echo "$user->firstName $user->lastName"; ?>" />
<!--						</div>-->
						<div class="text">
							<h4><a href="<?php echo $user->getPagePath(); ?>"><?php echo "$user->firstName $user->lastName"; ?></a> just entered daily goal progress, touching <?php echo $numGoalsTouched; ?> out of <?php echo $totalGoals; ?> of their goals.</h4>
							
							<p class="percent" ><?php echo $score; ?><span>%</span></p>
							<div class="links">
								<p><?php echo $goalLinkList; ?></p>
							</div>
						</div>
						<div class="cl">&nbsp;</div>
						<p class="time" ><?php echo $timeSinceStr; ?> ago</p>
					</li>
<?php
	}
	protected function printActivityPagePre() {
?>
			<div class="activity-page">
<?php
	}
	protected function printActivityPagePost() {
?>
			</div>
<?php
	}
	protected function printSignupPagePrint() {
		global $appAuth;
		$isExpanded= isset($_GET['expanded']);
		if(!$isExpanded) {
?>
			<div class="signup-box">
				<h3>Be Amazing.</h3>
				
				<div class="buttons">
					<a href="<?php echo PAGE_INDEX; ?>" data-transition="flip" class="left" >Log in &raquo;</a>
					<a href="<?php echo PAGE_INDEX; ?>?expanded" data-transition="flip" class="green right" >Sign up &raquo;</a>
					<div class="cl">&nbsp;</div>
				</div>
			</div>
<?php
		}
		else {
		?>
			<div class="signup-box">
				<h3>Be Amazing.</h3>
				
				<form action="<?php echo PAGE_INDEX; ?>?expanded" method="post" >
					<p>Signed in as: <strong><?php echo $appAuth->getUserEmail(); ?></strong></p>
					
					<label>Profile pic URL (50x50): </label>
					<input type="text" name="pictureURL" class="field" />
					<input type="submit" name="submit" value="Finish &raquo;" class="button" />
					<div class="cl">&nbsp;</div>
				</form>
			</div>
		<?php
		}
	}
	public function printAllGoalsPage() { // this page doesn't exist on mobile
	}
	public function printAboutPage() { // this page doesn't exist on mobile
	}
	public function printHelpPage() { // this page doesn't exist on mobile
	}
	public function printAllUsersPage() {
		global $viewSwitch;
		
		$this->printHeader(NAVNAME_USERS, array(new ChromeTitleElementHeader("All People")));
		// TEST: bare page
		if($viewSwitch->issetViewFlag("bare")) {
			echo "<p><b><font color='white'>USERS PAGE</font></b></p>";
			$this->printFooter(NAVNAME_USERS);
			return;
		}
?>
			<div class="friends-page">
<?php
		$this->userPrintListAll();
?>
				<div class="nav">
					<ul>
						<!--<li class="search" ><a href="#"><img src="css/images/search.png" alt="" /></a></li>-->
						<li><a href="#A">A</a></li>
						<li><a href="#B">B</a></li>
						<li><a href="#C">C</a></li>
						<li><a href="#D">D</a></li>
						<li><a href="#E">E</a></li>
						<li><a href="#F">F</a></li>
						<li><a href="#G">G</a></li>
						<li><a href="#H">H</a></li>
						<li><a href="#I">I</a></li>
						<li><a href="#J">J</a></li>
						<li><a href="#K">K</a></li>
						<li><a href="#L">L</a></li>
						<li><a href="#M">M</a></li>
						<li><a href="#N">N</a></li>
						<li><a href="#O">O</a></li>
						<li><a href="#P">P</a></li>
						<li><a href="#Q">Q</a></li>
						<li><a href="#R">R</a></li>
						<li><a href="#S">S</a></li>
						<li><a href="#T">T</a></li>
						<li><a href="#U">U</a></li>
						<li><a href="#V">V</a></li>
						<li><a href="#W">W</a></li>
						<li><a href="#X">X</a></li>
						<li><a href="#Y">Y</a></li>
						<li><a href="#Z">Z</a></li>
						<li><a href="#other">#</a></li>
					</ul>
				</div>
				<div class="cl">&nbsp;</div>
			</div>
<?php
		$this->printFooter(NAVNAME_USERS);
	}
	protected function userPrintListPre() {
?>
				<div class="content">
					<div class="row">
<?php
	}
	protected function userPrintListPost() {
?>
					</div>
				</div>
<?php
	}
	protected function userPrintListSectionPre($letter) {
?>
						<h3 id="<?php echo $letter;?>" ><?php echo $letter;?></h3>
<?php
	}
	protected function userPrintListSectionPost() {
?>
						<div class="cl">&nbsp;</div>
<?php
	}
	protected function userPrintCardPrint($user, $numGoals, $visitFreqText) {
?>
						<div class="box left">
							<h5><a href="<?php echo $user->getPagePath();?>"><?php echo "$user->firstName <b>$user->lastName</b>";?></a></h5>
							<p>
								<?php echo $numGoals;?> goals<br/>
								<?php echo $visitFreqText;?>
							</p>
							<a href="<?php echo $user->getPagePath();?>"><img class="img" src="<?php echo GPC::strToPrintable($user->pictureURL);?>" alt="<?php echo "$user->firstName $user->lastName";?>" /></a>
						</div>
<?php
	}
	
	// &&&&&&
	public function printUserPage($viewUser) {
		global $user, $db, $viewSwitch;
		$viewUserID = $viewUser->id;
		$viewingSelf = ($viewUserID == $user->id);
		$navName = $viewingSelf?NAVNAME_YOU:NAVNAME_USERS;
		$this->printHeader($navName, array());
		// TEST: bare page
		if($viewSwitch->issetViewFlag("bare")) {
			echo "<p><b><font color='white'>USER PAGE</font></b></p>";
			$this->printFooter(NAVNAME_YOU);
			return;
		}
?>
			<h2 class="arrow" ><?php echo "$viewUser->firstName $viewUser->lastName"; ?> <a href="#" class="arrows expand" >&nbsp;</a></h2>
			<div class="cl">&nbsp;</div>
			
			<div class="daily-entry-page">
<?php		
		$currentTime=time();
		$type = 'none';
		$this->goalstatusPrintList($viewUserID, $currentTime, $viewingSelf, $type);
?>
			</div>
<?php		

		$this->printFooter($navName);
	}
	protected function goalstatusPrintPre() {
?>
					<ul>
<?php
	}
	protected function goalstatusPrintPost() {
?>
					</ul>
<?php
	}
	protected function goalstatusPrintGoalstatusPrint($goal, $rowID, $goalstatus, $plusButtonDefaultDisplay, $eventDivDefaultDisplay, $dailytests, $letterGradeVal, $newLevelVal, $whyVal, $isEditable, $type) {
		global $db;
		
		static $testID = 1;
		$goalNumColor = "green";
		if($goalstatus->level < 7) {
			$goalNumColor = "yellow";
		}
		if($goalstatus->level < 4) {
			$goalNumColor = "red";
		}
?>
					<li>
						<form action="#" method="post" class="jqtransform" >
							<div class="title">
								<p class="num <?php echo $goalNumColor;?>" id="levelBox<?php echo $rowID;?>"><?php echo $goalstatus->level;?></p>
								<h5><?php echo GPC::strToPrintable($goal->name);?></h5>
								<span class="arrow" onclick="window.location='<?php echo $goal->getPagePath();?>';">&nbsp;</span>
								<div class="cl">&nbsp;</div>
							</div>
							<div class="holder">
<?php
			if($isEditable) {
				$originalLevelVal = $db->doQueryOne("SELECT event_new_level FROM stories WHERE user_id=%s AND event_goal_id=%s AND type='' AND entered_at_day <> %s ORDER BY entered_at DESC LIMIT 1", $goalstatus->userID, $goal->id, Date::now()->toDay());
				if(is_null($originalLevelVal)) {
					$originalLevelVal=5;
				}
				$this->goalstatusPrintAjaxEventSave($rowID, "eventNewLevel", "onChangeEvent", PAGE_AJAX_SAVEEVENT, $goalstatus, $goal, "eventWhy", "eventLetterGrade", "");
?>
								<input type="hidden" id="eventNewLevel<?php echo $rowID;?>" value="<?php echo $newLevelVal; ?>" />
								<input type="hidden" id="eventOriginalLevel<?php echo $rowID;?>" value="<?php echo $originalLevelVal; ?>" />
								<input type="hidden" id="eventLetterGrade<?php echo $rowID;?>" value="" />
								<div class="buttons">
									<a href="#" class="plus" onclick="adjustLevel<?php echo $rowID;?>(1);">+</a>
									<a href="#" class="minus" onclick="adjustLevel<?php echo $rowID;?>(-1);">-</a>
								</div>
								<script type="text/javascript">
									function adjustLevel<?php echo $rowID;?>(adjustment) {
										var currentLevel = document.getElementById('eventNewLevel<?php echo $rowID;?>').value;
										currentLevel = parseInt(currentLevel)+adjustment;
										if((currentLevel>=1) && (currentLevel<=10)) {
											document.getElementById('eventNewLevel<?php echo $rowID;?>').value=currentLevel;
											document.getElementById('levelBox<?php echo $rowID;?>').innerHTML=currentLevel;
											var newLevelColor = "green";
											if(currentLevel<7) {
												newLevelColor = "yellow";
											}
											if(currentLevel<4) {
												newLevelColor = "red";
											}
											document.getElementById('levelBox<?php echo $rowID;?>').className="num "+newLevelColor;
											
											originalLevel = parseInt(document.getElementById('eventOriginalLevel<?php echo $rowID;?>').value);
											letterGrade = "A";
											if(currentLevel<originalLevel) {
												letterGrade = "F";
											}
											document.getElementById('eventLetterGrade<?php echo $rowID;?>').value=letterGrade;
											
											expandEvent<?php echo $rowID;?>();
											onChangeEvent<?php echo $rowID;?>();
										}
									}
									function eventButtonClicked<?php echo $rowID;?>() {
										collapseEvent<?php echo $rowID;?>();
									}
									function expandEvent<?php echo $rowID;?>() {
										document.getElementById('testsDisplay<?php echo $rowID;?>').style.display="none";
										document.getElementById('eventDisplay<?php echo $rowID;?>').style.display="block";
										document.getElementById('eventButtonDisplay<?php echo $rowID;?>').style.display="block";
									}
									function collapseEvent<?php echo $rowID;?>() {
										document.getElementById('eventDisplay<?php echo $rowID;?>').style.display="none";
										document.getElementById('eventButtonDisplay<?php echo $rowID;?>').style.display="none";
										document.getElementById('testsDisplay<?php echo $rowID;?>').style.display="block";
									}
								</script>
								<div class="holder-right" id="eventDisplay<?php echo $rowID;?>" style="display:none;">
									<fieldset>
										<textarea rows="4" cols="50" class="field" id="eventWhy<?php echo $rowID;?>" onkeyup="onChangeEvent<?php echo $rowID;?>();" ><?php echo $whyVal; ?></textarea> 
									</fieldset>
								</div>
<?php
			}
?>
								<div class="holder-right" id="testsDisplay<?php echo $rowID;?>">
<?php
		foreach($dailytests as $dailytest) {
			$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $dailytest->id)?"checked":"";
			$ajaxSaveDailytestPath = PAGE_AJAX_SAVEDAILYTEST;
?>
									<div class="row">
										<h6><?php echo GPC::strToPrintable(ucfirst($dailytest->name));?></h6>
										<div class="cl">&nbsp;</div>
										<ul class="days">
<?php
			$i=-1;
			$today = Date::now();
			foreach($dailytest->getStashedStyleArray() as $style) {
				$dayNameFirstLetter = substr(date("D", $today->shiftDays($i)->toUT()),0,1);
				$style = ($style!="")?"class='green'":"";
?>
											<li <?php echo $style;?>><?php echo $dayNameFirstLetter;?></li>
<?php
				--$i;
			}
?>
										</ul>
										<div class="cl">&nbsp;</div>
<?php
			if($isEditable) {
				$this->goalstatusPrintAjaxCheckSave($goalstatus, $dailytest, $testID, $ajaxSaveDailytestPath, "onChangeCheck", "testCheck");
?>
										<input type="checkbox" data-role="none" value="Check" id="testCheck<?php echo $testID; ?>" <?php echo $checkedVal; ?> onchange="onChangeCheck<?php echo $testID; ?>();" />
<?php
			}
?>
									</div>
<?php
			++$testID;
		}		
?>
								</div>
								<div class="cl">&nbsp;</div>
							</div>
							<input type="button" value="Done" class="button" id="eventButtonDisplay<?php echo $rowID; ?>" onclick="eventButtonClicked<?php echo $rowID; ?>()" style="display:none;" />
						</form>
					</li>
<?php
	}
	public function printGoalPage($goalID) {
		global $user, $db;
	
		$this->printHeader(NAVNAME_GOALS, array());

		$goal = Goal::getObjFromGoalID($goalID);
		$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goalID);
?>
			<h2><a href="<?php echo PAGE_USER; ?>" class="arrow" >&nbsp;</a> <?php echo $goal->name; ?></h2>
			<div class="cl">&nbsp;</div>
<?php
		if(!$userHasGoal) {
			echo "<br/><p><b><font color='white'>You do not have this goal. Please visit the web version to adopt it!</font></b></p><br/>";
		}
		else {
			$obj = $db->doQueryRFR("SELECT * FROM goals_status WHERE user_id=%s", $user->id);
			$goalstatus = GoalStatus::getObjFromDBData($obj);
			$level = $goalstatus->level;

			$goalNumColor = "green";
			if($level < 7) {
				$goalNumColor = "yellow";
			}
			if($level < 4) {
				$goalNumColor = "red";
			}
?>
			<div class="goal-detail-page">
				<p class="num <?php echo $goalNumColor;?>"><?php echo $level;?></p>
				<div class="place">
					<img src="<?php echo "template/createGraphLevelHistory.php?userID=$goalstatus->userID&goalID=$goalstatus->goalID&big";?>" alt="Level History" style="padding:20px 0 0 0;" />
				</div>
				<div class="cl">&nbsp;</div>
				<p class="description" ><strong>Current KPI:</strong> Raise blah to blah by blah date by blah date by blah date </p>
				
				<h4>Tactics:</h4>
				<ul>
					<li>
						<div class="inner">
							<p>Buy some good books</p>							
						</div>
						<a href="#" class="more" >...</a>
					</li>
					<li>
						<div class="inner">
							<p>Buy some good books asdfasd sdfsd dfsd d</p> 
						</div>
						<a href="#" class="more" >...</a>
					</li>
					<li>
						<div class="inner">
							<p>Buy some good books</p>
						</div>
						<a href="#" class="more" >...</a>
					</li>
					<li>
						<div class="inner">
							<p>Buy some good books asdfasd sdfsd dfsd d</p>
						</div>
						<a href="#" class="more" >...</a>
					</li>
				</ul>
				
				<h4>To-do's:</h4>
				
				<form action="#" method="post" class="jqtransform" >
					<ul class="list">
						<li>
							<p>Buy the Procrastinator 5000</p>
							<input type="checkbox" data-role="none"  />
						</li>
					</ul>
				</form>
			</div>
<?php
		}
		
		$this->printFooter(NAVNAME_GOALS);
	}
};

abstract class ChromeTitleElement {
	// public
	abstract public function printElement();
};

class ChromeTitleElementHeader {
	// private
	private $text;
	
	// public
	public function __construct($text) {
		$this->text=$text;
	}
	public function printElement() {
?>
					<p class="name"><?php echo $this->text; ?></p>
<?php
	}
};

class ChromeTitleElementUserPic {
	// private
	private $user;
	
	// public
	public function __construct($user) {
		$this->user=$user;
	}
	public function printElement() {
?>
					<div class="user-image">
					 	<img src="<?php echo $this->user->pictureURL; ?>" alt="<?php echo $this->user->firstName." ".$this->user->lastName;?>" />
					</div> 
<?php
	}
};

class ChromeTitleElementTabs {
	// private
	private $tabList, $activeTabIndex;
	
	// public
	// keys are titles, values are links
	public function __construct($tabList, $activeTabIndex) {
		$this->tabList = $tabList;
		$this->activeTabIndex = $activeTabIndex;
	}
	public function printElement() {
?>
					<p class="nav">
<?php
		$i=0;
		foreach($this->tabList as $title=>$link) {
			$class = "";
			if($i==0) {
				$class.="first ";
			}
			if($i==$this->activeTabIndex) {
				$class.="active ";
			}
?>
						<a href="<?php echo $link; ?>" class="<?php echo $class; ?>"><?php echo $title; ?></a>
<?php
			++$i;
		}
?>
					</p>
<?php
	}
};

// view implementation for auth system
// HACK: ultimately this should be an implementation of an auth server view class
function printHeaderAuth($title) {
	include(dirname(__FILE__)."/../template/userFacingBase.php");
	
	global $view;
	$view->printHeader(NAVNAME_NONE, array(), true);
	echo "<div style='padding:20px; background-color:white; border-color:#808080; border-style:solid; border-width:thin' >";
}
function printFooterAuth() {
	include(dirname(__FILE__)."/../template/userFacingBase.php");

	global $view;
	echo "</div>";
	$view->printFooter(NAVNAME_NONE, true);
}

?>