<?php
require_once 'phpmailer/class.phpmailer.php';
require_once 'log/LoggerFactory.php';
class SessionController extends Zend_Controller_Action {
	private $logger;
	public function init() {
		$translate = new Zend_Translate ( 'ini', APPLICATION_PATH . "/language/English.ini", 'US' );
		$translate->addTranslation ( APPLICATION_PATH . "/language/Jp.ini", 'JP' );
		$filename = APPLICATION_PATH . "/configs/application.ini";
		$config = new Zend_Config_Ini ( $filename, 'production' );
		$translate->setlocale ( $config->mjs->locale );
		$this->logger = LoggerFactory::getSysLogger();
		$this->view->translate = $translate;
	}
	public function indexAction() {
		$sessionmodel = new Application_Model_Session ();
		$sessions = $sessionmodel->getSessionList ();
		$this->view->sessionlist = $sessions;
	}
	
	//创建session
	public function createAction() {
		$this->logger->logInfo("SessionController", "createAction", " enter create Session");
		if ($this->getRequest ()->isPost ()) {
			$params = $this->_request->getPost ();
			if ($this->checkStudentRemainMin ( $params )) {
				$instructorModel = new Application_Model_Instructor ();
				$instructorId = $instructorModel->saveOrupdateInstructor ( $params );
				
				$translatorId = "";
				$translatorEmail = "";
				$translatorModel = new Application_Model_Translator ();
				if (($params ['tFirstName'] != "") && ($params ['tLastName'] != "")) {
					$translatorId = $translatorModel->saveOrupdateTranslator ( $params );
				}
				$sessionModel = new Application_Model_Session ();
				$sessionInx = $sessionModel->createSession ( $params, $instructorId, $translatorId );
				
				$instructorEmail = $instructorModel->find ( $instructorId )->current ()->email;
				if ($translatorId != "") {
					$translatorEmail = $translatorModel->find ( $translatorId )->current ()->email;
				}
				$studentModel = new Application_Model_Student ();
				$studentEmail = $studentModel->find ( $params ["studentId"] )->current ()->email;
				$session = $sessionModel->find ( $sessionInx )->current ();
				
				$instructor = $instructorModel->find($instructorId)->current ();
				$student = $studentModel->find($params ["studentId"])->current ();
				$translator = "";
				if ($translatorId != "") {
					$translator = $translatorModel->find ( $translatorId )->current ();
				}
				
				//$mailcontent = "session start date is: " . $session->scheduleStartTime . " session end date is :" . $session->scheduleEndTime;
				$mailcontent ="お疲れ様です,<p/>
				
				新たな補習授業との手配が以下の通り<p/>
				
				学生 ".$student->firstName." ".$student->lastName." <p/>
				
				指導先生 ".$instructor->firstName." ".$instructor->lastName." <p/>
				
				通訳 ".$translator->firstName." ".$translator->lastName." <p/>
				
				補習授業の手配が:". $session->scheduleStartTime ."<p/>
				
				ありがとうございます。";
				
				$this->sendEmail ( $studentEmail, $instructorEmail, $translatorEmail, $mailcontent,"新たな補習授業との手配が以下の通り" );
				
				$this->view->resultmessage = $this->view->translate->_ ( "sescreated" );
			} else {
				$this->view->resultmessage = $this->view->translate->_ ( "stuminnotenough" );
			}
		}
	}
	//修改session
	public function editAction() {
		$this->logger->logInfo("SessionController", "editAction", " enter edit Session");
		if ($this->getRequest ()->isPost ()) {
			$params = $this->_request->getPost ();
			if ($this->checkStudentRemainMin ( $params )&&$this->checkSessionStatus($params)) {
				$instructorModel = new Application_Model_Instructor ();
				$instructorId = $instructorModel->saveOrupdateInstructor ( $params );
				$translatorId = null;
				if (($params ['tFirstName'] != "") && ($params ['tLastName'] != "")) {
					$translatorModel = new Application_Model_Translator ();
					$translatorId = $translatorModel->saveOrupdateTranslator ( $params );
				}
				
				$sessionModel = new Application_Model_Session ();
				$oldSession = $sessionModel->find($inx=$params["inx"])->current ();
				$oldSessionDate = $oldSession->scheduleStartTime;
				$sessionInx = $sessionModel->updateSession ( $params, $instructorId, $translatorId );
				$session = $sessionModel->find ( $sessionInx )->current ();
				
				$instructorEmail = $instructorModel->find ( $instructorId )->current ()->email;
				$translatorEmail="";
				if ($translatorId != "") {
					$translatorEmail = $translatorModel->find ( $translatorId )->current ()->email;
				}
				$studentModel = new Application_Model_Student ();
				$studentEmail = $studentModel->find ( $params ["studentId"] )->current ()->email;
				
				$this->logger->logInfo("SessionController", "editAction", " studentId:".$params ["studentId"]);
				$session = $sessionModel->find ( $sessionInx )->current ();

				
				$instructor = $instructorModel->find($instructorId)->current();
				$student = $studentModel->find($params ["studentId"])->current();
				$translator = "";
				if ($translatorId != "") {
					$translator = $translatorModel->find ( $translatorId )->current();
				}
				$this->logger->logInfo("SessionController", "editAction", " firstName:".$student->firstName);
				//$mailcontent = "session start date is: " . $session->scheduleStartTime . " session end date is :" . $session->scheduleEndTime;
				$mailcontent ="お疲れ様です,<p/>
						
				以前手配した" . $oldSessionDate ." 補習授業時間を変更しました <p/>
						
				新たな" . $session->scheduleStartTime ." 補習授業時間は<p/>
				
				補習授業との参加者は以下の通り<p/>
						
				学生 ".$student->firstName."  ".$student->lastName." <p/>
				
				指導先生 ".$instructor->firstName." ".$instructor->lastName." <p/>
				
				通訳 ".$translator->firstName." ".$translator->lastName." <p/>
				
				別途ご連絡させていただきます<p/>
						
				ありがとうございます。";
				
				$this->sendEmail($studentEmail, $instructorEmail, $translatorEmail, $mailcontent,"MJS補習授業時間を変更しました");
				$this->view->resultmessage = $this->view->translate->_ ( "sesupdate" );
			} else {
				if(!$this->checkSessionStatus($params)){
					$this->view->resultmessage = $this->view->translate->_ ( "sessioncannotupdate" );
				}else{
					$this->view->resultmessage = $this->view->translate->_ ( "stuminnotenough" );
				}
			}
		} else {
			// find studentinfo
			$studentInx = $this->_getParam ( "studentinx" );
			$student = new Application_Model_Student ();
			$studentlist = $student->find ( $studentInx );
			$this->view->studentlist = $studentlist [0];
			// find instructorinfo
			$instructorInx = $this->_getParam ( "instructorinx" );
			$instructor = new Application_Model_Instructor ();
			$instructorlist = $instructor->find ( $instructorInx );
			$this->view->instructorlist = $instructorlist [0];
			// find translatorinfo
			$translatorInx = $this->_getParam ( "translatorinx" );
			if ($translatorInx != null) {
				$translator = new Application_Model_Translator ();
				$translatorlist = $translator->find ( $translatorInx );
				$this->view->translatorlist = $translatorlist [0];
			}
			// find sessioninfo
			$sessionInx = $this->_getParam ( "sessioninx" );
			$session = new Application_Model_Session ();
			$sessionlist = $session->find ( $sessionInx );
			$this->view->session = $sessionlist [0];
		}
	}
	
	//删除session
	public function deleteAction() {
		$sessioninx = $this->getParam ( "inx" );
		$sessionmodel = new Application_Model_Session ();
		$session = $sessionmodel->find($sessioninx)->current();
		$studentinx = $session->studentInx;
		$instructorinx = $session->instructorInx;
		$translatorinx = $session->translatorInx;
		$studentModel = new Application_Model_Student ();
		$studentEmail = $studentModel->find($studentinx)->current()->email;
		$instructorModel = new Application_Model_Instructor ();
		$instructorEmail = $instructorModel->find($instructorinx)->current()->email;
		$translatorEmail = "";
		if($translatorinx!=null){
			$translatorModel = new Application_Model_Translator();
			$translatorEmail = $translatorModel->find($translatorinx)->current()->email;
		}
		
		$mailcontent ="お疲れ様です,<p/>
		
				以前手配した" . $session->scheduleStartTime ." 補習授業を取消しました<p/>
		
				ありがとうございます。";
		
		$this->sendEmail($studentEmail, $instructorEmail, $translatorEmail, $mailcontent,"補習授業時間を取消しました");
		
		$sessionmodel->deleteSession ( $sessioninx );
		$this->redirect ( "/session" );
	}
	
	private function sendEmail($studentEmail, $instructorEmail, $translatorEmail, $mailcontent,$subject) {
		$loginfo= $studentEmail . "-" . $instructorEmail . "-" . $translatorEmail ;
		$this->logger->logInfo("SessionController", "sendEditEmail", $loginfo);
		try {
			$filename = APPLICATION_PATH . "/configs/application.ini";
			$config = new Zend_Config_Ini ( $filename, 'production' );
			$mail = new PHPMailer ( true ); // New instance, with exceptions
			// enabled
			$body = file_get_contents ( APPLICATION_PATH . '/configs/mail_session.html' );
			// $body = preg_replace ( '/\\\\/', '', $body ); // Strip
			$body = preg_replace ( '/mailcontent/', $mailcontent, $body ); // Strip
			// backslashes
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
			// comment
			// out
			// and
			// test
			$mail->WordWrap = 80; // set word wrap
				
			$mail->MsgHTML ( $body );
				
			$mail->IsHTML ( true ); // send as HTML
				
			$mail->Send ();
			//echo 'Message has been sent.';
		} catch ( phpmailerException $e ) {
			//echo $e->errorMessage ();
		}
	}
	
	// 判断学生的剩余时间是否足够
	private function checkStudentRemainMin($params = array()) {
		$studentModel = new Application_Model_Student ();
		$row = $studentModel->find ( $params ["studentId"] )->current ();
		$remainMin = $row->minsRemaining;
		$this->logger->logInfo("SessionController", "checkStudentRemainMin", "remainMIn: " . $remainMin . " dur:" . $params ['dur']);
		return $remainMin > $params ["dur"];
	}
	
	//判断
	private function checkSessionStatus($params = array()){
		$sessionModel = new Application_Model_Session();
		$session = $sessionModel->find($params["inx"])->current();
		if($session->actualEndTime){
			return false;
		}
		if(strtotime($session->scheduleStartTime)>time()){
			return false;
		}
		return true;
	}
}







