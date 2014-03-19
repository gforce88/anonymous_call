<?php

class EmailSender {

	public static function sendPlainMail($fromName, $fromMail, $toName, $toMail, $subject, $content) {
		return self::sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $content, "text/plain");
	}

	public static function sendHtmlEmail($fromName, $fromMail, $toName, $toMail, $subject, $content) {
		return self::sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $content, "text/html");
	}

	private static function sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $content, $type) {
		$headers = "From: $fromName<$fromMail> \n";
		$headers .= "Content-type: $type; charset=utf-8 \n";
		echo "<br>" . $headers;
		echo "<br>" . $toName;
		echo "<br>" . $toMail;
		echo "<br>" . $subject;
		echo "<br>" . $content;
		echo "<br>" . $type;
		return mail("$toName<$toMail>", $subject, $content, $headers);
	}

}