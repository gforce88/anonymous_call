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
				str_replace("%$is", $param, $msg);
				$i++;
			}
		}
		
		return $msg;
	}

	private static function getEnglishText($key) {
		$englishTexts = Zend_Registry::get('ENGLISH_TEXTS');
		return $englishTexts[$key];
	}

	private static function getJapaneseText($key) {
		$englishTexts = Zend_Registry::get('JAPANESE_TEXTS');
		if ($englishTexts[$key] == null) {
			return getEnglish($key);
		} else {
			return $englishTexts[$key];
		}
	}

}