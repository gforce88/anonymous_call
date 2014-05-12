<?php
require_once 'log/LoggerFactory.php';
require_once 'service/PaypalService.php';
require_once 'util/MultiLang.php';
require_once 'service/IvrService.php';
require_once 'service/TropoService.php';
require_once 'models/userManager.php';
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class TestController extends Zend_Controller_Action {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		$paypalService = new PaypalService($_GET["partnerInx"], $_GET["inviteInx"]);
		$paypalToken = $paypalService->charge("CARD-05N57635TV839990SKNYNG6I", 10.12, "JPY");
		
		phpinfo();
		$this->renderScript("/empty.phtml");
	}

	public function inviteAction() {
		$partner = array ();
		$partner["inx"] = "1001";
		$invite = array ();
		$invite["numberToDial"] = "15167346602";
		$invite["callerId"] = "1020304050'";
		$invite["inx"] = "9001";
		$invite["partner"] = $partner;
		
		$tropoService = new TropoService();
		$tropoService->initCall($invite);
		
		$this->renderScript("/empty.phtml");
	}

	public function createcreditcardAction() {
		$paypalApiCtx = Zend_Registry::get("PAYPAL_API_CTX");
		
		$ccnumber = $_GET["cc_number"];
		$cctype = $_GET["cc_type"];
		$ccexpirem = $_GET["cc_expire_month"];
		$ccexpirey = $_GET["cc_expire_year"];
		$cvv = $_GET["cc_cvv"];
		$fname = $_GET["first_name"];
		$lname = $_GET["last_name"];
		
		$card = new CreditCard();
		$card->setType($cctype);
		$card->setNumber($ccnumber);
		$card->setExpire_month($ccexpirem);
		$card->setExpire_year($ccexpirey);
		$card->setCvv2($cvv);
		$card->setFirst_name($fname);
		$card->setLast_name($lname);
		$card->create($paypalApiCtx);
		
		var_dump($paypalApiCtx);
		echo "<br><br>";
		var_dump($card);
		
		$this->renderScript("/empty.phtml", $card);
	}

	public function chargeAction() {
		$paypalApiCtx = Zend_Registry::get("PAYPAL_API_CTX");
		
		$creditCardId = $_GET["card_id"];
		$creditCardToken = new CreditCardToken();
		$creditCardToken->setCredit_card_id($creditCardId);
		
		// create function instrument
		$fi = new FundingInstrument();
		$fi->setCredit_card_token($creditCardToken);
		
		$payer = new Payer();
		$payer->setPayment_method("credit_card");
		$payer->setFunding_instruments(array (
			$fi 
		));
		
		$amount = new Amount();
		$amount->setCurrency("USD");
		$amount->setTotal("1.00");
		
		$transaction = new Transaction();
		$transaction->setAmount($amount);
		$transaction->setDescription("Paypal test transaction.");
		
		$payment = new Payment();
		$payment->setIntent("sale");
		$payment->setPayer($payer);
		$payment->setTransactions(array (
			$transaction 
		));
		
		try {
			$payment->create($paypalApiCtx);
		} catch (\PPConnectionException $ex) {
			echo "Exception: " . $ex->getMessage() . PHP_EOL;
		}
		
		var_dump($payment);
		$this->renderScript("/empty.phtml");
	}

}

