<?php
require_once(dirname(__FILE__)."/../../common/include/functions.php"); 
require_once("constants.php");
require_once("core.php");

function printHeader($pageTitle) {
	global $user, $userLoggedIn;
?>
<html>
	<head>
		<title><?php echo $pageTitle; ?></title>
	</head>
	<body>
		<font size="6"><?php echo $pageTitle; ?></font><br/>
<?php
	if($userLoggedIn) {
		echo "<a href='".$user->getPagePath()."'>$user->firstName $user->lastName</a> | <a href='".PAGE_LOGOUT."'>Log out</a><br/>";
	}
?>
		<a href="<?php echo PAGE_ACTIVITY; ?>">Activity</a> | 
		<a href="<?php echo PAGE_USER; ?>">You</a> | 
		<a href="<?php echo PAGE_GOALS; ?>">Goals</a> | 
		<a href="<?php echo PAGE_USERS; ?>">People</a><br/><br/>
		
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
							newHTML = newHTML + types[i];
							newHTML = newHTML + "<ul>";
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
		<input type="text" name="searchBox" id="searchBox" onkeyup="onSearchBoxChange();" /><br/><br/>
		<div id="searchRecs">
		</div>
<?php
	StatusMessages::printMessages();
	echo "<hr/><br/>";
}

function printFooter() {
	global $user;
?>
		<br/><hr/><br/>
		<a href="<?php echo PAGE_ABOUT; ?>">About</a> | <a href="<?php echo PAGE_HELP; ?>">Help</a><br/>
<?php
	if(($user != null) && !$user->hasMadeDailyEntry()) {
		echo "<a href='".PAGE_USER."'>Make your daily entry &gt;</a><br/>";
	}
?>
	</body>
</html>
<?php
}

?>