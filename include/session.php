<?php

class Session {

	// private
	const VARNAME_USERID = 'superhumanGoals_userID';
	const VARNAME_AUTH_USERID = 'id';
	const VARNAME_AUTH_EMAIL = 'email';
	const VARNAME_STATUS_MESSAGES = 'superhumanGoals_statusMessages';
	private static $sessionStarted = false;
	private static $userID = null;
	private static $authUserID = null;
	private static $authEmail = null;
	private static $statusMessages = null;
	private function __construct() {} // static-only class
	private function setUserID($newID) {
		Session::$userID = $newID;
		$_SESSION[Session::VARNAME_USERID] = $newID;
	}
	private function setStatusMessagesBase($newStatusMessages) {
		Session::$statusMessages = $newStatusMessages;
		$_SESSION[Session::VARNAME_STATUS_MESSAGES] = $newStatusMessages;
	}
	
	// protected
	
	// public
	public static function init() {
		if(session_id()=="") {
			session_start();
		}
		Session::$userID = null;
		Session::$authUserID = null;
		Session::$authEmail = null;
		if(isset($_SESSION[Session::VARNAME_USERID])) {
			Session::$userID = GPC::strToInt($_SESSION[Session::VARNAME_USERID]);
		}
		if(isset($_SESSION[Session::VARNAME_AUTH_EMAIL])) {
			Session::$authEmail = GPC::strToEmail($_SESSION[Session::VARNAME_AUTH_EMAIL]);
		}
		if(isset($_SESSION[Session::VARNAME_AUTH_USERID])) {
			Session::$authUserID = GPC::strToInt($_SESSION[Session::VARNAME_AUTH_USERID]);
		}
		Session::$sessionStarted=true;
	}
	public static function isStarted() {
		return Session::$sessionStarted;
	}
	public static function getAuthUserID() {
		assert(Session::isStarted());
		return Session::$authUserID;
	}
	public static function getAuthEmail() {
		assert(Session::isStarted());
		return Session::$authEmail;
	}
	public static function getLoggedInUserID() {
		assert(Session::isStarted());
		return Session::$userID;
	}
	public static function setLoggedInUserID($newID) {
		assert(Session::isStarted());
		Session::setUserID($newID);
	}
	public static function clearLoggedInUserID() {
		assert(Session::isStarted());
		Session::setUserID(null);
	}
	public static function getStatusMessages() {
		assert(Session::isStarted());
		return Session::$statusMessages;
	}
	public static function setStatusMessages($newStatusMessages) {
		assert(Session::isStarted());
		Session::setStatusMessagesBase($newStatusMessages);
	}
	public static function clearStatusMessages() {
		assert(Session::isStarted());
		Session::setStatusMessagesBase(null);
	}
	
};

?>