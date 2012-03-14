<?php
require_once(dirname(__FILE__)."/../../../common/include/functions.php"); 

const API_ROOT = 'http://localhost/superhumanGoals/api';

echo curl_post(API_ROOT."/dailytests_status/4/8/2012-03-13", array("result"=>1));

echo "|ok";
?>