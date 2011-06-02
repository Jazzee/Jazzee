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
protected $_applicantSessionLifetime;

/**
 * @var string
 */
protected $_adminSessionLifetime;

/**
 * @var string
 */
protected $_mailServerType;

/**
 * @var string
 */
protected $_mailServerHost;

/**
 * @var string
 */
protected $_mailServerPort;

/**
 * @var string
 */
protected $_mailServerUsername;

/**
 * @var string
 */
protected $_mailServerPassword;

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
protected $_mailDefaultFromName;

/**
 * @var string
 */
protected $_mailOverrideToAddress;

/**
 * @var string
 */
protected $_mailOverrideToName;

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
 * @var string
 */
protected $_maximumApplicantFileUpload;

  /**
   * Construct
   * Load data from the ini file
   */
  public function __construct(){
    $configurationFile = realpath(__DIR__ . '/../../etc') . '/config.ini.php';
    if(!is_readable($configurationFile)) throw new Exception("Unable to load {$configurationFile}.", E_ERROR, 'We were unable to load the configuration file for this site.');
    $arr = parse_ini_file($configurationFile);
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
   * get mailServerType
   * @return string
   */
  public function getMailServerType() {
    return $this->_mailServerType;
  }
  
  /**
   * set mailServerType
   * @var string mailServerType
   */
  public function setMailServerType($mailServerType) {
    $this->_mailServerType = $mailServerType;
  }
    
  /**
   * get mailServerHost
   * @return string
   */
  public function getMailServeHostr() {
    return $this->_mailServerHost;
  }
  
  /**
   * set mailServerHost
   * @var string mailServerHost
   */
  public function setMailServerHost($mailServerHost) {
    $this->_mailServerHost = $mailServerHost;
  }
    
  /**
   * get mailServerPort
   * @return string
   */
  public function getMailServerPort() {
    return $this->_mailServerPort;
  }
  
  /**
   * set mailServerPort
   * @var string mailServerPort
   */
  public function setMailServerPort($mailServerPort) {
    $this->_mailServerPort = $mailServerPort;
  }
    
  /**
   * get mailServerUsername
   * @return string
   */
  public function getMailServerUsername() {
    return $this->_mailServerUsername;
  }
  
  /**
   * set mailServerUsername
   * @var string mailServerUsername
   */
  public function setMailServerUsername($mailServerUsername) {
    $this->_mailServerUsername = $mailServerUsername;
  }
    
  /**
   * get mailServerPassword
   * @return string
   */
  public function getMailServerPassword() {
    return $this->_mailServerPassword;
  }
  
  /**
   * set mailServerPassword
   * @var string mailServerPassword
   */
  public function setMailServerPassword($mailServerPassword) {
    $this->_mailServerPassword = $mailServerPassword;
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
   * get mailDefaultFromAddress
   * @return string
   */
  public function getMailDefaultFromAddress() {
    return $this->_mailDefaultFromAddress;
  }
  
  /**
   * set mailDefaultFrom
   * @var string mailDefaultFromAddress
   */
  public function setMailDefaultFromAddress($mailDefaultFromAddress) {
    $this->_mailDefaultFromAddress = $mailDefaultFromAddress;
  }
  
  /**
   * get mailDefaultName
   * @return string
   */
  public function getMailDefaultFromName() {
    return $this->_mailDefaultFromName;
  }
  
  /**
   * set mailDefaultName
   * @var string mailDefaultName
   */
  public function setMailDefaultFromName($mailDefaultFromName) {
    $this->_mailDefaultFromName = $mailDefaultFromName;
  }
  
  /**
   * get mailOverrideToAddress
   * @return string
   */
  public function getMailOverrideToAddress() {
    return $this->_mailOverrideToAddress;
  }
  
  /**
   * set mailOverrideToAddress
   * @var string mailOverrideTo
   */
  public function setMailOverrideToAddress($mailOverrideToAddress) {
    $this->_mailOverrideToAddress = $mailOverrideToAddress;
  }
  
  /**
   * get mailOverrideToName
   * @return string
   */
  public function getMailOverrideToName() {
    return $this->_mailOverrideToName;
  }
  
  /**
   * set mailOverrideToName
   * @var string mailOverrideToName
   */
  public function setMailOverrideToName($mailOverrideToName) {
    $this->_mailOverrideTonName = $mailOverrideToName;
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
  
  /**
   * get recaptchaPrivateKey
   * @return string
   */
  public function getRecaptchaPrivateKey() {
    return $this->_recaptchaPrivateKey;
  }
  
  /**
   * set recaptchaPrivateKey
   * @var string $recaptchaPrivateKey
   */
  public function setRecaptchaPrivateKey($recaptchaPrivateKey) {
    $this->_recaptchaPrivateKey = $recaptchaPrivateKey;
  }
  
  /**
   * get recaptchaPublicKey
   * @return string
   */
  public function getRecaptchaPublicKey() {
    return $this->_recaptchaPublicKey;
  }
  
  /**
   * set recaptchaPublicKey
   * @var string $recaptchaPublicKey
   */
  public function setRecaptchaPublicKey($recaptchaPublicKey) {
    $this->_recaptchaPublicKey = $recaptchaPublicKey;
  }
  
  /**
   * get maximumApplicantFileUpload
   * @return string
   */
  public function getMaximumApplicantFileUpload() {
    return $this->_maximumApplicantFileUpload;
  }
  
  /**
   * set maximumApplicantFileUpload
   * @var string $maximumApplicantFileUpload
   */
  public function setMaximumApplicantFileUpload($maximumApplicantFileUpload) {
    $this->_maximumApplicantFileUpload = $maximumApplicantFileUpload;
  }

}
