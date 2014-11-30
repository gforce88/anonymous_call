<?php

class UploadController extends Zend_Controller_Action {

	public function init() {
		// Disable layout because no return page
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNeverRender();
	}

	public function indexAction() {
		$target_path = "./" . basename($_FILES['uploadedfile']['name']);
		if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
			echo "The file " . basename($_FILES['uploadedfile']['name']) . " has been uploaded";
		} else {
			echo "There was an error uploading the file, please try again!";
		}
	}

}