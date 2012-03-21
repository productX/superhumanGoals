<?php
require_once(dirname(__FILE__)."/../../common/include/functions.php"); 
require_once("constants.php");
require_once("core.php");
require_once("globals.php");

abstract class BaseView {
	// public
	abstract public function printHeader($navSelect, $chromeTitleElements, $justOuterChrome);
	abstract public function printFooter($justOuterChrome);
	abstract public function printAboutPage();
	abstract public function printHelpPage();
	public function printActivityPage() {
		global $db;
		
		$this->printHeader(NAVNAME_ACTIVITY, array(new ChromeTitleElementHeader("Activity")));

		$rs = $db->doQuery("SELECT * FROM stories WHERE is_public=TRUE ORDER BY entered_at DESC LIMIT 100");
		Story::printListForRS($rs);

		$this->printFooter();
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

		$this->printHeader($viewingSelf?NAVNAME_YOU:NAVNAME_USERS, 
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
				Story::printListForRS($rs);
				break;
			case PAGEMODE_GOALS:
				$currentTime=time();
				GoalStatus::printRowList($viewUserID, $currentTime, $viewingSelf);
				break;
			default:
				break;
		}

		$this->printFooter();
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
				Story::printListForRS($rs);
				break;
			case PAGEMODE_PEOPLE:
				User::printListByGoal($goalID);
				break;
			default:
				break;
		}

		$this->printFooter();
	}
	abstract public function printAllGoalsPage();
	public function printSignupPage() {
		global $intranetAuth;
		
		$this->printHeader(NAVNAME_NONE, array(), true);
		?>

					<div class="signup-box">
						<h2>Be Amazing.</h2>
						<a href="#" class="signup-btn">Sign up &raquo;</a>
						<div class="upload-box">
							<p>Signed in as: <strong><?php echo $intranetAuth->getUserEmail(); ?></strong></p>
							<p>Profile pic URL (50x50):</p>
							<form action="<?php echo PAGE_SIGNUP; ?>" method="post">
								<!--<input type="file" name="file" id="file" value="" />-->
								<input type="text" name="pictureURL" />
								<input name="submit" type="submit" value="Sign up &raquo;" class="submit-btn" />
								<div class="cl">&nbsp;</div>
							</form>
						</div>
					</div>

		<?php
		$this->printFooter(true);
	}
	public function printAllUsersPage() {
		$this->printHeader(NAVNAME_USERS, array(new ChromeTitleElementHeader("All People")));
		User::printListAll();
		$this->printFooter();
	}
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
		<link rel="shortcut icon" href="ui/css/images/favicon.ico" />
		<link rel="stylesheet" href="ui/css/style.css" type="text/css" media="all" />
		<link rel="stylesheet" href="ui/css/enhanced.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="ui/css/jquery.jscrollpane.css" type="text/css" media="all" />
		
		<script src="ui/js/jquery-1.7.1.min.js" type="text/javascript"></script>
		<script src="ui/js/jquery.jscrollpane.min.js" type="text/javascript"></script>
		<script src="ui/js/jquery.mousewheel.js" type="text/javascript"></script>
		<script src="ui/js/jquery.fileinput.js" type="text/javascript"></script>
		<script src="ui/js/functions.js" type="text/javascript"></script>
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
		if($appAuth->isLoggedIn()) {
	?>
					<div class="user-image">
						<a href="<?php echo $user->getPagePath(); ?>"><img src="<?php echo $user->pictureURL; ?>" alt="<?php echo "$user->firstName $user->lastName";?>" /></a>
						<span class="anchor"><img src="ui/css/images/anchor.png" alt="Anchor" /></span>
						<div class="dd">
							<ul>
								<!--<li><a href="#">Change Password</a></li>-->
								<li><a href="<?php echo PAGE_LOGOUT; ?>">Log Out</a></li>
							</ul>
						</div>
					</div>
	<?php
		}
		else {
	?>
					<p class="right"><a href="<?php echo PAGE_LOGIN; ?>" class="login-btn">Log In &raquo;</a><a href="<?php echo PAGE_SIGNUP; ?>" class="signup-btn">Sign Up &raquo;</a></p>
	<?php
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
		}

		StatusMessages::printMessages();
	}
	public function printFooter($justOuterChrome=false) {
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
					<p class="nav"><a href="<?php echo PAGE_ABOUT; ?>">About</a><span>|</span><a href="<?php echo PAGE_HELP; ?>">Help</a></p>
	<?php
		if($appAuth->isLoggedIn() && !$user->hasMadeDailyEntry()) {
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
					echo "<li><a href='$pagePath'>".htmlspecialchars($goal->name)."</a> ($numAdopters)</li>";
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
		$this->printFooter();
	}
	public function printAboutPage() {
		$this->printHeader(NAVNAME_NONE, array(new ChromeTitleElementHeader("About")));
?>
<div style="padding:10px 0 0 10px;">
By winners, for winners.
</div>
<?php
		$this->printFooter();
	}
	public function printHelpPage() {
		$this->printHeader(NAVNAME_NONE, array(new ChromeTitleElementHeader("Help")));
?>
<div style="padding:10px 0 0 10px;">
Is it really that hard to figure out? :P
</div>
<?php
		$this->printFooter();
	}
};

class MobileView extends BaseView {
	// public
	public function printHeader($navSelect, $chromeTitleElements, $justOuterChrome=false) {
	}
	public function printFooter($justOuterChrome=false) {
	}
	public function printAllGoalsPage() { // this page doesn't exist on mobile
	}
	public function printAboutPage() { // this page doesn't exist on mobile
	}
	public function printHelpPage() { // this page doesn't exist on mobile
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

?>