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
		$totalGoals = $db->doQueryOne("SELECT COUNT(*) FROM goals_status WHERE user_id=%s", $user->id);
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
	protected function goalstatusPrintList($userID, $dayUT, $isEditable) {
		global $db;

		$this->goalstatusPrintPre();
		// ignore dayUT for now
		$rs = $db->doQuery("SELECT * FROM goals_status WHERE user_id=%s", $userID);
		while($obj = mysql_fetch_object($rs)) {
			$goalstatus = GoalStatus::getObjFromDBData($obj);
			$this->goalstatusPrintGoalstatus($goalstatus, $isEditable);
		}
		$this->goalstatusPrintPost();
	}
	abstract protected function goalstatusPrintPre();
	abstract protected function goalstatusPrintPost();
	protected function goalstatusPrintGoalstatus($goalstatus, $isEditable) {
		static $rowID = 1;
		if(!$goalstatus->isActive) {
			return;
		}
		
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
		static $numDaysBack = 13;
		$dailytests = Dailytest::getListFromGoalID($goalstatus->goalID);
		// HACK: stash styleArray's in the dailytest objects
		foreach($dailytests as $dailytest) {
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
					$style="background: #7bc545;";
				}
				$styleArray[] = $style;
			}
			$dailytest->setStashedStyleArray($styleArray);
		}
		
		$this->goalstatusPrintGoalstatusPrint($goal, $rowID, $goalstatus, $plusButtonDefaultDisplay, $eventDivDefaultDisplay, $dailytests, $letterGradeVal, $newLevelVal, $whyVal, $isEditable);
		++$rowID;
	}
	abstract protected function goalstatusPrintGoalstatusPrint($goal, $rowID, $goalstatus, $plusButtonDefaultDisplay, $eventDivDefaultDisplay, $dailytests, $letterGradeVal, $newLevelVal, $whyVal, $isEditable);
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
		
		$rs = $db->doQuery("SELECT user_id FROM goals_status WHERE goal_id=%s", $goalID);
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
		global $db;
		
		$this->printHeader(NAVNAME_ACTIVITY, array(new ChromeTitleElementHeader("Activity")));
	
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
		}
	}
	public function printFooter($navSelect, $justOuterChrome=false) {
		global $user, $appAuth;
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
	
		$this->printHeader(NAVNAME_GOALS, array(new ChromeTitleElementHeader("All Goals")));

		$rs = $db->doQuery("SELECT id FROM goals");
		$numGoals = mysql_num_rows($rs);
		define('NUM_COLS', 5);
		$numPerColumn = max($numGoals/NUM_COLS,4);
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
		?>
							<!-- Case -->
							<div class="case goals">
								<!-- Cols -->
								<div class="cols">
									<p>Goals</p>
		<?php
		for($i=0; $i<NUM_COLS; ++$i) {
			if(isset($colContents[$i])) {
				echo "<div class='col'><ul>";
				foreach($colContents[$i] as $goal) {
					$pagePath = $goal->getPagePath();
					$numAdopters = $goal->getNumAdopters();
					echo "<li><a href='$pagePath'>".GPC::strToPrintable($goal->name)."</a> ($numAdopters)</li>";
				}
				echo "</ul></div>";
			}
		}
		?>
									<div class="cl">&nbsp;</div>
								</div>
								<!-- End Cols -->

								<div class="form">
									<p>Don't see your goal? Add one here:</p>
									<form action="<?php echo PAGE_GOALS;?>" method="post" name="goalForm">
										<label for="name">Goal Name:</label>
										<input type="text" class="field" value="" id="newGoalName" name="newGoalName" />
										<div class="cl">&nbsp;</div>
										<label for="description">Description:</label>
										<textarea id="newGoalDescription" name="newGoalDescription" rows="2" cols="40"></textarea>
										<div class="cl">&nbsp;</div>

										<script type="text/javascript">
											var numDailytests = 0;
											
											function addDailytest(postedTo) {
												document.goalForm.numDailytests=++numDailytests;
												document.getElementById("dailytests").innerHTML=document.getElementById("dailytests").innerHTML+
													"<label class='small-label'>Test "+numDailytests+" Name:</label><input type='text' class='small-field' name='dailytestName"+numDailytests+"' /><label class='small-label'>&nbsp;&nbsp;Description:</label><input type='text' class='small-field' name='dailytestDescription"+numDailytests+"' /><div class='cl'>&nbsp;</div>";
												document.getElementById("numDailytests").value=numDailytests;
											}
										</script>
										<div id="dailytests"></div>

										<input type="button" value="+" onclick="addDailytest();" class="small-add-btn"/>
										<input type="hidden" name="numDailytests" id="numDailytests" value="0" />
										<div class="cl" style="height:5px;">&nbsp;</div>
										<input type="submit" value="Add Goal &raquo;" class="add-btn" />
									</form>
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
	public function printUserPage($viewUser) {
		global $user, $db;
		$viewUserID = $viewUser->id;
		$viewingSelf = ($viewUserID == $user->id);
	
		define('PAGEMODE_ACTIVITY','activity');
		define('PAGEMODE_GOALS','goals');
		$mode = PAGEMODE_GOALS;
		if(isset($_GET["t"])) {
			$mode = $_GET["t"];
		}
		$tabIndex = 0;
		switch($mode) {
			case PAGEMODE_ACTIVITY:
				$tabIndex = 0;
				break;
			case PAGEMODE_GOALS:
				$tabIndex = 1;
				break;
			default:
				assert(false);
				break;
		}

		$navName = $viewingSelf?NAVNAME_YOU:NAVNAME_USERS;
		$this->printHeader($navName, 
					array(	new ChromeTitleElementUserPic($viewUser),
							new ChromeTitleElementHeader("Person: $viewUser->firstName $viewUser->lastName"),
							new ChromeTitleElementTabs(	array(	"Activity"=>PAGE_USER."?id=$viewUserID&t=".PAGEMODE_ACTIVITY,
																"Goals"=>PAGE_USER."?id=$viewUserID&t=".PAGEMODE_GOALS
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
			case PAGEMODE_GOALS:
				$currentTime=time();
				$this->goalstatusPrintList($viewUserID, $currentTime, $viewingSelf);
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
	protected function goalstatusPrintGoalstatusPrint($goal, $rowID, $goalstatus, $plusButtonDefaultDisplay, $eventDivDefaultDisplay, $dailytests, $letterGradeVal, $newLevelVal, $whyVal, $isEditable) {
		static $testID = 1;
?>
						<!-- Box -->
						<div class="box">
							<!-- GOAL TITLE & LEVEL -->
							<div class="fitness" style="width:120px">
								<a href="<?php echo $goal->getPagePath();?>" class="title"><?php echo GPC::strToPrintable($goal->name);?></a>
								<span class="number" id="currentLevel<?php echo $rowID;?>"><?php echo $goalstatus->level;?> <a href="#" class="add" id="plusButton<?php echo $rowID;?>" onclick="expandEvent<?php echo $rowID;?>();" style="display:<?php echo $plusButtonDefaultDisplay;?>;">Add</a></span>
							</div>
							<!-- ADHERENCE TESTS -->
							<div class="tests">
<?php
		foreach($dailytests as $dailytest) {
			$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $dailytest->id)?"checked":"";
			$ajaxSaveDailytestPath = PAGE_AJAX_SAVEDAILYTEST;
?>
								<div class="row">
<?php
			if($isEditable) {
				$this->goalstatusPrintAjaxCheckSave($goalstatus, $dailytest, $testID, $ajaxSaveDailytestPath, "onChangeCheck", "testCheck");
?>
									<label for="testCheck<?php echo $testID; ?>"><input type="checkbox" value="Check" id="testCheck<?php echo $testID; ?>" <?php echo $checkedVal; ?> onchange="onChangeCheck<?php echo $testID; ?>();" /></label>
<?php
			}
?>
									<div class="test-cnt">
										<p><?php echo GPC::strToPrintable($dailytest->name);?></p>
										<div class="scale">
											<ul>
<?php
			foreach($dailytest->getStashedStyleArray() as $style) {
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

							<!-- LEVEL HISTORY GRAPH -->
							<div class="placeholder">
								<div class="image">
									<img src="<?php echo "template/createGraphLevelHistory.php?userID=$goalstatus->userID&goalID=$goalstatus->goalID";?>" id="graph<?php echo $rowID;?>" alt="Level History" />
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
							</script>
<?php
			$this->goalstatusPrintAjaxEventSave($rowID, "eventNewScore", "onChangeEvent", $ajaxSaveEventPath, $goalstatus, $goal, "eventWhy", "eventLetterGrade", "currentLevel");
?>
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
	}
	public function printGoalPage($goalID) {
		global $db, $user;
	
		$goal = Goal::getObjFromGoalID($goalID);
		$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goalID);

		define('PAGEMODE_FACTS','facts');
		define('PAGEMODE_ACTIVITY','activity');
		define('PAGEMODE_PEOPLE','people');
		$mode = PAGEMODE_FACTS;
		if(isset($_GET["t"])) {
			$mode = $_GET["t"];
		}
		$tabIndex = 0;
		switch($mode) {
			case PAGEMODE_FACTS:
				$tabIndex = 0;
				break;
			case PAGEMODE_ACTIVITY:
				$tabIndex = 1;
				break;
			case PAGEMODE_PEOPLE:
				$tabIndex = 2;
				break;
			default:
				assert(false);
				break;
		}
		$this->printHeader(NAVNAME_GOALS, array(
							new ChromeTitleElementHeader("Goal: $goal->name"),
							new ChromeTitleElementTabs(	array(	"Facts"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_FACTS,
																"Activity"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_ACTIVITY,
																"People"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_PEOPLE
														), $tabIndex)
					));

		switch($mode) {
			case PAGEMODE_FACTS:
				$numAdopters = $goal->getNumAdopters();
				$average = GoalStatus::getAverageGoalScore($goalID);
				if(is_null($average)) {
					$average=0;
				}
		?>
							<!-- Case -->
							<div class="case">
								<!-- Score -->
								<div class="score">
									<div class="text">
										<p><strong>What it's all about:</strong> <?php echo $goal->description; ?></p>
		<?php
				if(!$userHasGoal) {
		?>
										<a href="<?php echo PAGE_GOAL."?id=$goalID&adopt";?>" class="btn">Adopt Goal &raquo;</a>
		<?php
				}
		?>
										<div class="cl">&nbsp;</div>
									</div>
									<div class="results">
										<ul>
											<li><p class="res-box"><span><?php echo $numAdopters; ?></span></p><p class="label">People have this goal</p></li>
											<li class="last"><p class="res-box"><span><?php echo sprintf("%1.1f",$average); ?></span></p><p class="label">Average level</p></li>
										</ul>
									</div>
									<div class="cl">&nbsp;</div>
								</div>
								<!-- End Score -->
							</div>
							<!-- End Case -->
		<?php
				break;
			case PAGEMODE_ACTIVITY:
				// only returns event type stories for this goal
				$rs = $db->doQuery("SELECT * FROM stories WHERE is_public=TRUE AND type='".EventStory::STORY_TYPENAME."' AND event_goal_id=%s ORDER BY entered_at DESC LIMIT 100", $goalID);
				$this->storyPrintListForRS($rs);
				break;
			case PAGEMODE_PEOPLE:
				$this->userPrintListByGoal($goalID);
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
</head>
<body>
<div data-role="page">
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
		}
	}
	public function printFooter($navSelect, $justOuterChrome=false) {
?>
		</div>
		<!-- END Main -->
<?php
		if(!$justOuterChrome) {
?>
		<!--  Navigation -->
		<nav>
			<ul>
				<li><a href="<?php echo PAGE_USER; ?>" class="<?php echo ($navSelect==NAVNAME_YOU)?"active":"";?>"><span class="icon icon-1" >&nbsp;</span>Daily Entry</a></li>
				<li><a href="<?php echo PAGE_ACTIVITY; ?>" class="<?php echo ($navSelect==NAVNAME_ACTIVITY)?"active":"";?>" ><span class="icon icon-2" >&nbsp;</span>Activity</a></li>
				<li><a href="<?php echo PAGE_USERS; ?>" class="<?php echo ($navSelect==NAVNAME_USERS)?"active":"";?>"><span class="icon icon-3" >&nbsp;</span>Friends</a></li>
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
	}
	protected function storyPrintEventStoryPrint($user, $goal, $eventStory, $changeWord, $goodBad, $timeSinceStr) {
?>
					<li>
<!--						<div class="img">-->
							<a href="<?php echo $user->getPagePath(); ?>"><img class="img" src="<?php echo GPC::strToPrintable($user->pictureURL); ?>" alt="" /></a>
<!--						</div>-->
						<div class="text">
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
		$this->printHeader(NAVNAME_USERS, array(new ChromeTitleElementHeader("All People")));
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
	public function printUserPage($viewUser) {
		global $user, $db;
		$viewUserID = $viewUser->id;
		$viewingSelf = ($viewUserID == $user->id);
		$navName = $viewingSelf?NAVNAME_YOU:NAVNAME_USERS;
		$this->printHeader($navName, array());

?>
			<h2 class="arrow" ><?php echo "$viewUser->firstName $viewUser->lastName"; ?> <a href="#" class="arrows expand" >&nbsp;</a></h2>
			<div class="cl">&nbsp;</div>
			
			<div class="daily-entry-page">
<?php		
		$currentTime=time();
		$this->goalstatusPrintList($viewUserID, $currentTime, $viewingSelf);
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
	protected function goalstatusPrintGoalstatusPrint($goal, $rowID, $goalstatus, $plusButtonDefaultDisplay, $eventDivDefaultDisplay, $dailytests, $letterGradeVal, $newLevelVal, $whyVal, $isEditable) {
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
			echo "<br/>You do not have this goal. Please visit the web version to adopt it!<br/>";
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