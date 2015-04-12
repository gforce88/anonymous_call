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
        $this->app = Zend_Registry::get ( "APP_SETTING" );
	}
	public function steponeAction() {
        $param = array(
            "fname" => $this->_getParam("fname", ""),
            "lname" => $this->_getParam("lname", ""),
            "CardNo" => $this->_getParam("CardNo", ""),
            "phone" => $this->_getParam("phone", ""),
            "email" => $this->_getParam("email", ""),
            "CardType" => $this->_getParam("CardType", ""),
            "ExpireMonth" => $this->_getParam("ExpireMonth", ""),
            "ExpireYear" => $this->_getParam("ExpireYear", ""),
            "cvv" => $this->_getParam("cvv", ""),
            "validate" => $this->getParam("validate", true)
        );

        $this->view->formValue = (object) $param;
	}
	public function steptwoAction() {
		// action body
        $formValue = array(
            "fname" => $this->_getParam("fname"),
            "lname" => $this->_getParam("lname"),
            "Name" => $this->_getParam("fname") . " " . $this->_getParam("lname"),
            "CardNo" => $this->_getParam("CardNo"),
            "CardNoMask" => substr_replace($this->_getParam("CardNo"), str_repeat("*", strlen($this->_getParam("CardNo")) - 4), 4),
            "phone" => $this->_getParam("phone"),
            "email" => $this->_getParam("email"),
            "CardType" => $this->_getParam("CardType"),
            "CardTypeName" => self::$__CardType[$this->_getParam("CardType")],
            "ExpireMonth" => $this->_getParam("ExpireMonth"),
            "ExpireYear" => $this->_getParam("ExpireYear"),
            "ExpireDate" => $this->_getParam("ExpireMonth") . "月" . $this->_getParam("ExpireYear") . "年",
            "cvv" => $this->_getParam("cvv"),
            "cvvmask" => str_repeat("*", strlen($this->_getParam("cvv")))
        );

        $this->view->formValue = (object) $formValue;
	}

	public function stepthreeAction() {
        $formValue = array(
            "fname" => $this->_getParam("fname"),
            "lname" => $this->_getParam("lname"),
            "Name" => $this->_getParam("fname") . " " . $this->_getParam("lname"),
            "CardNo" => $this->_getParam("CardNo"),
            "CardNoMask" => substr_replace($this->_getParam("CardNo"), str_repeat("*", strlen($this->_getParam("CardNo")) - 4), 4),
            "phone" => $this->_getParam("phone"),
            "email" => $this->_getParam("email"),
            "CardType" => $this->_getParam("CardType"),
            "CardTypeName" => self::$__CardType[$this->_getParam("CardType")],
            "ExpireMonth" => $this->_getParam("ExpireMonth"),
            "ExpireYear" => $this->_getParam("ExpireYear"),
            "ExpireDate" => $this->_getParam("ExpireMonth") . "月" . $this->_getParam("ExpireYear") . "年",
            "cvv" => $this->_getParam("cvv"),
            "cvvmask" => str_repeat("*", strlen($this->_getParam("cvv")))
        );

        $this->view->formValue = (object) $formValue;
	}

    public function callAction() {
    	$this->_helper->viewRenderer->setNeverRender ();
        $formValue = array(
            "fname" => $this->_getParam("fname"),
            "lname" => $this->_getParam("lname"),
            "Name" => $this->_getParam("fname") . " " . $this->_getParam("lname"),
            "CardNo" => $this->_getParam("CardNo"),
            "CardNoMask" => substr_replace($this->_getParam("CardNo"), str_repeat("*", strlen($this->_getParam("CardNo")) - 4), 4),
            "phone" => $this->_getParam("phone"),
            "email" => $this->_getParam("email"),
            "CardType" => $this->_getParam("CardType"),
            "CardTypeName" => self::$__CardType[$this->_getParam("CardType")],
            "ExpireMonth" => $this->_getParam("ExpireMonth"),
            "ExpireYear" => $this->_getParam("ExpireYear"),
            "ExpireDate" => $this->_getParam("ExpireMonth") . "月" . $this->_getParam("ExpireYear") . "年",
            "cvv" => $this->_getParam("cvv"),
            "cvvmask" => str_repeat("*", strlen($this->_getParam("cvv")))
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

        $params = $call->createCall ( $params );

        $arr = array();
        $arr["inx"] = $params ["inx"];
        $arr["patientNumber"] = $params ["patientNumber"];
        $troposervice = new TropoService ();
        $troposervice->callpatient ( $arr );
        echo "starting call...";
    }

    public function validatecreditcardAction() {

        $creditCard = array (
            "firstName" => $this->_getParam("fname"),
            "lastName" => $this->_getParam("lname"),
            "cardType" => self::$__CardType[$this->_getParam("CardType")],
            "cardNumber" => $this->_getParam("CardNo"),
            "cvv" => $this->_getParam("cvv"),
            "expMonth" => $this->_getParam("ExpireMonth"),
            "expYear" => $this->_getParam("ExpireYear")
        );

        /*
        $creditCard = array (
            "firstName" => "xu",
            "lastName" => "weiming",
            "cardType" => "visa",
            "cardNumber" => "4417119669820331",
            "cvv" => "111",
            "expMonth" => "12",
            "expYear" => "2015"
        );
        */

        $paypalService = new PaypalService();
        $paypalToken = $paypalService->regist($creditCard);

        $param = array(
            "fname" => $this->_getParam("fname"),
            "lname" => $this->_getParam("lname"),
            "CardNo" => $this->_getParam("CardNo"),
            "phone" => $this->_getParam("phone"),
            "email" => $this->_getParam("email"),
            "CardType" => $this->_getParam("CardType"),
            "ExpireMonth" => $this->_getParam("ExpireMonth"),
            "ExpireYear" => $this->_getParam("ExpireYear"),
            "cvv" => $this->_getParam("cvv"),
        );

        if (is_null($paypalToken)) {
            $param["validate"] = false;
            $this->forward("stepone", "pc", null, $param);
            //$this->redirect($this->redirect("/pc/stepone", $param));
        } else {
            //$param["validate"] = true;
            $this->forward("steptwo", "pc", null, $param);
            //$this->redirect($this->redirect("/pc/steptwo", $param));
        }

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

