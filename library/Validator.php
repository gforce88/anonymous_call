<?php

class Validator {

	public static function isValidPhoneNumber($phoneNumber) {
		$phoneNumber = preg_replace("/[^\d]/", "", $phoneNumber);
		
		$patternUS = "/^(0){0,4}(1){1}[0-9]{10}$/";
		if (preg_match($patternUS, $phoneNumber)) {
			return true;
		}
		$patternJapan = "/^(0){0,4}(81){1}[0-9]{9,11}$/";
		if (preg_match($patternJapan, $phoneNumber)) {
			return true;
		}
		$patternUSwithoutCode = "/^[0-9]{10}$/";
		if (preg_match($patternUSwithoutCode, $phoneNumber)) {
			return true;
		}
		
		return false;
	}

	private static function isValidEmail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

}