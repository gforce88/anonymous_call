<?php
require_once 'log/LoggerFactory.php';
require_once 'tropo/tropo.class.php';
require_once 'util/HttpUtil.php';
require_once 'service/TropoService.php';
require_once 'service/EmailService.php';
require_once "service/PaypalService.php";

class SpController extends Zend_Controller_Action {

    private static $__CardType = array(
        "01" => "master",
        "02" => "visa",
        "03" => "AMEX",
    );

    private static $__PayCardType = array(
        "01" => "mastercard",
        "02" => "visa",
        "03" => "amex",
    );

	public function init() {
        $this->specialistsetting = Zend_Registry::get ( "SPECIALIST_SETTING" );
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
        if ($this->getRequest()->isPost()) {
            $formValue = array(
                "fname" => $this->_getParam("fname"),
                "lname" => $this->_getParam("lname"),
                "Name" => $this->_getParam("fname") . " " . $this->_getParam("lname"),
                "CardNo" => $this->_getParam("card_number"),
                "CardNoMask" => substr_replace($this->_getParam("card_number"), str_repeat("*", strlen($_POST["card_number"]) - 4), 0, strlen($_POST["card_number"]) - 4),
                "phone" => $this->_getParam("phone"),
                "email" => $this->_getParam("email"),
                "CardType" => $this->_getParam("card_type"),
                "CardTypeName" => self::$__CardType[$this->_getParam("card_type")],
                "ExpireMonth" => $this->_getParam("ExpireMonth"),
                "ExpireYear" => $this->_getParam("ExpireYear"),
                "ExpireDate" => $this->_getParam("ExpireMonth") . "月" . $this->_getParam("ExpireYear") . "年",
                "cvv" => $this->_getParam("card_cvv"),
                "cvvmask" => str_repeat("*", strlen($this->_getParam("card_cvv"))),
                "token" => $this->_getParam("token")
            );

            $this->view->formValue = (object)$formValue;
        } else {
            $this->redirect("/sp/stepone");
        }
    }

    public function stepthreeAction() {
        if ($this->getRequest()->isPost()) {
            $formValue = array(
                "phone" => $this->_getParam("phone"),
                "token" => $this->_getParam("token")
            );

            $this->view->formValue = (object)$formValue;
        } else {
            $this->redirect("/sp/stepone");
        }
    }

    public function callAction() {
        if ($this->getRequest()->isPost()) {
            $this->_helper->viewRenderer->setNeverRender();

            $arr = array();
            $arr["inx"] = $this->_getParam("token");
            $arr["patientNumber"] = $this->_getParam("phone");
            $troposervice = new TropoService ();
            $troposervice->callpatient($arr);
            echo "0"; //这里如果直接返回字符 譬如 staring call. 前台无法得到，只能返回数字，然后前台再处理
        } else {
            $this->redirect("/sp/stepone");
        }
    }

    public function validatecreditcardAction() {
        if ($this->getRequest()->isPost()) {
            $creditCard = array(
                "firstName" => $this->_getParam("fname"),
                "lastName" => $this->_getParam("lname"),
                "cardType" => self::$__PayCardType[$this->_getParam("card_type")],
                "cardNumber" => $this->_getParam("card_number"),
                "cvv" => $this->_getParam("card_cvv"),
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
                "CardNo" => $this->_getParam("card_number"),
                "phone" => $this->_getParam("phone"),
                "email" => $this->_getParam("email"),
                "CardType" => $this->_getParam("card_type"),
                "ExpireMonth" => $this->_getParam("ExpireMonth"),
                "ExpireYear" => $this->_getParam("ExpireYear"),
                "cvv" => $this->_getParam("card_cvv"),
            );

            if (is_null($paypalToken)) {
                $param["validate"] = false;
                $this->forward("stepone", "sp", null, $param);
            } else {

                $call = new Application_Model_Call ();

                $params = array();
                $params ["patientName"] = $this->_getParam("fname") . $this->_getParam("lname");
                $params ["lastName"] = $this->_getParam("lname");
                $params ["firstName"] = $this->_getParam("fname");
                $params ["patientNumber"] = $this->_getParam("phone");
                $params ["patientEmail"] = $this->_getParam("email");
                $params ["trytimes"] = "1";
                $params ["paypaltoken"] = $paypalToken;

                $row = $call->createCall($params);
                $param["token"] = $row["inx"];
                $this->forward("steptwo", "sp", null, $param);
            }
        } else {
            $this->redirect("/sp/stepone");
        }

    }

    public function thanksAction() {
    }

    public function closedAction() {
    }

}

