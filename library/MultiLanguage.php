<?php

class MultiLanguage {

	public static function getLanguage($key, $language) {
		$language = strtoupper($language);
		switch ($language) {
			case "EN" :
				return MultiLanguage::getEnglish($key);
			case "JP" :
			case "JA" :
				return MultiLanguage::getJapanese($key);
		}
	}

	private static function getEnglish($key) {
		$englishTexts = Zend_Registry::get('ENGLISH_TEXTS');
		return $englishTexts[$key];
	}

	private static function getJapanese($key) {
		$englishTexts = Zend_Registry::get('JAPANESE_TEXTS');
		if ($englishTexts[$key] == null) {
			return getEnglish($key);
		} else {
			return $englishTexts[$key];
		}
	}

}