<?php

class Session {

	// private
	const VARNAME_USERID = 'superhumanGoals_userID';
	const VARNAME_AUTH_USERID = 'id';
	const VARNAME_STATUS_MESSAGES = 'superhumanGoals_statusMessages';
	private static $sessionStarted = false;
	private static $userID = null;
	private static $authUserID = null;
	private static $statusMessages = null;
	private function __construct() {} // static-only class
	private function setUserID($newID) {
		Session::$userID = $newID;
		$_SESSION[Session::VARNAME_USERID] = $newID;
	}
	private function setStatusMessages($newStatusMessages) {
		Session::$statusMessages = $newStatusMessages;
		$_SESSION[Session::VARNAME_STATUS_MESSAGES] = $newStatusMessages;
	}
	
	// protected
	
	// public
	public static function init() {
		session_start();
		Session::$userID = $_SESSION[Session::VARNAME_USERID];
		Session::$authUserID = $_SESSION[Session::VARNAME_AUTH_USERID];
	}
	public static function isStarted() {
		return Session::$sessionStarted;
	}
	public static function getAuthUserID() {
		assert(Session::isStarted());
		return Session::$authUserID;
	}
	public static function getLoggedInUserID() {
		assert(Session::isStarted());
		return Session::$userID;
	}
	public static function setLoggedInUserID($newID) {
		assert(Session::isStarted());
		setUserID($newID);
	}
	public static function clearLoggedInUserID() {
		assert(Session::isStarted());
		setUserID(null);
	}
	public static function getStatusMessages() {
		assert(Session::isStarted());
		return Session::$statusMessages;
	}
	public static function setStatusMessages($newStatusMessages) {
		assert(Session::isStarted());
		setStatusMessages($newStatusMessages);
	}
	public static function clearStatusMessages() {
		assert(Session::isStarted());
		setStatusMessages(null);
	}
	
};

?>