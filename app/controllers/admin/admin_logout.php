<?php
/**
 * Logout Administrators
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 */
class AdminLogoutController extends AdminController {
  const MENU = 'My Account';
  const TITLE = 'Logout';
  const PATH = 'admin/logout';
  
  protected $sessionName = 'guest';
  protected $layout = 'default';
  /**
   * Destroy the session and redirect back to the login page
   */
  public function actionIndex(){
    Session::getInstance()->getStore('manage')->expire();
    $this->messages->write('success', "You have been loggout out successfully.");
    $this->redirectPath('admin/login');
  }
  
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    return true; //everyone is allowed to logout
  }
  
}
?>