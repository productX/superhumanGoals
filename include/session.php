<?php

class Session {

	// private
	const VARNAME_STATUS_MESSAGES = 'superhumanGoals_statusMessages';
	private static $sessionStarted = false;
	private static $statusMessages = null;
	private function __construct() {} // static-only class
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
		Session::$sessionStarted=true;
	}
	public static function isStarted() {
		return Session::$sessionStarted;
	}
	public static function getVar($name) {
		assert(Session::isStarted());
		return $_SESSION[$name];
	}
	public static function setVar($name, $value) {
		assert(Session::isStarted());
		$_SESSION[$name]=$value;
	}
	public static function clearVar($name) {
		assert(Session::isStarted());
		unset($_SESSION[$name]);
	}
	public static function issetVar($name) {
		assert(Session::isStarted());
		return isset($_SESSION[$name]);
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