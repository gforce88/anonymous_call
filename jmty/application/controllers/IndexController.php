<?php
require_once 'log/LoggerFactory.php';
require_once 'tropo/tropo.class.php';
require_once 'util/HttpUtil.php';
require_once 'service/TropoService.php';
require_once 'emailLib/AppEmail.php';
require_once "service/PaypalService.php";
class IndexController extends Zend_Controller_Action {
	public function init() {
		/* Initialize action controller here */
		$this->_helper->viewRenderer->setNeverRender ();
	}
	public function indexAction() {
		// action body
		//echo APPLICATION_PATH."/configs/emailTemplate.html";
		//$data =  "test index page";
		//$data =  "";
		//$this->_helper->json ( $data, true, false, true );
		$r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
		$r->gotoUrl('/pc/stepone')->redirectAndExit();

	}
	
	public function sorryAction(){
		//echo "We're sorry, this service is not available now, please try back tomorrow at 9am";
		$r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
		$r->gotoUrl('/pc/closed')->redirectAndExit();
	}
	
	public function aaAction(){
		
		//$appEmails->sendThankYouEmail ('1274263@qq.com',60,5000);
		
		//$appEmails->sendCardErrEmail ('1274263@qq.com');
	}
	
	public function testAction() {

		$call = new Application_Model_Call ();
		$params = array ();
		$params ["lastName"] = "xu";
		$params ["firstName"] = "weiming";
		$params ["firstName"] = "weiming";
		$params ["patientNumber"] = "+12176507163";
		$params ["patientCreditNumber"] = "0393939kejjuudu";
		$params ["patientEmail"] = "1274263@qq.com";
		$params["cardType"] = "visa";
		$params["expYear"] = "03";
		$params["expMonth"] = "01";
		$params["cvv"] = "332";
		
		$params = $call->createCall ( $params );
		echo $params["inx"];
		$arr = array();
		$arr["inx"] = $params ["inx"];
		$arr["patientNumber"] = $params ["patientNumber"];
		$troposervice = new TropoService ();
		$troposervice->callpatient ( $arr );
	}

    public function validatecreditcardAction() {
        /*
        $creditCard = array (
            "firstName" => $_POST["fname"],
            "lastName" => $_POST["lname"],
            "cardType" => self::$__CardType[$_POST["CardType"]],
            "cardNumber" => $_POST["CardNo"],
            "cvv" => $_POST["cvv"],
            "expMonth" => $_POST["ExpireMonth"],
            "expYear" => $_POST["ExpireYear"]
        );
        */

        $creditCard = array (
            "firstName" => "xu",
            "lastName" => "weiming",
            "cardType" => "visa",
            "cardNumber" => "4417119669820331",
            "cvv" => "111",
            "expMonth" => "12",
            "expYear" => "2015"
        );

        $paypalService = new PaypalService();
        $paypalToken = $paypalService->regist($creditCard);

        $ret = array();
        $ret["validateResult"] = !is_null($paypalToken);

        return $this->_helper->json($ret, true, false, true);
    }
}
