<?php
require_once(dirname(__FILE__)."/../../common/include/functions.php"); 
require_once("constants.php");
require_once("core.php");

function printHeader($pageTitle) {
?>
<html>
	<head>
		<title><?php echo $pageTitle; ?></title>
	</head>
	<body>
		<font size="6"><?php echo $pageTitle; ?></font><br/>
		<a href="<?php echo PAGE_ACTIVITY; ?>">You</a> | 
		<a href="<?php echo PAGE_USER."?id=$user->id"; ?>">Activity</a> | 
		<a href="<?php echo PAGE_GOALS; ?>">Goals</a> | 
		<a href="<?php echo PAGE_USERS; ?>">People</a><br/>
		
		<input type="text" name="searchBox" id="searchBox" onkeydown="onSearchBoxChange();" /><br/>
		<div id="searchRecs">
		</div>
		<script language="text/javascript">
			var timer=null;
			function onSearchBoxChange() {
				if(timer != null) {
					clearTimeout(timer);
				}
				timer=setTimeout("updateSearchResults()",1000);
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
						for(i=0; i<resultList.length; ++i) {
							resultName = resultList[i].childNodes[0].childNodes[0].nodeValue;
							resultLink = resultList[i].childNodes[1].childNodes[0].nodeValue;
							resultType = resultList[i].childNodes[2].childNodes[0].nodeValue;
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
						newHTML = "";
						for(i=0; i<types.length; ++i) {
							newHTML = newHTML + "<ul>";
							for(j=0; j<namesByType[i].length; ++j) {
								newHTML = newHTML + "<li><a href='"+linksByType[i][j]+"'>"+namesByType[i][j]+"</a></li>";
							}
							newHTML = newHTML + "</ul>";
						}
						document.getElementById("searchRecs").innerHTML = newHTML;
					}
				}
				var inputText = document.getElementById("searchBox").value;
				xmlhttp.open("GET","<?php echo PAGE_AJAX_GETSEARCHOPTIONS; ?>?inputText="+encode(inputText),true);
				xmlhttp.send();
			}
		</script>
<?php
	StatusMessages::printMessages();
}

function printFooter() {
?>
		<br/>
		<a href="<?php echo PAGE_ABOUT; ?>">About</a> | <a href="<?php echo PAGE_HELP; ?>">Help</a> | <a href="<?php echo PAGE_LOGOUT; ?>">Log out</a><br/>
<?php
	if(!$user->hasMadeDailyEntry()) {
		echo "<a href='".PAGE_USER."'>Make your daily entry &gt;</a><br/>";
	}
?>
	</body>
</html>
<?php
}

?>