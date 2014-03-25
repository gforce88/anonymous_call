<?php
require_once 'util/MultiLang.php';
require_once 'service/TropoService.php';
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
		$paramarray = array (
			"test" => "TEST" 
		);
		$_GET = array_merge($_GET, $paramarray);
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
    	
    	$appCtx = Zend_Registry::get("PAYPAL_APP_CTX");
		
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
        $card->create($appCtx);
        
        var_dump($appCtx);
        var_dump($card);
        
        $this->renderScript("/empty.phtml", $card);
	}
    
	public function chgAction(){
		
		$appCtx = Zend_Registry::get("PAYPAL_APP_CTX");
		
		// Saved credit card id from a previous call to
		// CreateCreditCard.php
		$creditCardId = 'CARD-5BT058015C739554AKE2GCEI';
		$creditCardToken = new CreditCardToken();
		$creditCardToken->setCredit_card_id($creditCardId);
		
		// ### FundingInstrument
		// A resource representing a Payer's funding instrument.
		// Use a Payer ID (A unique identifier of the payer generated
		// and provided by the facilitator. This is required when
		// creating or using a tokenized funding instrument)
		// and the `CreditCardDetails`
		$fi = new FundingInstrument();
		$fi->setCredit_card_token($creditCardToken);
		
		// ### Payer
		// A resource representing a Payer that funds a payment
		// Use the List of `FundingInstrument` and the Payment Method
		// as 'credit_card'
		$payer = new Payer();
		$payer->setPayment_method("credit_card");
		$payer->setFunding_instruments(array($fi));
		
		// ### Amount
		// Let's you specify a payment amount.
		$amount = new Amount();
		$amount->setCurrency("USD");
		$amount->setTotal("1.00");
		
		// ### Transaction
		// A transaction defines the contract of a
		// payment - what is the payment for and who
		// is fulfilling it. Transaction is created with
		// a `Payee` and `Amount` types
		$transaction = new Transaction();
		$transaction->setAmount($amount);
		$transaction->setDescription("This is the payment description.");
		
		// ### Payment
		// A Payment Resource; create one using
		// the above types and intent as 'sale'
		$payment = new Payment();
		$payment->setIntent("sale");
		$payment->setPayer($payer);
		$payment->setTransactions(array($transaction));
		
		// ###Create Payment
		// Create a payment by posting to the APIService
		// (See bootstrap.php for more on `ApiContext`)
		// The return object contains the status;
		try {
			$payment->create($appCtx);
		} catch (\PPConnectionException $ex) {
			echo "Exception: " . $ex->getMessage() . PHP_EOL;
		}
	}
	
    public function chargeAction() {
    	
    	$appCtx = Zend_Registry::get("PAYPAL_APP_CTX");
    	
    	$creditCardId = $_GET["card_id"];
    	$creditCardToken = new CreditCardToken();
    	$creditCardToken->setCredit_card_id($creditCardId);
    	
    	// create function instrument
    	$fi = new FundingInstrument();
    	$fi->setCredit_card_token($creditCardToken);
    	
    	$payer = new Payer();
    	$payer->setPayment_method("credit_card");
    	$payer->setFunding_instruments(array($fi));
    	
    	$amount = new Amount();
    	$amount->setCurrency("USD");
    	$amount->setTotal("1.00");
    	
    	$transaction = new Transaction();
    	$transaction->setAmount($amount);
    	$transaction->setDescription("Paypal test transaction.");
    	
    	$payment = new Payment();
    	$payment->setIntent("sale");
    	$payment->setPayer($payer);
    	$payment->setTransactions(array($transaction));
    	
    	try {
    		// $payment->create($appCtx);
    	} catch (\PPConnectionException $ex) {
    		echo "Exception: " . $ex->getMessage() . PHP_EOL;
    	}
    	
		$this->renderScript("/empty.phtml");
	}

}

