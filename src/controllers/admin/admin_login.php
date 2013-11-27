<?php

/**
 * Login
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminLoginController extends \Jazzee\AdminController
{

  const MENU = 'My Account';
  const TITLE = 'Login';
  const PATH = 'login';
  const REQUIRE_AUTHORIZATION = false;
  const REQUIRE_APPLICATION = false;

  /**
   * Display index
   */
  public function actionIndex()
  {
    $this->_adminAuthentication->loginUser();
    if ($this->_adminAuthentication->isValidUser()) {
      $this->_authLog->info('Successfull login for user ' . $this->_adminAuthentication->getUser()->getId() . ' from ' . $_SERVER['REMOTE_ADDR']);
      $this->redirectPath('welcome');
    }
    $this->setVar('authenticationClass', $this->_adminAuthentication);
  }

  /**
   * Only for non-authenticated
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if ($action == 'index') {
      return !(bool) $user;
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}