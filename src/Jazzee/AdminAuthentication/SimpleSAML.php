<?php
namespace Jazzee\AdminAuthentication;
/**
 * SimpleSAML admin authentication controller
 *
 * SimpleSAML is a PHP service provider which can be installed on any webserver
 * it provides strightforward integration with several identiy provider solutions
 * If no identiy provider is avilalbe it can act in that role as well
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SimpleSAML implements \Jazzee\Interfaces\AdminAuthentication
{

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
   * @param \Jazzee\Interfaces\AdminController
   */
  public function __construct(\Jazzee\Interfaces\AdminController $controller)
  {
    $config = $controller->getConfig();
    require_once($config->getSimpleSAMLIncludePath());

    $this->_as = new \SimpleSAML_Auth_Simple($config->getSimpleSAMLAuthenticationSource());
    $this->_as->requireAuth();
    $attrs = $this->_as->getAttributes();
    if (!isset($attrs[$config->getSimpleSAMLUsernameAttribute()][0])) {
      throw new Exception($config->getSimpleSAMLUsernameAttribute() . ' attribute is missing from authentication source.');
    }
    $this->_user = $controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName' => $attrs[$config->getSimpleSAMLUsernameAttribute()][0], 'isActive' => true));
    if ($this->_user) {
      $this->_user->setFirstName($attrs[$config->getSimpleSAMLFirstNameAttribute()][0]);
      $this->_user->setLastName($attrs[$config->getSimpleSAMLLastNameAttribute()][0]);
      $this->_user->setEmail($attrs[$config->getSimpleSAMLEmailAddressAttribute()][0]);
      $controller->getEntityManager()->persist($this->_user);
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
    return;
  }

  public function logoutUser()
  {
    $this->_as->logout();
  }

}

