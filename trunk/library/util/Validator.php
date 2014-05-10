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

	public static function isValidEmail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public static function isValidCardNumber($cardNumber) {
		return strlen($cardNumber) >= 13 && strlen($cardNumber) <= 16;
	}

	public static function isValidMonth($month) {
		return $month >= 1 && $month <= 12;
	}

	public static function isValidYear($year) {
		return $year >= 2014;
	}

	public static function isValidCvv($cvv) {
		return strlen($cvv) == 3;
	}

	public static function isExpired($expHour, $time) {
		$interval = strtotime((new DateTime())->format("Y-m-d H:i:s")) - strtotime($time);
		if ($interval > $expHour * 3600) {
			return true;
		} else {
			return false;
		}
	}

	public static function isCompleted($calls) {
		foreach ($calls as $call) {
			if ($call["callResult"] >= CALL_RESULT_2NDLEG_ANSWERED) {
				return true;
			}
		}
		return false;
	}

}
