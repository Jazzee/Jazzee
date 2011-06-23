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
class Shibboleth implements \Jazzee\AdminAuthentication{
  /**
   * Our authenticated user
   * @var \Jazzee\Entity\User
   */
  private $_user;
  
  /**
   * Constructor
   * 
   * Require authentication and setup the user if a valid session is detected
   * 
   * @param \Doctrine\ORM\EntityManager
   */
  public function __construct(\Doctrine\ORM\EntityManager $em){
    $config = new \Jazzee\Configuration();
    
    if (!isset($_SERVER[$config->getShibbolethUsernameAttribute()])) throw new \Jazzee\Exception($config->getShibbolethUsernameAttribute() . ' attribute is missing from authentication source.');
    
    $uniqueName = $_SERVER[$config->getShibbolethUsernameAttribute()];
    $firstName = $_SERVER[$config->getShibbolethFirstNameAttribute()];
    $lastName = $_SERVER[$config->getShibbolethLastNameAttribute()];
    $mail = $_SERVER[$config->getShibbolethEmailAddressAttribute()];
    
    $this->_user = $em->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName'=>$uniqueName));
    if($this->_user){
      $this->_user->setFirstName($firstName);
      $this->_user->setLastName($lastName);
      $this->_user->setEmail($mail);
      $em->persist($this->_user);
    }
  }
  
  public function isValidUser(){
    return (bool)$this->_user;
  }
  
  public function getUser(){
    return $this->_user;
  }
  
  public function logoutUser(){
    $this->_user = null;
    $session = new \Foundation\Session();
    $session->getStore('admin')->expire();
    $config = new \Jazzee\Configuration();
    header('Location: ' . $config->getShibbolethLogoutUrl());
    die();
  }
}

