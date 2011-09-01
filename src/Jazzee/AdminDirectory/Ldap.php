<?php
/**
 * LDAP Admin Directory
 * 
 * Use LDAP to find users and their attributes
 * 
 */
namespace Jazzee\AdminDirectory;
class Ldap implements \Jazzee\AdminDirectory{
  /**
   * Our directory Server resource
   * @var resource
   */
  private $_directoryServer;
  
  /**
   * Config instance
   * @var \Jazzee\Configuration
   */
  private $_config;
  
  /**
   * Constructor
   * 
   * Connect to the directory server and bind
   * @param \Doctrine\ORM\EntityManager
   */
  public function __construct(\Doctrine\ORM\EntityManager $em){
    $this->_config = new \Jazzee\Configuration();
    if(!$this->_directoryServer = ldap_connect($this->_config->getLdapHostname(), $this->_config->getLdapPort())){
      throw new \Jazzee\Exception('Unable to connect to ldap server ' . $this->_config->getLdapHostname() . ' at port' . $this->_config->getLdapPort());
    }
    if(!ldap_bind($this->_directoryServer, $this->_config->getLdapBindRdn(), $this->_config->getLdapBindPassword())){
      throw new \Jazzee\Exception('Unable to bind to ldap server');
    }
  }
  
  public function search(array $attributes){
    $result = array();
    $filters = array();
    $filter = '';
    foreach($attributes as $key=>$value)$filters[] = "{$key}={$value}";
    if(count($filters) == 1){
      $filter = $filters[0];
    } else if(count($filters) > 1){
      $filter = '(&';
      foreach($filters as $f) $filter .= "({$f})";
      $filter .= ')';
    }
    $searchResult = ldap_search($this->_directoryServer, $this->_config->getLdapSearchBase(), $filter);
    ldap_sort($this->_directoryServer, $searchResult, $this->_config->getLdapFirstNameAttribute());
    ldap_sort($this->_directoryServer, $searchResult, $this->_config->getLdapLastNameAttribute());
    if(ldap_count_entries($this->_directoryServer, $searchResult)){
      $entries = ldap_get_entries($this->_directoryServer, $searchResult);
      for ($i=0; $i<$entries["count"]; $i++) {
        $arr = array(
          'userName' => '',
          'firstName' => '',
          'lastName' => '',
          'emailAddress' => '',
        );
        if(!empty($entries[$i][strtolower($this->_config->getLdapUsernameAttribute())][0])) $arr['userName'] = $entries[$i][strtolower($this->_config->getLdapUsernameAttribute())][0];
        if(!empty($entries[$i][strtolower($this->_config->getLdapFirstNameAttribute())][0])) $arr['firstName'] = $entries[$i][strtolower($this->_config->getLdapFirstNameAttribute())][0];
        if(!empty($entries[$i][strtolower($this->_config->getLdapLastNameAttribute())][0])) $arr['lastName'] = $entries[$i][strtolower($this->_config->getLdapLastNameAttribute())][0];
        if(!empty($entries[$i][strtolower($this->_config->getLdapEmailAddressAttribute())][0])) $arr['emailAddress'] = $entries[$i][strtolower($this->_config->getLdapEmailAddressAttribute())][0];
        
        $result[] = $arr;
      }
    }
    return $result;
  }
}

