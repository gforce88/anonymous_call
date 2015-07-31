<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'class.phpmailer.php';

 function genSendEmail($emailSrvParams, $emailAddr, $subject, $mailcontent, $mailTempate ) {
 		
 	// You could also pass in a logger object if logging is required
 	
	try {
		
		$body = file_get_contents ($mailTempate);
		$body = preg_replace ( '/mailcontent/', $mailcontent, $body ); // Strip

		$mail = new PHPMailer();

		$mail->IsSMTP();
		$mail->CharSet = "utf-8";
		
		$mail->Host = $emailSrvParams['host'];  // specify main and backup server
		$mail->Port = $emailSrvParams['port']; // or 587
		$mail->Username = $emailSrvParams['userName']; // SMTP server username
		$mail->Password = $emailSrvParams ['password']; // SMTP password
		
		$mail->SMTPAuth = true;     // turn on SMTP authentication
			
		$mail->SMTPSecure = "ssl";

		$mail->SetFrom ( $mail->Username, $mail->Username );
		$mail->AddReplyTo ( $mail->Username, $mail->Username );
		
		$mail->AddAddress ( $emailAddr );
	
		$mail->Subject = "=?utf-8?B?" . base64_encode ( $subject ) . "?=";
		$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional,
		$mail->WordWrap = 80; // set word wrap
		$mail->MsgHTML ( $body );
		$mail->IsHTML ( true ); // send as HTML
		//$this->logger->logInfo ( "EmailService", "sendEmail", "ready to send with clients solution" );
		
		$mail->Send ();
		
	//$this->logger->logInfo ( "EmailService", "sendEmail", "mail send finish" );
	} catch ( phpmailerException $e ) {
	}
}

