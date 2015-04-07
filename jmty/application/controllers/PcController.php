<?php
class PcController extends Zend_Controller_Action {

    private static $__CardType = array(
        "01" => "master",
        "02" => "visa",
        "03" => "カード会社",
    );

	public function init() {
		/* Initialize action controller here */
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
	}
}

