<?php

class EmailSender {

	private $smtp;

	public function __construct() {
		$this->smtp = Zend_Registry::get('SMPT_ADAPTER');
	}

	public function sendPlainMail($fromName, $fromMail, $toName, $toMail, $subject, $bodyText) {
		return sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $bodyText, "test/plain");
	}

	public function sendHtmlEmail($fromName, $fromMail, $toName, $toMail, $subject, $bodyText) {
		return sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $bodyText, "test/html");
	}

	private function sendEmail($fromName, $fromMail, $toName, $toMail, $subject, $bodyText, $type) {
		$headers = "From: $fromName<$fromMail> \n";
		$headers .= "Content-type: $type; charset=utf-8 \n";
		$headers .= "X-Sender: Speak2leads \n";
		$headers .= "X-Mailer: PHP5 \n";
		return mail("$toName<$toMail>", $subject, $bodyText, $headers);
	}

}