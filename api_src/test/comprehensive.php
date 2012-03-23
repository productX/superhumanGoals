<?php
require_once(dirname(__FILE__)."/../../../common/include/functions.php"); 

// API ROOT (hardcoded for tests on Roger's machine
const API_ROOT = 'http://localhost/superhumanGoals/api';

// echo out results returned from test calls to the API
// test a set call for a dailytest
echo curl_post(API_ROOT."/dailytests_status/4/8/2012-03-13", array("result"=>1));

// echo that we've reached the end of the file
echo "ok";
?>