<?php

class TimerController extends Zend_Controller_Action {

	public function init() {
		// Disable layout because no return page
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
	}
	
	/*
	 * This function is called by shell Timer.php
	 */
	public function fireAction() {
		// TODO:
		// 1. Charge Paypal at 5:00
		// 2. Conference Tropo remind at 4:30
	}

}

