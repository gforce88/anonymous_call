<?php
require_once 'log/LoggerFactory.php';
require_once 'tropo/tropo.class.php';
require_once 'util/HttpUtil.php';
require_once 'service/TropoService.php';
require_once "service/PaypalService.php";
require_once 'emailLib/AppEmail.php';
class CallpatientController extends Zend_Controller_Action {
	public function init() {
		$this->tropologger = LoggerFactory::getTropoLogger ();
		$this->syslogger = LoggerFactory::getSysLogger ();
		$this->httpUtil = new HttpUtil ();
		$this->setting = Zend_Registry::get ( "TROPO_SETTING" );
		$this->app = Zend_Registry::get ( "APP_SETTING" );
		$this->_helper->viewRenderer->setNeverRender ();
		$this->specialistsetting = Zend_Registry::get("SPECIALIST_SETTING");
		$this->emailsetting = Zend_Registry::get ( "EMAIL_SETTING" );
	}
	public function indexAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallpatientController", "indexAction", "receive message from tropo callpatient app, message is :" . $tropoJson );
		
		$session = new Session ( $tropoJson );
		$inx = $session->getParameters ( "inx" );
		$patientNumber = $session->getParameters ( "patientNumber" );
		$patientSessionId = $session->getId ();
		$this->syslogger->logInfo ( "CallpatientController", "indexAction", "session  is: " . $patientSessionId );
		
		$callModel = new Application_Model_Call ();
		// 更新病人的tropo sessionId
		$row = $callModel->updatePatientSessionId ( $inx, $patientSessionId );
		// 检查病人的拨号次数
		$times = $callModel->checkCallTimes ( $inx );
		
		if ($times > 3) {
			$this->tropologger->logInfo ( "CallpatientController", "indexAction", "patient didn't pickup the call for 3 times. send email to notify" );
			// 病人三次不接电话,发送邮件通知
			$this->sendNotification ( $row );
		} else {
			// 拨病人电话
			$tropo = new Tropo ();
// 			$tropo->call ( $patientNumber );
			$callOptions = array (
					"timeout" => floatval("20.0")
			);
			$tropo->call ( $patientNumber, $callOptions);
			$tropo->on ( array (
					"event" => "continue",
					"next" => $this->app ["ctx"] . "/callpatient/welcome",
					//"say" => "Welcome to jmty Application! Please hold on for joining the conference."
					"say" => $this->app["hostip"].$this->app["ctx"]."/sound/voice_1L.mp3" 
			) );
			$tropo->on ( array (
					"event" => "incomplete",
					"next" => $this->app ["ctx"] . "/callpatient/incomplete" 
			) );
			$tropo->renderJSON ();
		}
	}
	
	// 病人电话接通
	public function welcomeAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallpatientController", "welcomeAction", "welcome message: " . $tropoJson );
		$result = new Result ( $tropoJson );
		$callModel = new Application_Model_Call ();
		$row = $callModel->updatePatientCallTime ( $result->getSessionId () );
		$tropo = new Tropo ();
		$confOptions = array (
				"name" => "conference",
				"id" => "jmtyconf" . $row ["inx"],
				"mute" => false,
				"allowSignals" => array (
						"specialistnoanswer",
						"hangup",
						"continue" 
				) 
		);
		
		$tropo->on ( array (
				"event" => "specialistnoanswer",
				"next" =>$this->app["ctx"]. "/callpatient/specialistnoanswer"
		) );
		
		$tropo->on ( array (
				"event" => "hangup",
				"next" =>$this->app["ctx"]. "/callpatient/hangup"
		) );
		$tropo->on ( array (
				"event" => "continue",
				"next" =>$this->app["ctx"]. "/callpatient/conference"
		) );
		$tropo->conference ( null, $confOptions );
		$tropo->renderJSON ();
		
		$arr = array();
		$arr["inx"] = $row ["inx"];
		$arr["specialistanumber"] = $this->specialistsetting["a"]["phone"];
		$troposervice = new TropoService ();
		$troposervice->callspecialist ( $arr );
	}
	
	// 病人没接电话，再次拨号
	public function incompleteAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallpatientController", "incompleteAction", "incomplete message: " . $tropoJson );
		$result = new Result ( $tropoJson );
		$callModel = new Application_Model_Call ();
		$call = $callModel->updateTryTimes ( $result->getSessionId () );
		
		$params = array ();
		$params ["inx"] = $call->inx;
		$params ["patientNumber"] = $call->patientNumber;
		
		$troposervice = new TropoService ();
		$troposervice->callpatient ( $params );
		$this->syslogger->logInfo ( "CallpatientController", "incompleteAction", "start try again call patient" );
	}
	
	public function specialistnoanswerAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallpatientController", "specialistnoanswerAction", "specialist not answer : " . $tropoJson );
		$tropo = new Tropo ();
		$tropo->say($this->app["hostip"].$this->app["ctx"]."/sound/voice_2L.mp3");
		//$tropo->say("specialist did not answer, the conference is end");
		$tropo->renderJSON ();
	
	}
	
	public function hangupAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallpatientController", "hangupAction", "hangup message: " . $tropoJson );
		
		$result = new Result ( $tropoJson );
		$callModel = new Application_Model_Call ();
		$row = $callModel->updateGrpCallEndTime ( $result->getSessionId () );
		if($row["specialistCallTime"]!=null){//只有当专家的接通时间不为空的时候才支付
			$port = $this->emailsetting["port"];
			$host = $this->emailsetting["host"];
			$username = $this->emailsetting["username"];
			$password = $this->emailsetting["password"];
			$appEmails = new AppEmails ($host,$port,$username,$password);
			$this->syslogger->logInfo ( "CallpatientController", "hangupAction", "conference over , go paypal for pay ");
			$usedmins = ceil ( (strtotime ( $row ["grpCallEndTime"] ) - strtotime ( $row ["specialistCallTime"] )) / 60 );
			$chargeAmt = $this->specialistsetting["cost"] * $usedmins;
			if ($this->doPayPalPayment($row)) {
			// 支付成功
			$this->syslogger->logInfo ( "CallpatientController", "hangupAction", "paypal pay ok ,and send billing info to user");
			//$usedmins = ceil ( (strtotime ( $row ["grpCallEndTime"] ) - strtotime ( $row ["specialistCallTime"] )) / 60 );
			//$chargeAmt = $this->specialistsetting["cost"] * $usedmins;
			$appEmails->sendThankYouEmail($row->patientEmail, $usedmins, $chargeAmt);
			$this->syslogger->logInfo ( "CallpatientController", "hangupAction", "mail has send to".$row->patientEmail);
			$appEmails->sendThankYouEmail ('incognito-info@unisrv.jp',$usedmins,$chargeAmt);
			$this->syslogger->logInfo ( "CallpatientController", "hangupAction", "mail has send to admin");
			} else {
			//支付失败
			$this->syslogger->logInfo ( "CallpatientController", "hangupAction", "paypal pay fail ,and send carderr info to admins");
			$appEmails->sendAdminCardErrEmail("ge.szeto@gmail.com",$row->patientEmail, $usedmins, $chargeAmt);
			$appEmails->sendAdminCardErrEmail("gwu@incognitosys.com",$row->patientEmail, $usedmins, $chargeAmt);
			$appEmails->sendAdminCardErrEmail("wkrogmann@incognitosys.com",$row->patientEmail, $usedmins, $chargeAmt);
			$appEmails->sendAdminCardErrEmail("daihuan@topmoon.com.cn",$row->patientEmail, $usedmins, $chargeAmt);
			$this->syslogger->logInfo ( "CallpatientController", "hangupAction", "mail has send to admins");
			}
		}
	}
	
	private function doPayPalPayment($call) {
		$this->syslogger->logInfo ( "CallpatientController", "doPayPalPayment", "start pay with token:".$call["paypaltoken"]);
		$paypalService = new PaypalService();
		$paypalToken = $call["paypaltoken"];
		$roundAmount = $paypalService->adjustAmount($this->calculateAmount($call["specialistCallTime"], $call["grpCallEndTime"]), "JPY");
		$this->syslogger->logInfo ( "CallpatientController", "doPayPalPayment", "roundAmount:".$roundAmount);
		return $paypalService->charge($paypalToken, $roundAmount, "JPY");
	}
	
	public function conferenceAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallpatientController", "conferenceAction", "conferenceAction message: " . $tropoJson );
	}
	
	// TODO 等客户完成发送EMAIL方法，
	// 病人三次不接电话 发送邮件通知
	private function sendNotification($row = null) {
		$this->syslogger->logInfo ( "CallpatientController", "sendNotification", "patient did not pick the call for 3 times, sending email to patient");
		$port = $this->emailsetting["port"];
		$host = $this->emailsetting["host"];
		$username = $this->emailsetting["username"];
		$password = $this->emailsetting["password"];
		$appEmails = new AppEmails ($host,$port,$username,$password);
		$appEmails->sendUserDidNotAnswerEmail($row["patientEmail"]);
		$this->syslogger->logInfo ( "CallpatientController", "sendNotification", "patient did not pick the call for 3 times,  email has send to  ".$row["patientEmail"]);
	}
	
// 	public function aaAction(){
// 		$paypalService = new PaypalService();
// 		$roundAmount = $paypalService->adjustAmount($this->calculateAmount("", ""), "JPY");
// 		echo $roundAmount;
// 	}
	
	private function calculateAmount($beginTime, $endTime) {
// 		$endTime = "2015-04-18 01:16:37";
// 		$beginTime = "2015-04-18 01:16:22";
		$duringMin = ceil ( (strtotime ( $endTime ) - strtotime ($beginTime )) / 60 );
		$amount = $this->specialistsetting["cost"] * $duringMin;
		return $amount;
	}
}

