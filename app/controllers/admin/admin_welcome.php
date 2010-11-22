<?php
/**
 * Welcome Page for Administrative Functions
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminWelcomeController extends AdminController {
  /**
   * Display index
   */
  public function actionIndex(){
    $this->setVar('firstName', $this->user->firstName);
    $this->setVar('lastName', $this->user->lastName);
    $this->setVar('failedLoginAttempts', $this->session->failedLoginAttempts);
    $this->setVar('lastFailedLogin_ip', $this->session->lastFailedLogin_ip);
    $this->setVar('lastLogin', $this->session->lastLogin);
    $this->setVar('lastLogin_ip', $this->session->lastLogin_ip);
  }
  
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    //Check to be sure a valid user object has been set
    //Any user is allowed access
    if($user){
      return true;
    }
    return false;
  }
}
?>