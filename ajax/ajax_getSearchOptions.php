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
$rs = Database::doQuery("SELECT id,name FROM goals WHERE name LIKE %s", new SQLArgLike($inputText));
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$results[] = array("type"=>"Goals", "name"=>$obj->name, "link"=>Goal::getObjFromGoalID($obj->id)->getPagePath());
}
$rs = Database::doQuery("SELECT id,full_name FROM users WHERE full_name LIKE %s", new SQLArgLike($inputText));
$obj = null;
while($obj = mysql_fetch_object($rs)) {
	$results[] = array("type"=>"People", "name"=>$obj->full_name, "link"=>User::getObjFromUserID($obj->id)->getPagePath());
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
