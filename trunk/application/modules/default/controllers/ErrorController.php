<?php

class ErrorController extends Zend_Controller_Action {

	public function errorAction() {
		$errors = $this->_getParam('error_handler');
		$this->_helper->layout->disableLayout();
		
		if ($this->_request->isXmlHttpRequest()) {
			$this->_helper->json->sendJson(array (
				'result' => null,
				'message' => $errors->exception->getMessage(),
				'successful' => false 
			));
		}
		switch ($errors->type) {
			// case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION :
				
				// 404 error -- controller or action not found
				$this->getResponse()->setHttpResponseCode(404);
				$this->view->title = "404 Error Page";
				$this->view->message = 'Page not found';
				break;
			
			default :
				switch (get_class($errors->exception)) {
					case 'Zend_Db_Adapter_Exception' :
						// db error
						$this->getResponse()->setHttpResponseCode(500);
						$this->view->title = "DB Error Page";
						$this->view->message = 'DB error';
						
						break;
					default :
						// application error
						$this->getResponse()->setHttpResponseCode(500);
						$this->view->title = "500 Error Page";
						$this->view->message = 'Application error';
						break;
				}
				break;
		}
		
		$this->view->exception = $errors->exception;
		$this->view->request = $errors->request;
		$this->renderScript("/error.phtml");
	}

	public function warningAction() {}

}