<?php

class MultiLang {

	public static function getText($key, $language, $params = null) {
		$language = strtoupper($language);
		switch ($language) {
			case COUNTRY_JP :
				$msg = self::getJapaneseText($key);
				break;
			default :
				$msg = self::getEnglishText($key);
		}
		
		if ($params != null) {
			$msg = self::replaceParams($msg, $params);
		}
		
		return $msg;
	}

	public static function getText2($key, $language, $param1, $param2) {
		$params = array (
			$param1,
			$param2 
		);
		return self::getText($key, $language, $params);
	}

	public static function getText3($key, $language, $param1, $param2, $param3) {
		$params = array (
			$param1,
			$param2,
			$param3 
		);
		return self::getText($key, $language, $params);
	}

	public static function replaceParams($msg, $params) {
		$i = 1;
		if (!is_array($params)) {
			$params = array (
				$params 
			);
		}
		foreach ($params as $param) {
			$msg = str_replace("%" . $i . "s", $param, $msg);
			$i++;
		}
		return $msg;
	}

	private static function getEnglishText($key) {
		$texts = Zend_Registry::get('ENGLISH_TEXTS');
		return $texts[$key];
	}

	private static function getJapaneseText($key) {
		$texts = Zend_Registry::get('JAPANESE_TEXTS');
		if ($texts[$key] == null) {
			return self::getEnglishText($key);
		} else {
			return $texts[$key];
		}
	}

}