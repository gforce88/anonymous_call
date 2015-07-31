<?php
require_once 'log/LoggerFactory.php';
require_once 'tropo/tropo.class.php';
require_once 'util/HttpUtil.php';
require_once 'service/TropoService.php';
require_once "service/PaypalService.php";
require_once 'emailLib/AppEmail.php';
class CallspecialistController extends Zend_Controller_Action {
	public function init() {
		$this->tropologger = LoggerFactory::getTropoLogger ();
		$this->syslogger = LoggerFactory::getSysLogger ();
		$this->httpUtil = new HttpUtil ();
		$this->setting = Zend_Registry::get ( "TROPO_SETTING" );
		$this->app = Zend_Registry::get ( "APP_SETTING" );
		$this->_helper->viewRenderer->setNeverRender ();
		$this->specialistsetting = Zend_Registry::get ( "SPECIALIST_SETTING" );
		$this->emailsetting = Zend_Registry::get ( "EMAIL_SETTING" );
	}
	public function indexAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallspecialistController", "indexAction", "receive message from tropo callpatient app, message is :" . $tropoJson );
		
		$session = new Session ( $tropoJson );
		$inx = $session->getParameters ( "inx" );
		$specialistanumber = $session->getParameters ( "specialistanumber" );
		$specialistSessionId = $session->getId ();
		$this->syslogger->logInfo ( "CallspecialistController", "indexAction", "session  is: " . $specialistSessionId );
		
		$callModel = new Application_Model_Call ();
		// 更新专家的tropo sessionId
		$callModel->updateSpecialistSessionId ( $inx, $specialistSessionId );
		
		// 拨专家A电话
		$tropo = new Tropo ();
		$callOptions = array (
				"timeout" => floatval("20.0")
		);
		$tropo->call ( $specialistanumber, $callOptions);
		$tropo->on ( array (
				"event" => "continue",
				"next" => $this->app ["ctx"] . "/callspecialist/welcome",
				"say" => $this->app["hostip"].$this->app["ctx"]."/sound/voice_3L.mp3"
		) );
		$tropo->on ( array (
				"event" => "incomplete",
				"next" => $this->app ["ctx"] . "/callspecialist/callb" 
		) );
		$tropo->renderJSON ();
	}
	
	// 专家A 没接电话，再次拨号 专家B
	public function callbAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallspecialistController", "callbAction", "callbAction message: " . $tropoJson );
		$this->syslogger->logInfo ( "CallspecialistController", "callbAction", "specialist A  is not pick the call ,will call specialist B" );

		$result = new Result ( $tropoJson );
		
		$callModel = new Application_Model_Call ();
		
		$row = $callModel->findCallBySpecialistSessionId($result->getSessionId ());
		
		$arr = array();
		$arr["inx"] = $row ["inx"];
		$arr["specialistbnumber"] = $this->specialistsetting["b"]["phone"];
		$troposervice = new TropoService ();
		$troposervice->callspecialistb ( $arr );
	}
	
	
	//拨b专家的电话，接收方法
	public function bAction(){
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallspecialistController", "bAction", "receive message from tropo callpatient app, message is :" . $tropoJson );
		
		$session = new Session ( $tropoJson );
		$inx = $session->getParameters ( "inx" );
		$specialistbnumber = $session->getParameters ( "specialistbnumber" );
		$specialistSessionId = $session->getId ();
		$this->syslogger->logInfo ( "CallspecialistController", "bAction", "session  is: " . $specialistSessionId );
		
		$callModel = new Application_Model_Call ();
		// 更新专家的tropo sessionId
		$callModel->updateSpecialistSessionId ( $inx, $specialistSessionId );
		
		// 拨专家A电话
		$tropo = new Tropo ();
		$callOptions = array (
				"timeout" => floatval("20.0")
		);
		$tropo->call ( $specialistbnumber, $callOptions);
		$tropo->on ( array (
				"event" => "continue",
				"next" => $this->app ["ctx"] . "/callspecialist/welcome",
				"say" => $this->app["hostip"].$this->app["ctx"]."/sound/voice_3L.mp3"
		) );
		$tropo->on ( array (
				"event" => "incomplete",
				"next" => $this->app ["ctx"] . "/callspecialist/notonline"
		) );
		$tropo->renderJSON ();
	}
	
	//两个专家都没接，通知病人，并发邮件
	public function notonlineAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallspecialistController", "notonlineAction", "incomplete message: " . $tropoJson );
		$result = new Result ( $tropoJson );
		
		$callModel = new Application_Model_Call ();
		$call = $callModel->findCallBySpecialistSessionId($result->getSessionId ());
		
		$troposervice = new TropoService ();
		$troposervice->specialistnoanswerRemind ( $call->patientSessionId );
		
		$this->sendEmailWhenSpecialistNotOnline($call);
	}
	//专家电话接通，开始会议
	public function welcomeAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallspecialistController", "welcomeAction", "welcome message: " . $tropoJson );
		$result = new Result ( $tropoJson );
		$callModel = new Application_Model_Call ();
		$row = $callModel->updateSpecialistCallTime ( $result->getSessionId () );
		$tropo = new Tropo ();
		$confOptions = array (
				"name" => "conference",
				"id" => "jmtyconf" . $row ["inx"],
				"mute" => false,
				"allowSignals" => array (
						"hangup",
						"continue"
				)
		);
	
		$tropo->on ( array (
				"event" => "hangup",
				"next" =>$this->app["ctx"]. "/callspecialist/hangup"
		) );
		$tropo->on ( array (
				"event" => "continue",
				"next" =>$this->app["ctx"]. "/callspecialist/conference"
		) );
		$tropo->conference ( null, $confOptions );
		$tropo->renderJSON ();
	
	}
	
	public function hangupAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallspecialistController", "hangupAction", "hangup message: " . $tropoJson );
		/*
		$result = new Result ( $tropoJson );
		$callModel = new Application_Model_Call ();
		$row = $callModel->updateGrpCallEndTime ( $result->getSessionId () );

		$port = $this->emailsetting["port"];
		$host = $this->emailsetting["host"];
		$username = $this->emailsetting["username"];
		$password = $this->emailsetting["password"];
		$appEmails = new AppEmails ($host,$port,$username,$password);
		$this->syslogger->logInfo ( "CallspecialistController", "hangupAction", "conference over , go paypal for pay ");
		$usedmins = ceil ( (strtotime ( $row ["grpCallEndTime"] ) - strtotime ( $row ["specialistCallTime"] )) / 60 );
        $chargeAmt = $this->specialistsetting["cost"] * $usedmins;
        if ($this->doPayPalPayment($row)) {
            // 支付成功
        	$this->syslogger->logInfo ( "CallspecialistController", "hangupAction", "paypal pay ok ,and send billing info to user");
        	//$usedmins = ceil ( (strtotime ( $row ["grpCallEndTime"] ) - strtotime ( $row ["specialistCallTime"] )) / 60 );
        	//$chargeAmt = $this->specialistsetting["cost"] * $usedmins;
        	$appEmails->sendThankYouEmail($row->patientEmail, $usedmins, $chargeAmt);
        	$this->syslogger->logInfo ( "CallspecialistController", "hangupAction", "mail has send to".$row->patientEmail);
        	$appEmails->sendThankYouEmail ('incognito-info@unisrv.jp',$usedmins,$chargeAmt);
        	$this->syslogger->logInfo ( "CallspecialistController", "hangupAction", "mail has send to admin");
        } else {
            //支付失败
        	$this->syslogger->logInfo ( "CallspecialistController", "hangupAction", "paypal pay fail ,and send carderr info to admins");
        	$appEmails->sendAdminCardErrEmail("ge.szeto@gmail.com",$row->patientEmail, $usedmins, $chargeAmt);
        	$appEmails->sendAdminCardErrEmail("gwu@incognitosys.com",$row->patientEmail, $usedmins, $chargeAmt);
        	$appEmails->sendAdminCardErrEmail("wkrogmann@incognitosys.com",$row->patientEmail, $usedmins, $chargeAmt);
        	$appEmails->sendAdminCardErrEmail("daihuan@topmoon.com.cn",$row->patientEmail, $usedmins, $chargeAmt);
        	$this->syslogger->logInfo ( "CallspecialistController", "hangupAction", "mail has send to admins");
        }
        */

	}
	
	public function testAction(){
		$callModel = new Application_Model_Call ();
		$row = $callModel->find("21")->current();
		$usedmins = ceil ( (strtotime ( $row ["grpCallEndTime"] ) - strtotime ( $row ["specialistCallTime"] )) / 60 );
		$chargeAmt = $this->specialistsetting["cost"] * $usedmins;
		echo $usedmins;
		echo "<br/>";
		echo $chargeAmt;
	}
	
	public function conferenceAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallspecialistController", "conferenceAction", "conferenceAction message: " . $tropoJson );
	}
	
	
	// 专家A,B都没接电话
	private function sendEmailWhenSpecialistNotOnline($call){
		$this->syslogger->logInfo ( "CallspecialistController", "sendEmailWhenSpecialistNotOnline", "TherapistNotAvail");
		$port = $this->emailsetting["port"];
		$host = $this->emailsetting["host"];
		$username = $this->emailsetting["username"];
		$password = $this->emailsetting["password"];
		$appEmails = new AppEmails ($host,$port,$username,$password);
		$appEmails->sendTherapistNotAvailEmail($call->patientEmail);
	}

    

    private function calculateAmount($beginTime, $endTime) {
    	$duringMin = ceil ( (strtotime ( $endTime ) - strtotime ($beginTime )) / 60 );
        $amount = $this->specialistsetting["cost"] * $duringMin;
        return $amount;
    }
}

