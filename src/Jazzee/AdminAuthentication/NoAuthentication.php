<?php
namespace Jazzee\AdminAuthentication;

/**
 * No Authentication
 *
 * Will only run in an environment set to TESTING
 * Doesn't require any authentication - the desired user is chosen from a list
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class NoAuthentication implements \Jazzee\Interfaces\AdminAuthentication
{
  const LOGIN_ELEMENT = 'NoAuthentication_Login';

  const SESSION_VAR_ID = 'noauth_userid';
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
   * Grab the desired user from the configuration file and log in as them
   * @param \Jazzee\Interfaces\AdminController
   */
  public function __construct(\Jazzee\Interfaces\AdminController $controller)
  {
    $this->_controller = $controller;
    if (!in_array($controller->getConfig()->getStatus(), array('DEVELOPMENT', 'PREVIEW'))) {
      throw new \Jazzee\Exception('Attmpted to use NoAuthentication in a non development/preview environment.');
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
    if ($form->processInput($_POST)) {
      if ($this->isAllowedIp($_SERVER['REMOTE_ADDR'])) {
        $this->_user = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findOneBy(array('id'=>$_POST['userid'], 'isActive'=>true));
        $this->_controller->getStore()->expire();
        $this->_controller->getStore()->touchAuthentication();
        $this->_controller->getStore()->set(self::SESSION_VAR_ID, $this->_user->getId());
      } else {
        throw new \Jazzee\Exception("{$_SERVER['REMOTE_ADDR']} is not a valid ip address for NoAuthentication.  Add it to the noAuthIpAddresses configuration to continue.");
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
      $element = $field->newElement('SelectList', 'userid');
      $element->setLabel('User');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      foreach ($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findByName('%', '%') as $user) {
        if ($user->isActive()) {
          $element->newItem($user->getId(), "{$user->getLastName()}, {$user->getFirstName()} - {$user->getEmail()}");
        }
      }
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

  /**
   * Check an IP address against the allowed address list
   * @param string $clientIp
   * @return boolean
   */
  protected function isAllowedIp($clientIp)
  {
    $allowedIps = explode(',', $this->_controller->getConfig()->getNoAuthIpAddresses());
    if (in_array($clientIp, $allowedIps)) {
      return true;
    }
    foreach($allowedIps as $allowedIp){
      if(\Foundation\Utility::ipInRange($clientIp, $allowedIp)){
        return true;
      }
    }

    return false;
  }
}