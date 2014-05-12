<?php
require_once 'service/PaypalService.php';
require_once 'service/TropoService.php';
require_once 'util/Validator.php';
require_once 'util/EmailSender.php';
require_once 'util/MultiLang.php';
require_once 'models/PartnerManager.php';
require_once 'models/CallManager.php';
require_once 'models/InviteManager.php';
require_once 'models/UserManager.php';
require_once 'models/EmailManager.php';
require_once 'BaseController.php';

class Widget_FollowingController extends BaseController {
	private $partnerManager;
	private $callManager;
	private $inviteManager;
	private $userManager;
	private $emailManager;

	public function init() {
		parent::init();
		$this->partnerManager = new PartnerManager();
		$this->callManager = new CallManager();
		$this->inviteManager = new InviteManager();
		$this->userManager = new UserManager();
		$this->emailManager = new EmailManager();
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
			return $this->renderScript("/notification/wrong.phtml");
		} else if (Validator::isExpired($partner["inviteExpireDur"], $invite["inviteTime"]) || Validator::isCompleted($calls)) {
			// The URL is expired or the call is already completed. It can NOT be inited again
			return $this->renderScript("/notification/expired.phtml");
		}
		
		$_SESSION["inviteInx"] = $invite["inx"];
		$_SESSION["inviteType"] = $invite["inviteType"];
		$_SESSION["partnerInx"] = $invite["partnerInx"];
		$_SESSION["inviterInx"] = $invite["inviterInx"];
		$_SESSION["inviteeInx"] = $invite["inviteeInx"];
		$_SESSION["country"] = $partner["country"];
		$_SESSION["retry"] = 0;
		
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		$this->view->assign("name", $invitee["name"]);
		$this->view->assign("freeCallDur", round($partner["freeCallDur"] / 60));
		$this->view->assign("chargeAmount", $partner["chargeAmount"]);
		$this->view->assign("minCallBlkDur", round($partner["minCallBlkDur"] / 60));
		
		$this->renderScript("/following/notification.phtml");
	}

	public function paypalAction() {
		if (!$this->isSessionValid()) {
			return;
		}
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
		if ($creditCard["cardType"] != null) {
			array_push($validFields, "cardTypeInvalid");
		} else {
			array_push($invalidFields, "cardTypeInvalid");
		}
		if (Validator::isValidCardNumber($creditCard["cardNumber"])) {
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
		
		// Dispatch
		if (count($invalidFields) == 0) {
			$paypalService = new PaypalService();
			$paypalToken = $paypalService->regist($creditCard);
			if ($paypalToken != null) {
				$toInviter = false;
				if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
					$user = array (
						"inx" => $_SESSION["inviterInx"] 
					);
				} else {
					$toInviter = true;
					$user = array (
						"inx" => $_SESSION["inviteeInx"] 
					);
				}
				
				$user["paypalToken"] = $paypalToken;
				$this->userManager->update($user, $toInviter);
				
				$email = $this->emailManager->findThanksEmail($_SESSION["inviteInx"]);
				EmailSender::sendReadyEmail($email, $_SESSION["inviteType"] == INVITE_TYPE_INVITEE_PAY);
				
				$result = array (
					"redirect" => true,
					"url" => APP_CTX . "/widget/following/thankyou" 
				);
			} else {
				$result = array (
					"redirect" => true,
					"url" => APP_CTX . "/widget/following/retry" 
				);
			}
		} else {
			$result = array (
				"redirect" => false,
				"validFields" => $validFields,
				"invalidFields" => $invalidFields 
			);
		}
		
		$this->_helper->json->sendJson($result);
	}

	public function thankyouAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			$user = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		} else {
			$user = $this->userManager->findInviterByInviteInx($_SESSION["inviteInx"]);
		}
		$this->view->assign("name", $user["name"]);
	}

	public function retryAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		$_SESSION["retry"] += 1;
		if ($_SESSION["retry"] > 3) {
			// Ask payer to retry
			$email = $this->emailManager->findSorryEmail($_SESSION["inviteInx"]);
			EmailSender::sendSorryEmail($email, $_SESSION["inviteType"] == INVITE_TYPE_INVITEE_PAY);
			$this->view->assign("buttonType", "hidden");
		} else {
			// Inform the other guy of sorry
			$email = $this->emailManager->findRetryEmail($_SESSION["inviteInx"]);
			EmailSender::sendRetryEmail($email, $_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY);
			$this->view->assign("buttonType", "submit");
		}
	}

	public function connectingAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		$partner = $this->partnerManager->findPartnerByInx($_SESSION["partnerInx"]);
		$inviter = $this->userManager->findInviterByInviteInx($_SESSION["inviteInx"]);
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		
		$call = array (
			"inviteInx" => $_SESSION["inviteInx"] 
		);
		$paramArr = array (
			"partnerInx" => $partner["inx"],
			"maxRingDur" => $partner["maxRingDur"],
			"inviteInx" => $_SESSION["inviteInx"],
			"partnerNumber" => $partner["phoneNum"],
			"country" => $partner["country"] 
		);
		
		if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
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
		$tropoService = new TropoService();
		$tropoService->initCall($paramArr);
	}

}
