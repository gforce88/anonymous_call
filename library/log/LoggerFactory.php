<?php
require_once 'log/Logger.php';

class LoggerFactory {

	private static $ivrLogger = null;

	private static $sysLogger = null;

	public static function getIvrLogger() {
		if (self::$ivrLogger == null) {
			self::$ivrLogger = new Logger(Zend_Registry::get('IVR_LOGGER'));
		}
		return self::$ivrLogger;
	}

	public static function getSysLogger() {
		if (self::$sysLogger == null) {
			self::$sysLogger = new Logger(Zend_Registry::get('SYS_LOGGER'));
		}
		return self::$sysLogger;
	}

}

