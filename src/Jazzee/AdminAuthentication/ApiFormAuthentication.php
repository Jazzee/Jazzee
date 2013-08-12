<?php
namespace Jazzee\AdminAuthentication;

/**
 * Form based API authentication
 *
 * Will only run in an environment set to TESTING
 * Used for automated testing, the system takes a user API key as
 * the form input and authenticated the user.  This is slightly more secure than
 * NoAuthentication method
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApiFormAuthentication implements \Jazzee\Interfaces\AdminAuthentication
{
  const LOGIN_ELEMENT = 'ApiFormAuthentication_Login';

  const SESSION_VAR_ID = 'apiformauthentication_userid';
  /**
   * Our user
   * @var \Jazzee\Entity\User
   */
  private $_user;

  /**
   * Config instance
   * @var \Jazzee\Controller
   */
  private $_controller;

  /**
   * Login form
   * @var \Foundation\Form
   */
  private $_form;

  /**
   * Constructor
   *
   * @param \Jazzee\Interfaces\AdminController
   */
  public function __construct(\Jazzee\Interfaces\AdminController $controller)
  {
    $this->_controller = $controller;
    if ($controller->getConfig()->getStatus() != 'DEVELOPMENT' AND $controller->getConfig()->getStatus() != 'PREVIEW') {
      throw new \Jazzee\Exception('Attmpted to use ApiFormAuthentication in a production environment.');
    }
    if ($this->_controller->getStore()->check(self::SESSION_VAR_ID)) {
      $this->_user = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->find($this->_controller->getStore()->get(self::SESSION_VAR_ID));
    }
  }

  public function isValidUser()
  {
    return (bool) $this->_user;
  }

  public function getUser()
  {
    return $this->_user;
  }

  public function loginUser()
  {
    $form = $this->getLoginForm();
    if ($input = $form->processInput($_POST)) {
      $allowedIps = explode(',', $this->_controller->getConfig()->getApiFormAuthenticationIpAddresses());
      if (in_array($_SERVER['REMOTE_ADDR'], $allowedIps)) {
        if( $this->_user = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findOneBy(array('apiKey'=>$input->get('apiKey'), 'isActive'=>true))){
          $this->_controller->getStore()->expire();
          $this->_controller->getStore()->touchAuthentication();
          $this->_controller->getStore()->set(self::SESSION_VAR_ID, $this->_user->getId());
        } else {
          $form->getElementByName('apiKey')->addMessage('That is not a valid ID');
          return false;
        }
      } else {
        throw new \Jazzee\Exception("{$_SERVER['REMOTE_ADDR']} is not a valid ip address for ApiFormAuthentication: {$allowedIps}.  Add it to the apiFormAuthenticationIpAddresses configuration to continue.");
      }
    }
  }

  /**
   * Get the login form
   *
   * @return \Foundation\Form
   */
  public function getLoginForm()
  {
    if (is_null($this->_form)) {
      $this->_form = new \Foundation\Form;
      $this->_form->setCSRFToken($this->_controller->getCSRFToken());
      $this->_form->setAction($this->_controller->path("login"));
      $field = $this->_form->newField();
      $field->setLegend('Select a user');
      $element = $field->newElement('TextInput', 'apiKey');
      $element->setLabel('API Key');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $this->_form->newButton('submit', 'Login');
    }

    return $this->_form;
  }

  /**
   * Logout the user
   */
  public function logoutUser()
  {
    $this->_user = null;
    $this->_controller->getStore()->expire();
  }
}