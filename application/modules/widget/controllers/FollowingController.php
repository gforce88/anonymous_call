<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'service/TropoService.php';
require_once 'util/Validator.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/CallManager.php';
require_once 'models/InviteManager.php';
require_once 'models/UserManager.php';
require_once 'models/EmailManager.php';

class Widget_FollowingController extends Zend_Controller_Action {
	private $logger;
	private $paypalService;
	private $tropoService;
	private $partnerManager;
	private $callManager;
	private $inviteManager;
	private $userManager;
	private $emailManager;

	public function init() {
		$this->logger = LoggerFactory::getSysLogger();
		$this->paypalService = new PaypalService();
		$this->tropoService = new TropoService();
		$this->partnerManager = new PartnerManager();
		$this->callManager = new CallManager();
		$this->inviteManager = new InviteManager();
		$this->userManager = new UserManager();
		$this->emailManager = new EmailManager();
		session_start();
	}

	public function indexAction() {
		$this->notificationAction();
	}

	public function notificationAction() {
		$invite = $this->inviteManager->findInviteByInxToken($_REQUEST["inx"], $_REQUEST["token"]);
		$partner = $this->partnerManager->findPartnerByInx($invite["partnerInx"]);
		$calls = $this->callManager->findAllCallsByInvite($_SESSION["inviteInx"]);
		if ($invite == null || $partner == null) {
			// The URL is invalid
			$this->view->assign("country", $_REQUEST["country"]);
			return $this->renderScript("/notification/wrong.phtml");
		} else if (Validator::isExpired($partner["inviteExpireDur"], $invite["inviteTime"]) || Validator::isCompleted($calls)) {
			// The URL is expired or the call is already completed. It can NOT be inited again
			$this->view->assign("country", $_REQUEST["country"]);
			return $this->renderScript("/notification/expired.phtml");
		}
		
		$_SESSION["inviteInx"] = $invite["inx"];
		$_SESSION["inviteType"] = $invite["inviteType"];
		$_SESSION["partnerInx"] = $invite["partnerInx"];
		$_SESSION["inviterInx"] = $invite["inviterInx"];
		$_SESSION["inviteeInx"] = $invite["inviteeInx"];
		$_SESSION["country"] = $partner["country"];
		
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		$this->view->assign("name", $invitee["name"]);
		$this->view->assign("country", $_SESSION["country"]);
		$this->view->assign("freeCallDur", round($partner["freeCallDur"] / 60));
		$this->view->assign("chargeAmount", $partner["chargeAmount"]);
		$this->view->assign("minCallBlkDur", round($partner["minCallBlkDur"] / 60));
		
		$this->renderScript("/following/notification.phtml");
	}

	public function paypalAction() {
		$this->view->assign("country", $_SESSION["country"]);
	}

	public function validateAction() {
		// Disable layout for return json
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
		
		$creditCard = array (
			"firstName" => $_POST["firstName"],
			"lastName" => $_POST["firstName"],
			"cardType" => $_POST["cardType"],
			"cardNumber" => $_POST["cardNumber"],
			"cvv" => $_POST["cvv"],
			"expMonth" => $_POST["expMonth"],
			"expYear" => $_POST["expYear"] 
		);
		
		// Validation
		$validFields = array ();
		$invalidFields = array ();
		if (Validator::isValidCreditCard($creditCard["cardNumber"])) {
			array_push($validFields, "cardNumberInvalid");
		} else {
			array_push($invalidFields, "cardNumberInvalid");
		}
		if (Validator::isValidCvv($creditCard["cvv"])) {
			array_push($validFields, "cvvInvalid");
		} else {
			array_push($invalidFields, "cvvInvalid");
		}
		if (Validator::isValidMonth($creditCard["expMonth"])) {
			array_push($validFields, "expDateInvalid");
		} else {
			array_push($invalidFields, "expDateInvalid");
		}
		if (Validator::isValidYear($creditCard["expYear"])) {
			array_push($validFields, "expDateInvalid");
		} else {
			array_push($invalidFields, "expDateInvalid");
		}
		
		$paypalToken = PaypalService::regist($_POST["creditCardNumber"], $_POST["creditCardExp"], $_POST["creditCardCvc"]);
		if ($paypalToken != null) {
			array_push($validFields, "creditCardInvalid");
		} else {
			array_push($invalidFields, "creditCardInvalid");
		}
		
		// Dispatch
		if (count($invalidFields) == 0) {
			if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
				$user = array (
					"inx" => $_SESSION["inviterInx"]
				);
			} else {
				$user = array (
						"inx" => $_SESSION["inviteeInx"]
				);
			}
			$user["paypalTomek"] = $paypalToken;
			$this->userManager->update($user);
			
			$email = $this->emailManager->findThanksEmail($_SESSION["inviteInx"]);
			$this->sendThanksEmail($email);
						
			$result = array (
				"redirect" => true,
				"url" => APP_CTX . "/widget/following/thanks" 
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

	public function thanksAction() {
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

	private function sendThanksEmail($email) {
		$subjectParam = array (
				$email["fromEmail"]
		);
		$contentParam = array (
				$email["fromEmail"]
		);
	
		$subject = MultiLang::replaceParams($email["thanksEmailSubject"], $subjectParam);
		$content = MultiLang::replaceParams($email["thanksEmailBody"], $contentParam);
	
		$this->logger->logInfo($email["partnerInx"], $email["inx"], "Sending thanks email to: [" . $email["toEmail"] . "] with URL: [$contentParam[1]]");
		$sendResult = EmailSender::sendHtmlEmail($email["partnerName"], $email["emailAddr"], "", $email["toEmail"], $subject, $content);
		$this->logger->logInfo($email["partnerInx"], $email["inx"], "Email sent result: [$sendResult]");
	
		return $sendResult;
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
