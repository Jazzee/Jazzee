<?php
/**
 * Shibboleth Admin Authentication Controller
 * 
 * Shibboleth is a leading identity provider for educational institutions
 * Because Shibboleth is the preferred authentication mechanism at the University of California this 
 * module is the most likley to be up to date.
 * 
 */
namespace Jazzee\AdminAuthentication;
class Shibboleth implements \Jazzee\Interfaces\AdminAuthentication{
  /**
   * Our authenticated user
   * @var \Jazzee\Entity\User
   */
  private $_user;
  
  /**
   * Config instance
   * @var \Jazzee\Configuration 
   */
  private $_config;
  
  /**
   * Constructor
   * 
   * Require authentication and setup the user if a valid session is detected
   * 
   * @param \Jazzee\Interfaces\AdminController
   */
  public function __construct(\Jazzee\Interfaces\AdminController $controller){
    $this->_config = $controller->getConfig();
    if(isset($_SERVER['Shib-Application-ID'])){
      if (!isset($_SERVER[$this->_config->getShibbolethUsernameAttribute()])) throw new \Jazzee\Exception($this->_config->getShibbolethUsernameAttribute() . ' attribute is missing from authentication source.');
      
      $uniqueName = $_SERVER[$this->_config->getShibbolethUsernameAttribute()];
      $firstName = $_SERVER[$this->_config->getShibbolethFirstNameAttribute()];
      $lastName = $_SERVER[$this->_config->getShibbolethLastNameAttribute()];
      $mail = $_SERVER[$this->_config->getShibbolethEmailAddressAttribute()];
      
      $this->_user = $controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName'=>$uniqueName, 'isActive'=>true));
      if($this->_user){
        $this->_user->setFirstName($firstName);
        $this->_user->setLastName($lastName);
        $this->_user->setEmail($mail);
        $controller->getEntityManager()->persist($this->_user);
      }
    }
  }
  
  public function isValidUser(){
    return (bool)$this->_user;
  }
  
  public function getUser(){
    return $this->_user;
  }
  
  public function loginUser(){
    $this->_user = null;
    $session = new \Foundation\Session();
    $session->getStore('admin')->expire();
    header('Location: ' . $this->_config->getShibbolethLoginUrl());
    die();
  }
  
  public function logoutUser(){
    $this->_user = null;
    $session = new \Foundation\Session();
    $session->getStore('admin')->expire();
    header('Location: ' . $this->_config->getShibbolethLogoutUrl());
    die();
  }
}

