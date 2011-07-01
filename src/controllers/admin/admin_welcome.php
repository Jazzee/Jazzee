<?php
/**
 * Welcome Page for Administrative Functions
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminWelcomeController extends \Jazzee\AdminController {
  const TITLE = 'Home';
  const PATH = 'welcome';
  const REQUIRE_AUTHORIZATION = false;
  const REQUIRE_APPLICATION = false;
  
  /**
   * Display index
   */
  public function actionIndex(){
    $this->setVar('firstName', $this->_user->getFirstName());
    $this->setVar('lastName', $this->_user->getLastName());
  }

}
?>