<?php
require_once 'log/LoggerFactory.php';
require_once 'tropo/tropo.class.php';
require_once 'util/HttpUtil.php';
require_once 'service/TropoService.php';
require_once 'service/EmailService.php';
class IndexController extends Zend_Controller_Action {
	public function init() {
		/* Initialize action controller here */
		$this->_helper->viewRenderer->setNeverRender ();
	}
	public function indexAction() {
		// action body
		$data =  "test index page";
		$this->_helper->json ( $data, true, false, true );

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
}
