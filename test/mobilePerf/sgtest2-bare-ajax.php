<!DOCTYPE html>
<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Superhuman</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=640"/>
	<!-- style -->
	<link rel="shortcut icon" href="http://www.superhumanGoals.com/ui/mobile/css/images/favicon.ico" />
	<link rel="stylesheet" href="http://www.superhumanGoals.com/ui/mobile/css/style.css" type="text/css" media="all" />
	<!-- app & base JS -->
	<script src="http://www.superhumanGoals.com/ui/mobile/js/jquery-1.7.1.min.js" type="text/javascript" charset="utf-8"></script>
	<!--<script src="http://www.superhumanGoals.com/ui/mobile/js/functions.js" type="text/javascript" charset="utf-8"></script>-->
	<script type="text/javascript">
		var lastPage = 'user';
		
		function loadPageHTMLAsync(page) {
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
					document.getElementById('main').innerHTML = response;
				}
			}
			xmlhttp.open("GET","sgtest2-bare-ajax-controller.php?rand="+Math.floor(Math.random()*1000000)+"&page="+page,true);
			xmlhttp.send();
		}
		function changePage(newPage) {
			document.getElementById(lastPage+'-button').setAttribute('class','');
			document.getElementById(newPage+'-button').setAttribute('class','active');
			loadPageHTMLAsync(newPage);
			lastPage = newPage;
		}
	</script>
</head>
<body>

<!-- Shell -->
<div class="shell">
	<!-- Header -->
	<header>
		<h1 id="logo" ><a href="index.php">Superhuman</a></h1>
		
			<div class="profile">
			<a href="#" class="link" >
				<img src="http://orquidomania.com/images/50x50.gif" alt="" style="width:87px;height:87px" />
				<span class="arrow" >&nbsp;</span>
			</a>
			<div class="dropdown">
				<ul>
					<!--<li><a href="#">Change Password</a></li>-->
					<li><a href="http://www.superhumanGoals.com/auth/server/sgAuthPage.php?page=logout">Log Out</a></li>
				</ul>
			</div>
		</div>
			<div class="cl">&nbsp;</div>
	</header>
	<!-- END Header -->
	
	<!-- Main -->
	<div id="main">
	</div>
	<!-- END Main -->
	
	<!--  Navigation -->
	<nav>
		<ul>
			<li><a href="#" data-transition="none" id="user-button" onclick="changePage('user');"><span class="icon icon-1" >&nbsp;</span>Daily Entry</a></li>
			<li><a href="#" data-transition="none" id="activity-button" onclick="changePage('activity');"><span class="icon icon-2" >&nbsp;</span>Activity</a></li>
			<li><a href="#" data-transition="none" id="users-button" onclick="changePage('users');"><span class="icon icon-3" >&nbsp;</span>Friends</a></li>
		</ul>
	</nav>
	<!-- END Navigation -->
</div>
<!-- END Shell -->

<script type="text/javascript">
	changePage('user');
</script>

</body>
</html>
