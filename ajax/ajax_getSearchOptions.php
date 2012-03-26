<?php
include("../template/userFacingBase.php");

if(	!isset($_GET["inputText"]) ) {
	exit;
}
$inputText = $_GET["inputText"];
?>

<!-- ProductX Rox Your Sox -->
<results>
<?php
$results = array();
$rs = $db->doQuery("SELECT goal_id,name FROM goals WHERE name LIKE %s", new SQLArgLike($inputText));
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$results[] = array("type"=>"Goals", "name"=>$obj->name, "link"=>Goal::getObjFromGoalID($obj->goal_id)->getPagePath());
}
$rs = $db->doQuery("SELECT user_id,full_name FROM users WHERE full_name LIKE %s", new SQLArgLike($inputText));
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$results[] = array("type"=>"People", "name"=>$obj->full_name, "link"=>User::getObjFromUserID($obj->user_id)->getPagePath());
}
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
