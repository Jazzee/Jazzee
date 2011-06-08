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
    $this->_adminAuthentication->logoutUser();
  }
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null){
    //Check to be sure a valid user object has been set
    //Any user is allowed access
    if($user){
      return true;
    }
    return false;
  }
}
?>