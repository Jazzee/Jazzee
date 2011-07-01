<?php
/**
 * Logout
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminLogoutController extends \Jazzee\AdminController {
  const MENU = 'My Account';
  const TITLE = 'Logout';
  const PATH = 'logout';
  
  /**
   * Display index
   */
  public function actionIndex(){
    $this->setLayoutVar('pageTitle', 'Logout');
    $this->setLayoutVar('layoutTitle', 'Logout');
    $this->_user = null;
    $this->_store->expire();
    $this->_program = null;
    $this->_cycle = null;
    $this->_application = null;
    $this->_adminAuthentication->logoutUser();
  }
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null){
    //anyone can logout
    return true;
  }
  
  /**
   * Get the navigation
   * @return Navigation
   */
  public function getNavigation(){
    return false;
  }
}
?>