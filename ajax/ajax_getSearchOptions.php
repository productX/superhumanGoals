<?php
// ensure user is logged in
include("../template/userFacingForceLogin.php");

// verify & pull parameters
if(	!isset($_GET["inputText"]) ) {
	exit;
}
$inputText = $_GET["inputText"];

// start generating XML output
// HACK: should probably use JSON here?
?>
<!-- ProductX Rox Your Sox -->
<results>
<?php

// pull every goal with a name similar to the search input
$results = array();
$rs = $db->doQuery("SELECT id,name FROM goals WHERE name LIKE %s", new SQLArgLike($inputText));

// create result objects for any goals that were returned
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$results[] = array("type"=>"Goals", "name"=>$obj->name, "link"=>Goal::getObjFromGoalID($obj->goal_id)->getPagePath());
}

// pull every user with a name similar to the search input
$rs = $db->doQuery("SELECT id,full_name FROM users WHERE full_name LIKE %s", new SQLArgLike($inputText));

// create result objects for any users that were returned
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$results[] = array("type"=>"People", "name"=>$obj->full_name, "link"=>User::getObjFromUserID($obj->user_id)->getPagePath());
}

// output XML for every result object
foreach($results as $result) {
	$type = $result["type"];
	$name = $result["name"];
	$link = $result["link"];
	$newXML = <<<EOT
<result>
	<type>$type</type>
	<name>$name</name>
	<link>$link</link>
</result>
EOT;
	echo $newXML;
}
?>
</results>
