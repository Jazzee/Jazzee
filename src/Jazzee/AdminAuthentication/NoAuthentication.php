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
    $jzConfig = new \Jazzee\Configuration();
    if($jzConfig->getStatus() != 'DEVELOPMENT'){
      throw new \Jazzee\Exception('Attmpted to use NoAuthentication in a non development environment.');
    }
    $configurationFile = realpath(__DIR__ . '/../../../etc') . '/noauthentication.ini.php';
    if(!is_readable($configurationFile)) throw new \Jazzee\Exception("Unable to load noauthentication configuration file: {$configurationFile}.", E_ERROR);
    $config = parse_ini_file($configurationFile);
    $allowedIps = explode(',', $config['ipAddresses']);
    if(in_array($_SERVER['SERVER_ADDR'], $allowedIps)){
      $this->_user = $em->getRepository('\Jazzee\Entity\User')->find($config['userId']);
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
  }
}

