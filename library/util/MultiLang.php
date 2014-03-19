<?php

class MultiLang {

	public static function getText($key, $language, $params = null) {
		$language = strtoupper($language);
		switch ($language) {
			case "JP" :
				$msg = MultiLang::getJapaneseText($key);
				break;
			default :
				$msg = MultiLang::getEnglishText($key);
		}
		
		if ($params != null) {
			$i = 1;
			foreach ($params as $param) {
				$msg = str_replace("%" . $i . "s", $param, $msg);
				$i++;
			}
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
			return getEnglish($key);
		} else {
			return $texts[$key];
		}
	}

}