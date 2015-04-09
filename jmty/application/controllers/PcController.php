<?php
require_once 'log/LoggerFactory.php';
require_once 'tropo/tropo.class.php';
require_once 'util/HttpUtil.php';
require_once 'service/TropoService.php';
require_once 'service/EmailService.php';
require_once "service/PaypalService.php";

class PcController extends Zend_Controller_Action {

    private static $__CardType = array(
        "01" => "master",
        "02" => "visa",
        "03" => "AMEX",
    );

	public function init() {
        $this->specialistsetting = Zend_Registry::get ( "SPECIALIST_SETTING" );
	}
	public function steponeAction() {
		//action body
	}
	public function steptwoAction() {
		// action body
        $formValue = array(
            "fname" => $_POST["fname"],
            "lname" => $_POST["lname"],
            "Name" => $_POST["fname"] . " " . $_POST["lname"],
            "CardNo" => $_POST["CardNo"],
            "CardNoMask" => substr_replace($_POST["CardNo"], str_repeat("*", strlen($_POST["CardNo"]) - 4), 4),
            "phone" => $_POST["phone"],
            "email" => $_POST["email"],
            "CardType" => $_POST["CardType"],
            "CardTypeName" => self::$__CardType[$_POST["CardType"]],
            "ExpireMonth" => $_POST["ExpireMonth"],
            "ExpireYear" => $_POST["ExpireYear"],
            "ExpireDate" => $_POST["ExpireMonth"] . "月" . $_POST["ExpireYear"] . "年",
            "cvv" => $_POST["cvv"],
            "cvvmask" => "****"
        );

        $this->view->formValue = (object) $formValue;
	}

	public function stepthreeAction() {
        $formValue = array(
            "fname" => $_POST["fname"],
            "lname" => $_POST["lname"],
            "Name" => $_POST["fname"] . " " . $_POST["lname"],
            "CardNo" => $_POST["CardNo"],
            "CardNoMask" => substr_replace($_POST["CardNo"], str_repeat("*", strlen($_POST["CardNo"]) - 4), 4),
            "phone" => $_POST["phone"],
            "email" => $_POST["email"],
            "CardType" => $_POST["CardType"],
            "CardTypeName" => self::$__CardType[$_POST["CardType"]],
            "ExpireMonth" => $_POST["ExpireMonth"],
            "ExpireYear" => $_POST["ExpireYear"],
            "ExpireDate" => $_POST["ExpireMonth"] . "月" . $_POST["ExpireYear"] . "年",
            "cvv" => $_POST["cvv"],
            "cvvmask" => "****"
        );

        $this->view->formValue = (object) $formValue;
	}

    public function callAction() {
        $formValue = array(
            "fname" => $_POST["fname"],
            "lname" => $_POST["lname"],
            "Name" => $_POST["fname"] . " " . $_POST["lname"],
            "CardNo" => $_POST["CardNo"],
            "CardNoMask" => substr_replace($_POST["CardNo"], str_repeat("*", strlen($_POST["CardNo"]) - 4), 4),
            "phone" => $_POST["phone"],
            "email" => $_POST["email"],
            "CardType" => $_POST["CardType"],
            "CardTypeName" => self::$__CardType[$_POST["CardType"]],
            "ExpireMonth" => $_POST["ExpireMonth"],
            "ExpireYear" => $_POST["ExpireYear"],
            "ExpireDate" => $_POST["ExpireMonth"] . "月" . $_POST["ExpireYear"] . "年",
            "cvv" => $_POST["cvv"],
            "cvvmask" => "****"
        );

        $call = new Application_Model_Call ();

        $params = array ();
        $params ["patientName"] = $formValue["Name"];
        $params ["lastName"] = $formValue["lname"];
        $params ["firstName"] = $formValue["fname"];
        $params ["patientNumber"] = $formValue["phone"];
        $params ["patientCreditNumber"] = $formValue["CardNo"];
        $params ["patientEmail"] = $formValue["email"];
        $params ["cardType"] = $formValue["CardTypeName"];
        $params ["expMonth"] = $formValue["ExpireMonth"];
        $params ["expYear"] = $formValue["ExpireYear"];
        $params ["cvv"] = $formValue["cvv"];
        $params ["trytimes"] = "1";

        $params ["inx"] = $call->createCall ( $params );

        $arr = array();
        $arr["inx"] = $params ["inx"];
        $arr["patientNumber"] = $params ["patientNumber"];
        $troposervice = new TropoService ();
        $troposervice->callpatient ( $arr );
    }

    /*
    public function payAction() {
        $paypalService = new PaypalService();
        $creditCard = array (
            "firstName" => "xu",
            "lastName" => "weiming",
            "cardType" => "visa",
            "cardNumber" => "4417119669820331",
            "cvv" => "111",
            "expMonth" => "12",
            "expYear" => "2015"
        );
        $paypalToken = $paypalService->regist($creditCard);
        $paypalService->charge($paypalToken, "1.00", "USD");

        echo "test payment";
    }
    */

}

