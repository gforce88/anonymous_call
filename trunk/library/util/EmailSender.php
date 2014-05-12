<?php
require_once 'log/LoggerFactory.php';

class EmailSender {
	private static $logger;

	private static function initLogger() {
		self::$logger = LoggerFactory::getSysLogger();
	}

	public static function sendInviteEmail($email) {
		$email = self::adjustEmail($email, false);
		$url = "http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/response?inx=" . $email["inviteInx"] . "&token=" . $email["inviteToken"] . "&country=" . $email["country"];
		return self::sendEmail($email, "invite", $url);
	}

	public static function sendAcceptEmail($email) {
		$email = self::adjustEmail($email, true);
		$url = "http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/following?inx=" . $email["inviteInx"] . "&token=" . $email["inviteToken"] . "&country=" . $email["country"];
		return self::sendEmail($email, "accept", $url);
	}

	public static function sendDeclineEmail($email) {
		$email = self::adjustEmail($email, true);
		return self::sendEmail($email, "decline");
	}

	public static function sendReadyEmail($email, $toInviter) {
		$email = self::adjustEmail($email, $toInviter);
		return self::sendEmail($email, "ready");
	}

	public static function sendSorryEmail($email, $toInviter) {
		$email = self::adjustEmail($email, $toInviter);
		return self::sendEmail($email, "sorry");
	}

	public static function sendRetryEmail($email, $toInviter) {
		$email = self::adjustEmail($email, $toInviter);
		$url = "http://" . $_SERVER["HTTP_HOST"] . APP_CTX . "/widget/following?inx=" . $email["inviteInx"] . "&token=" . $email["inviteToken"] . "&country=" . $email["country"];
		return self::sendEmail($email, "retry", $url);
	}

	public static function sendThanksEmail($email, $toInviter) {
		$email = self::adjustEmail($email, $toInviter);
		return self::sendEmail($email, "thanks");
	}

	private static function adjustEmail($email, $toInviter) {
		if ($toInviter) {
			$email["fromName"] = $email["inviteeName"];
			$email["fromEmail"] = $email["inviteeEmail"];
			$email["toEmail"] = $email["inviterEmail"];
		} else {
			$email["fromName"] = $email["inviterName"];
			$email["fromEmail"] = $email["inviterEmail"];
			$email["toEmail"] = $email["inviteeEmail"];
		}
		return $email;
	}

	private static function sendEmail($email, $emailType, $url = null) {
		self::initLogger();
		
		$subjectParam = array (
			$email["fromName"] 
		);
		$contentParam = array (
			$email["fromName"] 
		);

		$message = "Sending $emailType email to: [" . $email["toEmail"] . "]";
		if ($url != null) {
			$contentParam["url"] = $url;
			$message .= " URL: [$url]";
		}

		self::$logger->logInfo($email["partnerInx"], $email["inviteInx"], $emailType);
		self::$logger->logInfo($email["partnerInx"], $email["inviteInx"], $email[$emailType . "EmailSubject"]);
		self::$logger->logInfo($email["partnerInx"], $email["inviteInx"], $subjectParam);
		
		$subject = MultiLang::replaceParams($email[$emailType . "EmailSubject"], $subjectParam);
		$content = MultiLang::replaceParams($email[$emailType . "EmailBody"], $contentParam);
		self::$logger->logInfo($email["partnerInx"], $email["inviteInx"], $message);
		
		$headers = "From: " . $email["partnerName"] . "<" . $email["partnerEmail"] . "> \n";
		$headers .= "Content-type: text/html; charset=utf-8 \n";
		$sendResult = mail($email["toEmail"], $subject, $content, $headers);
		self::$logger->logInfo($email["partnerInx"], $email["inviteInx"], "$message Result: [$sendResult]");
		
		return $sendResult;
	}

}