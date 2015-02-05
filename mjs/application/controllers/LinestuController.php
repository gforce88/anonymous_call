<?php
require_once 'log/LoggerFactory.php';
require_once 'tropo/tropo.class.php';
require_once 'util/HttpUtil.php';
require_once 'service/TropoService.php';
require_once 'phpmailer/class.phpmailer.php';
class LinestuController extends Zend_Controller_Action {
	protected $logger;
	public function init() {
		$this->logger = LoggerFactory::getTropoLogger ();
		$this->httpUtil = new HttpUtil ();
		$this->setting = Zend_Registry::get ( "TROPO_SETTING" );
		$this->_helper->viewRenderer->setNeverRender ();
	}
	public function indexAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->logger->logInfo ( "LinestuController", "indexAction", "recieve from tropo server message is: " . $tropoJson );
		$session = new Session ( $tropoJson );
		$this->logger->logInfo ( "LinestuController", "indexAction", "session  is: " . $session->getId () );
		$params = $this->initSessionParameters ( $session );
		
		$callModel = new Application_Model_Call ();
		$callModel->updateStuCallSession ( $params ["sessionid"], $session->getId () );
		
		if ($callModel->checkStuCallTimes ( $params ) > 3) {
			$this->logger->logInfo ( "LinestuController", "indexAction", "student didn't answer the call for 3times" );
			$this->sendNotification ( $params ["sessionid"] );
		} else {
			$this->logger->logInfo ( "LinestuController", "indexAction", "call student:" . $params ["stuphone"] );
			$tropo = new Tropo ();
			$tropo->call ( $params ["stuphone"] );
			// 电话接通后
			if ($params ["notify"] == 1) { // 判断是否是提示电话
				$tropo->call ( $params ["mntphone"] );
				$tropo->on ( array (
						"event" => "continue",
						"next" => "/linestu/notify",
						"say" => "This call is to reminder you that you have a session call today soon." 
				) );
			} else {
				$tropo->on ( array (
						"event" => "continue",
						"next" => "/linestu/welcome",
						"say" => "Welcome to Mjs Application! You will joining the conference soon." 
				) );
			}
			// 电话未拨通
			$tropo->on ( array (
					"event" => "incomplete",
					"next" => "/linestu/incomplete" 
			) );
			// tropo应用发生错误
			$tropo->on ( array (
					"event" => "error",
					"next" => "/linestu/error" 
			) );
			
			$tropo->renderJSON ();
		}
	}
	public function notifyAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->logger->logInfo ( "LinestuController", "nofityAction", "notify message: " . $tropoJson );
	}
	public function hangupAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->logger->logInfo ( "LinestuController", "hangupAction", "student hangup message: " . $tropoJson );
		$result = new Result ( $tropoJson );
		$callModel = new Application_Model_Call ();
		$call = $callModel->groupEnd ( $result->getSessionId () );
		// 更新session实际结束时间
		$sessionModel = new Application_Model_Session ();
		$sessionModel->finishSession ( $call ["inx"] );
		
		// 更新学生记录的时间
		$uesdmins = ceil ( (strtotime ( $call ["grpCallEndTime"] ) - strtotime ( $call ["grpCallStartTime"] )) / 60 );
		$studentModel = new Application_Model_Student ();
		$studentModel->updateMinsRemaining ( $call ["party2Inx"], $uesdmins );
		
		// 发送session完成的邮件通知
		$this->sendEmailWhenCallEndToStu ( $call ["inx"] );
		sleep(5);
		$this->sendEmailWhenCallEndToMnt ( $call ["inx"] );
		sleep(5);
		$this->sendEmailWhenCallEndToTrl ( $call ["inx"] );
		
		$this->logger->logInfo ( "LinestuController", "hangupAction", "group session is over as student is hangup " );
	}
	public function welcomeAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->logger->logInfo ( "LinestuController", "continueAction", "student continue message: " . $tropoJson );
		$result = new Result ( $tropoJson );
		$callModel = new Application_Model_Call ();
		$row = $callModel->findSessionIdByStuCallsessionIdAndRecordTime ( $result->getSessionId () );
		$this->logger->logInfo ( "LinestuController", "welcomeAction", "session id: " . $result->getSessionId () );
		$tropo = new Tropo ();
		$confOptions = array (
				"name" => "conference",
				"id" => "mjsconf" . $row ["inx"],
				"mute" => false,
				"allowSignals" => array (
						"playremind",
						"exit" 
				) 
		);
		$tropo->on ( array (
				"event" => "hangup",
				"next" => "/linestu/hangup" 
		) );
		$tropo->conference ( null, $confOptions );
		$tropo->renderJSON ();
		// call translator
		$sessionModel = new Application_Model_Session ();
		$row = $sessionModel->getSessionForCallBySessionId ( $row ["inx"] );
		$paramArr = array ();
		$paramArr ["sessionid"] = $row ["inx"];
		$paramArr ["stuphone"] = $row ["b_phone"];
		$paramArr ["stuid"] = $row ["b_inx"];
		$paramArr ["mntphone"] = $row ["c_phone"];
		$paramArr ["mntid"] = $row ["c_inx"];
		$paramArr ["trlphone"] = $row ["d_phone"];
		$paramArr ["trlid"] = $row ["d_inx"];
		if ($paramArr ["trlid"] != null) {
			$troposervice = new TropoService ();
			$troposervice->calltrl ( $paramArr );
			$this->logger->logInfo ( "LinestuController", "welcomeAction", "call translator phone:--- " . $paramArr ["trlphone"] );
		} else {
			$callModel->groupStart ( $row ["inx"] );
		}
	}
	public function incompleteAction() {
		$tropoJson = file_get_contents ( "php://input" );
		
		$this->logger->logInfo ( "LinestuController", "incompleteAction", "incomplete message: " . $tropoJson );
		$result = new Result ( $tropoJson );
		$callModel = new Application_Model_Call ();
		$session = $callModel->findSessionIdByStuCallsessionIdAndUpdateCallTimes ( $result->getSessionId () );
		$this->logger->logInfo ( "LinestuController", "incompleteAction", "session id: " . $session ["inx"] );
		$sessionModel = new Application_Model_Session ();
		$row = $sessionModel->getSessionForCallBySessionId ( $session ["inx"] );
		$this->logger->logInfo ( "LinestuController", "incompleteAction", "row: " . $row ["inx"] );
		
		$paramArr = array ();
		$paramArr ["sessionid"] = $row ["inx"];
		$paramArr ["stuphone"] = $row ["b_phone"];
		$paramArr ["stuid"] = $row ["b_inx"];
		$paramArr ["mntphone"] = $row ["c_phone"];
		$paramArr ["mntid"] = $row ["c_inx"];
		$paramArr ["trlphone"] = $row ["d_phone"];
		$paramArr ["trlid"] = $row ["d_inx"];
		// 调用打电话应用并创建call记录
		$this->logger->logInfo ( "LinestuController", "incompleteAction", "call student for : " . $session ["party2CallRes"] . " times" );
		sleep ( 5 );
		$this->logger->logInfo ( "LinestuController", "incompleteAction", "sleep 5 seconds " );
		$troposervice = new TropoService ();
		$troposervice->callstu ( $paramArr );
	}
	public function errorAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->logger->logInfo ( "LinestuController", "errorAction", "student error message: " . $tropoJson );
	}
	protected function initSessionParameters($session) {
		// Parameters for call flow control
		$paramArr = array ();
		$paramArr ["session_id"] = $session->getId (); // tropo的session
		$paramArr ["sessionid"] = $session->getParameters ( "sessionid" ); // 课程的session
		$paramArr ["stuphone"] = $session->getParameters ( "stuphone" );
		$paramArr ["stuid"] = $session->getParameters ( "stuid" );
		$paramArr ["mntphone"] = $session->getParameters ( "mntphone" );
		$paramArr ["trlphone"] = $session->getParameters ( "trlphone" );
		return $paramArr;
	}
	protected function sendNotification($callinx = null) {
		$this->logger->logInfo ( "LinestuController", "sendNotification", "send email to 3 part, cause  instructor" );
		$callModel = new Application_Model_Call ();
		$call = $callModel->find ( $callinx )->current ();
		
		$instructorModel = new Application_Model_Instructor ();
		$instructorEmail = $instructorModel->find ( $call ["party1Inx"] )->current ()->email;
		
		$studentModel = new Application_Model_Student ();
		$studentEmail = $studentModel->find ( $call ["party2Inx"] )->current ()->email;
		
		$translatorModel = new Application_Model_Translator ();
		$translatorEmail = "";
		if ($call ["party3Inx"] != null) {
			$translatorEmail = $translatorModel->find ( $call ["party3Inx"] )->current ()->email;
		}
		$mailcontent = "session canceled As Student didn't answer the call";
		$this->sendEmail ( $studentEmail, $instructorEmail, $translatorEmail, $mailcontent, "session canceled As Student didn't answer the call" );
	}
	private function sendEmail($studentEmail, $instructorEmail, $translatorEmail, $mailcontent, $subject) {
		$loginfo = $studentEmail . "-" . $instructorEmail . "-" . $translatorEmail;
		$this->logger->logInfo ( "LinestuController", "sendEmail", $loginfo );
		try {
			$filename = APPLICATION_PATH . "/configs/application.ini";
			$config = new Zend_Config_Ini ( $filename, 'production' );
			$mail = new PHPMailer ( true ); // New instance, with exceptions
			$body = file_get_contents ( APPLICATION_PATH . '/configs/mail_groupfail.html' );
			$body = preg_replace ( '/mailcontent/', $mailcontent, $body ); // Strip
			$mail->IsSMTP (); // tell the class to use SMTP
			$mail->SMTPAuth = true; // enable SMTP authentication
			$mail->Port = $config->mail->port; // set the SMTP server port
			$mail->Host = $config->mail->host; // SMTP server
			$mail->Username = $config->mail->username; // SMTP server username
			$mail->Password = $config->mail->password; // SMTP server password
			$mail->IsSendmail (); // tell the class to use Sendmail
			$mail->AddReplyTo ( $mail->Username, $mail->Username );
			$mail->SetFrom ( $mail->Username, $mail->Username );
			$mail->AddAddress ( $studentEmail );
			$mail->AddAddress ( $instructorEmail );
			$mail->AddAddress ( $translatorEmail );
			$mail->Subject = $subject;
			$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional,
			$mail->WordWrap = 80; // set word wrap
			$mail->MsgHTML ( $body );
			$mail->IsHTML ( true ); // send as HTML
			$mail->Send ();
		} catch ( phpmailerException $e ) {
		}
	}
	private function sendEmailWhenCallEndToStu($sessioninx) {
		// $sessioninx=37;
		$subject = "session finished notify";
		$sessionModel = new Application_Model_Session ();
		$session = $sessionModel->find ( $sessioninx )->current ();
		$d2 = strtotime ( $session->scheduleStartTime );
		$d3 = strtotime ( $session->actualEndTime );
		$duration = ceil ( ($d3 - $d2) / 60 );
		$body = file_get_contents ( APPLICATION_PATH . '/configs/mail_session_finish_stu.html' );
		$date = $session->scheduleStartTime;
		$studentModel = new Application_Model_Student ();
		$student = $studentModel->find ( $session->studentInx )->current ();
		$tmm = $student->minsRemaining;
		$body = preg_replace ( '/{date}/', $date, $body ); // Strip
		$body = preg_replace ( '/{duration}/', $duration, $body ); // Strip
		$body = preg_replace ( '/{tmm}/', $tmm, $body ); // Strip
		
		try {
			$filename = APPLICATION_PATH . "/configs/application.ini";
			$config = new Zend_Config_Ini ( $filename, 'production' );
			$mail = new PHPMailer ( true ); // New instance, with exceptions
			$mail->IsSMTP (); // tell the class to use SMTP
			$mail->SMTPAuth = true; // enable SMTP authentication
			$mail->Port = $config->mail->port; // set the SMTP server port
			$mail->Host = $config->mail->host; // SMTP server
			$mail->Username = $config->mail->username; // SMTP server username
			$mail->Password = $config->mail->password; // SMTP server password
			$mail->IsSendmail (); // tell the class to use Sendmail
			$mail->AddReplyTo ( $mail->Username, $mail->Username );
			$mail->SetFrom ( $mail->Username, $mail->Username );
			$mail->AddAddress ( $student->email );
			$mail->Subject = $subject;
			$mail->WordWrap = 80; // set word wrap
			$mail->MsgHTML ( $body );
			$mail->IsHTML ( true ); // send as HTML
			$mail->Send ();
		} catch ( phpmailerException $e ) {
		}
		
		// echo $body;
	}
	private function sendEmailWhenCallEndToMnt($sessioninx = null) {
		$this->logger = LoggerFactory::getSysLogger ();
		$sessioninx = 37;
		$subject = "session finished notify";
		
		$sessionModel = new Application_Model_Session ();
		$tempsession = $sessionModel->find ( $sessioninx )->current ();
		
		$instructorModel = new Application_Model_Instructor ();
		$instructor = $instructorModel->find ( $tempsession->instructorInx )->current ();
		$instructorEmail = $instructor->email;
		
		// 查找老师当月参加的session
		$sessions = $sessionModel->findSessionsWhenCallEnd ( $tempsession->instructorInx, "mnt" );
		
		$mailcontent = "";
		$studentModel = new Application_Model_Student ();
		$totalduration = 0;
		foreach ( $sessions as $session ) {
			$student = $studentModel->find ( $session->studentInx )->current ();
			$d2 = strtotime ( $session->scheduleStartTime );
			$d3 = strtotime ( $session->actualEndTime );
			$duration = ceil ( ($d3 - $d2) / 60 );
			$mailcontent = $mailcontent . "student name :" . $student->firstName . " " . $student->lastName . "---- duration:" . $duration . " mins<br/>";
			$totalduration += $duration;
		}
		$mailcontent = $mailcontent . "<br/><br/>total duration :" . $totalduration." mins";
		
		$body = file_get_contents ( APPLICATION_PATH . '/configs/mail_session_finish_mnt.html' );
		$body = preg_replace ( '/{content}/', $mailcontent, $body ); // Strip
		
		try {
			$filename = APPLICATION_PATH . "/configs/application.ini";
			$config = new Zend_Config_Ini ( $filename, 'production' );
			$mail = new PHPMailer ( true ); // New instance, with exceptions
			$mail->IsSMTP (); // tell the class to use SMTP
			$mail->SMTPAuth = true; // enable SMTP authentication
			$mail->Port = $config->mail->port; // set the SMTP server port
			$mail->Host = $config->mail->host; // SMTP server
			$mail->Username = $config->mail->username; // SMTP server username
			$mail->Password = $config->mail->password; // SMTP server password
			$mail->IsSendmail (); // tell the class to use Sendmail
			$mail->AddReplyTo ( $mail->Username, $mail->Username );
			$mail->SetFrom ( $mail->Username, $mail->Username );
			$mail->AddAddress ( $instructorEmail );
			$mail->AddAddress($config->admin->first);
			$mail->AddAddress($config->admin->second);
			$mail->AddAddress($config->admin->third);
			$mail->Subject = $subject;
			$mail->WordWrap = 80; // set word wrap
			$mail->MsgHTML ( $body );
			$mail->IsHTML ( true ); // send as HTML
			$mail->Send ();
		} catch ( phpmailerException $e ) {
		}
		
		// echo $body;
	}
	private function sendEmailWhenCallEndToTrl($sessioninx = null) {
		$this->logger = LoggerFactory::getSysLogger ();
		$subject = "session finished notify";
		$sessionModel = new Application_Model_Session ();
		$tempsession = $sessionModel->find ( $sessioninx )->current ();
		if ($tempsession->translatorInx == null) {
			return;
		}
		$translatorModel = new Application_Model_Translator ();
		$translator = $translatorModel->find ( $tempsession->translatorInx )->current ();
		$translatorEmail = $translator->email;
		
		// 查找翻译当月参加的session
		$sessions = $sessionModel->findSessionsWhenCallEnd ( $tempsession->translatorInx, "trl" );
		
		$this->logger->logInfo ( "LinestuController", "ttAction", count ( $sessions ) );
		$mailcontent = "";
		$studentModel = new Application_Model_Student ();
		$totalduration = 0;
		foreach ( $sessions as $session ) {
			$student = $studentModel->find ( $session->studentInx )->current ();
			$d2 = strtotime ( $session->scheduleStartTime );
			$d3 = strtotime ( $session->actualEndTime );
			$duration = ceil ( ($d3 - $d2) / 60 );
			$mailcontent = $mailcontent . "student name :" . $student->firstName . " " . $student->lastName . "---- duration:" . $duration . " mins<br/>";
			$totalduration += $duration;
		}
		$mailcontent = $mailcontent . "<br/><br/>total duration :" . $totalduration." mins";
		
		$body = file_get_contents ( APPLICATION_PATH . '/configs/mail_session_finish_mnt.html' );
		$body = preg_replace ( '/{content}/', $mailcontent, $body ); // Strip
		
		try {
			$filename = APPLICATION_PATH . "/configs/application.ini";
			$config = new Zend_Config_Ini ( $filename, 'production' );
			$mail = new PHPMailer ( true ); // New instance, with exceptions
			$mail->IsSMTP (); // tell the class to use SMTP
			$mail->SMTPAuth = true; // enable SMTP authentication
			$mail->Port = $config->mail->port; // set the SMTP server port
			$mail->Host = $config->mail->host; // SMTP server
			$mail->Username = $config->mail->username; // SMTP server username
			$mail->Password = $config->mail->password; // SMTP server password
			$mail->IsSendmail (); // tell the class to use Sendmail
			$mail->AddReplyTo ( $mail->Username, $mail->Username );
			$mail->SetFrom ( $mail->Username, $mail->Username );
			$mail->AddAddress ( $translatorEmail );
			$mail->AddAddress($config->admin->first);
			$mail->AddAddress($config->admin->second);
			$mail->AddAddress($config->admin->third);
			$mail->Subject = $subject;
			$mail->WordWrap = 80; // set word wrap
			$mail->MsgHTML ( $body );
			$mail->IsHTML ( true ); // send as HTML
			$mail->Send ();
		} catch ( phpmailerException $e ) {
		}
	}
}

