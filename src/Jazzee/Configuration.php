<?php
namespace Jazzee;

/**
 * Config class
 * @package jazzee
 */
class Configuration
{

    /**
 * @var string
 */
protected $_mode;

/**
 * @var string
 */
protected $_status;

/**
 * @var string
 */
protected $_forceSSL;

/**
 * @var string
 */
protected $_dbHost;

/**
 * @var string
 */
protected $_dbPort;

/**
 * @var string
 */
protected $_dbName;

/**
 * @var string
 */
protected $_dbUser;

/**
 * @var string
 */
protected $_dbPassword;

/**
 * @var string
 */
protected $_dbDriver;

/**
 * @var string
 */
protected $_sessionName;

/**
 * @var string
 */
protected $_mailServer;

/**
 * @var string
 */
protected $_mailSubjectPrefix;

/**
 * @var string
 */
protected $_mailDefaultFrom;

/**
 * @var string
 */
protected $_mailDefaultName;

/**
 * @var string
 */
protected $_mailOverrideTo;

/**
 * @var string
 */
protected $_varPath;

/**
 * @var string
 */
protected $_adminEmail;

/**
 * @var string
 */
protected $_timezone;

/**
 * @var string
 */
protected $_localBootstrap;

  /**
   * Construct
   * Load data from the ini file
   */
  public function __construct(){
    $arr = parse_ini_file(__DIR__ . '/../../etc/config.ini.php');
    foreach($arr as $name => $value){
      $setter = 'set' . \ucfirst($name);
      if(!method_exists($this, $setter)) throw new Exception("Configuration variable ({$name}) found in file, but it is not a recognized option.");
      $this->$setter($value);
    }  
  }
  
  /**
   * get mode
   * @return string
   */
  public function getMode() {
    return $this->_mode;
  }
  
  /**
   * set mode
   * @var string mode
   */
  public function setMode($mode) {
    $this->_mode = $mode;
  }
  
  /**
   * get status
   * @return string
   */
  public function getStatus() {
    return $this->_status;
  }
  
  /**
   * set status
   * @var string status
   */
  public function setStatus($status) {
    $this->_status = $status;
  }
  
  /**
   * get forceSSL
   * @return string
   */
  public function getForceSSL() {
    return $this->_forceSSL;
  }
  
  /**
   * set forceSSL
   * @var string forceSSL
   */
  public function setForceSSL($forceSSL) {
    $this->_forceSSL = $forceSSL;
  }
  
  /**
   * get dbHost
   * @return string
   */
  public function getDbHost() {
    return $this->_dbHost;
  }
  
  /**
   * set dbHost
   * @var string dbHost
   */
  public function setDbHost($dbHost) {
    $this->_dbHost = $dbHost;
  }
  
  /**
   * get dbPort
   * @return string
   */
  public function getDbPort() {
    return $this->_dbPort;
  }
  
  /**
   * set dbPort
   * @var string dbPort
   */
  public function setDbPort($dbPort) {
    $this->_dbPort = $dbPort;
  }
  
  /**
   * get dbName
   * @return string
   */
  public function getDbName() {
    return $this->_dbName;
  }
  
  /**
   * set dbName
   * @var string dbName
   */
  public function setDbName($dbName) {
    $this->_dbName = $dbName;
  }
  
  /**
   * get dbUser
   * @return string
   */
  public function getDbUser() {
    return $this->_dbUser;
  }
  
  /**
   * set dbUser
   * @var string dbUser
   */
  public function setDbUser($dbUser) {
    $this->_dbUser = $dbUser;
  }
  
  /**
   * get dbPassword
   * @return string
   */
  public function getDbPassword() {
    return $this->_dbPassword;
  }
  
  /**
   * set dbPassword
   * @var string dbPassword
   */
  public function setDbPassword($dbPassword) {
    $this->_dbPassword = $dbPassword;
  }
  
  /**
   * get dbDriver
   * @return string
   */
  public function getDbDriver() {
    return $this->_dbDriver;
  }
  
  /**
   * set dbDriver
   * @var string dbDriver
   */
  public function setDbDriver($dbDriver) {
    $this->_dbDriver = $dbDriver;
  }
  
  /**
   * get sessionName
   * @return string
   */
  public function getSessionName() {
    return $this->_sessionName;
  }
  
  /**
   * set sessionName
   * @var string $sessionName
   */
  public function setSessionName($sessionName) {
    $this->_sessionName = $sessionName;
  }
  
  /**
   * get adminSessionLifetime
   * @return string
   */
  public function getAdminSessionLifetime() {
    return $this->_adminSessionLifetime;
  }
  
  /**
   * set adminSessionLifetime
   * @var string $lifetime
   */
  public function setAdminSessionLifetime($lifetime) {
    $this->_adminSessionLifetime = $lifetime;
  }
  
  /**
   * get Applicant SessionLifetime
   * @return string
   */
  public function getApplicantSessionLifetime() {
    return $this->_applicantSessionLifetime;
  }
  
  /**
   * set Applicant SessionLifetime
   * @var string $lifetime
   */
  public function setApplicantSessionLifetime($lifetime) {
    $this->_applicantSessionLifetime = $lifetime;
  }
    
  /**
   * get mailServer
   * @return string
   */
  public function getMailServer() {
    return $this->_mailServer;
  }
  
  /**
   * set mailServer
   * @var string mailServer
   */
  public function setMailServer($mailServer) {
    $this->_mailServer = $mailServer;
  }
  
  /**
   * get mailSubjectPrefix
   * @return string
   */
  public function getMailSubjectPrefix() {
    return $this->_mailSubjectPrefix;
  }
  
  /**
   * set mailSubjectPrefix
   * @var string mailSubjectPrefix
   */
  public function setMailSubjectPrefix($mailSubjectPrefix) {
    $this->_mailSubjectPrefix = $mailSubjectPrefix;
  }
  
  /**
   * get mailDefaultFrom
   * @return string
   */
  public function getMailDefaultFrom() {
    return $this->_mailDefaultFrom;
  }
  
  /**
   * set mailDefaultFrom
   * @var string mailDefaultFrom
   */
  public function setMailDefaultFrom($mailDefaultFrom) {
    $this->_mailDefaultFrom = $mailDefaultFrom;
  }
  
  /**
   * get mailDefaultName
   * @return string
   */
  public function getMailDefaultName() {
    return $this->_mailDefaultName;
  }
  
  /**
   * set mailDefaultName
   * @var string mailDefaultName
   */
  public function setMailDefaultName($mailDefaultName) {
    $this->_mailDefaultName = $mailDefaultName;
  }
  
  /**
   * get mailOverrideTo
   * @return string
   */
  public function getMailOverrideTo() {
    return $this->_mailOverrideTo;
  }
  
  /**
   * set mailOverrideTo
   * @var string mailOverrideTo
   */
  public function setMailOverrideTo($mailOverrideTo) {
    $this->_mailOverrideTo = $mailOverrideTo;
  }
  
  /**
   * get varPath
   * @return string
   */
  public function getVarPath() {
    return $this->_varPath;
  }
  
  /**
   * set varPath
   * @var string varPath
   */
  public function setVarPath($varPath) {
    $this->_varPath = $varPath;
  }
  
  /**
   * get adminEmail
   * @return string
   */
  public function getAdminEmail() {
    return $this->_adminEmail;
  }
  
  /**
   * set adminEmail
   * @var string adminEmail
   */
  public function setAdminEmail($adminEmail) {
    $this->_adminEmail = $adminEmail;
  }
  
  /**
   * get timezone
   * @return string
   */
  public function getTimezone() {
    return $this->_timezone;
  }
  
  /**
   * set timezone
   * @var string timezone
   */
  public function setTimezone($timezone) {
    $this->_timezone = $timezone;
  }
  
  /**
   * get localBootstrap
   * @return string
   */
  public function getLocalBootstrap() {
    return $this->_localBootstrap;
  }
  
  /**
   * set localBootstrap
   * @var string localBootstrap
   */
  public function setLocalBootstrap($localBootstrap) {
    $this->_localBootstrap = $localBootstrap;
  }

}
