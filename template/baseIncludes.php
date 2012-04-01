<?php
require_once(dirname(__FILE__)."/../include/globals.php");

initPerformanceMeter();
initError();
PerformanceMeter::addTimestamp("Error init done");
initTime();
PerformanceMeter::addTimestamp("Time init done");
initDB();
PerformanceMeter::addTimestamp("DB init done");
initSession();
PerformanceMeter::addTimestamp("Session init done");
?>