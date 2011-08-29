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
  const TITLE = '';
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
}
?>