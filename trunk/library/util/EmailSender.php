<?php
require_once 'log/LoggerFactory.php';

class EmailSender {
	private static $logger;

	private static function initLogger() {
		self::$logger = LoggerFactory::getSysLogger();
	}

	public static function sendInviteEmail($email) {
		$email = self::adjustEmail($email, true);
		return self::sendEmail($email, "invite");
	}

	public static function sendAcceptEmail($email) {
		$email = self::adjustEmail($email, false);
		return self::sendEmail($email, "accept");
	}

	public static function sendDeclineEmail($email) {
		$email = self::adjustEmail($email, false);
		return self::sendEmail($email, "decline");
	}

	public static function sendThanksEmail($email, $toInviter) {
		$email = self::adjustEmail($email, $toInviter);
		return self::sendEmail($email, "thanks");
	}

	private static function adjustEmail($email, $toInviter) {
		if ($toInviter) {
			$email["fromEmail"] = $email["inviteeEmail"];
			$email["toEmail"] = $email["inviterEmail"];
		} else {
			$email["fromEmail"] = $email["inviterEmail"];
			$email["toEmail"] = $email["inviteeEmail"];
		}
		return $email;
	}

	private static function sendEmail($email, $emailType, $url = null) {
		self::initLogger();
		
		$subjectParam = array (
			$email["fromEmail"] 
		);
		$contentParam = array (
			$email["fromEmail"] 
		);
		$message = "Sending thanks email to: [" . $email["toEmail"] . "]";
		
		if ($url != null) {
			$contentParam["url"] = $url;
			$message .= " with URL: $url";
		}
		
		$subject = MultiLang::replaceParams($email["$emailType.EmailSubject"], $subjectParam);
		$content = MultiLang::replaceParams($email["$emailType.EmailBody"], $contentParam);
		self::$logger->logInfo($email["partnerInx"], $email["inx"], $message);
		
		$headers = "From: " . $email["partnerName"] . "<" . $email["emailAddr"] . "> \n";
		$headers .= "Content-type: text/html; charset=utf-8 \n";
		$sendResult = mail($email["toEmail"], $subject, $content, $headers);
		self::$logger->logInfo($email["partnerInx"], $email["inx"], "Email sent result: [$sendResult]");
		
		return $sendResult;
	}

}