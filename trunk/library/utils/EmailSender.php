<?php

class EmailSender {

	private $smtp;

	public function __construct() {
		$this->smtp = Zend_Registry::get('SMPT_ADAPTER');
	}

	public function sendPlainMail($fromName, $fromMail, $toName, $toMail, $subject, $content) {
		return $this->sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $content, "text/plain");
	}

	public function sendHtmlEmail($fromName, $fromMail, $toName, $toMail, $subject, $content) {
		return $this->sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $content, "text/html");
	}

	private function sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $content, $type) {
		$headers = "From: $fromName<$fromMail> \n";
		$headers .= "Content-type: $type; charset=utf-8 \n";
		return mail("$toName<$toMail>", $subject, $content, $headers);
	}

}