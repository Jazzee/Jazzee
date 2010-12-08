<?php
/**
 * Logout Administrators
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminLogoutController extends AdminController {
  protected $sessionName = 'guest';
  protected $layout = 'default';
  /**
   * Destroy the session and redirect back to the login page
   */
  public function actionIndex(){
    Session::getInstance()->getStore('manage')->expire();
    setcookie('JazzeeLogin', '', time() - 3600);
    $this->setLayoutVar('layoutTitle', 'Application Management Logout');
    $this->messages->write('success', "You have been loggout out successfully.");
    $this->redirect($this->path("admin/login"));
    $this->afterAction();
    exit(0);
  }
  
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    return true; //everyone is allowed to logout
  }
  
}
?>
