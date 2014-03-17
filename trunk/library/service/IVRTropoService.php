<?php
require_once 'util/HttpUtil.php';
require_once 'service/IVRServiceInterface.php';
require_once 'models/ScheduleManager.php';
require_once 'models/TempQuerystringManager.php';

class IVRServiceTropo implements IVRServiceInterface{
	private $httpUtil;	
	private $querystringManager;
	public function __construct(){
		$this->httpUtil = new HttpUtil();
		$this->querystringManager = new TempQuerystringManager();
        $this->logger = Zend_Registry::get('LOGGER');
		$this->setting = Zend_Registry::get ( 'tropo_setting' );
    }
	
	/**
     * Ountbound call with tropo platform
     * @param $parameters array
     */
	public function initCall($parameters){
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Call Mode  " . $parameters["callMode"]);
		if($parameters["callMode"] == CALL_MODE_REVERSE_CALL_REP){	// reverse call lead
			if($parameters['callType']==ScheduleManager::SCHEDULE_TYPE_SIMULTANEOUS){
				$this->initSimultaneousHack($parameters);	// init call with simultaneous script
			}else if($parameters['callType']==ScheduleManager::SCHEDULE_TYPE_ROUND || $parameters['callType']==ScheduleManager::SCHEDULE_TYPE_STEP){
				$this->initStepRingRoundRobin($parameters);
			}
		}else if( $parameters["callMode"] == CALL_MODE_REVERSE_CALL_LEAD ){	// reverse call rep	
				$this->initStepRingRoundRobin($parameters);
		}else{
		
			if($parameters['callType']==ScheduleManager::SCHEDULE_TYPE_SIMULTANEOUS){
				$this->initSimultaneousHack($parameters);	// init call with simultaneous script
				return;
			}else if($parameters['callType']==ScheduleManager::SCHEDULE_TYPE_ROUND){
				$parameters['callType'] = "ROUNDROBIN";
			}else if($parameters['callType']==ScheduleManager::SCHEDULE_TYPE_BROADCAST) {
				$parameters['callType'] = "BROADCAST";
				$this->initBroadcast($parameters);
				return;
			}else{
				$parameters['callType'] = "STEPRING";
			}
			
			$this->initStepRingRoundRobin($parameters);
		}
	}
	
	private function initSimultaneousHack($parameters){
		$url = $this->setting['url'];
		$token = $this->setting['token'];
		$parameters['callType'] = "SIMULTANEOUS";
		if(!isset($parameters['queueId'])){
			$parameters['queueId'] = 0;
		}
		$parameters['toRepNumbers'] = $parameters['numberToDial'];
		$parameters['numberToDial'] = "";
		$inquiryid = $parameters["inquiryId"]; 
		$hackToken = urlencode($this->setting["simultaneousHackToken"]);
		$s2lSipNum = urlencode($this->setting["s2lSipNum"]);
		$locationoffset = urlencode($parameters["locationOffset"]);
		$notificationTimes = urlencode($parameters["notificationTimes"]);
		$querystringId=$this->querystringManager->insert(http_build_query($parameters));
		$params = "action=create&token=$hackToken&s2lSipNum=$s2lSipNum&querystringid=$querystringId&inquiryid=$inquiryid&locationoffset=$locationoffset&notificationtimes=$notificationTimes&".http_build_query($parameters);
		$response = $this->httpUtil->doHTTPPOST($url, $params);
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Request URL [$url?$params] ");
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Response :");
		foreach($response as $key => $value){ $this->logger->info(" $key => $value" );	}
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Finished ");
	}
	
	private function initStepRingRoundRobin($parameters){
		$url = $this->setting['url'];
		$token = $this->setting['token'];
		$params = "action=create&token=$token&".http_build_query($parameters);
		$response = $this->httpUtil->doHTTPPOST($url, $params);
		
		
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Request URL [$url?$params] ");
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Response :");
		foreach($response as $key => $value){ $this->logger->info(" $key => $value" );	}
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Finished ");
	}
	
	private function initBroadcast($parameters){
		/*
		$url = $this->setting['url'];
		$token = $this->setting['token'];
		$params = "action=create&token=$token&".http_build_query($parameters);
		$response = $this->httpUtil->doHTTPPOST($url, $params);
		
		
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Request URL [$url?$params] ");
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Response :");
		foreach($response as $key => $value){ $this->logger->info(" $key => $value" );	}
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Finished ");
		*/
		$this->logger->info("parameters numberToDial :".$parameters["numberToDial"]);//+15013911006|+12176507163
		if(strpos($parameters["numberToDial"], "|")){
			$numbersArray = explode("|",$parameters["numberToDial"]);
		}else{
			$numbersArray[1] = $parameters["numberToDial"];
		}
		//var_dump($numbersArray);
		foreach ($numbersArray as $repNumber){
			$this->logger->info("calling rep number is ".$repNumber);
			$parameters["numberToDial"]= $repNumber;
			$url = $this->setting['url'];
			$token = $this->setting['token'];
			$params = "action=create&token=$token&".http_build_query($parameters);
			$response = $this->httpUtil->doHTTPPOST($url, $params);
			
			
			$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Request URL [$url?$params] ");
			$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Response :");
			foreach($response as $key => $value){ $this->logger->info(" $key => $value" );	}
			$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Finished ");
		}
	}

	public function initCpaHack($numberToCall, $callerID, $sentences, $parameters) {
		$url = $this->setting['url'];
		$hackToken = urlencode($this->setting["cpaHackToken"]);
		$s2lSipNum = urlencode($this->setting["cpaHackNumber"]);
		$sentences = str_replace(" ", "~", $sentences);
		$querystringId = $this->querystringManager->insert(http_build_query($parameters));
		$params = "action=create&token=$hackToken&numberToCall=$numberToCall&callerID=$callerID&initialMessage=$sentences&s2lSipNum=$s2lSipNum&querystringId=$querystringId";
		$response = $this->httpUtil->doHTTPPOST($url, $params);
		$this->logger->info("[INQUIRY TRACKING FOR " . $parameters["inquiryId"] . "]" .  " Request URL [$url?$params] ");
	}
	
}
?>