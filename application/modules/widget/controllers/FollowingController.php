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
require_once 'BaseController.php';

class Widget_FollowingController extends BaseController {
	private $partnerManager;
	private $callManager;
	private $inviteManager;
	private $userManager;

	public function init() {
		parent::init();
		$this->partnerManager = new PartnerManager();
		$this->callManager = new CallManager();
		$this->inviteManager = new InviteManager();
		$this->userManager = new UserManager();
	}

	public function indexAction() {
		$invite = $this->inviteManager->findInviteByInxToken($_REQUEST["inx"], $_REQUEST["token"]);
		$partner = $this->partnerManager->findPartnerByInx($invite["partnerInx"]);
		$calls = $this->callManager->findAllCallsByInvite($_SESSION["inviteInx"]);
		if ($invite == null || $partner == null) {
			// The URL is invalid
			return $this->renderScript("/notification/wrong.phtml");
		} else if (Validator::isExpired($partner["inviteExpireDur"], $invite["inviteTime"]) || Validator::isCompleted($calls)) {
			// The URL is expired or the call is already completed. It can NOT be inited again
			return $this->renderScript("/notification/invalid.phtml");
		}
		
		$_SESSION["inviteInx"] = $invite["inx"];
		$_SESSION["inviteType"] = $invite["inviteType"];
		$_SESSION["partnerInx"] = $invite["partnerInx"];
		$_SESSION["inviterInx"] = $invite["inviterInx"];
		$_SESSION["inviteeInx"] = $invite["inviteeInx"];
		$_SESSION["currentUserSex"] = MAN;
		$_SESSION["country"] = $partner["country"];
		
		if ($_REQUEST["retry"] == null) {
			$_SESSION["retry"] = 0;
		} else {
			$_SESSION["retry"] = Protection::decrypt(urldecode($_REQUEST["retry"]), "retry");
			if ($_SESSION["retry"] >= 3) {
				$_SESSION["retry"] = -1;
				$this->retryAction();
			}
		}
		
		$this->notificationAction();
	}

	public function notificationAction() {
		$partner = $this->partnerManager->findPartnerByInx($_SESSION["partnerInx"]);
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		$this->view->assign("name", $invitee["name"]);
		$this->view->assign("freeCallDur", $this->sec2min($partner["freeCallDur"]));
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
		if ($creditCard["firstName"] != null) {
			array_push($validFields, "firstNameInvalid");
		} else {
			array_push($invalidFields, "firstNameInvalid");
		}
		if ($creditCard["lastName"] != null) {
			array_push($validFields, "lastNameInvalid");
		} else {
			array_push($invalidFields, "lastNameInvalid");
		}
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
			$paypalService = new PaypalService($_SESSION["partnerInx"], $_SESSION["inviteInx"]);
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
				
				$invite = array (
					"inx" => $_SESSION["inviteInx"],
					"inviteResult" => INVITE_RESULT_PAYED 
				);
				$this->inviteManager->update($invite);
				
				$email = $this->userManager->findEmail($_SESSION["inviteInx"]);
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
		
		$invite = $this->inviteManager->findInviteByInx($_SESSION["inviteInx"]);
		$inviter = $this->userManager->findInviterByInviteInx($_SESSION["inviteInx"]);
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		if ($invite["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			$this->view->assign("man", $inviter["name"]);
			$this->view->assign("woman", $invitee["name"]);
		} else {
			$this->view->assign("woman", $inviter["name"]);
			$this->view->assign("man", $invitee["name"]);
		}
	}

	public function retryAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		if ($_SESSION["retry"] == -1) { // from retry email
			$this->view->assign("buttonType", "hidden");
		} else {
			$_SESSION["retry"] += 1;
			if ($_SESSION["retry"] >= 3) {
				$invite = array (
					"inx" => $_SESSION["inviteInx"],
					"inviteResult" => INVITE_RESULT_NOPAY 
				);
				$this->inviteManager->update($invite);
				
				$this->view->assign("buttonType", "hidden");
			} else {
				// Ask payer to retry
				$email = $this->userManager->findEmail($_SESSION["inviteInx"]);
				EmailSender::sendRetryEmail($email, $_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY);
				
				$this->view->assign("buttonType", "submit");
			}
		}
		
		$this->view->assign("img", APP_CTX . "/images/Phones_M2.png");
	}

	public function connectingAction() {
		if (!$this->isSessionValid()) {
			return;
		}
		
		// If the user click the history back button then click the call now button, the application should not raise a new call
		$calls = $this->callManager->findAllCallsByInvite($_SESSION["inviteInx"]);
		if (Validator::isCompleted($calls)) {
			return $this->renderScript("/notification/invalid.phtml");
		}
		
		$partner = $this->partnerManager->findPartnerByInx($_SESSION["partnerInx"]);
		$inviter = $this->userManager->findInviterByInviteInx($_SESSION["inviteInx"]);
		$invitee = $this->userManager->findInviteeByInviteInx($_SESSION["inviteInx"]);
		
		if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			$this->view->assign("name", $invitee["name"]);
		} else {
			$this->view->assign("name", $inviter["name"]);
		}
		
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
		
		$call["callType"] = $_SESSION["inviteType"];
		if ($_SESSION["inviteType"] == INVITE_TYPE_INVITER_PAY) {
			// Pay by Inviter, first call inviter
			$paramArr["1stLegNumber"] = $inviter["phoneNum"];
			$paramArr["2ndLegNumber"] = $invitee["phoneNum"];
		} else {
			// Pay by Invitee, first call invitee
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
