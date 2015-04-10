<?php
require_once 'log/LoggerFactory.php';
//use PayPal\Api\CreditCard;
//use PayPal\Api\CreditCardToken;
//use PayPal\Api\FundingInstrument;
//use PayPal\Api\Amount;
//use PayPal\Api\Payer;
//use PayPal\Api\Payment;
//use PayPal\Api\Transaction;
//use PayPal\Rest\ApiContext;
//use PayPal\Auth\OAuthTokenCredential;

use PayPal\Api\Amount;
//use PayPal\Api\Details;
//use PayPal\Api\Item;
//use PayPal\Api\ItemList;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Transaction;
use PayPal\Exception\PPConnectionException;

class PaypalService {
	private static $CURRENCY_USD = "USD";
	private static $CURRENCY_JPY = "JPY";
	private $logger;
	private $partnerInx;
	private $inviteInx;

	public function __construct($partnerInx = null, $inviteInx = null) {
		$this->logger = LoggerFactory::getSysLogger();
		$this->partnerInx = $partnerInx;
		$this->inviteInx = $inviteInx;
	}

	public function regist($param) {
		$paypalApiCtx = Zend_Registry::get("PAYPAL_API_CTX");
		
		$card = new CreditCard();
		//$card->setFirst_name($param["firstName"]);
		//$card->setLast_name($param["lastName"]);
        $card->setFirstName($param["firstName"]);
        $card->setLastName($param["lastName"]);
		$card->setType($param["cardType"]);
		$card->setNumber($param["cardNumber"]);
		$card->setCvv2($param["cvv"]);
		//$card->setExpire_month($param["expMonth"]);
		//$card->setExpire_year($param["expYear"]);
        $card->setExpireMonth($param["expMonth"]);
        $card->setExpireYear($param["expYear"]);
		
		try {
			$card->create($paypalApiCtx);
		} catch (PPConnectionException $ex) {
			$this->logger->logError($this->partnerInx, $this->inviteInx, "Failed to regist Paypal token: " . $ex->getData());
			return null;
		}
		
		$paypalToken = $card->getId();
		$this->logger->logInfo($this->partnerInx, $this->inviteInx, "Regist Paypal token: $paypalToken");
		return $paypalToken;
	}

	public function charge($paypalToken, $chargeAmount, $chargeCurrency) {
		$paypalApiCtx = Zend_Registry::get("PAYPAL_API_CTX");
		
		$creditCardToken = new CreditCardToken();
		//$creditCardToken->setCredit_card_id($paypalToken);
        $creditCardToken->setCreditCardId($paypalToken);
		
		// create function instrument
		$fi = new FundingInstrument();
		//$fi->setCredit_card_token($creditCardToken);
        $fi->setCreditCardToken($creditCardToken);
		
		$payer = new Payer();
		//$payer->setPayment_method("credit_card");
        $payer->setPaymentMethod("credit_card");
		//$payer->setFunding_instruments(array (
        $payer->setFundingInstruments(array($fi));
		
		$amount = new Amount();
		$amount->setCurrency($chargeCurrency);
		$amount->setTotal($chargeAmount);
		
		$transaction = new Transaction();
		$transaction->setAmount($amount);
		$transaction->setDescription("Paypal transaction");
		
		$payment = new Payment();
		$payment->setIntent("sale");
		$payment->setPayer($payer);
		$payment->setTransactions(array ($transaction));
		
		try {
			$payment->create($paypalApiCtx);
		} catch (PPConnectionException $ex) {
			$this->logger->logError($this->partnerInx, $this->inviteInx, "Failed to charge Paypal with token: $paypalToken Message: " . $ex->getData());
			return false;
		}
		
		if ($payment->getState() == "approved" || $payment->getState() == "pending") {
			$this->logger->logInfo($this->partnerInx, $this->inviteInx, "Charged $chargeAmount $chargeCurrency with token: $paypalToken");
			return true;
		} else {
			$this->logger->logWarn($this->partnerInx, $this->inviteInx, "Probably failed to charge Paypal with token: $paypalToken State: " . $payment->getState());
			return false;
		}
	}

	public function adjustAmount($chargeAmount, $chargeCurrency) {
		// Check all the possible of currency in partners table.
		switch ($chargeCurrency) {
			case self::$CURRENCY_JPY :
				return round($chargeAmount);
			default :
				return round($chargeAmount, 2);
		}
	}

}