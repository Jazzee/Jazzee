<?php

/**
 * View and reset API key for a user
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminApikeyController extends \Jazzee\AdminController
{

  const MENU = 'My Account';
  const TITLE = 'API Key';
  const PATH = 'apikey';
  const REQUIRE_AUTHORIZATION = true;
  const REQUIRE_APPLICATION = false;

  /**
   * Display key
   */
  public function actionIndex()
  {
    $this->setVar('apiKey', $this->_user->getApiKey());
  }

  /**
   * Update the user key
   */
  public function actionUpdateKey()
  {
    $this->_user->generateApiKey();
    $this->_em->persist($this->_user);
    $this->addMessage('success', 'Key updated successfully');
    $this->redirectPath('apikey');
  }

  /**
   * Only allow access for users with API keys
   * These keys are generated in the Manage Users menu
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if (in_array($action, array('index', 'updateKey')) AND $user AND $user->getApiKey()) {
      return true;
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}