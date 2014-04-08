<?php
require_once 'log/LoggerFactory.php';
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class PaypalService {
	private $logger;

	public function __construct() {
		$this->logger = LoggerFactory::getSysLogger();
	}

	public function regist($ccNumber, $ccType, $ccExp, $ccCvc, $firstName, $lastName) {
		return "test_payapl_token"; // For testing only
		$paypalApiCtx = Zend_Registry::get("PAYPAL_API_CTX");
		
		$ccExpArr = explode("/", $ccExp);
		
		$card = new CreditCard();
		$card->setNumber($ccNumber);
		$card->setType($ccType);
		$card->setExpire_month($ccExpArr[0]);
		$card->setExpire_year($ccExpArr[1]);
		$card->setCvv2($ccCvc);
		$card->setFirst_name($firstName);
		$card->setLast_name($lastName);
		
		try {
			$card->create($paypalApiCtx);
		} catch (\PPConnectionException $ex) {
			$this->logger->logError("PaypalService", "regist", $ex->getMessage());
			return false;
		}
		
		return $card->getId();
	}

	public function charge($paypalToken, $chargeAmount) {
		$paypalApiCtx = Zend_Registry::get("PAYPAL_API_CTX");
		
		$creditCardToken = new CreditCardToken();
		$creditCardToken->setCredit_card_id($paypalToken);
		
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
		$amount->setTotal($chargeAmount);
		
		$transaction = new Transaction();
		$transaction->setAmount($amount);
		$transaction->setDescription("Paypal transaction");
		
		$payment = new Payment();
		$payment->setIntent("sale");
		$payment->setPayer($payer);
		$payment->setTransactions(array (
			$transaction 
		));
		
		try {
			$payment->create($paypalApiCtx);
		} catch (\PPConnectionException $ex) {
			$this->logger->logError("PaypalService", "regist", $ex->getMessage());
			return false;
		}
		
		if ($payment->getState() == "approved") {
			return true;
		} else {
			return false;
		}
	}

}