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

	public function regist($ccNumber, $ccExp, $ccCvc, $firstName, $lastName) {
		return "test_payapl_token"; // For testing only
		$paypalApiCtx = Zend_Registry::get("PAYPAL_API_CTX");
		
		$ccExpArr = explode("/", $ccExp);
		
		$ccNum1 = substr($ccNumber, 0, 1);
		$ccNum2 = substr($ccNumber, 0, 2);
		$ccNum4 = substr($ccNumber, 0, 4);
		
		if ($ccNum4 == "6011" || $ccNum2 == "65") {
			$ccType = "discover";
		} else if ($ccNum2 == "51" || $ccNum2 == "52" || $ccNum2 == "53" || $ccNum2 == "54" || $ccNum2 == "55") {
			$ccType = "mastercard";
		} else if ($ccNum2 == "34" || $ccNum2 == "37") {
			$ccType = "amex";
		} else if ($ccNum1 == "4") {
			$ccType = "visa";
		} else {
			return false;
		}
		
		$card = new CreditCard();
		$card->setType($ccType);
		$card->setNumber($ccNumber);
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

	public function charge($paypalToken, $amount) {
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
		$amount->setTotal($amount);
		
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