<?php

/**
 * Welcome Page for Administrative Functions
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminWelcomeController extends \Jazzee\AdminController
{

  const TITLE = 'Home';
  const PATH = 'welcome';
  const REQUIRE_AUTHORIZATION = false;
  const REQUIRE_APPLICATION = false;

  /**
   * Display index
   */
  public function actionIndex()
  {
    if ($this->_config->getLoginMessage()) {
        $this->addMessage('error', $this->_config->getLoginMessage());
    }
    if ($this->_user) {
      $this->setVar('user', $this->_user);
    }
  }

}