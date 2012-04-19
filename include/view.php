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
		$numGoals = 0;
		while($obj = mysql_fetch_object($rs)) {
			$goalstatus = GoalStatus::getObjFromDBData($obj);
			
			//&&&&&& Gets objects for each adopted goal
			$this->goalstatusPrintGoalstatus($goalstatus, $isEditable, $type);
			++$numGoals;
		}
		$this->goalstatusPrintPost($numGoals);
	}
	
	
	abstract protected function goalstatusPrintPre();
	abstract protected function goalstatusPrintPost($numGoals);
	abstract public function handleNoGoalForGoalPage();
	
	
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
		static $numDaysBack = 9;
		
		//&&&&&& Get all the strategies from the DB
		$dailytests = Dailytest::getListFromUserIDGoalID($goalstatus->goalID,$goalstatus->userID, 'user');
		
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
		$uniqueLastNameLetters = array();
		foreach($userListLetters as $lUser) {
			$currentLetter = strtoupper(substr($lUser->lastName, 0, 1));
			if($currentLetter != $lastLetter) {
				$lastLetter = $currentLetter;
				$this->userPrintListSectionPost();
				$this->userPrintListSectionPre($currentLetter);
				$uniqueLastNameLetters[] = $currentLetter;
			}
			$this->userPrintCard($lUser);
		}
		
		$this->userPrintListSectionPost();
		$this->userPrintListPost();
		
		return $uniqueLastNameLetters;
	}
	protected function userPrintListAll() {
		global $db;
		
		$rs = $db->doQuery("SELECT id FROM users");
		$userIDList = array();
		while($obj = mysql_fetch_object($rs)) {
			$userIDList[] = $obj->id;
		}

		return $this->userPrintListBase($userIDList);
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
	abstract public function printHeader($navSelect, $chromeTitleElements, $justOuterChrome, $justBody);
	abstract public function printFooter($navSelect, $justOuterChrome, $justBody);
	abstract public function printAboutPage();
	abstract public function printHelpPage();
	abstract public function printAllGoalsPage();
	public function printActivityPage() {
		$this->printHeader(NAVNAME_ACTIVITY, array(new ChromeTitleElementHeader("Activity")));

		$this->printActivityPageMainDiv();

		$this->printFooter(NAVNAME_ACTIVITY);
	}
	public function printActivityPageMainDiv() {
		// TEST: bare page
		global $db, $viewSwitch;
		if($viewSwitch->issetViewFlag("bare")) {
			echo "<p><b><font color='white'>ACTIVITY PAGE</font></b></p>";
			return;
		}
		$this->printActivityPagePre();
		$rs = $db->doQuery("SELECT * FROM stories WHERE is_public=TRUE ORDER BY entered_at DESC LIMIT 100");
		$this->storyPrintListForRS($rs);
		$this->printActivityPagePost();
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
	public function handleNoGoalForGoalPage() {
		redirect(PAGE_GOALS);
	}

	// public
	public function printHeader($navSelect, $chromeTitleElements, $justOuterChrome=false, $justBody=false) {
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
		<link type="text/css" href="<?php echo BASEPATH_UI;?>/web/js/css/ui-lightness/jquery-ui-1.8.19.custom.css" rel="Stylesheet" />	

		<script src="<?php echo BASEPATH_UI;?>/web/js/jquery-1.7.1.min.js" type="text/javascript"></script>
		<script src="<?php echo BASEPATH_UI;?>/web/js/jquery.jscrollpane.min.js" type="text/javascript"></script>
		<script src="<?php echo BASEPATH_UI;?>/web/js/jquery.mousewheel.js" type="text/javascript"></script>
		<script src="<?php echo BASEPATH_UI;?>/web/js/jquery.fileinput.js" type="text/javascript"></script>
		<script src="<?php echo BASEPATH_UI;?>/web/js/functions.js" type="text/javascript"></script>
		<script type="text/javascript" src="<?php echo BASEPATH_UI;?>/web/js/jquery-ui-1.8.19.custom.min.js"></script>		

		<!-- GA -->		
		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-30993891-1']);
			_gaq.push(['_setDomainName', 'superhumangoals.com']);
			_gaq.push(['_trackPageview']);

			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
		<script type="text/javascript">
			$(document).ready(function() { autoHeightContainer(); })
		</script>
		<!-- USERVOICE -->
		<script type="text/javascript">
			var uvOptions = {};
			(function() {
				var uv = document.createElement('script'); uv.type = 'text/javascript'; uv.async = true;
				uv.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'widget.uservoice.com/79l8TjaTAxyl1MaLK0VWMQ.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(uv, s);
			})();
		</script>
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
	public function printFooter($navSelect, $justOuterChrome=false, $justBody=false) {
		global $user, $appAuth, $viewSwitch;
		
		if(!$justOuterChrome) {
			PerformanceMeter::addTimestamp("Page render done");
			if($viewSwitch->issetViewFlag("pagereport")) {
				PerformanceMeter::printReport();
			}

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
					<p class="nav"><a href="<?php echo PAGE_HELP; ?>">About</a><span>|</span><a href="<?php echo PAGE_HELP; ?>">Help</a></p>
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
		$i = 0;
		while($obj = mysql_fetch_object($rs)) {
		
			$goal = Goal::getObjFromGoalID($obj->id);
			$colContents[$i][] = $goal;
			++$i;
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
		<div class="goals_section">
				<p>My Goals</p>
		<?php
		$k = 0;
		for($i=0; $i<count($colContents); ++$i) {
			if(isset($colContents[$i])) {
				echo "<div>";
				foreach($colContents[$i] as $goal) {
					$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goal->id);
					if($userHasGoal){
					$pagePath = $goal->getPagePath();
					$numAdopters = $goal->getNumAdopters();					
					
					?>
					<div class="goal_list" id="goalEntry<?php echo $k; ?>"><a href="<?php echo $pagePath;?>&t=edit"><?php echo htmlspecialchars($goal->name); ?></a> (<span id="numAdopters<?php echo $k;?>"><?php echo $numAdopters;?></span>)
					<?php 		
					if($userHasGoal){?>
					<a style="color: #999; text-decoration:none;" class="deactivate" id="deactivate<?php echo $k;?>" onclick="modifySpecificGoal('remove', <?php echo $goal->id; ?>, <?php echo $numAdopters; ?>, <?php echo $k; ?>, '<?php echo $goal->name; ?>')">remove</a>
					<?php
					}
					if($user->permissions == 1){?>
					<a style="color: red; text-decoration:none;" class="delete" id="deleteGoal<?php echo $k; ?>" onclick="modifySpecificGoal('delete', <?php echo $goal->id; ?>, <?php echo $numAdopters; ?>, <?php echo $k; ?>, '<?php echo $goal->name; ?>')"> delete</a>
					<?php 
					}
					
					?>
					</div>

					<?php
					$k = $k+1;
				}}
				echo "</div>";
				
			}
		}
		?>
		</div>
		<div style="clear:both;"/>
		
		<div class="other_goals_section">
				<p>More Goals</p>
		<?php
		for($i=0; $i<count($colContents); ++$i) {
			if(isset($colContents[$i])) {
				echo "<div>";
				foreach($colContents[$i] as $goal) {
					$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goal->id);
					if(!$userHasGoal){
					$pagePath = $goal->getPagePath();
					$numAdopters = $goal->getNumAdopters();					
					
					?>
					<div class="goal_list" id="goalEntry<?php echo $k; ?>"><a href="<?php echo $pagePath;?>&t=edit"><?php echo htmlspecialchars($goal->name); ?></a> (<span id="numAdopters<?php echo $k;?>"><?php echo $numAdopters;?></span>)
					
					<a style="color: #999; text-decoration:none;" class="deactivate" id="deactivate<?php echo $k;?>" onclick="modifySpecificGoal('remove', <?php echo $goal->id; ?>, <?php echo $numAdopters; ?>, <?php echo $k; ?>, '<?php echo $goal->name; ?>')">remove</a>
					<?php
					
					if($user->permissions == 1){?>
					<a style="color: red; text-decoration:none;" class="delete" id="deleteGoal<?php echo $k; ?>" onclick="modifySpecificGoal('delete', <?php echo $goal->id; ?>, <?php echo $numAdopters; ?>, <?php echo $k; ?>, '<?php echo $goal->name; ?>')"> delete</a>
					<?php 
					}
					
					?>
					</div>

					<?php
					$k = $k+1;
				}}
				echo "</div>";
				
			}
		}
		?>
		</div>
		
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
					var newStrategy = "<label class='small-label'>Strategy "+numDailytests+":</label><input type='text' class='small-field' name='dailytestName"+numDailytests+"' /><label class='small-label'>&nbsp;&nbsp;Description:</label><input type='text' class='small-field' name='dailytestDescription"+numDailytests+"' /><label class='small-label'>&nbsp;&nbsp;Type:</label><select name='dailytestType"+numDailytests+"'><option value='adherence'>Habit</option><option value='todo'>ToDo</option><option value='tactic'>Tactic</option></select><div class='cl'>&nbsp;</div>";

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
	</div>
<?php	}
			else {
?>
<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;Don't see your goal here? <a href="http://superhuman.uservoice.com/forums/158093-general">Let us know.</a><br/>
<?php
			}
?>
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
	<p style="margin-bottom:10px;">Welcome to Superhuman - it's great to have you here!</p>
	<p style="margin-bottom:10px;">Superhuman is a tool to track your personal goals, with community built right in.</p>
	<p style="margin-bottom:5px;">How to use Superhuman:</p>
	<font style="font-size:14px">
	1. Visit the <a href="activity.php">Activity page</a> to get a feel for the community. Maybe click on a few users to see their Goals.<br/>
	2. Visit the <a href="goals.php">Goals page</a> and adopt some Goals. A Goal is a discipline you'd like to master that's more specific than "Vehicle Operation", but more general than "Street Racing w/ a 2005 Acura". Examples: Energy, Productivity, Entrepreneurship, Fashion, Leadership, Nutrition, Chinese Language.<br/>
	3. Visit the <a href="user.php">My Goals</a> page to see your Goals.<br/>
	4. Click on a Goal to learn more about it. Click Edit to set up your Goal. You can choose KPI's (quantitative milestones you'd like to hit) and Strategies (Habits, Todos, and Tactics that can help you achieve the Goal).<br/>
	5. Do this for all of your Goals.<br/>
	6. Start visiting the My Goals page daily to track your progress. Visit the Activity page to learn from others.<br/>
	7. Become Superhuman!<br/>
	</font>
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
				echo "<div style='height:30px'>&nbsp;</div>";
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
	protected function goalstatusPrintPost($numGoals) {
?>
					</div>
					<!-- End Case -->
<?php
	}
	



	// &&&&&&
	protected function goalstatusPrintGoalstatusPrint($goal, $rowID, $goalstatus, $plusButtonDefaultDisplay, $eventDivDefaultDisplay, $dailytests, $letterGradeVal, $newLevelVal, $whyVal, $isEditable, $type) {
		global $user;
		global $viewUser;
		static $testID = 1;
		$viewUserID = $viewUser->id;
		$viewingSelf = ($viewUserID == $user->id);
		$dayID = 1;
		
		$ajaxSaveDailytestPath = PAGE_AJAX_SAVEDAILYTEST;
		$ajaxSaveEventPath = PAGE_AJAX_SAVEEVENT;
		$ajaxModifyStrategy = PAGE_AJAX_MODIFY_STRATEGY;
		$ajaxModifyKPI = PAGE_AJAX_MODIFY_KPI;
		
		$noHabitStrategies = 0;

		//print_r($dailytests);	

		if(empty($dailytests)){
			$noHabitStrategies = 1;
		}
		
		foreach($dailytests as $dailytest) {
			if($dailytest->strategy_type == 'adherence'){
				$noHabitStrategies == 0;
			}
		}
		
		if( empty($_GET['id']) ){
		      $is_user = true;
        }elseif ($_GET['id'] != $user->id){
              $is_user = false;
        }else{
              $is_user = true;
		}

?>

<script>
		///////////////////////////////////////////////////////////////////////////////
		// AJAX for modifying (adding/removing/readopting, not creating) a Strategy //
		/////////////////////////////////////////////////////////////////////////////

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
			
		//////////////////////////////////////////////////////////////////////////
		//                         Other JS                                    //
		////////////////////////////////////////////////////////////////////////
			
			
		function modify_lightbox(display, element_id, type){

			if(type == 'goal'){
				if(display == 1){
				     $("#goal-lightbox-panel"+element_id).show();
				}else{
				     $("#goal-lightbox-panel"+element_id).hide();
				}
			}else if(type == 'tactic'){
				if(display == 1){
				     $("#tactic-lightbox-panel"+element_id).show();
				}else{
				     $("#tactic-lightbox-panel"+element_id).hide();
				}
			}else if(type == 'todo'){
				if(display == 1){
				     $("#todo-lightbox-panel"+element_id).show();
				}else{
				     $("#todo-lightbox-panel"+element_id).hide();
				}
			}else if(type == 'kpi'){
				if(display == 1){
				     $("#kpi-lightbox-panel"+element_id).show();
				}else{
				     $("#kpi-lightbox-panel"+element_id).hide();
				}
			}else if(type == 'habit'){
				if(display == 1){
				     $("#habit-lightbox-panel"+element_id).show();
				}else{
				     $("#habit-lightbox-panel"+element_id).hide();
				}
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
		    $("#goal-lightbox-panel"+goal_id).fadeOut();
		    
		}

		function clearWhy(goal_id){
			$("#eventWhy" + goal_id).one("click", function(){
				$("#eventWhy" + goal_id).css("color","black");
				$("#eventWhy" + goal_id).attr("value","");
			});
		}


			////////////////////////////////////
			// AJAX for modifying a Strategy //
			//////////////////////////////////
			
			function modifyStrategy(strategy_id, goal_id, type, strategy_type){
				
				var go = false;
				
				if( type == 'edit' ) {
					var new_strategy_name = $("#newStrategyName" + strategy_id).attr("value");
										
				}else if(type == 'remove'){
					var cur_name = $("#curElementText" + strategy_id).html();
				    var answer = confirm('Remove "' + cur_name + '"?');
				}else if((type == 'create') && (strategy_type == 'todo')){
					var new_strategy_name = $("#newToDoName" + goal_id).attr("value"); 
					var new_strategy_description = $("#newToDoDescription" + goal_id).attr("value");
					var is_public = $("#newToDoIsPublic" + goal_id + " option:selected").text();
				}else if((type == 'create') && (strategy_type == 'tactic')){
					var new_strategy_name = $("#newTacticName" + goal_id).attr("value"); 
					var new_strategy_description = $("#newTacticDescription" + goal_id).attr("value");
					var is_public = $("#newTacticIsPublic" + goal_id + " option:selected").text();
				}    			

				if(is_public == "Public"){
					is_public = 1;
				}else if(is_public == "Private"){
					is_public = 0;
				}

    			if ( ( answer ) || ( type == 'create') || ( type == 'completed') || (type == 'edit' ) || (type == 'remove' ) ) {
    				var go = true;
    			}
				if( ( is_public == 'None') && (type == 'create') ) {
					go = false;
					alert("Please choose a privacy setting");										
				}
				if( ( type == 'create' ) && ( !new_strategy_name ) ){
					go = false;
					alert("Please enter a name");
				}

    			if(  go == true ){	
				    $.ajax({  
				        type: "POST", 
				        url: '<?php echo $ajaxModifyStrategy; ?>', 
				        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&strategyID="+ strategy_id+"&newStrategyName="+ new_strategy_name+"&newStrategyDescription="+ new_strategy_description+"&type="+ type+"&strategyType="+ strategy_type+"&page="+ 0+"&isPublic="+ is_public,
				        dataType: "html",
				        complete: function(data){				        
							var val = data.responseText;       	
							new_strategy_id = val;

							if( (strategy_type == 'todo') && (type == 'create') ){
								var newTodohtml = "<label for='testCheck"+new_strategy_id+"' style='float:left;'><input type='checkbox' value='Check' id='testCheck"+new_strategy_id+"' onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;completed&quot;, &quot;"+strategy_type+"&quot;)' /></label><div class='todo_label' id='strategyBox"+new_strategy_id+"'><div style='display:none;' id='element"+new_strategy_id+"'> <input id='newStrategyName"+new_strategy_id+"' type='text' value='"+new_strategy_name+"' style='width:375px; font-size:13px; color:#666;'/><button onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;edit&quot;, &quot;"+strategy_type+"&quot;)'>submit</button><button  onclick='editElement("+new_strategy_id+",0)'>cancel</button></div><span style='' id='curElementText"+new_strategy_id+"'>"+new_strategy_name+"</span><span class='editLink' id='editButton"+new_strategy_id+"' onclick='editElement("+new_strategy_id+",1)'> edit</span><span class='editLink' style='float:right;' id='removeButton"+new_strategy_id+"' onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;remove&quot;, &quot;"+strategy_type+"&quot;)'>x</span></div><div class='cl'>&nbsp;</div>";

							 	$("#new_todo_place"+goal_id).prepend(newTodohtml);
							 	$("#todo-lightbox-panel"+goal_id).hide();
							 	$("#no_todo_elements"+goal_id).hide();							
							}else if( (strategy_type == 'tactic') && (type == 'create') ){						
							
								var newTactichtml = "<div class='tactic_label' id='strategyBox"+new_strategy_id+"'><li><div style='display:none;' id='element"+new_strategy_id+"'><input id='newStrategyName"+new_strategy_id+"' type='text' value='"+new_strategy_name+"' style='width:375px; font-size:13px; color:#666;'/><button onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;edit&quot;, &quot;"+strategy_type+"&quot;)'>submit</button><button  onclick='editElement("+new_strategy_id+",0)'>cancel</button></div><span id='curElementText"+new_strategy_id+"'>"+new_strategy_name+"</span><span class='editLink' id='editButton"+new_strategy_id+">' onclick='editElement("+new_strategy_id+",1)'> edit</span><span class='editLink' style='float:right;' id='removeButton"+new_strategy_id+"' onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;remove&quot;, &quot;"+strategy_type+"&quot;)'>x</span></li></div><div class='cl'>&nbsp;</div>";

							 	$("#new_tactic_place"+goal_id).prepend(newTactichtml);
							 	$("#tactic-lightbox-panel"+goal_id).hide();
							 	$("#no_tactic_elements"+goal_id).hide();							
				        	}  
				       }  
				    });
				}
							 
				 if(type == 'edit'){
					$("#element"+strategy_id).hide();	
					$("#editButton"+strategy_id).show();
					$("#curElementText"+strategy_id).html(new_strategy_name );	
					$("#curElementText"+strategy_id).show();	
				 }else if (type == 'completed'){
				 	if($("#testCheck"+strategy_id).prop('checked') == true){
				 		$("#curElementText"+strategy_id).css("text-decoration", "line-through");
				 	}else{
				 		$("#curElementText"+strategy_id).css("text-decoration", "");
				 	}
				 }else if (type == 'remove'){
    				if (answer){
    				
				 		$("#strategyBox"+strategy_id).hide();
				 		if(strategy_type == 'todo'){
				 			$("#testCheck"+strategy_id).hide();
						}
						
				 	}
				 }
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
					
					
						
			function modifyKPI(kpi_id, goal_id, type, test_id){
				 
				 var go = false;
				 
				 if(type == 'edit'){
					var new_kpi_name = $("#newKPIName" + kpi_id).attr("value");
					var new_kpi_test_name = $("#newKPITestName" + kpi_id).attr("value");
				}else if(type == 'remove'){
					var cur_kpi_name = $("#curKPIElementText" + kpi_id).html();
				    var answer = confirm('Remove "' + cur_kpi_name + '"?');
				}else if(type == 'create'){
					var new_kpi_name = $("#newKPIName" + goal_id).attr("value"); 
					var new_kpi_description = $("#newKPIDescription" + goal_id).attr("value");
					var new_kpi_test_name = $("#newKPITestName" + goal_id).attr("value");
					var new_kpi_test_description = $("#newKPITestDescription" + goal_id).attr("value"); 
					var new_kpi_test_frequency = $("#newKPITestFrequency" + goal_id).attr("value");
					var is_public = $("#newKPIIsPublic" + goal_id + " option:selected").text();
				}


				if(is_public == "Public"){
					is_public = 1;
				}else if(is_public == "Private"){
					is_public = 0;
				}

				if((( type == 'edit' ) && ( new_kpi_name != '' ))){
					var go = true;
				}else if(((type == 'remove') && (answer))){
					var go = true;
				}else if( type == 'create' ) {
					if(new_kpi_name != ''){
						var go = true;
					}else{
						alert('Please enter a Measure/Milestone name');
					}
				}
				
				if( ( is_public == 'None') && (type == 'create') ) {
					go = false;
					alert("Please choose a privacy setting");										
				}				
				
				
				if(go == true){
				    $.ajax({  
				        type: "POST", 
				        url: '<?php echo $ajaxModifyKPI; ?>', 
				        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&kpiID="+ kpi_id+"&newKPIName="+ new_kpi_name+"&type="+ type+"&newKPITestName="+ new_kpi_test_name+"&newKPIDescription="+ new_kpi_description+"&newKPITestDescription="+ new_kpi_test_description+"&newKPITestFrequency="+ new_kpi_test_frequency+"&testID="+ test_id+"&isPublic="+ is_public,
				        dataType: "html",
				        complete: function(data){
							var val = jQuery.parseJSON(data.responseText);  

				        	// Get the new KPI id
				        	var kpi_id = val[0];
				        	// Get the new Test id
				        	var kpi_test_id = val[1];
				        	
				        	if(typeof kpi_test_id == 'undefined'){
				        		var kpi_test_id = '';
				        	}
				        	
				        	if (typeof new_kpi_test_name == 'undefined'){
								name_text = '';
				        	}
				        	
						 	if( new_kpi_test_name != '') {
						 		var display = '';
						 		var name_text = " (" + new_kpi_test_name + ")";
						 	}else{
						 		var display = 'none';
						 		var name_text = '';
						 	}
						 							 	
						 	// create the html for the new KPI and insert it into the html 
							var newKPIhtml = "<label for='testKPICheck"+kpi_id+"' style='float:left;'><input onclick='modifyKPI("+kpi_id+","+goal_id+", &quot;completed&quot;,&quot;&quot;)' type='checkbox' value='Check' id='testKPICheck"+kpi_id+"' onclick='' /></label><div class='kpi_label' id='kpiBox"+kpi_id+"'><div style='display:none;' id='KPIElement"+kpi_id+"'> Name: <input id='newKPIName"+kpi_id+"' type='text' value='"+new_kpi_name+"' style='width:275px; font-size:13px; color:#666;'/> Test: <input id='newKPITestName"+kpi_id+"' type='text' value='"+new_kpi_test_name+"' style='width:145px; font-size:13px; color:#666;'/><button onclick='modifyKPI("+kpi_id+","+goal_id+", 'edit', "+ kpi_test_id +")'>submit</button><button  onclick='editKPIElement("+kpi_id+",0)'>cancel</button></div> <span style='' id='curKPIElementText"+kpi_id+"'>"+new_kpi_name+"</span><span style='display:'"+display+"' id='curKPITestText"+kpi_id+"'>"+name_text+"</span><span class='editLink' id='editKPIButton"+kpi_id+"' onclick='editKPIElement("+kpi_id+",1)'> edit</span><span class='editLink' style='float:right;' id='removeKPIButton"+kpi_id+"' onclick='modifyKPI("+kpi_id+","+goal_id+", &quot;remove&quot;,&quot;&quot;)'>x</span></div><div class='cl'>&nbsp;</div>";
						 	
						 	$("#new_kpi_place"+goal_id).prepend(newKPIhtml);
						 	$("#kpi-lightbox-panel"+goal_id).hide();
						 	$("#no_kpi_elements"+goal_id).hide();
						 	
						 	
				        }  
				    });
			 	}


				 if(type == 'edit'){
					$("#KPIElement"+kpi_id).hide();	
					$("#editKPIButton"+kpi_id).show();
					if(new_kpi_name != ''){				
						$("#curKPIElementText"+kpi_id).html(new_kpi_name);
						$("#curKPITestText"+kpi_id).html(new_kpi_test_name);	
					}
					$("#curKPIElementText"+kpi_id).show();	
					$("#curKPITestText"+kpi_id).show();	

				 }else if(type == 'completed'){
				 
				 	if($("#testKPICheck"+kpi_id).prop('checked') == true){
				 		$("#curKPIElementText"+kpi_id).css("text-decoration", "line-through");
				 		$("#curKPITestText"+kpi_id).css("text-decoration", "line-through");
				 	}else{
				 		$("#curKPIElementText"+kpi_id).css("text-decoration", "");
				 		$("#curKPITestText"+kpi_id).css("text-decoration", "");
				 	}
				 }else if (type == 'remove'){
    				if (answer){
				 		$("#testKPICheck"+kpi_id).hide();
				 		$("#kpiBox"+kpi_id).hide();
				 	}
				 }		 
			}
			
			function editKPIElement(element_id, status){
				if(status == 1){
					$("#editKPIButton"+element_id).hide();	
					$("#curKPIElementText"+element_id).hide();
					$("#curKPITestText"+element_id).hide();
					$("#KPIElement"+element_id).fadeIn();	
				}else{
					$("#KPIElement"+element_id).hide();	
					$("#editKPIButton"+element_id).show();	
					$("#curKPIElementText"+element_id).show();
					$("#curKPITestText"+element_id).show();
				}
			}
			
			function change_lock(goal_id, type){
		
					if(type == 'todo'){
						var is_public = $("#newToDoIsPublic" + goal_id + " option:selected").text();
					}else if(type == 'tactic'){
						var is_public = $("#newTacticIsPublic" + goal_id + " option:selected").text();
					}else if(type == 'habit'){
						var is_public = $("#isPublic option:selected").text();
					}else if(type == 'kpi'){
						var is_public = $("#newKPIIsPublic" + goal_id + " option:selected").text();
					}
			
					if(is_public == "Public"){		
						$("#"+ type +"Unlocked"+goal_id).show();
						$("#"+ type +"Locked"+goal_id).hide();
					}else if(is_public == "Private"){
						$("#"+ type +"Unlocked"+goal_id).hide();
						$("#"+ type +"Locked"+goal_id).show();
					}
			}
			
			
</script>
<?php 

				if( ( $type == 'habits') && ( !empty($dailytests)) && ($noHabitStrategies != 1) ) {
				
					// Check if there are any habits that are not private. If none or if the goal is private show nothing
					$show_goal = 0;
					foreach($dailytests as $dailytest) {
						if($dailytest->strategy_type == 'adherence'){
						 	if( $dailytest->is_public == 1 ){
						 		$show_goal = 1;
						 	}
						}
					}
					
					if($goal->is_public == 0){
						$show_goal == 0;
					}
					
					if( empty($is_user) && ($show_goal == 0)){}else{
					
?>						
				<!-- Box -->
				<div class="box">
					<!-- GOAL TITLE & CATEGORY(?) -->
					<div class="habit_box" >
						<div class="habit_title"><span class="goal_level" style="margin-right:4px;" id="goalLevel<?php echo $goal->id;?>" <?php if($is_user){ ?> onclick="modify_lightbox(1, <?php echo $goal->id; ?>,'goal')" <?php } ?>> <?php echo $goalstatus->level; ?></span><a <?php if($is_user){ ?> href="<?php echo $goal->getPagePath();?>" <?php }else{ echo 'style="text-decoration:none !important;"'; }  ?>  class="title"><?php echo GPC::strToPrintable($goal->name);?></a><!--<a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo $goal->id; ?>,'goal')" href="#">+</a>--></div>
						
					<!-- Lightbox for issuing Goal Events -->
					<div class="lightbox-panel" id="goal-lightbox-panel<?php echo $goal->id; ?>" style="display:none;">
						<a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->id; ?>,'goal')">X</a>
						<div class="newscore-row">
									<span class="new_level" style="font-weight:bold;"><?php echo GPC::strToPrintable($goal->name);?> </span><br/>
									<span class="new_level">New Level:</span><input type="text" class="field" id="eventNewScore<?php echo $goal->id;?>"  />
							<div class="cl">&nbsp;</div>
						</div>
						<div class="grade-row">
							<span class="new_level">Letter grade:</span>
							<select name="grade" id="eventLetterGrade<?php echo $goal->id;?>" size="1">
								<option value="A" >A</option>
								<option value="B" >B</option>
								<option value="C" >C</option>
								<option value="D" >D</option>
								<option value="F" >F</option>
							</select>
						</div>
						<div class="cl">&nbsp;</div>
						<textarea name="textarea" style="color:#999; margin-top:5px;" onclick="clearWhy(<?php echo $goal->id;?>)" id="eventWhy<?php echo $goal->id;?>" class="field" rows="4" cols="40">Why?</textarea>
						<button type="submit" value="submit" <?php if($is_user){ ?> onclick="issueGoalEvent(<?php echo $user->id; ?>, <?php echo $goal->id; ?>, <?php echo $goalstatus->level; ?>)" <?php } ?>>submit</button>
					</div><!-- /lightbox-panel -->						
					<div class="lightbox" id="lightbox<?php echo $goal->id; ?>"> </div><!-- /lightbox -->


					<!-- Lightbox for creating Habits -->
					<div class="lightbox-panel" id="habit-lightbox-panel<?php echo $goal->id; ?>" style="display:none;">
					    <a class="close_window" id="close-panel" href="#" <?php if($is_user){ ?>  onclick="modify_lightbox(0, <?php echo $goal->id; ?>,'habit')" <?php } ?>>X</a>
					    <form method="POST" <?php if($is_user){ ?>  action="../ajax/ajax_modifyStrategy.php" <?php } ?> >

    						<div class="newscore-row">
    							<div class="new_habit" style="font-weight:bold;">New <?php echo GPC::strToPrintable($goal->name);?> Habit</div>
									<div class="new_tactic_privacy">
											<img id="habitLocked<?php echo $goal->id;?>" style="display:none;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock.png"/>
											<img id="habitUnlocked<?php echo $goal->id;?>"  src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock.png"/>
											<select onclick="change_lock(<?php echo $goal->id;?>, 'habit')" class="strategy_dropdown" name="isPublic" id="isPublic" >
										  <option >Public</option>
										  <option >Private</option>
										</select>
									</div>    							
    							<div class="new_habit">Habit Name: <input type="text" class="text_input" name="newStrategyName" id="newStrategyName<?php echo $goal->id;?>"  /></div>
    							<div class="new_habit">Habit Description: </span><input type="text" class="text_input" name="newStrategyDescription" id="newStrategyDescription<?php echo $goal->id;?>"  /><span class="optional_input">(optional)</div>
                                <input type="hidden" value="<?php echo $user->id; ?>" name="userID"/>
                                <input type="hidden" value="<?php echo $goal->id; ?>" name="goalID"/>
                                <input type="hidden" value="adherence" name="strategyType"/>
                                <input type="hidden" value="create" name="type"/>
                                <input type="hidden" value="0" name="strategyID"/>
                                <input type="hidden" value="1" name="page"/>
    							<div class="cl">&nbsp;</div>
    						</div>
    						<div class="cl">&nbsp;</div>
    						<center><button type="submit" value="submit">submit</button></center>
                        </form>
					</div><!-- /lightbox-panel -->						
					<div class="lightbox" id="lightbox<?php echo $goal->id; ?>"> </div><!-- /lightbox -->


						
					<!-- HABITS -->
					<div class="tests">
<?php
						for($t=0;$t<9;$t++){
							$today = date("D", strtotime("-".$t." day")); 	
							$today = (string)$today;
							if($t == 0){ $margin = '263px; font-size:12px; font-weight:bold';}elseif( $t == 1 ){ $margin = '14px; font-size:14px'; }else{ $margin = '7px; font-size:14px'; }
							?>
							
							<div style="float:left; margin-left:<?php echo $margin; ?>; width:44px;"><center><?php if($t == 0){ echo 'Today';}else{echo $today;}?></center></div>	
<?php									
						}
						?><?php

					foreach($dailytests as $dailytest) {
						
						if($dailytest->strategy_type == 'adherence'){
							$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $dailytest->id, date("Y-m-d"))?"checked":"";
							
							 	if( empty($is_user) && ( $dailytest->is_public == 0 ) ){}else{
	/*
							echo "<pre>";
							print_r($dailytests);
							echo "</pre>";
	*/						
	?>
							<div class="row">
									
								<div class="habit_label">
                        			<div style="display:none;" id="element<?php echo $dailytest->id;?>"> 
										<input id="newStrategyName<?php echo $dailytest->id;?>" type="text" value="<?php echo GPC::strToPrintable($dailytest->name);?>" style="width:235px; font-size:13px; color:#666;"/>
										<button <?php if($is_user){ ?>  onclick="modifyStrategy(<?php echo $dailytest->id;?>,<?php echo $goal->id;?>, 'edit', '<?php echo $dailytest->strategy_type;?>')" <?php } ?> >submit</button><button  onclick="editElement(<?php echo $dailytest->id;?>,0)">cancel</button>
									</div> 
								    <span id="curElementText<?php echo $dailytest->id;?>"><?php echo GPC::strToPrintable($dailytest->name);?></span>
								    <?php if($is_user){ ?><span class="editLink" id="editButton<?php echo $dailytest->id;?>"   onclick="editElement(<?php echo $dailytest->id;?>,1)"> edit</span> <?php } ?>

								</div>
	<?php
								if($isEditable) {
					?>
														<label for="testCheck<?php echo $dayID; echo $dailytest->id;?>"><input type="checkbox" class="dailies" value="Check" id="testCheck<?php echo $dayID; echo $dailytest->id;?>" <?php echo $checkedVal; ?>   onclick="modifyDailyStrategy(<?php echo $user->id; ?>, <?php echo $dailytest->id;?>, <?php echo $dayID; echo $dailytest->id;?>, '<?php echo date("Y-m-d");?>');"  /></label>
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
											<label for="testCheck<?php echo $dayID; echo $dailytest->id;?>"><input class="dailies" type="checkbox" value="Check" id="testCheck<?php echo $dayID; echo $dailytest->id;?>" <?php echo $checkedVal; ?> <?php if($is_user){ ?> onclick="modifyDailyStrategy(<?php echo $user->id; ?>, <?php echo $dailytest->id;?>, <?php echo $dayID; echo $dailytest->id;?>, '<?php echo $date;?>');" <?php }else{ ?> disabled="disabled" <?php } ?> /></label>
						<?php				++$dayID;
											++$r;
										}?>
										<div class="cl">&nbsp;</div>
									</div>
								</div>
								
								<div class="cl">&nbsp;</div>
							</div>
<?php					  }
						}
					}?>
					<?php if($is_user){ ?> <a class="add_habit" id="show-panel" <?php if($is_user){ ?>  onclick="modify_lightbox(1, <?php echo GPC::strToPrintable($goal->id);?>,'habit')" <?php } ?> href="#">create a new habit</a> <?php } ?>
					</div>
				</div>
					<div class="cl">&nbsp;</div>
				</div>
				<!-- End Box -->
<?php
			

			}
		}
		elseif($type == 'goals') {
			if($is_user) {
				$user_id = $user->id;
			}
			else {
				$user_id = $viewUserID;
			}
			
			$kpis = KPI::getListFromGoalID($goal->id, $user_id);

		?>
						<!-- Box -->
						<div class="box">
							<!-- GOAL TITLE & LEVEL -->
							<div class="habit_title"><span class="goal_level" style="margin-right:4px;" id="goalLevel<?php echo $goal->id;?>" <?php if($is_user){ ?> onclick="modify_lightbox(1, <?php echo $goal->id; ?>,'goal')"<?php } ?>> <?php echo $goalstatus->level; ?></span><a <?php if($is_user){ ?> href="<?php echo $goal->getPagePath();?>" <?php }else{ echo 'style="text-decoration:none !important;"'; } ?> class="title"><?php echo GPC::strToPrintable($goal->name);?></a><!--<a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo $goal->id; ?>,'goal')" href="#">+</a>--></div>
							
							<!-- %%%%%%%%%%%% LIGHTBOXES FOR ELEMENT CREATION %%%%%%%%%%%% -->
							
							<!-- Lightbox for creating Goal Events -->
							<div class="lightbox-panel" id="goal-lightbox-panel<?php echo $goal->id; ?>" style="display:none;">
							    <a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->id; ?>,'goal')">X</a>
								<div class="newscore-row">
									<span class="new_level" style="font-weight:bold;"><?php echo GPC::strToPrintable($goal->name);?> </span><br/>
									<span class="new_level">New Level:</span><input type="text" class="field" id="eventNewScore<?php echo $goal->id;?>"  />
									<div class="cl">&nbsp;</div>
								</div>
								<div class="grade-row">
									<span class="new_level">Letter grade:</span>
									<select name="grade" id="eventLetterGrade<?php echo $goal->id;?>" size="1">
										<option value="A" >A</option>
										<option value="B" >B</option>
										<option value="C" >C</option>
										<option value="D" >D</option>
										<option value="F" >F</option>
									</select>
								</div>
								<div class="cl">&nbsp;</div>
								<textarea name="textarea" style="color:#999; margin-top:5px;" onclick="clearWhy(<?php echo $goal->id;?>)" id="eventWhy<?php echo $goal->id;?>" class="field" rows="4" cols="40">Why?</textarea>
								<button type="submit" value="submit" <?php if($is_user){ ?> onclick="issueGoalEvent(<?php echo $user->id; ?>, <?php echo $goal->id; ?>, <?php echo $goalstatus->level; ?>)" <?php } ?>>submit</button>
							</div><!-- /lightbox-panel -->						
							<div class="lightbox" id="lightbox<?php echo $goal->id; ?>"> </div><!-- /lightbox -->


							<!-- Lightbox for creating Tactics -->
							<div class="lightbox-panel" id="tactic-lightbox-panel<?php echo $goal->id; ?>" style="display:none;">
							    <a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->id; ?>,'tactic')">X</a>
								<div class="newscore-row">
									<div class="new_tactic" style="font-weight:bold;">New <?php echo GPC::strToPrintable($goal->name);?> Tactic</div>
									<div class="new_tactic_privacy">
										<form>
											<img id="tacticLocked<?php echo $goal->id;?>" style="display:none;"  style="display:none;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock.png"/>
											<img id="tacticUnlocked<?php echo $goal->id;?>" src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock.png"/>
											<select onclick="change_lock(<?php echo $goal->id;?>, 'tactic', 'strategy')" class="strategy_dropdown" name="newTacticIsPublic<?php echo $goal->id;?>" id="newTacticIsPublic<?php echo $goal->id;?>" >
											  <option >Public</option>
											  <option >Private</option>
											</select>
										</form>																				
									</div>
									<div class="new_tactic">Tactic Name: <input type="text" class="text_input" id="newTacticName<?php echo $goal->id;?>"  /></div>
									<div class="new_tactic">Tactic Description: <input type="text" class="text_input" id="newTacticDescription<?php echo $goal->id;?>"  /><span class="optional_input">(optional)</span></div><br/>
									<div class="cl">&nbsp;</div>
								</div>
								<div class="cl">&nbsp;</div>
								<center><button type="submit" value="submit" <?php if($is_user){ ?> onclick="modifyStrategy('', <?php echo $goal->id; ?>, 'create','tactic')" <?php } ?>>submit</button></center>
							</div><!-- /lightbox-panel -->						
							<div class="lightbox" id="lightbox<?php echo $goal->id; ?>"> </div><!-- /lightbox -->


							<!-- Lightbox for creating Todos -->
							<div class="lightbox-panel" id="todo-lightbox-panel<?php echo $goal->id; ?>" style="display:none;">
							    <a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->id; ?>,'todo')">X</a>
								<div class="newscore-row">
									<div class="new_tactic" style="font-weight:bold;">New <?php echo GPC::strToPrintable($goal->name);?> ToDo</div>
									<div class="new_tactic_privacy">
										<form>
											<img id="todoLocked<?php echo $goal->id;?>" style="display:none;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock.png"/>
											<img id="todoUnlocked<?php echo $goal->id;?>"  src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock.png"/>
											<select onclick="change_lock(<?php echo $goal->id;?>, 'todo')" class="strategy_dropdown" name="newToDoIsPublic<?php echo $goal->id;?>" id="newToDoIsPublic<?php echo $goal->id;?>" >
											  <option >Public</option>
											  <option >Private</option>
											</select>
										</form>																				
									</div>
									<div class="new_tactic">Todo Name: <input type="text" class="text_input" id="newToDoName<?php echo $goal->id;?>"  /></div>
									<div class="new_tactic">Todo Description: </span><input type="text" class="text_input" id="newToDoDescription<?php echo $goal->id;?>"  /><span class="optional_input">(optional)</div>
									<div class="cl">&nbsp;</div>
								</div>
								<div class="cl">&nbsp;</div>
								<center><button type="submit" value="submit" <?php if($is_user){ ?> onclick="modifyStrategy('', <?php echo $goal->id; ?>, 'create','todo')" <?php } ?>>submit</button></center>
							</div><!-- /lightbox-panel -->						
							<div class="lightbox" id="lightbox<?php echo $goal->id; ?>"> </div><!-- /lightbox -->

							<!-- Lightbox for creating Measuerments and Milestones -->
							<div class="lightbox-panel" id="kpi-lightbox-panel<?php echo $goal->id; ?>" style="display:none;">
							    <a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->id; ?>,'kpi')">X</a>
								<div class="newscore-row">
									<div class="new_tactic" style="font-weight:bold;">New <?php echo GPC::strToPrintable($goal->name);?> Measure / Milestone</div>
									<div class="new_tactic_privacy">
										<img id="kpiLocked<?php echo $goal->id;?>" style="display:none;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock.png"/>
										<img id="kpiUnlocked<?php echo $goal->id;?>"  src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock.png"/>
										<select onclick="change_lock(<?php echo $goal->id;?>, 'kpi')" class="strategy_dropdown" name="newKPIIsPublic<?php echo $goal->id;?>" id="newKPIIsPublic<?php echo $goal->id;?>" >
										  <option >Public</option>
										  <option >Private</option>
										</select>																				
									</div>
									<div class="new_tactic">Name: <input type="text" class="text_input" id="newKPIName<?php echo $goal->id;?>"  /></div>
									<div class="new_tactic">Description: </span><input type="text" class="text_input" id="newKPIDescription<?php echo $goal->id;?>"  /><span class="optional_input">(optional)</div>
									<div class="new_tactic">Test Name: <input type="text" class="text_input" id="newKPITestName<?php echo $goal->id;?>"  /><span class="optional_input">(optional)</span></div>
									<div class="new_tactic">Test Description: </span><input type="text" class="text_input" id="newKPITestDescription<?php echo $goal->id;?>"  /><span class="optional_input">(optional)</div>
									<div class="new_tactic">Test Frequency: </span><input type="text" class="text_input" id="newKPITestFrequency<?php echo $goal->id;?>"  /><span class="optional_input">(optional)</div>
									<div class="cl">&nbsp;</div>
								</div>
								<div class="cl">&nbsp;</div>
								<center><button type="submit" value="submit" <?php if($is_user){ ?> onclick="modifyKPI(0, <?php echo $goal->id; ?>, 'create',0)" <?php } ?>>submit</button></center>
							</div><!-- /lightbox-panel -->						
							<div class="lightbox" id="lightbox<?php echo $goal->id; ?>"> </div><!-- /lightbox -->

<?php		
			// pull out tactics, habits, todos
			$numTactics = 0;
			$numHabits = 0;
			$numTodos = 0;
			$tactics = array();
			$habits = array();
			$todos = array();
			foreach($dailytests as $dailytest) {
				switch($dailytest->strategy_type) {
					case 'tactic':
						$tactics[] = $dailytest;
						$numTactics++;
						break;
					case 'adherence':
						$habits[] = $dailytest;
						$numHabits++;
						break;
					case 'todo':
						$todos[] = $dailytest;
						$numTodos++;
						break;
					default:
						// shouldn't be another type
						assert(false);
				}
			}

			// TACTICS
			if($is_user || ($numTactics>0)) {
?>
			<div class="user_page_items">
				<span class="user_page_sub_title"> Tactics </span><?php if($is_user){ ?><a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo GPC::strToPrintable($goal->id);?>,'tactic')" href="#">+</a><?php } ?><br/><div id="new_tactic_place<?php echo GPC::strToPrintable($goal->id);?>"></div>
<?php
			}
			if($numTactics>0) {
?>
					<ul style="list-style-type:square;">
<?php
				foreach($tactics as $tactic) {
					$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $tactic->id, date("Y-m-d"))?"checked":"";
?>
							<div class="tactic_label" id="strategyBox<?php echo $tactic->id;?>">
								<li>
									<div style="display:none;" id="element<?php echo $tactic->id;?>"> 
										<input id="newStrategyName<?php echo $tactic->id;?>" type="text" value="<?php echo GPC::strToPrintable($tactic->name);?>" style="width:375px; font-size:13px; color:#666;"/>
										<?php if($is_user){ ?><button onclick="modifyStrategy(<?php echo $tactic->id;?>,<?php echo $goal->id;?>, 'edit', '<?php echo $tactic->strategy_type;?>')">submit</button><button  onclick="editElement(<?php echo $tactic->id;?>,0)">cancel</button><?php } ?>
									</div> 
									<span id="curElementText<?php echo $tactic->id;?>"><?php echo GPC::strToPrintable($tactic->name);?></span>
									<?php if($is_user){ ?><span class="editLink" id="editButton<?php echo $tactic->id;?>" onclick="editElement(<?php echo $tactic->id;?>,1)">edit</span><span class="editLinkRemove" style="float:right;" id="removeButton<?php echo $tactic->id;?>" onclick="modifyStrategy(<?php echo $tactic->id;?>,<?php echo $goal->id;?>, 'remove', '<?php echo $tactic->strategy_type;?>')">x</span><?php } ?>
								</li>
							</div>
							<div class="cl">&nbsp;</div>
<?php
				}
?>
					</ul>
<?php
			}
			else {
				if($is_user) {
					echo "<span class='no_tactic_elements' id='no_tactic_elements" . $goal->id . "'> Adopt some Tactics here.</span>";
				}
			}
			if($is_user || ($numTactics>0)) {
?>
				</div>
<?php
			}

			// TODOS
			if($is_user || ($numTodos>0)) {
?>
		<div class="user_page_items">
			<span class="user_page_sub_title"> ToDos </span><?php if($is_user){ ?><a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo GPC::strToPrintable($goal->id);?>,'todo')" href="#">+</a><?php } ?><br/><div id="new_todo_place<?php echo GPC::strToPrintable($goal->id);?>"></div>

<?php 				
			}
			if($numTodos>0) {
				foreach($todos as $todo) {
					$checkedVal = Dailytest::getCompletedStatus($user->id, $todo->id)?"checked":"";
					if($checkedVal == "checked") {
						$strikethrough = "text-decoration: line-through;";
					}
					else {
						$strikethrough = "";
					}
					if($isEditable) {
?>
				<label for="testCheck<?php echo $todo->id;?>" style="float:left;">
					<input type="checkbox" value="Check" id="testCheck<?php echo $todo->id;?>" <?php echo $checkedVal; ?> onclick="modifyStrategy(<?php echo $todo->id;?>,<?php echo $goal->id;?>, 'completed', '<?php echo $todo->strategy_type;?>')" />
				</label>
<?php
					}
?>
				<div class="todo_label" id="strategyBox<?php echo $todo->id;?>">
						<div style="display:none;" id="element<?php echo $todo->id;?>"> 
							<input id="newStrategyName<?php echo $todo->id;?>" type="text" value="<?php echo GPC::strToPrintable($todo->name);?>" style="width:375px; font-size:13px; color:#666;"/>
							<button onclick="modifyStrategy(<?php echo $todo->id;?>,<?php echo $goal->id;?>, 'edit', '<?php echo $todo->strategy_type;?>')">submit</button><button  onclick="editElement(<?php echo $todo->id;?>,0)">cancel</button>
						</div> 
						<span style="<?php echo $strikethrough; ?>" id="curElementText<?php echo $todo->id;?>"><?php echo GPC::strToPrintable($todo->name);?></span>
						<?php if($is_user){ ?><span class="editLink" id="editButton<?php echo $todo->id;?>" onclick="editElement(<?php echo $todo->id;?>,1)">edit</span><span class="editLinkRemove" style="float:right;" id="removeButton<?php echo $todo->id;?>" onclick="modifyStrategy(<?php echo $todo->id;?>,<?php echo $goal->id;?>, 'remove', '<?php echo $todo->strategy_type;?>')">x</span><?php } ?>
				</div>
				<div class="cl">&nbsp;</div>
<?php			}
			}
			else {
				if($is_user) {
					echo "<span class='no_todo_elements' id='no_todo_elements" . $goal->id . "'> Adopt some ToDos here.</span>";
				}
			}
			if($is_user || ($numTodos>0)) {
?>
		</div>		
<?php
			}

			// KPIs
			$activeKPIs = array();
			foreach($kpis as $kpi) {
				if($kpi->kpi_active==1) {
					$activeKPIs[] = $kpi;
				}
			}
			if($is_user || count($activeKPIs)) {
?>
		<div class="user_page_items">
			<span class="user_page_sub_title"> Measurements and Milestones </span><?php if($is_user){ ?><a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo GPC::strToPrintable($goal->id);?>,'kpi')" href="#">+</a><?php } ?><br/><div id="new_kpi_place<?php echo GPC::strToPrintable($goal->id);?>"></div>
<?php 
			}
			foreach($activeKPIs as $kpi) {
				if($isEditable) {
?>
			<label for="testKPICheck<?php echo $kpi->id;?>" style="float:left;">
				<input onclick="modifyKPI(<?php echo $kpi->id;?>,<?php echo $goal->id;?>, 'completed','')" type="checkbox" value="Check" id="testKPICheck<?php echo $kpi->id;?>" <?php echo $checkedVal; ?> onclick="" />
			</label>
<?php
				}
?>
			<div class="kpi_label" id="kpiBox<?php echo $kpi->id;?>">
				<div style="display:none;" id="KPIElement<?php echo $kpi->id;?>"> 
					Name: <input id="newKPIName<?php echo $kpi->id;?>" type="text" value="<?php echo GPC::strToPrintable($kpi->kpi_name);?>" style="width:275px; font-size:13px; color:#666;"/> Test: <input id="newKPITestName<?php echo $kpi->id;?>" type="text" value="<?php if(!empty($kpi->kpi_tests[0]->test_name)){ echo $kpi->kpi_tests[0]->test_name;}?>" style="width:145px; font-size:13px; color:#666;"/>
					<button onclick="modifyKPI(<?php echo $kpi->id;?>,<?php echo $goal->id;?>, 'edit', <?php if(!empty($kpi->kpi_tests[0]->id)){ echo $kpi->kpi_tests[0]->id; }else{ echo '';} ?>)">submit</button><button  onclick="editKPIElement(<?php echo $kpi->id;?>,0)">cancel</button>
				</div> 
				<span style="" id="curKPIElementText<?php echo $kpi->id;?>"><?php echo GPC::strToPrintable($kpi->kpi_name);?></span>
					
<?php
				if(!empty($kpi->kpi_tests[0]->test_name)) {
					$isTest = "";
				}
				else {
					$isTest = "none"; 
				}

?>
				<span style='display:'<?php echo $isTest;?>' id='curKPITestText<?php echo $kpi->id;?>'><?php if(!empty($kpi->kpi_tests[0]->test_name)){ echo "("; echo $kpi->kpi_tests[0]->test_name; echo ")"; } ?></span>
				<?php if($is_user){ ?><span class="editLink" id="editKPIButton<?php echo $kpi->id;?>" onclick="editKPIElement(<?php echo $kpi->id;?>,1)">edit</span>
				<span class="editLink" style="float:right;" id="removeKPIButton<?php echo $kpi->id;?>" onclick="modifyKPI(<?php echo $kpi->id;?>,<?php echo $goal->id;?>, 'remove','')">x</span><?php } ?> 
			</div>
			<div class="cl">&nbsp;</div>
<?php					
			}
			if(!count($activeKPIs)) {
				if($is_user) {
					echo "<span class='no_kpi_elements' id='no_kpi_elements" . $goal->id . "'> Adopt some Measurements and Milestones here.</span>";
				}
			}
			if($is_user || count($activeKPIs)) {
?>
		</div>
<?php
			}
?>
	</div>
<?php
		}
	}

	public function printGoalPage($goalID) {
		global $db, $user;
		
		$obj = $db->doQueryRFR("SELECT * FROM goals_status WHERE user_id=%s AND goal_id=%s AND is_active = 1", $user->id, $goalID);
		$userHasGoal = !is_null($obj);
		$goalstatus = null;
		if($userHasGoal) {
			$goalstatus = GoalStatus::getObjFromDBData($obj);
		}

		$ajaxModifyGoal = PAGE_AJAX_MODIFY_GOAL;
		$ajaxModifyKPI = PAGE_AJAX_MODIFY_KPI;
		$ajaxCreateKPI = PAGE_AJAX_CREATE_KPI;
		$ajaxModifyTestStatus = PAGE_AJAX_MODIFY_TEST_STATUS;
		$ajaxModifyStrategy = PAGE_AJAX_MODIFY_STRATEGY;
		$ajaxCreateStrategy = PAGE_AJAX_CREATE_STRATEGY;
		$ajaxSetTracking = PAGE_AJAX_SET_TRACKING;
		$ajaxAlterGoalDescription = PAGE_AJAX_ALTER_GOAL_DESCRIPTION;
		$ajaxSaveDailytestPath = PAGE_AJAX_SAVEDAILYTEST;
		$ajaxSaveEventPath = PAGE_AJAX_SAVEEVENT;

		$goal = Goal::getFullObjFromGoalID($goalID, $user->id);

		$goal_name = $goal->goal->name;
		$goal_description = $goal->goal->description;
		
		$mode = PAGEMODE_ACTIVITY;
		
		# Get all the KPIs and the strategies for the goal being viewed
		$kpis = KPI::getListFromGoalID($goal->goal->id, $user->id);
		$dailytests = Dailytest::getListFromUserIDGoalID($goal->goal->id, $user->id, 'user');
		$adoptableStrategiesList = Dailytest::getListFromUserIDGoalID($goal->goal->id, $user->id, 'adoptable');
		
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

				static $testID = 1;
				$dayID = 1;
				$noHabitStrategies = 0;	
					
				if(	(count($dailytests) == 1) && ( $dailytests[0]->strategy_type != 'adherence')){		
					$noHabitStrategies = 1;
				}
				
?>
<script>

		//////////////////////////////////////////////////////////////////
		// Transition to edit mode when somebody elects to Adopt a goal	/
		////////////////////////////////////////////////////////////////
			
			function removeShowAdopt(){				
				modifyGoal('insert');
			}
		
			function removeGoal(){
				modifyGoal('remove');	
				$(".pre_adopt").show();
				$(".pre_adopt").css("margin-top","305px");
				$("#if_adopt").css("display","none");

			}
		
		
			function onEdit(){
					$("#if_adopt").show();
					$(".pre_adopt").hide();
			}

		/////////////////////////////////////////
		// AJAX for adopting/removing a Goal //
		///////////////////////////////////////
		
			function modifyGoal(type){

				var status = 'active';
				
				
				if (type == 'insert') {
					$('#dialog-confirm').show();
					
					$('#dialog-confirm').dialog({
					    resizable: true,
					    height: 220
					});		
							
				    $('#dialog-confirm').dialog('option', 'buttons', [
				    {
				        text: 'Private',
				        click: function() { 
				        $(this).dialog('close'); 
				        $.ajax({  
				            type: "POST", 
				            url: '<?php echo $ajaxModifyGoal; ?>', 
				            data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&type="+type+"&isPublic=0",
				            dataType: "html",
				            complete: function(data){
				                $("#ratingBox").html(data.responseText);  
				            }  
				        });  
				        
							$("#if_adopt").show();
							$(".pre_adopt").hide();
				      }
				    },
				    
				    {
				        text: 'Public',
				        click: function() { 
				        $(this).dialog('close'); 
						$.ajax({  
				            type: "POST", 
				            url: '<?php echo $ajaxModifyGoal; ?>', 
				            data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&type="+type+"&isPublic=1",
				            dataType: "html",
				            complete: function(data){
				                $("#ratingBox").html(data.responseText);  
				            }  
				        });  
						if(type == 'insert'){			
							$("#if_adopt").show();
							$(".pre_adopt").hide();
						}
				    }
				    
				    }]);
				}else{
				        $.ajax({  
				            type: "POST", 
				            url: '<?php echo $ajaxModifyGoal; ?>', 
				            data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&type="+type+"&isPublic=is_public",
				            dataType: "html",
				            complete: function(data){
				                $("#ratingBox").html(data.responseText);  
				            }  
				        });  
				}
		    }




		///////////////////////////////////////////////////////////////////////////////
		// AJAX for modifying (adding/removing/readopting, not creating) a Strategy //
		/////////////////////////////////////////////////////////////////////////////

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
			
		//////////////////////////////////////////////////////////////////////////
		//                         Other JS                                    //
		////////////////////////////////////////////////////////////////////////
			
			
		function modify_lightbox(display, element_id, type){

			if(type == 'goal'){
				if(display == 1){
				     $("#goal-lightbox-panel"+element_id).show();
				}else{
				     $("#goal-lightbox-panel"+element_id).hide();
				}
			}else if(type == 'tactic'){
				if(display == 1){
				     $("#tactic-lightbox-panel"+element_id).show();
				}else{
				     $("#tactic-lightbox-panel"+element_id).hide();
				}
			}else if(type == 'todo'){
				if(display == 1){
				     $("#todo-lightbox-panel"+element_id).show();
				}else{
				     $("#todo-lightbox-panel"+element_id).hide();
				}
			}else if(type == 'kpi'){
				if(display == 1){
				     $("#kpi-lightbox-panel"+element_id).show();
				}else{
				     $("#kpi-lightbox-panel"+element_id).hide();
				}
			}else if(type == 'habit'){
				if(display == 1){
				     $("#habit-lightbox-panel"+element_id).show();
				}else{
				     $("#habit-lightbox-panel"+element_id).hide();
				}
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
		    $("#goal-lightbox-panel" + goal_id).fadeOut();
		    
		}

		function clearWhy(goal_id){
			$("#eventWhy" + goal_id).one("click", function(){
				$("#eventWhy" + goal_id).css("color","black");
				$("#eventWhy" + goal_id).attr("value","");
			});
		}


			////////////////////////////////////
			// AJAX for modifying a Strategy //
			//////////////////////////////////
			
			function modifyStrategy(strategy_id, goal_id, type, strategy_type){
			
				var go = false;
				if( type == 'edit' ) {
					var new_strategy_name = $("#newStrategyName" + strategy_id).attr("value");
				}else if(type == 'remove'){
					var cur_name = $("#curElementText" + strategy_id).html();
				    var answer = confirm('Remove "' + cur_name + '"?');
				}else if((type == 'create') && (strategy_type == 'todo')){
					var new_strategy_name = $("#newToDoName" + goal_id).attr("value"); 
					var new_strategy_description = $("#newToDoDescription" + goal_id).attr("value");
					var is_public = $("#newToDoIsPublic" + goal_id + " option:selected").text();
				/*	alert(is_public)
					var is_public_habit = $("#newHabitIsPublic" + goal_id + " option:selected").text();
					var is_public_tactic = $("#newTacticIsPublic" + goal_id + " option:selected").text();
					var is_public_todo = $("#newToDoIsPublic" + goal_id + " option:selected").text();
					alert("habit: "  + is_public_habit);
					alert("todo: "  +is_public_todo);					
					alert("tactic: "  +is_public_tactic);
				*/
				}else if((type == 'create') && (strategy_type == 'tactic')){
					var new_strategy_name = $("#newTacticName" + goal_id).attr("value"); 
					var new_strategy_description = $("#newTacticDescription" + goal_id).attr("value");
					var is_public = $("#newTacticIsPublic" + goal_id + " option:selected").text();
				}else if((type == 'create') && (strategy_type == 'habit')){
					var new_strategy_name = $("#newHabitName" + goal_id).attr("value"); 
					var new_strategy_description = $("#newHabitDescription" + goal_id).attr("value");
					var is_public = $("#newHabitIsPublic" + goal_id + " option:selected").text();
				}
				
				if(is_public == "Public"){
					is_public = 1;
				}else if(is_public == "Private"){
					is_public = 0;
				}

    			if ( ( answer ) || ( type == 'create') || ( type == 'completed') || (type == 'edit' ) || (type == 'remove' ) || (type == 'adopt') ) {
    				var go = true;
    			}
				if( ( is_public == 'None') && (type == 'create') ) {
					go = false;
					alert("Please choose a privacy setting");										
				}
				if( ( type == 'create' ) && ( !new_strategy_name ) ){
					go = false;
					alert("Please enter a name");
				}
    			
    			if(  go == true ){	
    							
					if (type == 'adopt') {
						$('#dialog-confirm').show();
						$('#dialog-confirm').dialog({
						    resizable: true,
						    height: 220
						});		
								
					    $('#dialog-confirm').dialog('option', 'buttons', [
					    {
					        text: 'Private',
					        click: function() { 
					        $(this).dialog('close'); 
					        $.ajax({  				                
						        type: "POST", 
						        url: '<?php echo $ajaxModifyStrategy; ?>', 
						        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&strategyID="+ strategy_id+"&newStrategyName="+ new_strategy_name+"&newStrategyDescription="+ new_strategy_description+"&type="+ type+"&strategyType="+ strategy_type+"&page="+ 0+"&isPublic=0",
						        dataType: "html",
						        complete: function(data){				        
									var val = data.responseText;       	
									new_strategy_id = val;				                				                
					            }  
					        });  
					        
							if(strategy_type == 'tactic'){			
							 	$("#new_tactic_place"+goal_id).append($("#liAdopt"+strategy_id).html());
							 	$("#new_tactic_place"+goal_id).append("<br/>");
							  	$("#adoptStrategyBox"+ strategy_id +" #liAdopt"+strategy_id).remove();
							 	$("#new_tactic_place" + goal_id + " #removeButton"+strategy_id).show();				 	
							 	$("#new_tactic_place" + goal_id + " #editButton"+strategy_id).show();
							 	$("#new_tactic_place" + goal_id + " #adoptButton"+strategy_id).hide();
							}else if (strategy_type == 'todo'){
							 	$("#new_todo_place"+goal_id).append($("#liAdopt"+strategy_id).html());
							 	$("#new_todo_place"+goal_id).append("<br/>");
							  	$("#adoptStrategyBox"+ strategy_id +" #liAdopt"+strategy_id).remove();
							  	$("#testCheck"+ strategy_id).remove();
							 	$("#new_todo_place" + goal_id + " #removeButton"+strategy_id).show();				 	
							 	$("#new_todo_place" + goal_id + " #editButton"+strategy_id).show();
							 	$("#new_todo_place" + goal_id + " #adoptButton"+strategy_id).hide();
							}else if (strategy_type == 'habit'){
							 	$("#new_habit_place"+goal_id).append($("#liAdopt"+strategy_id).html());
							 	$("#new_habit_place"+goal_id).append("<br/><br/>");
							  	$("#adoptStrategyBox"+ strategy_id +" #liAdopt"+strategy_id).remove();
							  	$("#testCheck"+ strategy_id).remove();
							 	$("#new_habit_place" + goal_id + " #removeButton"+strategy_id).show();				 	
							 	$("#new_habit_place" + goal_id + " #editButton"+strategy_id).show();
							 	$("#new_habit_place" + goal_id + " #adoptButton"+strategy_id).hide();
							}
					        
					      }
					    },
					    
					    {
					        text: 'Public',
					        click: function() { 
					        $(this).dialog('close'); 
					        $.ajax({  				                
						        type: "POST", 
						        url: '<?php echo $ajaxModifyStrategy; ?>', 
						        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&strategyID="+ strategy_id+"&newStrategyName="+ new_strategy_name+"&newStrategyDescription="+ new_strategy_description+"&type="+ type+"&strategyType="+ strategy_type+"&page="+ 0+"&isPublic=1",
						        dataType: "html",
						        complete: function(data){				        
									var val = data.responseText;       	
									new_strategy_id = val;				                				                
					            }  
					        });  
					        
							if(strategy_type == 'tactic'){			
							 	$("#new_tactic_place"+goal_id).append($("#liAdopt"+strategy_id).html());
							 	$("#new_tactic_place"+goal_id).append("<br/>");
							  	$("#adoptStrategyBox"+ strategy_id +" #liAdopt"+strategy_id).remove();
							 	$("#new_tactic_place" + goal_id + " #removeButton"+strategy_id).show();				 	
							 	$("#new_tactic_place" + goal_id + " #editButton"+strategy_id).show();
							 	$("#new_tactic_place" + goal_id + " #adoptButton"+strategy_id).hide();
							}else if (strategy_type == 'todo'){
							 	$("#new_todo_place"+goal_id).append($("#liAdopt"+strategy_id).html());
							 	$("#new_todo_place"+goal_id).append("<br/>");
							  	$("#adoptStrategyBox"+ strategy_id +" #liAdopt"+strategy_id).remove();
							  	$("#testCheck"+ strategy_id).remove();
							 	$("#new_todo_place" + goal_id + " #removeButton"+strategy_id).show();				 	
							 	$("#new_todo_place" + goal_id + " #editButton"+strategy_id).show();
							 	$("#new_todo_place" + goal_id + " #adoptButton"+strategy_id).hide();
							}else if (strategy_type == 'habit'){
							 	$("#new_habit_place"+goal_id).append($("#liAdopt"+strategy_id).html());
							 	$("#new_habit_place"+goal_id).append("<br/><br/>");
							  	$("#adoptStrategyBox"+ strategy_id +" #liAdopt"+strategy_id).remove();
							  	$("#testCheck"+ strategy_id).remove();
							 	$("#new_habit_place" + goal_id + " #removeButton"+strategy_id).show();				 	
							 	$("#new_habit_place" + goal_id + " #editButton"+strategy_id).show();
							 	$("#new_habit_place" + goal_id + " #adoptButton"+strategy_id).hide();
							}
					        
					    }
					    
					    }]);
					}else{

				    $.ajax({  
				        type: "POST", 
				        url: '<?php echo $ajaxModifyStrategy; ?>', 
				        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&strategyID="+ strategy_id+"&newStrategyName="+ new_strategy_name+"&newStrategyDescription="+ new_strategy_description+"&type="+ type+"&strategyType="+ strategy_type+"&page="+ 0+"&isPublic="+ is_public,
				        dataType: "html",
				        complete: function(data){				        
							var val = data.responseText;       	
							new_strategy_id = val;

							if( (strategy_type == 'todo') && (type == 'create') ){
								var newTodohtml = "<label for='testCheck"+new_strategy_id+"' style='float:left;'><input type='checkbox' value='Check' id='testCheck"+new_strategy_id+"' onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;completed&quot;, &quot;"+strategy_type+"&quot;)' /></label><div class='todo_label' id='strategyBox"+new_strategy_id+"'><div style='display:none;' id='element"+new_strategy_id+"'> <input id='newStrategyName"+new_strategy_id+"' type='text' value='"+new_strategy_name+"' style='width:375px; font-size:13px; color:#666;'/><button onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;edit&quot;, &quot;"+strategy_type+"&quot;)'>submit</button><button  onclick='editElement("+new_strategy_id+",0)'>cancel</button></div><span style='' id='curElementText"+new_strategy_id+"'>"+new_strategy_name+"</span><span class='editLink' id='editButton"+new_strategy_id+"' onclick='editElement("+new_strategy_id+",1)'> edit</span><span class='editLink' style='float:right;' id='removeButton"+new_strategy_id+"' onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;remove&quot;, &quot;"+strategy_type+"&quot;)'>x</span></div><div class='cl'>&nbsp;</div>";

							 	$("#new_todo_place"+goal_id).prepend(newTodohtml);
							 	$("#todo-lightbox-panel"+goal_id).hide();
							 	$("#no_todo_elements"+goal_id).hide();							
							}else if( (strategy_type == 'tactic') && (type == 'create') ){						
							
								var newTactichtml = "<div class='tactic_label' id='strategyBox"+new_strategy_id+"'><li><div style='display:none;' id='element"+new_strategy_id+"'><input id='newStrategyName"+new_strategy_id+"' type='text' value='"+new_strategy_name+"' style='width:375px; font-size:13px; color:#666;'/><button onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;edit&quot;, &quot;"+strategy_type+"&quot;)'>submit</button><button  onclick='editElement("+new_strategy_id+",0)'>cancel</button></div><span id='curElementText"+new_strategy_id+"'>"+new_strategy_name+"</span><span class='editLink' id='editButton"+new_strategy_id+">' onclick='editElement("+new_strategy_id+",1)'> edit</span><span class='editLink' style='float:right;' id='removeButton"+new_strategy_id+"' onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;remove&quot;, &quot;"+strategy_type+"&quot;)'>x</span></li></div><div class='cl'>&nbsp;</div>";

							 	$("#new_tactic_place"+goal_id).prepend(newTactichtml);
							 	$("#tactic-lightbox-panel"+goal_id).hide();
							 	$("#no_tactic_elements"+goal_id).hide();							
				        	}else if( (strategy_type == 'habit') && (type == 'create') ){						
							
								var newHabithtml = "<div class='tactic_label' id='strategyBox"+new_strategy_id+"'><li><div style='display:none;' id='element"+new_strategy_id+"'><input id='newStrategyName"+new_strategy_id+"' type='text' value='"+new_strategy_name+"' style='width:375px; font-size:13px; color:#666;'/><button onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;edit&quot;, &quot;"+strategy_type+"&quot;)'>submit</button><button  onclick='editElement("+new_strategy_id+",0)'>cancel</button></div><span id='curElementText"+new_strategy_id+"'>"+new_strategy_name+"</span><span class='editLink' id='editButton"+new_strategy_id+">' onclick='editElement("+new_strategy_id+",1)'> edit</span><span class='editLink' style='float:right;' id='removeButton"+new_strategy_id+"' onclick='modifyStrategy("+new_strategy_id+","+goal_id+", &quot;remove&quot;, &quot;"+strategy_type+"&quot;)'>x</span></li></div><div class='cl'>&nbsp;</div>";

							 	$("#new_habit_place"+goal_id).prepend(newHabithtml);
							 	$("#habit-lightbox-panel"+goal_id).hide();
							 	$("#no_tactic_elements"+goal_id).hide();							
				        	}

				        	  
				       }  
				    });
				}
			}
							 
				 if(type == 'edit'){
					$("#element"+strategy_id).hide();	
					$("#editButton"+strategy_id).show();
					$("#curElementText"+strategy_id).html(new_strategy_name );	
					$("#curElementText"+strategy_id).show();	
				 }else if (type == 'completed'){
				 	if($("#testCheck"+strategy_id).prop('checked') == true){
				 		$("#curElementText"+strategy_id).css("text-decoration", "line-through");
				 	}else{
				 		$("#curElementText"+strategy_id).css("text-decoration", "");
				 	}
				 }else if (type == 'remove'){
    				if (answer){
				 		$("#strategyBox"+strategy_id).hide();
				 		if(strategy_type == 'todo'){
				 			$("#testCheck"+strategy_id).hide();
						}
				 	}
				 } 
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
										
			function modifyKPI(kpi_id, goal_id, type, test_id){
			
				go = false;
			
				 if(type == 'edit'){
					var new_kpi_name = $("#newKPIName" + kpi_id).attr("value");
					var new_kpi_test_name = $("#newKPITestName" + kpi_id).attr("value");
				}else if(type == 'remove'){
					var cur_kpi_name = $("#curKPIElementText" + kpi_id).html();
				    var answer = confirm('Remove "' + cur_kpi_name + '"?');
				}else if(type == 'create'){
					var new_kpi_name = $("#newKPIName" + goal_id).attr("value"); 
					var new_kpi_description = $("#newKPIDescription" + goal_id).attr("value");
					var new_kpi_test_name = $("#newKPITestName" + goal_id).attr("value");
					var new_kpi_test_description = $("#newKPITestDescription" + goal_id).attr("value"); 
					var new_kpi_test_frequency = $("#newKPITestFrequency" + goal_id).attr("value"); 
					var is_public = $("#newKPIIsPublic" + goal_id + " option:selected").text();
				}

				if(is_public == "Public"){
					is_public = 1;
				}else if(is_public == "Private"){
					is_public = 0;
				}

				if((( type == 'edit' ) && ( new_kpi_name != '' ))){
					var go = true;
				}else if(((type == 'remove') && (answer))){
					var go = true;
				}else if( ( type == 'create' ) || ( type == 'adopt' ) ) {
					if(new_kpi_name != ''){
						var go = true;
					}else{
						alert('Please enter a Measure/Milestone name');
					}
				}
				
				if( ( is_public == 'None') && (type == 'create') ) {
					go = false;
					alert("Please choose a privacy setting");										
				}				
				
				if(go == true){
				
					if (type == 'adopt') {
						$('#dialog-confirm').show();
						$('#dialog-confirm').dialog({
						    resizable: true,
						    height: 220
						});		
								
					    $('#dialog-confirm').dialog('option', 'buttons', [
					    {
					        text: 'Private',
					        click: function() { 
					        $(this).dialog('close'); 
					        $.ajax({  				                
						        type: "POST", 
						        url: '<?php echo $ajaxModifyKPI; ?>', 
						        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&kpiID="+ kpi_id+"&newKPIName="+ new_kpi_name+"&type="+ type+"&newKPITestName="+ new_kpi_test_name+"&newKPIDescription="+ new_kpi_description+"&newKPITestDescription="+ new_kpi_test_description+"&newKPITestFrequency="+ new_kpi_test_frequency+"&testID="+ test_id+"&isPublic=0",
						        dataType: "html",
						        complete: function(data){
									var val = jQuery.parseJSON(data.responseText);  
		
						        	// Get the new KPI id
						        	var kpi_id = val[0];
						        	// Get the new Test id
						        	var kpi_test_id = val[1];
						        	
						        	if(typeof kpi_test_id == 'undefined'){
						        		var kpi_test_id = '';
						        	}
						        	
						        	if (typeof new_kpi_test_name == 'undefined'){
										name_text = '';
						        	}
						        	
								 	if( new_kpi_test_name != '') {
								 		var display = '';
								 		var name_text = " (" + new_kpi_test_name + ")";
								 	}else{
								 		var display = 'none';
								 		var name_text = '';
								 	}

					            }  
					        });  
					        
						 	$("#new_kpi_place"+goal_id).append($("#liKPIAdopt"+kpi_id).html());
						 	$("#new_kpi_place"+goal_id).append("<br/>");
						  	$("#adoptKPIBox"+ kpi_id +" #liKPIAdopt"+kpi_id).remove();
						  	$("#testCheck"+ kpi_id).remove();
						 	$("#new_kpi_place" + goal_id + " #removeKPIButton"+kpi_id).show();				 	
						 	$("#new_kpi_place" + goal_id + " #editKPIButton"+kpi_id).show();
						 	$("#new_kpi_place" + goal_id + " #adoptKPIButton"+kpi_id).hide();
					        
					      }
					    },
					    
					    {
					        text: 'Public',
					        click: function() { 
					        $(this).dialog('close'); 
					        $.ajax({  				                
						        type: "POST", 
						        url: '<?php echo $ajaxModifyKPI; ?>', 
						        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&kpiID="+ kpi_id+"&newKPIName="+ new_kpi_name+"&type="+ type+"&newKPITestName="+ new_kpi_test_name+"&newKPIDescription="+ new_kpi_description+"&newKPITestDescription="+ new_kpi_test_description+"&newKPITestFrequency="+ new_kpi_test_frequency+"&testID="+ test_id+"&isPublic=1",
						        dataType: "html",
						        complete: function(data){
									var val = jQuery.parseJSON(data.responseText);  
		
						        	// Get the new KPI id
						        	var kpi_id = val[0];
						        	// Get the new Test id
						        	var kpi_test_id = val[1];
						        	
						        	if(typeof kpi_test_id == 'undefined'){
						        		var kpi_test_id = '';
						        	}
						        	
						        	if (typeof new_kpi_test_name == 'undefined'){
										name_text = '';
						        	}
						        	
								 	if( new_kpi_test_name != '') {
								 		var display = '';
								 		var name_text = " (" + new_kpi_test_name + ")";
								 	}else{
								 		var display = 'none';
								 		var name_text = '';
								 	}
					            }  
					        });  
					        
						 	$("#new_kpi_place"+goal_id).append($("#liKPIAdopt"+kpi_id).html());
						 	$("#new_kpi_place"+goal_id).append("<br/>");
						  	$("#adoptKPIBox"+ kpi_id +" #liKPIAdopt"+kpi_id).remove();
						  	$("#testCheck"+ kpi_id).remove();
						 	$("#new_kpi_place" + goal_id + " #removeKPIButton"+kpi_id).show();				 	
						 	$("#new_kpi_place" + goal_id + " #editKPIButton"+kpi_id).show();
						 	$("#new_kpi_place" + goal_id + " #adoptKPIButton"+kpi_id).hide();
					        
					    	}
					    
					    }]);
					  }
					}else{				
				
				    $.ajax({  
				        type: "POST", 
				        url: '<?php echo $ajaxModifyKPI; ?>', 
				        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&kpiID="+ kpi_id+"&newKPIName="+ new_kpi_name+"&type="+ type+"&newKPITestName="+ new_kpi_test_name+"&newKPIDescription="+ new_kpi_description+"&newKPITestDescription="+ new_kpi_test_description+"&newKPITestFrequency="+ new_kpi_test_frequency+"&testID="+ test_id+"&isPublic="+ is_public,
				        dataType: "html",
				        complete: function(data){
							var val = jQuery.parseJSON(data.responseText);  

				        	// Get the new KPI id
				        	var kpi_id = val[0];
				        	// Get the new Test id
				        	var kpi_test_id = val[1];
				        	
				        	if(typeof kpi_test_id == 'undefined'){
				        		var kpi_test_id = '';
				        	}
				        	
				        	if (typeof new_kpi_test_name == 'undefined'){
								name_text = '';
				        	}
				        	
						 	if( new_kpi_test_name != '') {
						 		var display = '';
						 		var name_text = " (" + new_kpi_test_name + ")";
						 	}else{
						 		var display = 'none';
						 		var name_text = '';
						 	}
						 	
						 	// create the html for the new KPI and insert it into the html 
							var newKPIhtml = "<label for='testKPICheck"+kpi_id+"' style='float:left;'><input onclick='modifyKPI("+kpi_id+","+goal_id+", &quot;completed&quot;,&quot;&quot;)' type='checkbox' value='Check' id='testKPICheck"+kpi_id+"' onclick='' /></label><div class='kpi_label' id='kpiBox"+kpi_id+"'><div style='display:none;' id='KPIElement"+kpi_id+"'> Name: <input id='newKPIName"+kpi_id+"' type='text' value='"+new_kpi_name+"' style='width:275px; font-size:13px; color:#666;'/> Test: <input id='newKPITestName"+kpi_id+"' type='text' value='"+new_kpi_test_name+"' style='width:145px; font-size:13px; color:#666;'/><button onclick='modifyKPI("+kpi_id+","+goal_id+", 'edit', "+ kpi_test_id +")'>submit</button><button  onclick='editKPIElement("+kpi_id+",0)'>cancel</button></div> <span style='' id='curKPIElementText"+kpi_id+"'>"+new_kpi_name+"</span><span style='display:'"+display+"' id='curKPITestText"+kpi_id+"'>"+name_text+"</span><span class='editLink' id='editKPIButton"+kpi_id+"' onclick='editKPIElement("+kpi_id+",1)'> edit</span><span class='editLink' style='float:right;' id='removeKPIButton"+kpi_id+"' onclick='modifyKPI("+kpi_id+","+goal_id+", &quot;remove&quot;,&quot;&quot;)'>x</span></div><div class='cl'>&nbsp;</div>";
						 	
						 	$("#new_kpi_place"+goal_id).prepend(newKPIhtml);
						 	$("#kpi-lightbox-panel"+goal_id).hide();
						 	$("#no_kpi_elements"+goal_id).hide();
				        }  
				    });
			 	}

				 if(type == 'edit'){
					$("#KPIElement"+kpi_id).hide();	
					$("#editKPIButton"+kpi_id).show();
					if(new_kpi_name != ''){				
						$("#curKPIElementText"+kpi_id).html(new_kpi_name);
						$("#curKPITestText"+kpi_id).html(new_kpi_test_name);	
					}
					$("#curKPIElementText"+kpi_id).show();	
					$("#curKPITestText"+kpi_id).show();	

				 }else if(type == 'completed'){
				 
				 	if($("#testKPICheck"+kpi_id).prop('checked') == true){
				 		$("#curKPIElementText"+kpi_id).css("text-decoration", "line-through");
				 		$("#curKPITestText"+kpi_id).css("text-decoration", "line-through");
				 	}else{
				 		$("#curKPIElementText"+kpi_id).css("text-decoration", "");
				 		$("#curKPITestText"+kpi_id).css("text-decoration", "");
				 	}
				 }else if (type == 'remove'){
    				if (answer){
				 		$("#testKPICheck"+kpi_id).hide();
				 		$("#kpiBox"+kpi_id).hide();
				 	}
				 }				 
			}
			
			function editKPIElement(element_id, status){
				if(status == 1){
					$("#editKPIButton"+element_id).hide();	
					$("#curKPIElementText"+element_id).hide();
					$("#curKPITestText"+element_id).hide();
					$("#KPIElement"+element_id).fadeIn();	
				}else{
					$("#KPIElement"+element_id).hide();	
					$("#editKPIButton"+element_id).show();	
					$("#curKPIElementText"+element_id).show();
					$("#curKPITestText"+element_id).show();
				}
			}

			function change_lock(goal_id, type){
		
					if(type == 'todo'){
						var is_public = $("#newToDoIsPublic" + goal_id + " option:selected").text();
					}else if(type == 'tactic'){
						var is_public = $("#newTacticIsPublic" + goal_id + " option:selected").text();
					}else if(type == 'habit'){
						var is_public = $("#newHabitIsPublic" + goal_id + " option:selected").text();
					}else if(type == 'kpi'){
						var is_public = $("#newKPIIsPublic" + goal_id + " option:selected").text();
					}
			
					if(is_public == "Public"){		
						$("#"+ type +"Unlocked"+goal_id).show();
						$("#"+ type +"Locked"+goal_id).hide();
					}else if(is_public == "Private"){
						$("#"+ type +"Unlocked"+goal_id).hide();
						$("#"+ type +"Locked"+goal_id).show();
					}
			}


			function changeStrategyPrivacy(goal_id, strategy_id, status){
				if(status == 'locked'){
					$("#strategyUnlocked"+goal_id+strategy_id).show();
					$("#strategyLocked"+goal_id+strategy_id).hide();
					is_public = 1;
				}else{
					$("#strategyUnlocked"+goal_id+strategy_id).hide();
					$("#strategyLocked"+goal_id+strategy_id).show();
					is_public = 0;
				}	

			    $.ajax({  
			        type: "POST", 
			        url: '<?php echo $ajaxModifyStrategy; ?>', 
			        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&strategyID="+ strategy_id+"&type=privacy"+"&isPublic="+ is_public,
			        dataType: "html",
			        complete: function(data){				        
						var val = data.responseText;       	
			       }  
			    });
			}
		
			function changeKPIPrivacy(goal_id, kpi_id, status){
				if(status == 'locked'){
					$("#kpiUnlocked"+goal_id+kpi_id).show();
					$("#kpiLocked"+goal_id+kpi_id).hide();
					is_public = 1;
				}else{
					$("#kpiUnlocked"+goal_id+kpi_id).hide();
					$("#kpiLocked"+goal_id+kpi_id).show();
					is_public = 0;
				}								

			    $.ajax({  
			        type: "POST", 
			        url: '<?php echo $ajaxModifyKPI; ?>', 
			        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&kpiID="+ kpi_id+"&type=privacy"+"&isPublic="+ is_public,
			        dataType: "html",
			        complete: function(data){
			       }  
			    });
			}

			function changeGoalPrivacy(goal_id, status){
				if(status == 'locked'){
					$("#goalUnlocked"+goal_id).show();
					$("#goalLocked"+goal_id).hide();
					is_public = 1;
				}else{
					$("#goalUnlocked"+goal_id).hide();
					$("#goalLocked"+goal_id).show();
					is_public = 0;
				}								

			    $.ajax({  
			        type: "POST", 
			        url: '<?php echo $ajaxModifyGoal; ?>', 
			        data: "userID="+<?php echo $user->id; ?>+"&goalID="+goal_id+"&type=privacy"+"&isPublic="+ is_public,
			        dataType: "html",
			        complete: function(data){
			       }  
			    });
			}

</script>

<div id="dialog-confirm" title="Public or Private?" style="display:none;">
    <p >You can always change your setting later.</p>
</div>

<?php 		if(!$userHasGoal){?>
				<div class="pre_adopt">
					<p id="suggested_description"><strong> Description:</strong> <?php echo GPC::strToPrintable($goal_description); ?></p>
					<button class="adopt-goal-btn" id="show_adopt_options" onclick="removeShowAdopt();">Adopt this goal</button>
				</div>
				
<?php 
				$display = 'none';
			}
			else{
				$display = 'block';
			}
			
			if($goal->is_public == '1'){
				$locked_status = 'none';
				$unlocked_status = '';
			}
			else{
				$locked_status = '';
				$unlocked_status = 'none';							
			}			
			?>

					<div id="if_adopt" name="if_adopt" style="display:<?php echo $display; ?>;">
						<!-- Box -->
						<div class="box">
							<!-- GOAL TITLE & LEVEL -->
							<div class="habit_title">
								<span class="goal_level" style="margin-right:4px;" id="goalLevel<?php echo $goal->goal->id;?>">
<?php 
			if(!is_null($goalstatus)) {
				echo $goalstatus->level;
			}
			else {
				echo "5";
			}
?>
								</span>
								<a href="<?php echo $goal->goal->getPagePath();?>" class="title"><?php echo GPC::strToPrintable($goal->goal->name);?></a>
								<span>
									<img id="goalLocked<?php echo $goal->goal->id;?>" class="large_lock_goal_page" class="small_lock_goal_page" onclick="changeGoalPrivacy(<?php echo $goal->goal->id;?>,'locked');" style="display:<?php echo $locked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock_small.png"/>
									<img id="goalUnlocked<?php echo $goal->goal->id;?>" class="large_lock_goal_page" onclick="changeGoalPrivacy(<?php echo $goal->goal->id;?>, 'unlocked');" style="display:<?php echo $unlocked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock_small.png"/>
								</span>
								<span style="float:right">
									<a class="add_goal_comment" style="border:1px #999 solid; width:auto; padding:3px; font-size:15px;" onclick="modify_lightbox(1, <?php echo $goal->goal->id; ?>,'goal')" >
										Set Level
									</a>
								</span>
								<!--<a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo $goal->goal->id; ?>,'goal')" href="#">+</a>--></div>

								<!-- %%%%%%%%%%%% LIGHTBOXES FOR ELEMENT CREATION %%%%%%%%%%%% -->
							
								<!-- Lightbox for creating Goal Events -->
								<div class="lightbox-panel" id="goal-lightbox-panel<?php echo $goal->goal->id; ?>" style="display:none;">
									<a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->goal->id; ?>,'goal')">X</a>
									<div class="newscore-row">
										<span class="new_level" style="font-weight:bold;"><?php echo GPC::strToPrintable($goal->goal->name);?> </span><br/>
										<span class="new_level">New Level:</span><input type="text" class="field" id="eventNewScore<?php echo $goal->goal->id;?>"  />
										<div class="cl">&nbsp;</div>
									</div>
									<div class="grade-row">
										<span class="new_level">Letter grade:</span>
										<select name="grade" id="eventLetterGrade<?php echo $goal->goal->id;?>" size="1">
											<option value="A" >A</option>
											<option value="B" >B</option>
											<option value="C" >C</option>
											<option value="D" >D</option>
											<option value="F" >F</option>
										</select>
									</div>
									<div class="cl">&nbsp;</div>
									<textarea name="textarea" style="color:#999; margin-top:5px;" onclick="clearWhy(<?php echo $goal->goal->id;?>)" id="eventWhy<?php echo $goal->goal->id;?>" class="field" rows="4" cols="40">Why?</textarea>
									<button type="submit" value="submit"  onclick="issueGoalEvent(<?php echo $user->id; ?>, <?php echo $goal->goal->id; ?>, <?php echo $goalstatus->level; ?>)" >submit</button>
								</div><!-- /lightbox-panel -->						
								<div class="lightbox" id="lightbox<?php echo $goal->goal->id; ?>"> </div><!-- /lightbox -->


								<!-- Lightbox for creating Tactics -->
								<div class="lightbox-panel" id="tactic-lightbox-panel<?php echo $goal->goal->id; ?>" style="display:none;">
									<a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->goal->id; ?>,'tactic')">X</a>
									<div class="newscore-row">
										<div class="new_tactic" style="font-weight:bold;">New <?php echo GPC::strToPrintable($goal->goal->name);?> Tactic</div>
										<div class="new_tactic_privacy">
											<form>
												<img id="tacticLocked<?php echo $goal->goal->id;?>" style="display:none;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock.png"/>
												<img id="tacticUnlocked<?php echo $goal->goal->id;?>"  src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock.png"/>
												<select onclick="change_lock(<?php echo $goal->goal->id;?>, 'tactic', 'strategy')" class="strategy_dropdown" name="newTacticIsPublic<?php echo $goal->goal->id;?>" id="newTacticIsPublic<?php echo $goal->goal->id;?>" >
												  <option >Public</option>
												  <option >Private</option>
												</select>
											</form>																				
										</div>									
										<div class="new_tactic">Tactic Name: <input type="text" class="text_input" id="newTacticName<?php echo $goal->goal->id;?>"  /></div>
										<div class="new_tactic">Tactic Description: <input type="text" class="text_input" id="newTacticDescription<?php echo $goal->goal->id;?>"  /><span class="optional_input">(optional)</span></div><br/>
										<div class="cl">&nbsp;</div>
									</div>
									<div class="cl">&nbsp;</div>
									<center><button type="submit" value="submit"  onclick="modifyStrategy('', <?php echo $goal->goal->id; ?>, 'create','tactic')" >submit</button></center>
								</div><!-- /lightbox-panel -->						
								<div class="lightbox" id="lightbox<?php echo $goal->goal->id; ?>"> </div><!-- /lightbox -->


								<!-- Lightbox for creating Todos -->
								<div class="lightbox-panel" id="todo-lightbox-panel<?php echo $goal->goal->id; ?>" style="display:none;">
									<a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->goal->id; ?>,'todo')">X</a>
									<div class="newscore-row">
										<div class="new_tactic" style="font-weight:bold;">New <?php echo GPC::strToPrintable($goal->goal->name);?> ToDo</div>
										<div class="new_tactic_privacy">
											<form>
												<img id="todoLocked<?php echo $goal->goal->id;?>" style="display:none;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock.png"/>
												<img id="todoUnlocked<?php echo $goal->goal->id;?>"  src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock.png"/>
												<select onclick="change_lock(<?php echo $goal->goal->id;?>, 'todo')" class="strategy_dropdown" name="newToDoIsPublic<?php echo $goal->goal->id;?>" id="newToDoIsPublic<?php echo $goal->goal->id;?>" >
												  <option >Public</option>
												  <option >Private</option>
												</select>
											</form>																				
										</div>
										<div class="new_tactic">Todo Name: <input type="text" class="text_input" id="newToDoName<?php echo $goal->goal->id;?>"  /></div>
										<div class="new_tactic">Todo Description: </span><input type="text" class="text_input" id="newToDoDescription<?php echo $goal->goal->id;?>"  /><span class="optional_input">(optional)</div>
										<div class="cl">&nbsp;</div>
									</div>
									<div class="cl">&nbsp;</div>
									<center><button type="submit" value="submit"  onclick="modifyStrategy('', <?php echo $goal->goal->id; ?>, 'create','todo')" >submit</button></center>
								</div><!-- /lightbox-panel -->						
								<div class="lightbox" id="lightbox<?php echo $goal->goal->id; ?>"> </div><!-- /lightbox -->




								<!-- Lightbox for creating Habits -->
								<div class="lightbox-panel" id="habit-lightbox-panel<?php echo $goal->goal->id; ?>" style="display:none;">
									<a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->goal->id; ?>,'habit')">X</a>
									<div class="newscore-row">
										<div class="new_tactic" style="font-weight:bold;">New <?php echo GPC::strToPrintable($goal->goal->name);?> Habit</div>
										<div class="new_tactic_privacy">
											<form>
												<img id="habitLocked<?php echo $goal->goal->id;?>" style="display:none;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock.png"/>
												<img id="habitUnlocked<?php echo $goal->goal->id;?>"  src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock.png"/>
												<select onclick="change_lock(<?php echo $goal->goal->id;?>, 'habit')" class="strategy_dropdown" name="newHabitIsPublic<?php echo $goal->goal->id;?>" id="newHabitIsPublic<?php echo $goal->goal->id;?>" >
											  <option >Public</option>
											  <option >Private</option>
											</select>
											</form>																				
										</div>
										<div class="new_tactic">Habit Name: <input type="text" class="text_input" id="newHabitName<?php echo $goal->goal->id;?>"  /></div>
										<div class="new_tactic">Habit Description: </span><input type="text" class="text_input" id="newHabitDescription<?php echo $goal->goal->id;?>"  /><span class="optional_input">(optional)</div>
										<div class="cl">&nbsp;</div>
									</div>
									<div class="cl">&nbsp;</div>
									<center><button type="submit" value="submit"  onclick="modifyStrategy('', <?php echo $goal->goal->id; ?>, 'create','habit')" >submit</button></center>
								</div><!-- /lightbox-panel -->						
								<div class="lightbox" id="lightbox<?php echo $goal->goal->id; ?>"> </div><!-- /lightbox -->



								<!-- Lightbox for creating Measuerments and Milestones -->
								<div class="lightbox-panel" id="kpi-lightbox-panel<?php echo $goal->goal->id; ?>" style="display:none;">
									<a class="close_window" id="close-panel" href="#" onclick="modify_lightbox(0, <?php echo $goal->goal->id; ?>,'kpi')">X</a>
									<div class="newscore-row">
										<div class="new_tactic" style="font-weight:bold;">New <?php echo GPC::strToPrintable($goal->goal->name);?> Measure / Milestone</div>
										<div class="new_tactic_privacy">
												<img id="kpiLocked<?php echo $goal->goal->id;?>" style="display:none;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock.png"/>
												<img id="kpiUnlocked<?php echo $goal->goal->id;?>"  src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock.png"/>
												<select onclick="change_lock(<?php echo $goal->goal->id;?>, 'kpi')" class="strategy_dropdown" name="newKPIIsPublic<?php echo $goal->goal->id;?>" id="newKPIIsPublic<?php echo $goal->goal->id;?>" >
												  <option >Public</option>
												  <option >Private</option>
												</select>																				
										</div>
										<div class="new_tactic">Name: <input type="text" class="text_input" id="newKPIName<?php echo $goal->goal->id;?>"  /></div>
										<div class="new_tactic">Description: </span><input type="text" class="text_input" id="newKPIDescription<?php echo $goal->goal->id;?>"  /><span class="optional_input">(optional)</div>
										<div class="new_tactic">Test Name: <input type="text" class="text_input" id="newKPITestName<?php echo $goal->goal->id;?>"  /><span class="optional_input">(optional)</span></div>
										<div class="new_tactic">Test Description: </span><input type="text" class="text_input" id="newKPITestDescription<?php echo $goal->goal->id;?>"  /><span class="optional_input">(optional)</div>
										<div class="new_tactic">Test Frequency: </span><input type="text" class="text_input" id="newKPITestFrequency<?php echo $goal->goal->id;?>"  /><span class="optional_input">(optional)</div>
										<div class="cl">&nbsp;</div>
									</div>
									<div class="cl">&nbsp;</div>
									<center><button type="submit" value="submit"  onclick="modifyKPI(0, <?php echo $goal->goal->id; ?>, 'create',0)" >submit</button></center>
								</div><!-- /lightbox-panel -->						
								<div class="lightbox" id="lightbox<?php echo $goal->goal->id; ?>"> </div><!-- /lightbox -->						
						
								<!-- Tactics start here -->	
								<div class="user_page_items">
									<span class="user_page_sub_title"> Tactics </span><a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo GPC::strToPrintable($goal->goal->id);?>,'tactic')" href="#">+</a><br/><div class="adopted_strategies" id="new_tactic_place<?php echo GPC::strToPrintable($goal->goal->id);?>"></div>
		<?php if(!empty($dailytests)){?>
						<ul style="list-style-type:square;">
<?php				$isToDo = 0;
					foreach($dailytests as $dailytest) {
					
						if($dailytest->strategy_type == 'tactic'){
							$isToDo = 1;
							$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $dailytest->id, date("Y-m-d"))?"checked":"";
							/*
							echo "<pre>";
							print_r($dailytest);
							echo "</pre>";
							*/
							
							if($dailytest->is_public == '1'){
								$locked_status = 'none';
								$unlocked_status = '';
							}else{
								$locked_status = '';
								$unlocked_status = 'none';							
							}
	?>
	
							<div class="tactic_label" id="strategyBox<?php echo $dailytest->id;?>">
								<li>
									<div style="display:none;" id="element<?php echo $dailytest->id;?>"> 
										<input id="newStrategyName<?php echo $dailytest->id;?>" type="text" value="<?php echo GPC::strToPrintable($dailytest->name);?>" style="width:375px; font-size:13px; color:#666;"/>
										<button onclick="modifyStrategy(<?php echo $dailytest->id;?>,<?php echo $goal->goal->id;?>, 'edit', '<?php echo $dailytest->strategy_type;?>')">submit</button><button  onclick="editElement(<?php echo $dailytest->id;?>,0)">cancel</button>
									</div> 
									<span id="curElementText<?php echo $dailytest->id;?>"><?php echo GPC::strToPrintable($dailytest->name);?></span>
									<span class="editLink" id="editButton<?php echo $dailytest->id;?>" onclick="editElement(<?php echo $dailytest->id;?>,1)">edit</span>
									<span class="editLinkRemove" style="float:right;" id="removeButton<?php echo $dailytest->id;?>" onclick="modifyStrategy(<?php echo $dailytest->id;?>,<?php echo $goal->goal->id;?>, 'remove', '<?php echo $dailytest->strategy_type;?>')">x</span>
									<span>
										<img id="strategyLocked<?php echo $goal->goal->id;?><?php echo $dailytest->id;?>" class="small_lock_goal_page" class="small_lock_goal_page" onclick="changeStrategyPrivacy(<?php echo $goal->goal->id;?>,<?php echo $dailytest->id;?>,'locked');" style="display:<?php echo $locked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock_small.png"/>
										<img id="strategyUnlocked<?php echo $goal->goal->id;?><?php echo $dailytest->id;?>" class="small_lock_goal_page" onclick="changeStrategyPrivacy(<?php echo $goal->goal->id;?>,<?php echo $dailytest->id;?>, 'unlocked');" style="display:<?php echo $unlocked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock_small.png"/>
									</span>

								</li>
							</div>
							<div class="cl">&nbsp;</div>
<?php					}
					}
					?></ul> <?php
					if($isToDo == 0){
					 echo "<span class='no_tactic_elements' id='no_tactic_elements" . $goal->goal->id . "'>  Adopt some Tactics here.</span>";
					}
					?>
			<?php }else{
					 echo "<span class='no_tactic_elements' id='no_tactic_elements" . $goal->goal->id . "'> Adopt some Tactics here.</span>";
			}?>
			
			
				<div style="clear:both; margin-top:15px;"/>
				<div class="adoptable_strategies">
				<span class="user_page_sub_title"> Adoptable Tactics </span><br/>
		<?php if(!empty($adoptableStrategiesList)){?>
						<ul style="list-style-type:square;">
<?php				$isToDo = 0;
					foreach($adoptableStrategiesList as $adoptableStrategiesItem) {
					
						if($adoptableStrategiesItem->strategy_type == 'tactic'){
							$isToDo = 1;
							$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $adoptableStrategiesItem->id, date("Y-m-d"))?"checked":"";
							
							if($adoptableStrategiesItem->is_public == '1'){
								$locked_status = 'none';
								$unlocked_status = '';
							}else{
								$locked_status = '';
								$unlocked_status = 'none';							
							}
	?>
	
							<div class="tactic_label" id="adoptStrategyBox<?php echo $adoptableStrategiesItem->id;?>">
								<li id="liAdopt<?php echo $adoptableStrategiesItem->id;?>">
									<div style="display:none;" id="element<?php echo $adoptableStrategiesItem->id;?>"> 
										<input id="newStrategyName<?php echo $adoptableStrategiesItem->id;?>" type="text" value="<?php echo GPC::strToPrintable($adoptableStrategiesItem->name);?>" style="width:375px; font-size:13px; color:#666;"/>
										<button onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id;?>, 'edit', '<?php echo $adoptableStrategiesItem->strategy_type;?>')">submit</button><button  onclick="editElement(<?php echo $adoptableStrategiesItem->id;?>,0)">cancel</button>
									</div>
									
									<div id="strategyBox<?php echo $adoptableStrategiesItem->id;?>">
										<span id="curElementText<?php echo $adoptableStrategiesItem->id;?>"><?php echo GPC::strToPrintable($adoptableStrategiesItem->name);?></span>
										
										<span class="editLink" id="adoptButton<?php echo $adoptableStrategiesItem->id;?>" onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id; ?>, 'adopt', 'tactic')">adopt</span>
										<span class="editLink" style="display:none;" id="editButton<?php echo $adoptableStrategiesItem->id;?>" onclick="editElement(<?php echo $adoptableStrategiesItem->id;?>,1)">edit</span>
										<span class="editLinkRemove"  style="float:right; display:none;" id="removeButton<?php echo $adoptableStrategiesItem->id;?>" onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id;?>, 'remove', '<?php echo $adoptableStrategiesItem->strategy_type;?>')">x</span>
									</div>
								</li>
							</div>
							<div class="cl">&nbsp;</div>
<?php					}
					}
					?></ul> <?php
					if($isToDo == 0){
					 echo "<span class='no_tactic_elements' id='no_tactic_elements" . $goal->goal->id . "'>  Adopt some Tactics here.</span>";
					}
					?>
			<?php }else{
					 echo "<span class='no_tactic_elements' id='no_tactic_elements" . $goal->goal->id . "'> Adopt some Tactics here.</span>";
			}?>
				</div>	
				</div>		
			</div>		


		<!-- TODOS start here -->
		<div class="user_page_items">
			<span class="user_page_sub_title"> ToDos </span><a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo GPC::strToPrintable($goal->goal->id);?>,'todo')" href="#">+</a><br/><div class="adopted_strategies" id="new_todo_place<?php echo GPC::strToPrintable($goal->goal->id);?>"></div>
		<?php 				
				if(!empty($dailytests)){?>
<?php				$isToDo = 0;
					foreach($dailytests as $dailytest) {
					
						if($dailytest->strategy_type == 'todo'){
							$isToDo = 1;
							$checkedVal = Dailytest::getCompletedStatus($user->id, $dailytest->id)?"checked":"";
							if($checkedVal == "checked"){
								$strikethrough = "text-decoration: line-through;";
							}else{
								$strikethrough = "";
							}
							
							if($dailytest->is_public == '1'){
								$locked_status = 'none';
								$unlocked_status = '';
							}else{
								$locked_status = '';
								$unlocked_status = 'none';							
							}
				?>
									<label for="testCheck<?php echo $dailytest->id;?>" style="float:left;">
										<input type="checkbox" value="Check" id="testCheck<?php echo $dailytest->id;?>" <?php echo $checkedVal; ?> onclick="modifyStrategy(<?php echo $dailytest->id;?>,<?php echo $goal->goal->id;?>, 'completed', '<?php echo $dailytest->strategy_type;?>')" />
									</label>

							<div class="todo_label" id="strategyBox<?php echo $dailytest->id;?>">
									<div style="display:none;" id="element<?php echo $dailytest->id;?>"> 
										<input id="newStrategyName<?php echo $dailytest->id;?>" type="text" value="<?php echo GPC::strToPrintable($dailytest->name);?>" style="width:375px; font-size:13px; color:#666;"/>
										<button onclick="modifyStrategy(<?php echo $dailytest->id;?>,<?php echo $goal->goal->id;?>, 'edit', '<?php echo $dailytest->strategy_type;?>')">submit</button><button  onclick="editElement(<?php echo $dailytest->id;?>,0)">cancel</button>
									</div> 
									<span style="<?php echo $strikethrough; ?>" id="curElementText<?php echo $dailytest->id;?>"><?php echo GPC::strToPrintable($dailytest->name);?></span>
									<span class="editLink" id="editButton<?php echo $dailytest->id;?>" onclick="editElement(<?php echo $dailytest->id;?>,1)">edit</span>
									<span class="editLinkRemove" style="float:right;" id="removeButton<?php echo $dailytest->id;?>" onclick="modifyStrategy(<?php echo $dailytest->id;?>,<?php echo $goal->goal->id;?>, 'remove', '<?php echo $dailytest->strategy_type;?>')">x</span>
									<span>
										<img id="strategyLocked<?php echo $goal->goal->id;?><?php echo $dailytest->id;?>" class="small_lock_goal_page" class="small_lock_goal_page" onclick="changeStrategyPrivacy(<?php echo $goal->goal->id;?>,<?php echo $dailytest->id;?>,'locked');" style="display:<?php echo $locked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock_small.png"/>
										<img id="strategyUnlocked<?php echo $goal->goal->id;?><?php echo $dailytest->id;?>" class="small_lock_goal_page" onclick="changeStrategyPrivacy(<?php echo $goal->goal->id;?>,<?php echo $dailytest->id;?>, 'unlocked');" style="display:<?php echo $unlocked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock_small.png"/>
									</span>
							</div>
							<div class="cl">&nbsp;</div>
<?php					}
					}
					if($isToDo == 0){
						 echo "<span class='no_todo_elements' id='no_todo_elements" . $goal->goal->id . "'> Adopt some ToDos here.</span>";
					}?>
			<?php }else{
					     echo "<span class='no_todo_elements' id='no_todo_elements" . $goal->goal->id . "'> Adopt some ToDos here.</span>";
			}?>			

				<div style="clear:both;"/>
				
			<div class="adoptable_strategies">
			<span class="user_page_sub_title"> Adoptable ToDos </span><br/><div id="new_todo_place<?php echo GPC::strToPrintable($goal->goal->id);?>"></div>
		<?php 				
				if(!empty($adoptableStrategiesList)){?>
<?php				$isToDo = 0;
					foreach($adoptableStrategiesList as $adoptableStrategiesItem) {
					
						if($adoptableStrategiesItem->strategy_type == 'todo'){
							$isToDo = 1;
							$checkedVal = Dailytest::getCompletedStatus($user->id, $adoptableStrategiesItem->id)?"checked":"";
							if($checkedVal == "checked"){
								$strikethrough = "text-decoration: line-through;";
							}else{
								$strikethrough = "";
							}
							
							
							if($adoptableStrategiesItem->is_public == '1'){
								$locked_status = 'none';
								$unlocked_status = '';
							}else{
								$locked_status = '';
								$unlocked_status = 'none';							
							}
							
							
							
				?>
									<label for="testCheck<?php echo $adoptableStrategiesItem->id;?>" style="float:left;">
										<input type="checkbox" value="Check" id="testCheck<?php echo $adoptableStrategiesItem->id;?>" <?php echo $checkedVal; ?> onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id;?>, 'completed', '<?php echo $adoptableStrategiesItem->strategy_type;?>')" />
									</label>

							<div class="adopt_todo_label" id="adoptStrategyBox<?php echo $adoptableStrategiesItem->id;?>">
								<div id="liAdopt<?php echo $adoptableStrategiesItem->id;?>">
									<div style="display:none;" id="element<?php echo $adoptableStrategiesItem->id;?>"> 
										<input id="newStrategyName<?php echo $adoptableStrategiesItem->id;?>" type="text" value="<?php echo GPC::strToPrintable($adoptableStrategiesItem->name);?>" style="width:375px; font-size:13px; color:#666;"/>
										<button onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id;?>, 'edit', '<?php echo $adoptableStrategiesItem->strategy_type;?>')">submit</button><button  onclick="editElement(<?php echo $adoptableStrategiesItem->id;?>,0)">cancel</button>
									</div> 
									
									<div id="strategyBox<?php echo $adoptableStrategiesItem->id;?>">
										<span style="<?php echo $strikethrough; ?>" id="curElementText<?php echo $adoptableStrategiesItem->id;?>"><?php echo GPC::strToPrintable($adoptableStrategiesItem->name);?></span>
										<span class="editLink" id="adoptButton<?php echo $adoptableStrategiesItem->id;?>" onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id; ?>, 'adopt', 'todo')">adopt</span>
										<span class="editLink" style="display:none;" id="editButton<?php echo $adoptableStrategiesItem->id;?>" onclick="editElement(<?php echo $adoptableStrategiesItem->id;?>,1)">edit</span>
										<span class="editLinkRemove"  style="float:right; display:none;" style="float:right;" id="removeButton<?php echo $adoptableStrategiesItem->id;?>" onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id;?>, 'remove', '<?php echo $adoptableStrategiesItem->strategy_type;?>')">x</span>
								</div>
							</div>
							</div>
							<div class="cl">&nbsp;</div>
<?php					}
					}
					if($isToDo == 0){
						 echo "<span class='no_todo_elements' id='no_todo_elements" . $goal->goal->id . "'> Adopt some ToDos here.</span>";
					}?>
			<?php }else{
					     echo "<span class='no_todo_elements' id='no_todo_elements" . $goal->goal->id . "'> Adopt some ToDos here.</span>";
			}?>			
		</div>		
		</div>
		</div>


		<!-- Habits start here -->
		<div class="user_page_items">
			<span class="user_page_sub_title"> Habits </span><a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo GPC::strToPrintable($goal->goal->id);?>,'habit')" href="#">+</a><br/><div class="adopted_strategies"  id="new_habit_place<?php echo GPC::strToPrintable($goal->goal->id);?>"></div>
		<?php 				
				if(!empty($dailytests)){?>
<?php				$isToDo = 0;
					foreach($dailytests as $dailytest) {
						if($dailytest->strategy_type == 'adherence'){
							$isToDo = 1;
							$checkedVal = Dailytest::getCompletedStatus($user->id, $dailytest->id)?"checked":"";
							if($checkedVal == "checked"){
								$strikethrough = "text-decoration: line-through;";
							}
							else{
								$strikethrough = "";
							}
							if($dailytest->is_public == '1'){
								$locked_status = 'none';
								$unlocked_status = '';
							}
							else{
								$locked_status = '';
								$unlocked_status = 'none';							
							}
				?>
							<div class="todo_label" id="strategyBox<?php echo $dailytest->id;?>">
									<div style="display:none;" id="element<?php echo $dailytest->id;?>"> 
										<input id="newStrategyName<?php echo $dailytest->id;?>" type="text" value="<?php echo GPC::strToPrintable($dailytest->name);?>" style="width:375px; font-size:13px; color:#666;"/>
										<button onclick="modifyStrategy(<?php echo $dailytest->id;?>,<?php echo $goal->goal->id;?>, 'edit', '<?php echo $dailytest->strategy_type;?>')">submit</button><button  onclick="editElement(<?php echo $dailytest->id;?>,0)">cancel</button>
									</div> 
									<span style="<?php echo $strikethrough; ?>" id="curElementText<?php echo $dailytest->id;?>"><?php echo GPC::strToPrintable($dailytest->name);?></span>
									<span class="editLinkRemove" style="float:right;" id="removeButton<?php echo $dailytest->id;?>" onclick="modifyStrategy(<?php echo $dailytest->id;?>,<?php echo $goal->goal->id;?>, 'remove', '<?php echo $dailytest->strategy_type;?>')">x</span>
									<span>
										<img id="strategyLocked<?php echo $goal->goal->id;?><?php echo $dailytest->id;?>" class="small_lock_goal_page" class="small_lock_goal_page" onclick="changeStrategyPrivacy(<?php echo $goal->goal->id;?>,<?php echo $dailytest->id;?>,'locked');" style="display:<?php echo $locked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock_small.png"/>
										<img id="strategyUnlocked<?php echo $goal->goal->id;?><?php echo $dailytest->id;?>" class="small_lock_goal_page" onclick="changeStrategyPrivacy(<?php echo $goal->goal->id;?>,<?php echo $dailytest->id;?>, 'unlocked');" style="display:<?php echo $unlocked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock_small.png"/>
									</span>
									<span class="editLink" id="editButton<?php echo $dailytest->id;?>" onclick="editElement(<?php echo $dailytest->id;?>,1)">edit</span>
							</div>
							<div class="cl">&nbsp;</div>
<?php					}
					}
					if($isToDo == 0){
						 echo "<span class='no_habit_elements' id='no_todo_elements" . $goal->goal->id . "'> Adopt some Habits here.</span>";
					}?>
			<?php }else{
					     echo "<span class='no_habit_elements' id='no_todo_elements" . $goal->goal->id . "'> Adopt some Habits here.</span>";
			}?>			

				<div style="clear:both;"/>
				
				
				
				
			<div class="adoptable_strategies">
			<span class="user_page_sub_title"> Adoptable Habits </span><br/>
		<?php 				
				if(!empty($adoptableStrategiesList)){?>
<?php				$isToDo = 0;
					foreach($adoptableStrategiesList as $adoptableStrategiesItem) {
					
						if($adoptableStrategiesItem->strategy_type == 'adherence'){
							$isToDo = 1;
							$checkedVal = Dailytest::getCompletedStatus($user->id, $adoptableStrategiesItem->id)?"checked":"";
							if($checkedVal == "checked"){
								$strikethrough = "text-decoration: line-through;";
							}else{
								$strikethrough = "";
							}
							
							if($adoptableStrategiesItem->is_public == '1'){
								$locked_status = 'none';
								$unlocked_status = '';
							}else{
								$locked_status = '';
								$unlocked_status = 'none';							
							}
							
							
				?>
							<div class="adopt_todo_label" id="adoptStrategyBox<?php echo $adoptableStrategiesItem->id;?>">
								<div id="liAdopt<?php echo $adoptableStrategiesItem->id;?>">
									<div style="display:none;" id="element<?php echo $adoptableStrategiesItem->id;?>"> 
										<input id="newStrategyName<?php echo $adoptableStrategiesItem->id;?>" type="text" value="<?php echo GPC::strToPrintable($adoptableStrategiesItem->name);?>" style="width:375px; font-size:13px; color:#666;"/>
										<button onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id;?>, 'edit', '<?php echo $adoptableStrategiesItem->strategy_type;?>')">submit</button><button  onclick="editElement(<?php echo $adoptableStrategiesItem->id;?>,0)">cancel</button>
									</div> 
									
								<div id="strategyBox<?php echo $adoptableStrategiesItem->id;?>">
									<span style="<?php echo $strikethrough; ?>" id="curElementText<?php echo $adoptableStrategiesItem->id;?>"><?php echo GPC::strToPrintable($adoptableStrategiesItem->name);?></span>
									<span class="editLink" id="adoptButton<?php echo $adoptableStrategiesItem->id;?>" onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id; ?>, 'adopt', 'habit')">adopt</span>
									<span class="editLink" style="display:none;" id="editButton<?php echo $adoptableStrategiesItem->id;?>" onclick="editElement(<?php echo $adoptableStrategiesItem->id;?>,1)">edit</span>
									<span class="editLinkRemove" style="display:none; float:right;" id="removeButton<?php echo $adoptableStrategiesItem->id;?>" onclick="modifyStrategy(<?php echo $adoptableStrategiesItem->id;?>,<?php echo $goal->goal->id;?>, 'remove', '<?php echo $adoptableStrategiesItem->strategy_type;?>')">x</span>
							</div>
							</div>
							</div>
							<div class="cl">&nbsp;</div>
<?php					}
					}
					if($isToDo == 0){
						 echo "<span class='no_todo_elements' id='no_todo_elements" . $goal->goal->id . "'> Adopt some Habits here.</span>";
					}?>
			<?php }else{
					     echo "<span class='no_todo_elements' id='no_todo_elements" . $goal->goal->id . "'> Adopt some Habits here.</span>";
			}?>			
		</div>		
		</div>
		</div>



		<!-- KPIS start here -->
					<div class="user_page_items">
						<span class="user_page_sub_title"> Measurements and Milestones </span><a class="add_goal_comment" id="show-panel" onclick="modify_lightbox(1, <?php echo GPC::strToPrintable($goal->goal->id);?>,'kpi')" href="#">+</a><br/><div class="adopted_strategies" id="new_kpi_place<?php echo GPC::strToPrintable($goal->goal->id);?>"></div>
		<?php 
			 $kpi_active = 0;
			 if(!empty($kpis)){
					foreach($kpis as $kpi) {	
						if($kpi->kpi_active == 1){
							$kpi_active = 1;		
							/*
							echo "<pre>";
							print_r($kpi);
							echo "</pre>";
							*/			
							if($kpi->kpi_public == '1'){
								$locked_status = 'none';
								$unlocked_status = '';
							}else{
								$locked_status = '';
								$unlocked_status = 'none';							
							}
										
							?>
									<label for="testKPICheck<?php echo $kpi->id;?>" style="float:left;">
										<input onclick="modifyKPI(<?php echo $kpi->id;?>,<?php echo $goal->goal->id;?>, 'completed','')" type="checkbox" value="Check" id="testKPICheck<?php echo $kpi->id;?>" <?php echo $checkedVal; ?> onclick="" />
									</label>
						
							<div class="kpi_label" id="kpiBox<?php echo $kpi->id;?>">
									<div style="display:none;" id="KPIElement<?php echo $kpi->id;?>"> 
										Name: <input id="newKPIName<?php echo $kpi->id;?>" type="text" value="<?php echo GPC::strToPrintable($kpi->kpi_name);?>" style="width:275px; font-size:13px; color:#666;"/> Test: <input id="newKPITestName<?php echo $kpi->id;?>" type="text" value="<?php if(!empty($kpi->kpi_tests[0]->test_name)){ echo $kpi->kpi_tests[0]->test_name;}?>" style="width:145px; font-size:13px; color:#666;"/>
										<button onclick="modifyKPI(<?php echo $kpi->id;?>,<?php echo $goal->goal->id;?>, 'edit', <?php if(!empty($kpi->kpi_tests[0]->id)){ echo $kpi->kpi_tests[0]->id; }else{ echo '';} ?>)">submit</button><button  onclick="editKPIElement(<?php echo $kpi->id;?>,0)">cancel</button>
									</div> 
									<span style="<?php echo $strikethrough; ?>" id="curKPIElementText<?php echo $kpi->id;?>"><?php echo GPC::strToPrintable($kpi->kpi_name);?></span>
									
									<?php if(!empty($kpi->kpi_tests[0]->test_name)){
										$isTest = "";
									}else{ 
										$isTest = "none"; 
									}

									?><span style='display:'<?php echo $isTest;?>' id='curKPITestText<?php echo $kpi->id;?>'><?php if(!empty($kpi->kpi_tests[0]->test_name)){ echo "("; echo $kpi->kpi_tests[0]->test_name; echo ")"; } ?></span>
									<span class="editLinkRemove" style="float:right;" id="removeKPIButton<?php echo $kpi->id;?>" onclick="modifyKPI(<?php echo $kpi->id;?>,<?php echo $goal->goal->id;?>, 'remove','')">x</span>
									<span class="editLink" id="editKPIButton<?php echo $kpi->id;?>" onclick="editKPIElement(<?php echo $kpi->id;?>,1)">edit</span>
									<span>
										<img id="kpiLocked<?php echo $goal->goal->id;?><?php echo $kpi->id;?>" class="small_lock_goal_page" class="small_lock_goal_page" onclick="changeKPIPrivacy(<?php echo $goal->goal->id;?>,<?php echo $kpi->id;?>,'locked');" style="display:<?php echo $locked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/lock_small.png"/>
										<img id="kpiUnlocked<?php echo $goal->goal->id;?><?php echo $kpi->id;?>" class="small_lock_goal_page" onclick="changeKPIPrivacy(<?php echo $goal->goal->id;?>,<?php echo $kpi->id;?>, 'unlocked');" style="display:<?php echo $unlocked_status; ?>;" src="<?php echo BASEPATH_UI;?>/src/lock_icons/unlock_small.png"/>
									</span>
									
									
									
							</div>
							<div class="cl">&nbsp;</div>
<?php					
					}
					?>
			<?php }
			if($kpi_active == 0){
						 echo "<span class='no_kpi_elements' id='no_kpi_elements" . $goal->goal->id . "'> Adopt some Measurements and Milestones here.</span>"; 
			}
			
			}
			?>
					</div>		
				
				
					<div class="user_page_items">
						<span class="user_page_sub_title"> Adoptable Measurements and Milestones </span><br/><div id="new_kpi_place<?php echo GPC::strToPrintable($goal->goal->id);?>"></div>
		<?php 
			 $kpi_active = 0;
			 if(!empty($kpis)){
					foreach($kpis as $kpi) {	
						if($kpi->kpi_active == 0){
							$kpi_active = 1;						
							?>
						
							<div class="kpi_label" id="adoptKPIBox<?php echo $kpi->id;?>">
								<div id="liKPIAdopt<?php echo $kpi->id;?>">
									<div style="display:none;" id="KPIElement<?php echo $kpi->id;?>"> 
										Name: <input id="newKPIName<?php echo $kpi->id;?>" type="text" value="<?php echo GPC::strToPrintable($kpi->kpi_name);?>" style="width:275px; font-size:13px; color:#666;"/> Test: <input id="newKPITestName<?php echo $kpi->id;?>" type="text" value="<?php if(!empty($kpi->kpi_tests[0]->test_name)){ echo $kpi->kpi_tests[0]->test_name;}?>" style="width:145px; font-size:13px; color:#666;"/>
										<button onclick="modifyKPI(<?php echo $kpi->id;?>,<?php echo $goal->goal->id;?>, 'edit', <?php if(!empty($kpi->kpi_tests[0]->id)){ echo $kpi->kpi_tests[0]->id; }else{ echo '';} ?>)">submit</button><button  onclick="editKPIElement(<?php echo $kpi->id;?>,0)">cancel</button>
									</div> 
									
									<div id="kpiBox<?php echo $kpi->id;?>">
										<span style="<?php echo $strikethrough; ?>" id="curKPIElementText<?php echo $kpi->id;?>"><?php echo GPC::strToPrintable($kpi->kpi_name);?></span>									
										<?php if(!empty($kpi->kpi_tests[0]->test_name)){
											$isTest = "";
										}else{ 
											$isTest = "none"; 
										}
	
										?>
										<span style='display:'<?php echo $isTest;?>' id='curKPITestText<?php echo $kpi->id;?>'><?php if(!empty($kpi->kpi_tests[0]->test_name)){ echo "("; echo $kpi->kpi_tests[0]->test_name; echo ")"; } ?></span>
										
										<span class="editLink" id="adoptKPIButton<?php echo $kpi->id;?>" onclick="modifyKPI(<?php echo $kpi->id;?>,<?php echo $goal->goal->id;?>, 'adopt','')">adopt</span>
										<span class="editLink" style="display:none;" id="editKPIButton<?php echo $kpi->id;?>" onclick="editKPIElement(<?php echo $kpi->id;?>,1)">edit</span>
										<span class="editLinkRemove" style="display:none; float:right;" id="removeKPIButton<?php echo $kpi->id;?>" onclick="modifyKPI(<?php echo $kpi->id;?>,<?php echo $goal->goal->id;?>, 'remove','')">x</span>
									</div>
							</div>
							</div>
							<div class="cl">&nbsp;</div>
<?php					
					}
					?>
			<?php }
			if($kpi_active == 0){
						 echo "<span class='no_kpi_elements' id='no_kpi_elements" . $goal->goal->id . "'> Adopt some Measurements and Milestones here.</span>"; 
			}
			
			}
			?>
					</div>
					</div>
					
					<div class="remove_goal">
							<button class="remove-goal-btn" id="remove_goal" onclick="removeGoal();">Deactivate goal</button>
					</div>
					
					
				</div>			
				
				
				
		<!-- End Box -->		
		<?php
				break;
				
			case PAGEMODE_ACTIVITY:
				// they can adopt if they don't have goal
				if(!$userHasGoal) {
?>
<a href="<?php echo PAGE_GOAL."?id=$goalID&t=".PAGEMODE_EDIT; ?>">Click here to adopt -&gt;</a><br/><br/>
<?php
				}
				
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
				$numAdopters = $goal->goal->getNumAdopters();
				$average = $db->doQueryOne("SELECT AVG(level) FROM goals_status WHERE goal_id=$goalID");
?>
		<div class="facts">
			<div class="adopted_desc">
				<p>
				</p>
				<input id="removeDescButton" type="button" value="X" onclick="removeDescription();" class="small-add-btn up-down" style="display:none;"/>
			</div>
		
			<div class="score" id="who_else_adopted">
				<br/><br/>
				<div class="results" style="margin-left:auto; margin-right:auto;">
					<ul>
						<li><p class="res-box"><span><?php echo $numAdopters; ?></span></p><p class="label"><a href="<?php echo $goal->goal->getPagePath();?>&t=people">People</a> have this goal</p></li>
						<li class="last"><p class="res-box"><span><?php echo sprintf("%1.1f",$average); ?></span></p><p class="label">Average level</p></li>
					</ul>
				</div>
				<div class="cl">&nbsp;</div>
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
	public function handleNoGoalForGoalPage() {
		die("ERROR: must specify a goalID");
	}

	// private
	private function printJQMPage($page) {
		$this->printPageHead();
	?>
	<div data-role='page' id='home'>
		<!-- redirect to the first page -->
		<script type="text/javascript">
			$('#home').live('pagecreate',function(event){
				$.mobile.changePage("#<?php echo $page; ?>");
			});
		</script>
	</div>
	<div data-role='page' id='<?php echo NAVNAME_USERS;?>'>
	<?php
		$this->printHeader(NAVNAME_USERS, array(new ChromeTitleElementHeader("All People")), false, true);
		$this->printFooter(NAVNAME_USERS, false, true);
	?>
	</div>
	<div data-role='page' id='<?php echo NAVNAME_ACTIVITY;?>'>
	<?php
		$this->printHeader(NAVNAME_ACTIVITY, array(new ChromeTitleElementHeader("Activity")), false, true);
		$this->printFooter(NAVNAME_ACTIVITY, false, true);
	?>
	</div>
	<div data-role='page' id='<?php echo NAVNAME_GOAL;?>'>
	<?php
		$this->printHeader(NAVNAME_GOAL, array(), false, true);
		$this->printFooter(NAVNAME_GOAL, false, true);
	?>
	</div>
	<div data-role='page' id='<?php echo NAVNAME_MYHABITS;?>'>
	<?php
		$this->printHeader(NAVNAME_MYHABITS, array(), false, true);
		$this->printFooter(NAVNAME_MYHABITS, false, true);
	?>
	</div>
	<div data-role='page' id='<?php echo NAVNAME_MYGOALS;?>'>
	<?php
		$this->printHeader(NAVNAME_MYGOALS, array(), false, true);
		$this->printFooter(NAVNAME_MYGOALS, false, true);
	?>
	</div>
	<script type="text/javascript">
		var nextQS = "";
		$('div').live('pagebeforeshow',function(event, ui) {
			// pull the page's ID
			var pageID = $.mobile.activePage.attr('id');
			
			// clear the main div's HTML
			//$('#main-'+pageID).html('');

			// load the new stuff
			$.ajax({
				url: "<?php echo PAGE_AJAX_MOBILE_PAGE; ?>?"+nextQS+"&d="+Math.floor(Math.random()*1000000)+"&page="+pageID,
				success: function(data) {
					$('#main-'+pageID).html(data);
					$("form.jqtransform").jqTransform();
				}
			});
			
			// clear it to not confuse next page
			setNextQS('');
		});
		function setNextQS(newNextQS) {
			nextQS = newNextQS;
		}		
	</script>
	<?php
		$this->printPageFoot();
	}
	private function printPageHead() {
	?>
<!DOCTYPE html>
<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Superhuman</title>
	<!-- GENERAL -->
	<meta charset="utf-8" />
	<meta name="viewport" content="width=640px"/>  
	<!-- JQ -->
	<script src="<?php echo BASEPATH_UI;?>/mobile/js/jquery-1.7.1.min.js" type="text/javascript" charset="utf-8"></script>
	<!-- JQ SPARKLINE -->
	<script src="<?php echo BASEPATH_UI;?>/jquery.sparkline.min.js" type="text/javascript" charset="utf-8"></script>
	<!-- JQ TRANSFORM -->
	<link rel="stylesheet" href="<?php echo BASEPATH_UI;?>/mobile/css/jqtransform.css" type="text/css" media="all" />
	<script src="<?php echo BASEPATH_UI;?>/mobile/js/jquery.jqtransform.js" type="text/javascript" charset="utf-8"></script>
	<!-- JQ MOBILE -->
	<link rel="stylesheet" href="<?php echo BASEPATH_UI;?>/mobile/css/jquery.mobile.structure-1.1.0-rc.1.css" type="text/css" media="all" />
	<script src="<?php echo BASEPATH_UI;?>/mobile/js/jquery.mobile-1.1.0-rc.1.min.js" type="text/javascript" charset="utf-8"></script>
	<!-- APP -->
	<link rel="shortcut icon" href="<?php echo BASEPATH_UI;?>/mobile/css/images/favicon.ico" />
	<link rel="stylesheet" href="<?php echo BASEPATH_UI;?>/mobile/css/style.css" type="text/css" media="all" />
	<script src="<?php echo BASEPATH_UI;?>/mobile/js/functions.js" type="text/javascript" charset="utf-8"></script>
</head>
<body>
	<?php
	}
	private function printPageFoot() {
	?>
</body>
</html>
	<?php
	}

	// public
	public function printHeader($navSelect, $chromeTitleElements, $justOuterChrome=false, $justBody=false) {
		global $user, $appAuth;
		static $headerID = 0;
		
		if(!$justBody) {
			$this->printPageHead();
			echo "<div data-role='page'>";
		}
?>
	<!-- Shell -->
	<div class="shell">
		<!-- Header -->
		<header>
			<h1 id="logo" ><a href="#<?php echo NAVNAME_ACTIVITY; ?>">Superhuman</a></h1>
			
	<?php
		if(isset($appAuth) && $appAuth->isLoggedIn()) {
	?>
			<div class="profile" id="profile<?php echo $headerID; ?>">
				<a href="#" class="link" >
					<img src="<?php echo GPC::strToPrintable($user->pictureURL); ?>" alt="" style="width:70px;height:70px" />
					<span class="arrow" >&nbsp;</span>
				</a>
				<div class="dropdown">
					<ul>
						<!--<li><a href="#">Change Password</a></li>-->
						<li><a href="<?php echo $appAuth->getLogoutPageURL(); ?>" rel="external">Log Out</a></li>
					</ul>
				</div>
			</div>
			<script type="text/javascript">
				$('#profile<?php echo $headerID; ?>').on('vclick', function() {
					$(this).find('.dropdown').toggle();
				});
			</script>
	<?php
		}
	?>
			<div class="cl">&nbsp;</div>
		</header>
		<!-- END Header -->
		
		<!-- Main -->
		<div id="main-<?php echo $navSelect;?>">
<?php
		if(!$justOuterChrome) {
			StatusMessages::printMessages();
			PerformanceMeter::addTimestamp("Header render done");
		}
		
		++$headerID;
	}
	public function printFooter($navSelect, $justOuterChrome=false, $justBody=false) {
		global $viewSwitch;
		
		if(!$justOuterChrome) {
			PerformanceMeter::addTimestamp("Page render done");
			if($viewSwitch->issetViewFlag("pagereport")) {
				PerformanceMeter::printReport();
			}
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
				<li><a href="#<?php echo NAVNAME_MYHABITS; ?>" data-transition="none" class="<?php echo ($navSelect==NAVNAME_MYHABITS)?"active":"";?>"><span class="icon icon-1" >&nbsp;</span>Habits</a></li>
				<li><a href="#<?php echo NAVNAME_MYGOALS; ?>" data-transition="none" class="<?php echo ($navSelect==NAVNAME_MYGOALS)?"active":"";?>" ><span class="icon icon-2" >&nbsp;</span>Goals</a></li>
				<li><a href="#<?php echo NAVNAME_ACTIVITY; ?>" data-transition="none" class="<?php echo ($navSelect==NAVNAME_ACTIVITY)?"active":"";?>" ><span class="icon icon-3" >&nbsp;</span>Activity</a></li>
				<li><a href="#<?php echo NAVNAME_USERS; ?>" data-transition="none" class="<?php echo ($navSelect==NAVNAME_USERS)?"active":"";?>"><span class="icon icon-4" >&nbsp;</span>Friends</a></li>
			</ul>
		</nav>
		<!-- END Navigation -->
<?php
		}
?>
	</div>
	<!-- END Shell -->
<?php
		if(!$justBody) {
			echo "</div>";
			$this->printPageFoot();
		}
		PerformanceMeter::addTimestamp("Footer render done");
	}
	protected function storyPrintEventStoryPrint($user, $goal, $eventStory, $changeWord, $goodBad, $timeSinceStr) {
		static $divID = 1;
?>
					<li>
						<div class="text">
							<a href="#<?php echo NAVNAME_MYGOALS;?>" onclick="setNextQS('id=<?php echo $user->id; ?>')" id="<?php echo "imgholder_$divID";?>">
								<img class='img' src="<?php echo GPC::strToPrintable($user->pictureURL); ?>" />
								<!-- HA/CK: image is inserted later. if specified literally here, safari will load the page 2x. no other fix could be found. -->
							</a>
							<!--<script type="text/javascript">
								setTimeout("loadUserImage('<?php echo "imgholder_$divID"; ?>', '<?php echo GPC::strToPrintable($user->pictureURL); ?>')", 4000);
								function loadUserImage(divID, imgPath) {
									document.getElementById(divID).innerHTML="<img class='img' src='"+imgPath+"' />";
								}
							</script>-->
							<h4><a href="#<?php echo NAVNAME_MYGOALS;?>" onclick="setNextQS('id=<?php echo $user->id; ?>')"><?php echo "$user->firstName $user->lastName"; ?></a> <?php echo $changeWord; ?> his level for <a href="#<?php echo NAVNAME_GOAL;?>" onclick="setNextQS('id=<?php echo $goal->id; ?>')"><?php echo GPC::strToPrintable($goal->name); ?></a> from <?php echo $eventStory->oldLevel; ?> to <?php echo $eventStory->newLevel; ?>.</h4>
							
							<p class="letter" ><?php echo GPC::strToPrintable($eventStory->letterGrade); ?></p>
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
						<!--<div class="img" style="background:url();">-->
							<a href="#<?php echo NAVNAME_MYGOALS;?>" onclick="setNextQS('id=<?php echo $user->id; ?>')"><img class="img" src="<?php echo GPC::strToPrintable($user->pictureURL); ?>" alt="<?php echo "$user->firstName $user->lastName"; ?>" /></a>
						<!--</div>-->
						<div class="text">
							<h4><a href="#<?php echo NAVNAME_MYGOALS;?>" onclick="setNextQS('id=<?php echo $user->id; ?>')"><?php echo "$user->firstName $user->lastName"; ?></a> just entered daily goal progress, touching <?php echo $numGoalsTouched; ?> out of <?php echo $totalGoals; ?> of their goals.</h4>
							
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
	public function printActivityPage() {
		$this->printJQMPage(NAVNAME_ACTIVITY);
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
		$this->printJQMPage(NAVNAME_USERS);
	}
	public function printAllUsersPageMainDiv() {
		// TEST: bare page
		global $viewSwitch;
		if($viewSwitch->issetViewFlag("bare")) {
			echo "<p><b><font color='white'>USERS PAGE</font></b></p>";
			return;
		}
?>
			<div class="friends-page">
<?php
		// print user list, return section letters
		$uniqueLastNameLetters = $this->userPrintListAll();
?>
				<div class="nav">
					<ul>
						<!--<li class="search" ><a href="#"><img src="css/images/search.png" alt="" /></a></li>-->
<?php
		// build list of links for each letter in right-aligned letter map
		$links = null;
		$baseASCIIVal = 65;
		if(count($uniqueLastNameLetters)) {
			$lnOrds = array();
			foreach($uniqueLastNameLetters as $val) {
				$lnOrds[] = ord($val)-$baseASCIIVal;
			}
			$links = array();
			for($i=0; $i<26; ++$i) {
				$closest = array_closest_num($lnOrds, $i);
				$links[] = chr($baseASCIIVal+$closest);
			}
		}
		else {
			$links = array_fill(0,26,"A");
		}
		
		// loop through & display all letters
		for($i=0; $i<26; ++$i) {
			$letter = chr($baseASCIIVal+$i);
			echo "<li><a href='#".$links[$i]."'>$letter</a></li>";
		}
?>
						<li><a href="#other">#</a></li>
					</ul>
				</div>
				<div class="cl">&nbsp;</div>
			</div>
<?php
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
							<h5><a href="#<?php echo NAVNAME_MYGOALS;?>" onclick="setNextQS('id=<?php echo $user->id; ?>')" data-transition="fade"><?php echo "$user->firstName <b>$user->lastName</b>";?></a></h5>
							<p>
								<?php echo $numGoals;?> goals<br/>
								<?php echo $visitFreqText;?>
							</p>
							<a href="#<?php echo NAVNAME_MYGOALS;?>" onclick="setNextQS('id=<?php echo $user->id; ?>')" data-transition="fade"><img class="img" src="<?php echo GPC::strToPrintable($user->pictureURL);?>" alt="<?php echo "$user->firstName $user->lastName";?>" /></a>
						</div>
<?php
	}
	
	// &&&&&&
	public function printUserPage($viewUser) {
		$this->printJQMPage(NAVNAME_MYHABITS);
	}
	public function printUserPageMainDiv($viewUser, $mode) {
		global $user, $db, $viewSwitch;
		$viewUserID = $viewUser->id;
		$viewingSelf = ($viewUserID == $user->id);

		// TEST: bare page
		if($viewSwitch->issetViewFlag("bare")) {
			echo "<p><b><font color='white'>USER PAGE</font></b></p>";
			return;
		}
?>
			<h2 class="arrow" ><?php echo "$viewUser->firstName $viewUser->lastName"; ?></h2>
			<div class="cl">&nbsp;</div>
			
			<div class="daily-entry-page">
<?php		
		$currentTime=time();
		$type = 'none';
		switch($mode) {
			case USERPAGEMODE_MYHABITS:
				$type = "habits";
				break;
			case USERPAGEMODE_MYGOALS:
				$type = "goals";
				break;
			default:
				break;
		}
		$this->goalstatusPrintList($viewUserID, $currentTime, $viewingSelf, $type);
?>
			</div>
<?php		
	}
	protected function goalstatusPrintPre() {
?>
					<ul>
<?php
	}
	protected function goalstatusPrintPost($numGoals) {
?>
						<div style="height:<?php echo max(0,(8-$numGoals)*80);?>px">&nbsp;</div>
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
		
		$numHabits=0;
		$numNonHabits=0;
		foreach($dailytests as $test) {
			if($test->strategy_type=="adherence") {
				$numHabits++;
			}
			else {
				$numNonHabits++;
			}
		}
		if(($type=="habits")&&($numHabits==0)) {
			return;
		}
?>
					<li>
						<form action="#" method="post" class="jqtransform" >
							<div class="title">
								<p class="num <?php echo $goalNumColor;?>" id="levelBox<?php echo $rowID;?>"><?php echo $goalstatus->level;?></p>
								<h5 onclick="setNextQS('id=<?php echo $goal->id; ?>'); $.mobile.changePage('#<?php echo NAVNAME_GOAL; ?>');" style="cursor:pointer"><?php echo GPC::strToPrintable($goal->name);?></h5>
<?php
		if(($type=="goals") && ($numNonHabits<2) && $isEditable) {
?>
								<div class="buttons-hor">
									<a href="#" class="minus" onclick="adjustLevel<?php echo $rowID;?>(-1);" style="position:relative; z-index:10">-</a>
									<div style="position:relative; display:inline;">
										<font style="font-size:40px; color:#888; top:-31px; left:-6px; position:absolute">|</font>
									</div>
									<div style="position:relative; display:inline;">
										<a href="#" class="plus" onclick="adjustLevel<?php echo $rowID;?>(1);" style="margin-left:14px;position:relative; bottom:3px">+</a>
									</div>
								</div>
<?php
		}
?>
								<div class="cl">&nbsp;</div>
							</div>
							<div class="holder">
<?php
		if($type=="habits") {
?>
								<div class="holder-right" id="testsDisplay<?php echo $rowID;?>">
<?php
			foreach($dailytests as $dailytest) {
				if($dailytest->strategy_type!="adherence") {
					continue;
				}
				$checkedVal = DailytestStatus::getTodayStatus($goalstatus->userID, $dailytest->id, Date::now()->toDay())?"checked":"";
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
<?php
		}
		elseif($type=="goals") {
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
<?php
				if($numNonHabits>=2) {
?>
								<div class="buttons">
									<a href="#" class="plus" onclick="adjustLevel<?php echo $rowID;?>(1);" style="position:relative; z-index:10">+</a>
									<a href="#" class="minus" onclick="adjustLevel<?php echo $rowID;?>(-1);" style="position:relative">-</a>
								</div>
<?php
				}
?>
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
<?php
			}
?>
								<div class="holder-right-strategies" id="testsDisplay<?php echo $rowID;?>">
<?php
			$this->printStrategyList($dailytests, $goalstatus->userID, false, $isEditable);
?>
								</div>
<?php
			if($isEditable) {
?>
								<div class="holder-right" id="eventDisplay<?php echo $rowID;?>" style="display:none;">
									<fieldset>
										<textarea rows="4" cols="50" class="field" id="eventWhy<?php echo $rowID;?>" onkeyup="onChangeEvent<?php echo $rowID;?>();" ><?php echo $whyVal; ?></textarea> 
									</fieldset>
								</div>
<?php
			}
		}
?>
								<div class="cl">&nbsp;</div>
							</div>
							<input type="button" value="Done" class="ui-btn" id="eventButtonDisplay<?php echo $rowID; ?>" onclick="eventButtonClicked<?php echo $rowID; ?>()" style="display:none;" />
						</form>
					</li>
<?php
	}
	public function printGoalPage($goalID) {
		$this->printJQMPage(NAVNAME_GOAL);
	}
	public function printGoalPageMainDiv($goalID) {
		global $user, $db;
		$goal = Goal::getObjFromGoalID($goalID);
		$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goalID);
?>
			<h2><a href="#" onclick="history.back();" class="arrow" >&nbsp;</a> <?php echo $goal->name; ?></h2>
			<div class="cl">&nbsp;</div>
<?php
		if(!$userHasGoal) {
			echo "<br/><p><b><font color='white'>&nbsp;&nbsp;You do not have this goal. Please visit the web version to adopt it!</font></b></p><br/>";
		}
		else {
			$obj = $db->doQueryRFR("SELECT * FROM goals_status WHERE user_id=%s AND goal_id=%s", $user->id, $goalID);
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
				<div class="place" style="padding:20px 0 0 0;">
					<script type="text/javascript">
						$.ajax({  
							type: "GET", 
							url: '<?php echo "template/createGraphLevelHistory.php?userID=$goalstatus->userID&goalID=$goalstatus->goalID&big";?>', 
							dataType: "html",
							complete: function(data){
								$(".place").html(data.responseText);  
							}  
						});  						
					</script>
				</div>
				<div class="cl">&nbsp;</div>
<?php
			if($goal->description!="") {
?>
				<p class="description" ><strong>Description:</strong> <?php echo $goal->description; ?></p>
<?php
			}
			
			//&&&&&& Get all the strategies from the DB
			$strategies = Dailytest::getListFromUserIDGoalID($goal->id, $user->id, 'user');
			$this->printStrategyList($strategies, $user->id);
?>
			</div>
<?php
		}
	}
	private function printStrategyList($strategies, $userID, $showEmptyCategories=true, $isEditable=true) {
		$tactics = array();
		$todoIDs = array();
		$todoNames = array();
		$todoChecks = array();
		foreach($strategies as $strategy) {
			$goalID = $strategy->goalID;
			
			if($strategy->strategy_type=="tactic") {
				$tactics[] = $strategy->name;
			}
			elseif($strategy->strategy_type=="todo") {
				$todoIDs[] = $strategy->id;
				$todoNames[] = $strategy->name;
				$todoChecks[] = Dailytest::getCompletedStatus($userID, $strategy->id);
			}
		}
		if($showEmptyCategories || count($tactics)) {
?>
				<h4>Tactics:</h4>
				<ul>
<?php
			if(!count($tactics)) {
?>
					<li>
						<div class="inner">
							<p>Visit the web version to add some tactics...</p>
						</div>
					</li>
<?php
			}
			foreach($tactics as $tactic) {	
?>
					<li style="overflow:hidden;">
						<div class="inner">
							<p><?php echo $tactic; ?></p>
						</div>
						<a href="#" class="more" >...</a>
					</li>
<?php
			}
?>
				</ul>
<?php
		}
		if($showEmptyCategories || count($todoIDs)) {
			if($isEditable) {
?>				
				<script type="text/javascript">
					// modify state of a todo
					function modifyTodo(strategy_id) {
						$.ajax({  
							type: "POST", 
							url: '<?php echo PAGE_AJAX_MODIFY_STRATEGY ?>', 
							data: "userID="+<?php echo $userID; ?>+"&goalID="+<?php echo $goalID; ?>+"&strategyID="+ strategy_id+"&type=completed&strategyType=todo",
							dataType: "html",
							complete: function(data){				        
								var val = data.responseText;       	
						   }  
						});

						// check the last state
						if($("#todoCheck"+strategy_id).prop('checked') == true) {
							$("#todoText"+strategy_id).css("text-decoration", "");
						}
						else {
							$("#todoText"+strategy_id).css("text-decoration", "line-through");
						}
					}
				</script>
<?php
			}
?>
				<h4>To-do's:</h4>
				<form action="#" method="post" class="jqtransform" >
					<ul class="list">
<?php
			if(!count($todoIDs)) {
?>
						<li>
							<p>Visit the web version to add some todos...</p>
						</li>
<?php
			}
			for($i=0; $i<count($todoIDs); ++$i) {
				$id = $todoIDs[$i];
				$name = $todoNames[$i];
				$check = $todoChecks[$i];
?>
						<li>
							<p id="todoText<?php echo $id;?>" style="text-decoration:<?php echo $check?"line-through":"";?>"><?php echo $name;?></p>
							<input type="checkbox" data-role="none" <?php echo $check?"checked":"";?> id="todoCheck<?php echo $id;?>"
<?php
				if($isEditable) {
?>
								onclick="modifyTodo(<?php echo $id;?>);"
<?php
				}
				else {
					echo "disabled";
				}
?>
							/>
						</li>
<?php
			}
?>
					</ul>
				</form>
<?php
		}
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
	
	// get view
	//global $view;
	// HACK: enforce web no matter where user is
	$view = new WebView();

	$view->printHeader(NAVNAME_NONE, array(), true);
	echo "<div style='padding:20px; background-color:white; border-color:#808080; border-style:solid; border-width:thin' >";
}
function printFooterAuth() {
	include(dirname(__FILE__)."/../template/userFacingBase.php");

	// get view
	//global $view;
	// HACK: enforce web no matter where user is
	$view = new WebView();

	echo "</div>";
	$view->printFooter(NAVNAME_NONE, true);
}

?>