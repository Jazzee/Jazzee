<?php

/**
 * Logout
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminLogoutController extends \Jazzee\AdminController
{

  const MENU = 'My Account';
  const TITLE = 'Logout';
  const PATH = 'logout';
  const REQUIRE_AUTHORIZATION = false;
  const REQUIRE_APPLICATION = false;

  /**
   * Display index
   */
  public function actionIndex()
  {
    $this->setLayoutVar('pageTitle', 'Logout');
    $this->setLayoutVar('layoutTitle', 'Logout');
    $this->_user = null;
    $this->_store->expire();
    $this->_program = null;
    $this->_cycle = null;
    $this->_application = null;
    $this->_adminAuthentication->logoutUser();
  }

  /**
   * Get the navigation
   * @return Navigation
   */
  public function getNavigation()
  {
    return false;
  }

  /**
   * Only for authenticated users
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if ($action == 'index') {
      return (bool) $user;
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}