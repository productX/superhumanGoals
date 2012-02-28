<?php
require_once(dirname(__FILE__)."/../../common/include/functions.php"); 
require_once("constants.php");
require_once("core.php");

function printHeader($navSelect, $chromeTitleElements, $justOuterChrome=false) {
	global $user, $userLoggedIn;
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
	if($userLoggedIn) {
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


function printFooter($justOuterChrome=false) {
	global $user, $userLoggedIn;
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
	if($userLoggedIn && !$user->hasMadeDailyEntry()) {
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

?>