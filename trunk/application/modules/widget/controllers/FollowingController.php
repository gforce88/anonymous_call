<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'service/TropoService.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/CallManager.php';
require_once 'models/InviteManager.php';
require_once 'models/EmailManager.php';

class Widget_FollowingController extends Zend_Controller_Action {
	private $logger;
	private $paypalService;
	private $tropoService;
	private $partnerManager;
	private $callManager;
	private $inviteManager;
	private $emailManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->paypalService = new PaypalService();
		$this->tropoService = new TropoService();
		$this->partnerManager = new PartnerManager();
		$this->callManager = new CallManager();
		$this->inviteManager = new InviteManager();
		$this->emailManager = new EmailManager();
		session_start();
	}

	public function indexAction() {
		if ($_SESSION["inviteInx"] == null) {
			$invite = $this->inviteManager->findInviteByInxToken($_REQUEST["inx"], $_REQUEST["token"]);
			$partner = $this->partnerManager->findPartnerByInx($invite["partnerInx"]);
			if ($invite == null || $partner == null) {
				// The URL is invalid
				$this->view->assign("invalidReason", MultiLang::getText("This_link_is_invalid", $_REQUEST["country"]));
				return $this->renderScript("/response/invalid.phtml");
			}
			
			$_SESSION["inviteInx"] = $invite["inviteInx"];
			$_SESSION["inviteType"] = $invite["inviteType"];
			$_SESSION["partnerInx"] = $invite["partnerInx"];
			$_SESSION["inviterInx"] = $invite["inviterInx"];
			$_SESSION["inviteeInx"] = $invite["inviteeInx"];
			$_SESSION["country"] = $partner["country"];
		}
	}

	public function readyAction() {
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function problemAction() {
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function refreshAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$call = $this->callManager->findCallByInx($_POST["callInx"]);
		$callConnectTime = strtotime($call["callConnectTime"]);
		$callEndTime = strtotime($call["callEndTime"]);
		if ($callConnectTime <= 0) {
			// Call is not started
			$result = array (
				"status" => 0 
			);
		} else if ($callEndTime <= 0) {
			// Call is connected but not completed
			$result = array (
				"status" => 1,
				"totalTime" => time() - $callConnectTime 
			);
		} else {
			// Call is completed
			$result = array (
				"status" => 2,
				"totalTime" => $callEndTime - $callConnectTime 
			);
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function test() {
		// Dispatch
		if (count($invalidFields) == 0) {
			$partner = $this->partnerManager->findPartnerByInx($_POST["partnerInx"]);
			$inviter = $this->userManager->findUserByInx($_POST["inviterInx"]);
			$invitee = $this->userManager->findUserByInx($_POST["inviteeInx"]);
			$invitee["phoneNum"] = $_POST["inviteePhoneNumber"];
			$invitee["paypalToken"] = $paypalToken;
			$this->userManager->update($invitee);
			
			$call = array (
				"inviteInx" => $_POST["inviteInx"] 
			);
			$paramArr = array (
				"partnerInx" => $partner["inx"],
				"maxRingDur" => $partner["maxRingDur"],
				"inviteInx" => $call["inviteInx"],
				"partnerNumber" => $partner["phoneNum"],
				"country" => $partner["country"] 
			);
			
			if ($paypalToken == null) {
				// Pay by Inviter, first call inviter
				$call["callType"] = CALL_TYPE_FIRST_CALL_INVITER;
				$paramArr["callType"] = CALL_TYPE_FIRST_CALL_INVITER;
				$paramArr["1stLegNumber"] = $inviter["phoneNum"];
				$paramArr["2ndLegNumber"] = $invitee["phoneNum"];
			} else {
				// Pay by Invitee, first call invitee
				$call["callType"] = CALL_TYPE_FIRST_CALL_INVITEE;
				$paramArr["callType"] = CALL_TYPE_FIRST_CALL_INVITEE;
				$paramArr["1stLegNumber"] = $invitee["phoneNum"];
				$paramArr["2ndLegNumber"] = $inviter["phoneNum"];
			}
			$call = $this->callManager->insert($call);
			$paramArr["callInx"] = $call["inx"];
			
			// Init a Tropo call
			$this->tropoService->initCall($paramArr);
			
			$result = array (
				"redirect" => true,
				"url" => APP_CTX . "/widget/following?callInx=" . $call["inx"] 
			);
		} else {
			$result = array (
				"redirect" => false,
				"validFields" => $validFields,
				"invalidFields" => $invalidFields 
			);
		}
		
		$this->_helper->json->sendJson($result);
	}

}
