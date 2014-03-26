<?php

class TimerController extends Zend_Controller_Action {

	public function init() {
		// Disable layout because no return page
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
	}
	
	/*
	 * This function is called by shell TimerFire.php
	 */
	public function fireAction() {
		// TODO:
	}

}

