<?php
/**
 * Login
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminLoginController extends \Jazzee\AdminController {
  const MENU = 'My Account';
  const TITLE = 'Login';
  const PATH = 'login';
  const REQUIRE_AUTHORIZATION = false;
  const REQUIRE_APPLICATION = false;
  
  /**
   * Display index
   */
  public function actionIndex(){
    $this->_adminAuthentication->loginUser();
    $this->redirectPath('welcome');
  }
  
  /**
   * Get the navigation
   * @return Navigation
   */
  public function getNavigation(){
    return false;
  }
  
  /**
   * Only for non-authenticated
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null){
    if($action=='index'){
      return !(bool)$user;
    }
    return parent::isAllowed($controller, $action, $user, $program, $application);
  }
}
?>