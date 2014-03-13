<?php
/**
 *
 * @author R2109
 * @version 
 */
class Zend_View_Helper_MainLayout extends Zend_Controller_Action_Helper_Abstract{
  public $view;

  public function mainLayout() {return $this; }

  public function nav() { return $this->view->render('inc/nav.phtml'); }

  public function loginUser() {
    $auth = Zend_Auth::getInstance();
    if($auth->hasIdentity()) {
      $username = $auth->getIdentity();
      return $username;
    }
  }

  public function setView(Zend_View_Interface $view) { $this->view = $view; }
}

