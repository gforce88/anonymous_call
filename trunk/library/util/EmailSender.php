<?php

class EmailSender {

	public static function sendPlainMail($fromName, $fromMail, $toName, $toMail, $subject, $content) {
		return self::sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $content, "text/plain");
	}

	public static function sendHtmlEmail($fromName, $fromMail, $toName, $toMail, $subject, $content) {
		return self::sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $content, "text/html");
	}

	private static function sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $content, $type) {
		// $headers = "From: $fromName<$fromMail> \n";
		$headers = "From: Tokumei_Invite@incognitosys.com \n";
		$headers .= "Content-type: $type; charset=utf-8 \n";
		return mail($toMail, $subject, $content, $headers);
	}

}