<?php
require_once 'log/LoggerFactory.php';
require_once 'tropo/tropo.class.php';
require_once 'util/HttpUtil.php';
require_once 'service/TropoService.php';
class CallspecialistController extends Zend_Controller_Action {
	public function init() {
		$this->tropologger = LoggerFactory::getTropoLogger ();
		$this->syslogger = LoggerFactory::getSysLogger ();
		$this->httpUtil = new HttpUtil ();
		$this->setting = Zend_Registry::get ( "TROPO_SETTING" );
		$this->app = Zend_Registry::get ( "APP_SETTING" );
		$this->_helper->viewRenderer->setNeverRender ();
		$this->specialistsetting = Zend_Registry::get ( "SPECIALIST_SETTING" );
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
		$tropo->call ( $specialistanumber );
		$tropo->on ( array (
				"event" => "continue",
				"next" => $this->app ["ctx"] . "/callspecialist/welcome",
				"say" => "Welcome to jmty Application! Please hold on for joining the conference." 
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
		$this->tropologger->logInfo ( "CallpatientController", "callbAction", "callbAction message: " . $tropoJson );
		$this->syslogger->logInfo ( "CallpatientController", "callbAction", "specialist A  is not pick the call ,will call specialist B" );

		// 拨专家B电话
		$tropo = new Tropo ();
		$tropo->call ( $this->specialistsetting["b"]["phone"] );
		$tropo->on ( array (
				"event" => "continue",
				"next" => $this->app ["ctx"] . "/callspecialist/welcome",
				"say" => "Welcome to jmty Application! Please hold on for joining the conference."
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
				"next" =>$this->app["ctx"]. "/callpatient/hangup"
		) );
		$tropo->on ( array (
				"event" => "continue",
				"next" =>$this->app["ctx"]. "/callpatient/conference"
		) );
		$tropo->conference ( null, $confOptions );
		$tropo->renderJSON ();
	
	}
	
	public function hangupAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallspecialistController", "hangupAction", "hangup message: " . $tropoJson );
		$result = new Result ( $tropoJson );
		$callModel = new Application_Model_Call ();
		$this->tropologger->logInfo ( "CallspecialistController", "hangupAction", "update grp call end time start");
		$row = $callModel->updateGrpCallEndTime ( $result->getSessionId () );
		$this->tropologger->logInfo ( "CallspecialistController", "hangupAction", "update grp call end time end");
		$this->sendNotificationWhenCallOver($row->inx);
	}
	
	public function conferenceAction() {
		$tropoJson = file_get_contents ( "php://input" );
		$this->tropologger->logInfo ( "CallspecialistController", "conferenceAction", "conferenceAction message: " . $tropoJson );
	}
	
	// TODO 等客户完成发送EMAIL方法，
	// 会议结束，发详细扣款邮件给病人
	private function sendNotificationWhenCallOver($inx = null) {
		$this->syslogger->logInfo ( "CallspecialistController", "sendNotificationWhenCallOver", "call is over, sending email to patient");
	}
	
	//TODO TODO 等客户完成发送EMAIL方法，
	// 专家A,B都没接电话
	private function sendEmailWhenSpecialistNotOnline($call){
		$this->syslogger->logInfo ( "CallspecialistController", "sendEmailWhenSpecialistNotOnline", "specialist A,B didn't pick the call");
	}
}

