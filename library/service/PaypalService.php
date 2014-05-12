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
use PayPal\Exception\PPConnectionException;

class PaypalService {
	private $logger;

	public function __construct() {
		$this->logger = LoggerFactory::getSysLogger();
	}

	public function regist($param) {
		$paypalApiCtx = Zend_Registry::get("PAYPAL_API_CTX");
		
		$card = new CreditCard();
		$card->setFirst_name($param["firstName"]);
		$card->setLast_name($param["lastName"]);
		$card->setType($param["cardType"]);
		$card->setNumber($param["cardNumber"]);
		$card->setCvv2($param["cvv"]);
		$card->setExpire_month($param["expMonth"]);
		$card->setExpire_year($param["expYear"]);
		
		try {
			$card->create($paypalApiCtx);
		} catch (PPConnectionException $ex) {
			$this->logger->logInfo("PaypalService", "regist", $ex->getMessage());
			return null;
		}
		
		$paypalToken = $card->getId();
		$this->logger->logInfo("PaypalService", "regist", $paypalToken);
		return $paypalToken;
	}

	public function charge($paypalToken, $chargeAmount, $chargeCurrency) {
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
		$amount->setCurrency($chargeCurrency);
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