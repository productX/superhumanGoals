
<!DOCTYPE html>
<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Superhuman</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=640"/>  
	<link rel="shortcut icon" href="http://www.superhumanGoals.com/ui/mobile/css/images/favicon.ico" />
	<link rel="stylesheet" href="http://www.superhumanGoals.com/ui/mobile/css/jquery.mobile.structure-1.1.0-rc.1.css" type="text/css" media="all" />
	<link rel="stylesheet" href="http://www.superhumanGoals.com/ui/mobile/css/style.css" type="text/css" media="all" />
	<link rel="stylesheet" href="http://www.superhumanGoals.com/ui/mobile/css/jqtransform.css" type="text/css" media="all" />
	<script src="http://www.superhumanGoals.com/ui/mobile/js/jquery-1.7.1.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.superhumanGoals.com/ui/mobile/js/jquery.jqtransform.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.superhumanGoals.com/ui/mobile/js/functions.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.superhumanGoals.com/ui/mobile/js/jquery.mobile-1.1.0-rc.1.min.js" type="text/javascript" charset="utf-8"></script>
</head>
<body>


<div data-role="page" id="user">
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
			<h2 class="arrow" >Roger Dickey <a href="#" class="arrows expand" >&nbsp;</a></h2>
			<div class="cl">&nbsp;</div>
			
			<div class="daily-entry-page">
					<ul>
					<li>
						<form action="#" method="post" class="jqtransform" >
							<div class="title">
								<p class="num green" id="levelBox1">8</p>
								<h5>Career</h5>
								<span class="arrow" onclick="window.location='goal.php?id=12';">&nbsp;</span>
								<div class="cl">&nbsp;</div>
							</div>
							<div class="holder">
							<script type="text/javascript">								
								var timer=null;
								function onChangeEvent1() {
									// validate
									if(parseFloat(document.all['eventNewLevel1'].value)==0) {
										return;
									}
								
									// trigger save timer
									if(timer != null) {
										clearTimeout(timer);
									}
									timer=setTimeout("doSaveEvent1()",200);
								}
								
								function doSaveEvent1() {
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
										}
									}
									xmlhttp.open("GET","ajax/ajax_saveEvent.php?userID=10&goalID=12&oldLevel=8&newLevel="+parseFloat(document.getElementById("eventNewLevel1").value)+"&letterGrade="+document.getElementById("eventLetterGrade1").value+"&why="+escape(document.getElementById("eventWhy1").value),true);
									xmlhttp.send();
								}
							</script>
								<input type="hidden" id="eventNewLevel1" value="8" />
								<input type="hidden" id="eventOriginalLevel1" value="5" />
								<input type="hidden" id="eventLetterGrade1" value="" />
								<div class="buttons">
									<a href="#" class="plus" onclick="adjustLevel1(1);">+</a>
									<a href="#" class="minus" onclick="adjustLevel1(-1);">-</a>
								</div>
								<script type="text/javascript">
									function adjustLevel1(adjustment) {
										var currentLevel = document.getElementById('eventNewLevel1').value;
										currentLevel = parseInt(currentLevel)+adjustment;
										if((currentLevel>=1) && (currentLevel<=10)) {
											document.getElementById('eventNewLevel1').value=currentLevel;
											document.getElementById('levelBox1').innerHTML=currentLevel;
											var newLevelColor = "green";
											if(currentLevel<7) {
												newLevelColor = "yellow";
											}
											if(currentLevel<4) {
												newLevelColor = "red";
											}
											document.getElementById('levelBox1').className="num "+newLevelColor;
											
											originalLevel = parseInt(document.getElementById('eventOriginalLevel1').value);
											letterGrade = "A";
											if(currentLevel<originalLevel) {
												letterGrade = "F";
											}
											document.getElementById('eventLetterGrade1').value=letterGrade;
											
											expandEvent1();
											onChangeEvent1();
										}
									}
									function eventButtonClicked1() {
										collapseEvent1();
									}
									function expandEvent1() {
										document.getElementById('testsDisplay1').style.display="none";
										document.getElementById('eventDisplay1').style.display="block";
										document.getElementById('eventButtonDisplay1').style.display="block";
									}
									function collapseEvent1() {
										document.getElementById('eventDisplay1').style.display="none";
										document.getElementById('eventButtonDisplay1').style.display="none";
										document.getElementById('testsDisplay1').style.display="block";
									}
								</script>
								<div class="holder-right" id="eventDisplay1" style="display:none;">
									<fieldset>
										<textarea rows="4" cols="50" class="field" id="eventWhy1" onkeyup="onChangeEvent1();" >cats</textarea> 
									</fieldset>
								</div>
								<div class="holder-right" id="testsDisplay1">
								</div>
								<div class="cl">&nbsp;</div>
							</div>
							<input type="button" value="Done" class="button" id="eventButtonDisplay1" onclick="eventButtonClicked1()" style="display:none;" />
						</form>
					</li>
					<li>
						<form action="#" method="post" class="jqtransform" >
							<div class="title">
								<p class="num yellow" id="levelBox2">6</p>
								<h5>Money</h5>
								<span class="arrow" onclick="window.location='goal.php?id=11';">&nbsp;</span>
								<div class="cl">&nbsp;</div>
							</div>
							<div class="holder">
							<script type="text/javascript">								
								var timer=null;
								function onChangeEvent2() {
									// validate
									if(parseFloat(document.all['eventNewLevel2'].value)==0) {
										return;
									}
								
									// trigger save timer
									if(timer != null) {
										clearTimeout(timer);
									}
									timer=setTimeout("doSaveEvent2()",200);
								}
								
								function doSaveEvent2() {
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
										}
									}
									xmlhttp.open("GET","ajax/ajax_saveEvent.php?userID=10&goalID=11&oldLevel=6&newLevel="+parseFloat(document.getElementById("eventNewLevel2").value)+"&letterGrade="+document.getElementById("eventLetterGrade2").value+"&why="+escape(document.getElementById("eventWhy2").value),true);
									xmlhttp.send();
								}
							</script>
								<input type="hidden" id="eventNewLevel2" value="6" />
								<input type="hidden" id="eventOriginalLevel2" value="5" />
								<input type="hidden" id="eventLetterGrade2" value="" />
								<div class="buttons">
									<a href="#" class="plus" onclick="adjustLevel2(1);">+</a>
									<a href="#" class="minus" onclick="adjustLevel2(-1);">-</a>
								</div>
								<script type="text/javascript">
									function adjustLevel2(adjustment) {
										var currentLevel = document.getElementById('eventNewLevel2').value;
										currentLevel = parseInt(currentLevel)+adjustment;
										if((currentLevel>=1) && (currentLevel<=10)) {
											document.getElementById('eventNewLevel2').value=currentLevel;
											document.getElementById('levelBox2').innerHTML=currentLevel;
											var newLevelColor = "green";
											if(currentLevel<7) {
												newLevelColor = "yellow";
											}
											if(currentLevel<4) {
												newLevelColor = "red";
											}
											document.getElementById('levelBox2').className="num "+newLevelColor;
											
											originalLevel = parseInt(document.getElementById('eventOriginalLevel2').value);
											letterGrade = "A";
											if(currentLevel<originalLevel) {
												letterGrade = "F";
											}
											document.getElementById('eventLetterGrade2').value=letterGrade;
											
											expandEvent2();
											onChangeEvent2();
										}
									}
									function eventButtonClicked2() {
										collapseEvent2();
									}
									function expandEvent2() {
										document.getElementById('testsDisplay2').style.display="none";
										document.getElementById('eventDisplay2').style.display="block";
										document.getElementById('eventButtonDisplay2').style.display="block";
									}
									function collapseEvent2() {
										document.getElementById('eventDisplay2').style.display="none";
										document.getElementById('eventButtonDisplay2').style.display="none";
										document.getElementById('testsDisplay2').style.display="block";
									}
								</script>
								<div class="holder-right" id="eventDisplay2" style="display:none;">
									<fieldset>
										<textarea rows="4" cols="50" class="field" id="eventWhy2" onkeyup="onChangeEvent2();" >got the dough $$$$$$$$</textarea> 
									</fieldset>
								</div>
								<div class="holder-right" id="testsDisplay2">
								</div>
								<div class="cl">&nbsp;</div>
							</div>
							<input type="button" value="Done" class="button" id="eventButtonDisplay2" onclick="eventButtonClicked2()" style="display:none;" />
						</form>
					</li>
					<li>
						<form action="#" method="post" class="jqtransform" >
							<div class="title">
								<p class="num green" id="levelBox3">7</p>
								<h5>Lifestyle</h5>
								<span class="arrow" onclick="window.location='goal.php?id=14';">&nbsp;</span>
								<div class="cl">&nbsp;</div>
							</div>
							<div class="holder">
							<script type="text/javascript">								
								var timer=null;
								function onChangeEvent3() {
									// validate
									if(parseFloat(document.all['eventNewLevel3'].value)==0) {
										return;
									}
								
									// trigger save timer
									if(timer != null) {
										clearTimeout(timer);
									}
									timer=setTimeout("doSaveEvent3()",200);
								}
								
								function doSaveEvent3() {
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
										}
									}
									xmlhttp.open("GET","ajax/ajax_saveEvent.php?userID=10&goalID=14&oldLevel=7&newLevel="+parseFloat(document.getElementById("eventNewLevel3").value)+"&letterGrade="+document.getElementById("eventLetterGrade3").value+"&why="+escape(document.getElementById("eventWhy3").value),true);
									xmlhttp.send();
								}
							</script>
								<input type="hidden" id="eventNewLevel3" value="7" />
								<input type="hidden" id="eventOriginalLevel3" value="5" />
								<input type="hidden" id="eventLetterGrade3" value="" />
								<div class="buttons">
									<a href="#" class="plus" onclick="adjustLevel3(1);">+</a>
									<a href="#" class="minus" onclick="adjustLevel3(-1);">-</a>
								</div>
								<script type="text/javascript">
									function adjustLevel3(adjustment) {
										var currentLevel = document.getElementById('eventNewLevel3').value;
										currentLevel = parseInt(currentLevel)+adjustment;
										if((currentLevel>=1) && (currentLevel<=10)) {
											document.getElementById('eventNewLevel3').value=currentLevel;
											document.getElementById('levelBox3').innerHTML=currentLevel;
											var newLevelColor = "green";
											if(currentLevel<7) {
												newLevelColor = "yellow";
											}
											if(currentLevel<4) {
												newLevelColor = "red";
											}
											document.getElementById('levelBox3').className="num "+newLevelColor;
											
											originalLevel = parseInt(document.getElementById('eventOriginalLevel3').value);
											letterGrade = "A";
											if(currentLevel<originalLevel) {
												letterGrade = "F";
											}
											document.getElementById('eventLetterGrade3').value=letterGrade;
											
											expandEvent3();
											onChangeEvent3();
										}
									}
									function eventButtonClicked3() {
										collapseEvent3();
									}
									function expandEvent3() {
										document.getElementById('testsDisplay3').style.display="none";
										document.getElementById('eventDisplay3').style.display="block";
										document.getElementById('eventButtonDisplay3').style.display="block";
									}
									function collapseEvent3() {
										document.getElementById('eventDisplay3').style.display="none";
										document.getElementById('eventButtonDisplay3').style.display="none";
										document.getElementById('testsDisplay3').style.display="block";
									}
								</script>
								<div class="holder-right" id="eventDisplay3" style="display:none;">
									<fieldset>
										<textarea rows="4" cols="50" class="field" id="eventWhy3" onkeyup="onChangeEvent3();" >dog catcher</textarea> 
									</fieldset>
								</div>
								<div class="holder-right" id="testsDisplay3">
								</div>
								<div class="cl">&nbsp;</div>
							</div>
							<input type="button" value="Done" class="button" id="eventButtonDisplay3" onclick="eventButtonClicked3()" style="display:none;" />
						</form>
					</li>
					<li>
						<form action="#" method="post" class="jqtransform" >
							<div class="title">
								<p class="num yellow" id="levelBox4">5</p>
								<h5>Fashion / Looks</h5>
								<span class="arrow" onclick="window.location='goal.php?id=21';">&nbsp;</span>
								<div class="cl">&nbsp;</div>
							</div>
							<div class="holder">
							<script type="text/javascript">								
								var timer=null;
								function onChangeEvent4() {
									// validate
									if(parseFloat(document.all['eventNewLevel4'].value)==0) {
										return;
									}
								
									// trigger save timer
									if(timer != null) {
										clearTimeout(timer);
									}
									timer=setTimeout("doSaveEvent4()",200);
								}
								
								function doSaveEvent4() {
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
										}
									}
									xmlhttp.open("GET","ajax/ajax_saveEvent.php?userID=10&goalID=21&oldLevel=5&newLevel="+parseFloat(document.getElementById("eventNewLevel4").value)+"&letterGrade="+document.getElementById("eventLetterGrade4").value+"&why="+escape(document.getElementById("eventWhy4").value),true);
									xmlhttp.send();
								}
							</script>
								<input type="hidden" id="eventNewLevel4" value="5" />
								<input type="hidden" id="eventOriginalLevel4" value="5" />
								<input type="hidden" id="eventLetterGrade4" value="" />
								<div class="buttons">
									<a href="#" class="plus" onclick="adjustLevel4(1);">+</a>
									<a href="#" class="minus" onclick="adjustLevel4(-1);">-</a>
								</div>
								<script type="text/javascript">
									function adjustLevel4(adjustment) {
										var currentLevel = document.getElementById('eventNewLevel4').value;
										currentLevel = parseInt(currentLevel)+adjustment;
										if((currentLevel>=1) && (currentLevel<=10)) {
											document.getElementById('eventNewLevel4').value=currentLevel;
											document.getElementById('levelBox4').innerHTML=currentLevel;
											var newLevelColor = "green";
											if(currentLevel<7) {
												newLevelColor = "yellow";
											}
											if(currentLevel<4) {
												newLevelColor = "red";
											}
											document.getElementById('levelBox4').className="num "+newLevelColor;
											
											originalLevel = parseInt(document.getElementById('eventOriginalLevel4').value);
											letterGrade = "A";
											if(currentLevel<originalLevel) {
												letterGrade = "F";
											}
											document.getElementById('eventLetterGrade4').value=letterGrade;
											
											expandEvent4();
											onChangeEvent4();
										}
									}
									function eventButtonClicked4() {
										collapseEvent4();
									}
									function expandEvent4() {
										document.getElementById('testsDisplay4').style.display="none";
										document.getElementById('eventDisplay4').style.display="block";
										document.getElementById('eventButtonDisplay4').style.display="block";
									}
									function collapseEvent4() {
										document.getElementById('eventDisplay4').style.display="none";
										document.getElementById('eventButtonDisplay4').style.display="none";
										document.getElementById('testsDisplay4').style.display="block";
									}
								</script>
								<div class="holder-right" id="eventDisplay4" style="display:none;">
									<fieldset>
										<textarea rows="4" cols="50" class="field" id="eventWhy4" onkeyup="onChangeEvent4();" ></textarea> 
									</fieldset>
								</div>
								<div class="holder-right" id="testsDisplay4">
								</div>
								<div class="cl">&nbsp;</div>
							</div>
							<input type="button" value="Done" class="button" id="eventButtonDisplay4" onclick="eventButtonClicked4()" style="display:none;" />
						</form>
					</li>
					</ul>
			</div>
		</div>
		<!-- END Main -->
		<!--  Navigation -->
		<nav>
			<ul>
				<li><a href="#user" data-transition="none" class="active"><span class="icon icon-1" >&nbsp;</span>Daily Entry</a></li>
				<li><a href="#activity" data-transition="none" class="" ><span class="icon icon-2" >&nbsp;</span>Activity</a></li>
				<li><a href="#users" data-transition="none" class=""><span class="icon icon-3" >&nbsp;</span>Friends</a></li>
			</ul>
		</nav>
		<!-- END Navigation -->
	</div>
	<!-- END Shell -->
</div>













<div data-role="page" id="activity">
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
			<div class="activity-page">
<ul>					<li>
						<div class="text">
							<a href="user.php?id=10" id="imgholder_1">
								<img class='img' src="http://orquidomania.com/images/50x50.gif" />
								<!-- HACK: image is inserted later. if specified literally here, safari will load the page 2x. no other fix could be found. -->
							</a>
							<!--<script type="text/javascript">
								setTimeout("loadUserImage('imgholder_1', 'http://orquidomania.com/images/50x50.gif')", 4000);
								function loadUserImage(divID, imgPath) {
									document.getElementById(divID).innerHTML="<img class='img' src='"+imgPath+"' />";
								}
							</script>-->
							<h4><a href="user.php?id=10">Roger Dickey</a> raised his level for <a href="goal.php?id=11">Money</a> from 5 to 6.</h4>
							
							<p class="letter" >A</p>
							<div class="quote">
								<p>got the dough $$$$$$$$</p>
								<span class="quote-top" >&ldquo;</span>
								<span class="quote-bottom" >&rdquo;</span>
							</div>
						</div>
						<div class="cl">&nbsp;</div>
						<p class="time" >4 minutes ago</p>
					</li>
					<li>
						<div class="text">
							<a href="user.php?id=10" id="imgholder_2">
								<img class='img' src="http://orquidomania.com/images/50x50.gif" />
								<!-- HACK: image is inserted later. if specified literally here, safari will load the page 2x. no other fix could be found. -->
							</a>
							<!--<script type="text/javascript">
								setTimeout("loadUserImage('imgholder_2', 'http://orquidomania.com/images/50x50.gif')", 4000);
								function loadUserImage(divID, imgPath) {
									document.getElementById(divID).innerHTML="<img class='img' src='"+imgPath+"' />";
								}
							</script>-->
							<h4><a href="user.php?id=10">Roger Dickey</a> raised his level for <a href="goal.php?id=14">Lifestyle</a> from 5 to 7.</h4>
							
							<p class="letter" >A</p>
							<div class="quote">
								<p>dog catcher</p>
								<span class="quote-top" >&ldquo;</span>
								<span class="quote-bottom" >&rdquo;</span>
							</div>
						</div>
						<div class="cl">&nbsp;</div>
						<p class="time" >4 minutes ago</p>
					</li>
					<li>
						<div class="text">
							<a href="user.php?id=10" id="imgholder_3">
								<img class='img' src="http://orquidomania.com/images/50x50.gif" />
								<!-- HACK: image is inserted later. if specified literally here, safari will load the page 2x. no other fix could be found. -->
							</a>
							<!--<script type="text/javascript">
								setTimeout("loadUserImage('imgholder_3', 'http://orquidomania.com/images/50x50.gif')", 4000);
								function loadUserImage(divID, imgPath) {
									document.getElementById(divID).innerHTML="<img class='img' src='"+imgPath+"' />";
								}
							</script>-->
							<h4><a href="user.php?id=10">Roger Dickey</a> raised his level for <a href="goal.php?id=12">Career</a> from 5 to 8.</h4>
							
							<p class="letter" >A</p>
							<div class="quote">
								<p>cats</p>
								<span class="quote-top" >&ldquo;</span>
								<span class="quote-bottom" >&rdquo;</span>
							</div>
						</div>
						<div class="cl">&nbsp;</div>
						<p class="time" >8 minutes ago</p>
					</li>
</ul>			</div>
		</div>
		<!-- END Main -->
		<!--  Navigation -->
		<nav>
			<ul>
				<li><a href="#user" data-transition="none" class=""><span class="icon icon-1" >&nbsp;</span>Daily Entry</a></li>
				<li><a href="#activity" data-transition="none" class="active" ><span class="icon icon-2" >&nbsp;</span>Activity</a></li>
				<li><a href="#users" data-transition="none" class=""><span class="icon icon-3" >&nbsp;</span>Friends</a></li>
			</ul>
		</nav>
		<!-- END Navigation -->
	</div>
	<!-- END Shell -->
</div>














<div data-role="page" id="users">
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
			<div class="friends-page">
				<div class="content">
					<div class="row">
						<div class="cl">&nbsp;</div>
						<h3 id="D" >D</h3>
						<div class="box left">
							<h5><a href="user.php?id=10">Roger <b>Dickey</b></a></h5>
							<p>
								4 goals<br/>
								Visits daily							</p>
							<a href="user.php?id=10"><img class="img" src="http://orquidomania.com/images/50x50.gif" alt="Roger Dickey" /></a>
						</div>
						<div class="cl">&nbsp;</div>
					</div>
				</div>
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
		</div>
		<!-- END Main -->
		<!--  Navigation -->
		<nav>
			<ul>
				<li><a href="#user" data-transition="none" class=""><span class="icon icon-1" >&nbsp;</span>Daily Entry</a></li>
				<li><a href="#activity" data-transition="none" class="" ><span class="icon icon-2" >&nbsp;</span>Activity</a></li>
				<li><a href="#users" data-transition="none" class="active"><span class="icon icon-3" >&nbsp;</span>Friends</a></li>
			</ul>
		</nav>
		<!-- END Navigation -->
	</div>
	<!-- END Shell -->
</div>


</body>
</html>
