<?php
/**
 * No Authentication
 * 
 * Will only run in an environment set to TESTING
 * Doesn't require any authentication - the desired user is chosen from a list
 * 
 */
namespace Jazzee\AdminAuthentication;
class NoAuthentication implements \Jazzee\AdminAuthentication{
  /**
   * Our user
   * @var \Jazzee\Entity\User
   */
  private $_user;
  
  /**
   * Constructor
   * 
   * Grab the desired user from the configuration file and log in as them
   * @param \Doctrine\ORM\EntityManager
   */
  public function __construct(\Doctrine\ORM\EntityManager $em){
    $config = new \Jazzee\Configuration();
    if($config->getStatus() != 'DEVELOPMENT'){
      throw new \Jazzee\Exception('Attmpted to use NoAuthentication in a non development environment.');
    }
    
    $allowedIps = explode(',', $config->getNoAuthIpAddresses());
    if(in_array($_SERVER['REMOTE_ADDR'], $allowedIps)){
      $this->_user = $em->getRepository('\Jazzee\Entity\User')->findOneBy(array('id'=>$config->getNoAuthUserId(), 'isActive'=>true));
    }
  }
  
  public function isValidUser(){
    return (bool)$this->_user;
  }
  
  public function getUser(){
    return $this->_user;
  }
  
  public function loginUser(){
    return;
  }
  
  public function logoutUser(){
    $this->_user = null;
  }
}

