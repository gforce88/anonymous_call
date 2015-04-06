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
	}
	public function testAction() {
		$call = new Application_Model_Call ();
		
		$params = array ();
		$params ["patientName"] = "xuweiming";
		$params ["patientNumber"] = "+17023580286";
		$params ["patientCreditNumber"] = "0393939kejjuudu";
		$params ["patientEmail"] = "1274263@qq.com";
		$params ["trytimes"] = "1";
		
		$params ["inx"] = $call->createCall ( $params );
		
		$arr = array();
		$arr["inx"] = $params ["inx"];
		$arr["patientNumber"] = $params ["patientNumber"];
		$troposervice = new TropoService ();
		$troposervice->callpatient ( $arr );
	}
}

