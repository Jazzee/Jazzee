<?php
namespace Jazzee\AdminDirectory;
/**
 * LDAP Admin Directory
 *
 * Use LDAP to find users and their attributes
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Ldap implements \Jazzee\Interfaces\AdminDirectory
{

  /**
   * Our directory Server resource
   * @var resource
   */
  protected $_directoryServer;

  /**
   * Controller instance
   * @var \Jazzee\AdminController
   */
  protected $_controller;

  /**
   * Limited Attributes
   * @var array
   */
  protected $_limitedAttributes;

  public function __construct(\Jazzee\Interfaces\AdminController $controller)
  {
    $this->_controller = $controller;
    if (!$this->_directoryServer = ldap_connect($this->_controller->getConfig()->getLdapHostname(), $this->_controller->getConfig()->getLdapPort())) {
      throw new \Jazzee\Exception('Unable to connect to ldap server ' . $this->_controller->getConfig()->getLdapHostname() . ' at port' . $this->_controller->getConfig()->getLdapPort());
    }
    if (!ldap_bind($this->_directoryServer, $this->_controller->getConfig()->getLdapBindRdn(), $this->_controller->getConfig()->getLdapBindPassword())) {
      throw new \Jazzee\Exception('Unable to bind to ldap server');
    }
    //limit the LDAP attributes for efficiency
    $this->_limitedAttributes = array(
      $this->_controller->getConfig()->getLdapUsernameAttribute(),
      $this->_controller->getConfig()->getLdapFirstNameAttribute(),
      $this->_controller->getConfig()->getLdapLastNameAttribute(),
      $this->_controller->getConfig()->getLdapEmailAddressAttribute()
    );
  }

  public function search($firstName, $lastName)
  {
    $attributes = array();
    if (!empty($firstName)) {
      $attributes[$this->_controller->getConfig()->getLdapFirstNameAttribute()] = $firstName . '*';
    }
    if (!empty($lastName)) {
      $attributes[$this->_controller->getConfig()->getLdapLastNameAttribute()] = $lastName . '*';
    }
    $filters = array();
    $filter = '';
    foreach ($attributes as $key => $value) {
      $filters[] = "{$key}={$value}";
    }
    if (count($filters) == 1) {
      $filter = $filters[0];
    } else if (count($filters) > 1) {
      $filter = '(&';
      foreach ($filters as $f) {
        $filter .= "({$f})";
      }
      $filter .= ')';
    }

    $searchResult = ldap_search($this->_directoryServer, $this->_controller->getConfig()->getLdapSearchBase(), $filter, $this->_limitedAttributes);

    return $this->parseSearchResult($searchResult);
  }

  public function findByUniqueName($uniqueName)
  {
    $filter = "{$this->_controller->getConfig()->getLdapUsernameAttribute()}={$uniqueName}";
    $searchResult = ldap_search($this->_directoryServer, $this->_controller->getConfig()->getLdapSearchBase(), $filter, $this->_limitedAttributes);

    return $this->parseSearchResult($searchResult);
  }

  /**
   * Parse the LDAP search results into a nice array
   *
   * @param resource $searchResult
   * @return array
   */
  protected function parseSearchResult($searchResult)
  {
    $result = array();
    ldap_sort($this->_directoryServer, $searchResult, $this->_controller->getConfig()->getLdapFirstNameAttribute());
    ldap_sort($this->_directoryServer, $searchResult, $this->_controller->getConfig()->getLdapLastNameAttribute());
    if (ldap_count_entries($this->_directoryServer, $searchResult)) {
      $entries = ldap_get_entries($this->_directoryServer, $searchResult);
      for ($i = 0; $i < $entries["count"]; $i++) {
        $arr = array(
          'userName' => '',
          'firstName' => '',
          'lastName' => '',
          'emailAddress' => '',
        );
        if (!empty($entries[$i][strtolower($this->_controller->getConfig()->getLdapUsernameAttribute())][0])) {
          $arr['userName'] = $entries[$i][strtolower($this->_controller->getConfig()->getLdapUsernameAttribute())][0];
        }
        if (!empty($entries[$i][strtolower($this->_controller->getConfig()->getLdapFirstNameAttribute())][0])) {
          $arr['firstName'] = $entries[$i][strtolower($this->_controller->getConfig()->getLdapFirstNameAttribute())][0];
        }
        if (!empty($entries[$i][strtolower($this->_controller->getConfig()->getLdapLastNameAttribute())][0])) {
          $arr['lastName'] = $entries[$i][strtolower($this->_controller->getConfig()->getLdapLastNameAttribute())][0];
        }
        if (!empty($entries[$i][strtolower($this->_controller->getConfig()->getLdapEmailAddressAttribute())][0])) {
          $arr['emailAddress'] = $entries[$i][strtolower($this->_controller->getConfig()->getLdapEmailAddressAttribute())][0];
        }
        $result[] = $arr;
      }
    }

    return $result;
  }

}