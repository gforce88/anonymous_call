<?php

class MultiLanguage {

	public static function getText($key, $language) {
		$language = strtoupper($language);
		switch ($language) {
			case "EN" :
				return MultiLanguage::getEnglishText($key);
			case "JP" :
			case "JA" :
				return MultiLanguage::getJapaneseText($key);
		}
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