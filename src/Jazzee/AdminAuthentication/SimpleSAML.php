<?php
/**
 * SimpleSAML admin authentication controller
 * 
 * SimpleSAML is a PHP service provider which can be installed on any webserver
 * it provides strightforward integration with several identiy provider solutions
 * If no identiy provider is avilalbe it can act in that role as well
 * 
 */
namespace Jazzee\AdminAuthentication;
class SimpleSAML implements \Jazzee\AdminAuthentication{
  /**
   * Our authenticated user
   * @var \Jazzee\Entity\User
   */
  private $_user;
  
  /**
   * Our authentication source
   * @var \SimpleSAME_Auth_Simple
   */
  private $_as;
  
  /**
   * Constructor
   * 
   * Require authentication and setup the user if a valid session is detected
   * 
   * @param \Doctrine\ORM\EntityManager
   */
  public function __construct(\Doctrine\ORM\EntityManager $em){
    $configurationFile = realpath(__DIR__ . '/../../../etc') . '/simplesaml.ini.php';
    if(!is_readable($configurationFile)) throw new \Jazzee\Exception("Unable to load simplesaml configuration file: {$configurationFile}.", E_ERROR);
    $config = parse_ini_file($configurationFile);
    require_once($config['includePath']);
    
    $this->_as = new \SimpleSAML_Auth_Simple($config['authenticationSource']);
    $this->_as->requireAuth();
    $attrs = $this->_as->getAttributes();
    if (!isset($attrs[$config['usernameAttribute']][0])) throw new Exception($config['usernameAttribute'] . ' attribute is missing from authentication source.');
    $this->_user = $em->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName'=>$attrs[$config['usernameAttribute']][0]));
    if($this->_user){
      $this->_user->setFirstName($attrs[$config['firstNameAttribute']][0]);
      $this->_user->setLastName($attrs[$config['lastNameAttribute']][0]);
      $this->_user->setEmail($attrs[$config['emailAddressAttribute']][0]);
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
    $this->_as->logout();
  }
}

