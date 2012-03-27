<?php
/*
404 page

DESCRIPTION
- this script is called when the user visits a non-existant page
- this non-existant page could really be a call into an API

SETUP
- set up in the apache virtual host with the relative path off of the document root, eg
ErrorDocument 404 /superhumanGoals/404.php

*/

// include the basics & the API in case this is an API call
include("template/baseIncludes.php");
require_once("api_src/api.php");

// check to see if it's an API call; if so process accordingly
tryURLForAPICall();

// if no handlers catch the page then print 404 error
die("404 not found!!");
?>