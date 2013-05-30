<?php
namespace Jazzee\AdminAuthentication;

/**
 * Authentication for the Preview application
 * 
 * Used for the administrator generated previews where privileges are set
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PreviewApplication implements \Jazzee\Interfaces\AdminAuthentication
{
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
   * our session store ID
   */
  const SESSION_VAR_ID = 'previewapplication_userid';

  /**
   * Constructor
   *
   * @param \Jazzee\Interfaces\AdminController
   */
  public function __construct(\Jazzee\Interfaces\AdminController $controller)
  {
    if(!$controller->isPreviewMode()){
      throw new \Jazzee\Exception('Preview mode authentication is only available in preview mode.');
    }
    $this->_controller = $controller;
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
    $this->_user = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName'=>'previewuser', 'isActive'=>true));
    $this->_controller->getStore()->expire();
    $this->_controller->getStore()->touchAuthentication();
    $this->_controller->getStore()->set(self::SESSION_VAR_ID, $this->_user->getId());
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