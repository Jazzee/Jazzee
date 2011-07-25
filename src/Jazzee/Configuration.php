<?php
namespace Jazzee;

/**
 * Config class
 * @package jazzee
 */
class Configuration
{
  
  /**
   * Path to the configuration file
   * @var string
   */
  static protected $_configPath;

    /**
 * @var string
 */
protected $_mode;

/**
 * @var string
 */
protected $_maintenanceModeMessage;

/**
 * @var string
 */
protected $_broadcastMessage;

/**
 * @var string
 */
protected $_status;

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
protected $_mailDefaultFromAddress;

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
protected $_varPath;

/**
 * @var string
 */
protected $_adminEmail;

/**
 * @var string
 */
protected $_maximumApplicantFileUploadSize;

/**
 * @var string
 */
protected $_maximumAdminFileUploadSize;

/**
 * @var string
 */
protected $_adminAuthenticationClass;

/**
 * @var string
 */
protected $_shibbolethUsernameAttribute;

/**
 * @var string
 */
protected $_shibbolethFirstNameAttribute;

/**
 * @var string
 */
protected $_shibbolethLastNameAttribute;

/**
 * @var string
 */
protected $_shibbolethEmailAddressAttribute;

/**
 * @var string
 */
protected $_shibbolethLogoutUrl;

/**
 * @var string
 */
protected $_noAuthUserId;

/**
 * @var string
 */
protected $_noAuthIpAddresses;

/**
 * @var string
 */
protected $_simpleSAMLIncludePath;

/**
 * @var string
 */
protected $_simpleSAMLAuthenticationSource;

/**
 * @var string
 */
protected $_simpleSAMLUsernameAttribute;

/**
 * @var string
 */
protected $_simpleSAMLFirstNameAttribute;

/**
 * @var string
 */
protected $_simpleSAMLLastNameAttribute;

/**
 * @var string
 */
protected $_simpleSAMLEmailAddressAttribute;

/**
 * @var string
 */
protected $_publicKeyCertificatePath;

/**
 * @var string
 */
protected $_recaptchaPrivateKey;

/**
 * @var string
 */
protected $_recaptchaPublicKey;

/**
 * @var string
 */
protected $_adminDirectoryClass;

/**
 * @var string
 * */
protected $_ldapHostname;

/**
 * @var string
 */
protected $_ldapPort;
/**
 * @var string
 */
protected $_ldapBindRdn;
/**
 * @var string
 */
protected $_ldapBindPassword;
/**
 * @var string
 */
protected $_ldapUsernameAttribute;
/**
 * @var string
 */
protected $_ldapFirstNameAttribute;
/**
 * @var string
 */
protected $_ldapLastNameAttribute;
/**
 * @var string
 */
protected $_ldapEmailAddressAttribute;
/**
 * @var string
 */
protected $_ldapSearchBase;

  /**
   * Construct
   * Load data from the ini file
   */
  public function __construct(){
    if(!self::$_configPath){
      //try the default path
      self::setPath(__DIR__ . '/../../etc/config.ini.php');
    }
    $arr = parse_ini_file(self::$_configPath);
    foreach($arr as $name => $value){
      $setter = 'set' . \ucfirst($name);
      if(!method_exists($this, $setter)) throw new Exception("Configuration variable ({$name}) found in file, but it is not a recognized option.");
      $this->$setter($value);
    }  
  }
  
  /**
   * Set the configuration path
   * @param string $path
   */
  public static function setPath($path){
    if(!$realPath = \realpath($path) or !\is_readable($realPath)){
      if($realPath) $path = $realPath;
      throw new Exception("Unable to load {$path}.", E_ERROR); 
    }
    self::$_configPath=$realPath;
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
   * get maintenanceModeMessage
   * @return string
   */
  public function getMaintenanceModeMessage() {
    return $this->_maintenanceModeMessage;
  }
  
  /**
   * set maintenanceModeMessage
   * @var string $maintenanceModeMessage
   */
  public function setMaintenanceModeMessage($maintenanceModeMessage) {
    $this->_maintenanceModeMessage = $maintenanceModeMessage;
  }

  /**
   * get broadcastMessage
   * @return string
   */
  public function getBroadcastMessage() {
    return $this->_broadcastMessage;
  }
  
  /**
   * set broadcastMessage
   * @var string $broadcastMessage
   */
  public function setbroadcastMessage($broadcastMessage) {
    $this->_broadcastMessage = $broadcastMessage;
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
  public function getMailServeHost() {
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
   * get maximumApplicantFileUploadSize
   * @return string
   */
  public function getMaximumApplicantFileUploadSize() {
    return $this->_maximumApplicantFileUploadSize;
  }
  
  /**
   * set maximumApplicantFileUploadSize
   * @var string $maximumApplicantFileUploadSize
   */
  public function setMaximumApplicantFileUploadSize($maximumApplicantFileUploadSize) {
    if(\convertIniShorthandValue($maximumApplicantFileUploadSize) > \convertIniShorthandValue(\ini_get('upload_max_filesize'))){
      throw new \Jazzee\Exception('Configured Applicant File Upload Size is larger than PHP upload_max_filesize');
    }
    $this->_maximumApplicantFileUploadSize = \convertIniShorthandValue($maximumApplicantFileUploadSize);
  }
  
  /**
   * get maximumAdminFileUploadSize
   * @return string
   */
  public function getMaximumAdminFileUploadSize() {
    return $this->_maximumAdminFileUploadSize;
  }
  
  /**
   * set maximumAdminFileUploadSize
   * @var string $maximumAdminFileUploadSize
   */
  public function setMaximumAdminFileUploadSize($maximumAdminFileUploadSize) {
    if(\convertIniShorthandValue($maximumAdminFileUploadSize) > \convertIniShorthandValue(\ini_get('upload_max_filesize'))){
      throw new \Jazzee\Exception('Configured Admin File Upload Size is larger than PHP upload_max_filesize');
    }
    $this->_maximumAdminFileUploadSize = \convertIniShorthandValue($maximumAdminFileUploadSize);
  }
  
  /**
   * get adminAuthClass
   * @return string
   */
  public function getAdminAuthenticationClass() {
    return $this->_adminAuthClass;
  }
  
  /**
   * set adminAuthClass
   * @var string $adminAuthClass
   */
  public function setAdminAuthenticationClass($adminAuthClass) {
    $this->_adminAuthClass = $adminAuthClass;
  }
  
  /**
   * get shibbolethUsernameAttribute
   * @return string
   */
  public function getShibbolethUsernameAttribute() {
    return $this->_shibbolethUsernameAttribute;
  }
  
  /**
   * set shibbolethUsernameAttribute
   * @var string $shibbolethUsernameAttribute
   */
  public function setShibbolethUsernameAttribute($shibbolethUsernameAttribute) {
    $this->_shibbolethUsernameAttribute = $shibbolethUsernameAttribute;
  }  
  
  /**
   * get shibbolethFirstNameAttribute
   * @return string
   */
  public function getShibbolethFirstNameAttribute() {
    return $this->_shibbolethFirstNameAttribute;
  }
  
  /**
   * set shibbolethFirstNameAttribute
   * @var string $shibbolethFirstNameAttribute
   */
  public function setShibbolethFirstNameAttribute($shibbolethFirstNameAttribute) {
    $this->_shibbolethFirstNameAttribute = $shibbolethFirstNameAttribute;
  }  
  
  /**
   * get shibbolethLastNameAttribute
   * @return string
   */
  public function getShibbolethLastNameAttribute() {
    return $this->_shibbolethLastNameAttribute;
  }
  
  /**
   * set shibbolethLastNameAttribute
   * @var string $shibbolethLastNameAttribute
   */
  public function setShibbolethLastNameAttribute($shibbolethLastNameAttribute) {
    $this->_shibbolethLastNameAttribute = $shibbolethLastNameAttribute;
  }  
  
  /**
   * get shibbolethEmailAddressAttribute
   * @return string
   */
  public function getShibbolethEmailAddressAttribute() {
    return $this->_shibbolethEmailAddressAttribute;
  }
  
  /**
   * set shibbolethEmailAddressAttribute
   * @var string $shibbolethEmailAddressAttribute
   */
  public function setShibbolethEmailAddressAttribute($shibbolethEmailAddressAttribute) {
    $this->_shibbolethEmailAddressAttribute = $shibbolethEmailAddressAttribute;
  }  
  
  /**
   * get shibbolethLogoutUrl
   * @return string
   */
  public function getShibbolethLogoutUrl() {
    return $this->_shibbolethLogoutUrl;
  }
  
  /**
   * set shibbolethLogoutUrl
   * @var string $shibbolethLogoutUrl
   */
  public function setShibbolethLogoutUrl($shibbolethLogoutUrl) {
    $this->_shibbolethLogoutUrl = $shibbolethLogoutUrl;
  }
  
  /**
   * get noAuthUserId
   * @return string
   */
  public function getNoAuthUserId() {
    return $this->_noAuthUserId;
  }
  
  /**
   * set noAuthUserId
   * @var string $noAuthUserId
   */
  public function setNoAuthUserId($noAuthUserId) {
    $this->_noAuthUserId = $noAuthUserId;
  }
  
  /**
   * get noAuthIpAddresses
   * @return string
   */
  public function getNoAuthIpAddresses() {
    return $this->_noAuthIpAddresses;
  }
  
  /**
   * set noAuthIpAddresses
   * @var string $noAuthIpAddresses
   */
  public function setNoAuthIpAddresses($noAuthIpAddresses) {
    $this->_noAuthIpAddresses = $noAuthIpAddresses;
  }
  
  /**
   * get simpleSAMLIncludePath
   * @return string
   */
  public function getSimpleSAMLIncludePath() {
    return $this->_simpleSAMLIncludePath;
  }
  
  /**
   * set simpleSAMLIncludePath
   * @var string $simpleSAMLIncludePath
   */
  public function setSimpleSAMLIncludePath($simpleSAMLIncludePath) {
    $this->_simpleSAMLIncludePath = $simpleSAMLIncludePath;
  }  
  
  /**
   * get simpleSAMLAuthenticationSource
   * @return string
   */
  public function getSimpleSAMLAuthenticationSource() {
    return $this->_simpleSAMLAuthenticationSource;
  }
  
  /**
   * set simpleSAMLAuthenticationSource
   * @var string $simpleSAMLAuthenticationSource
   */
  public function setSimpleSAMLAuthenticationSource($simpleSAMLAuthenticationSource) {
    $this->_simpleSAMLAuthenticationSource = $simpleSAMLAuthenticationSource;
  }  
  
  /**
   * get simpleSAMLUsernameAttribute
   * @return string
   */
  public function getSimpleSAMLUsernameAttribute() {
    return $this->_simpleSAMLUsernameAttribute;
  }
  
  /**
   * set simpleSAMLUsernameAttribute
   * @var string $simpleSAMLUsernameAttribute
   */
  public function setSimpleSAMLUsernameAttribute($simpleSAMLUsernameAttribute) {
    $this->_simpleSAMLUsernameAttribute = $simpleSAMLUsernameAttribute;
  }  
  
  /**
   * get simpleSAMLFirstNameAttribute
   * @return string
   */
  public function getSimpleSAMLFirstNameAttribute() {
    return $this->_simpleSAMLFirstNameAttribute;
  }
  
  /**
   * set simpleSAMLFirstNameAttribute
   * @var string $simpleSAMLFirstNameAttribute
   */
  public function setSimpleSAMLFirstNameAttribute($simpleSAMLFirstNameAttribute) {
    $this->_simpleSAMLFirstNameAttribute = $simpleSAMLFirstNameAttribute;
  }  
  
  /**
   * get simpleSAMLLastNameAttribute
   * @return string
   */
  public function getSimpleSAMLLastNameAttribute() {
    return $this->_simpleSAMLLastNameAttribute;
  }
  
  /**
   * set simpleSAMLLastNameAttribute
   * @var string $simpleSAMLLastNameAttribute
   */
  public function setSimpleSAMLLastNameAttribute($simpleSAMLLastNameAttribute) {
    $this->_simpleSAMLLastNameAttribute = $simpleSAMLLastNameAttribute;
  }  
  
  /**
   * get simpleSAMLEmailAddressAttribute
   * @return string
   */
  public function getSimpleSAMLEmailAddressAttribute() {
    return $this->_simpleSAMLEmailAddressAttribute;
  }
  
  /**
   * set simpleSAMLEmailAddressAttribute
   * @var string $simpleSAMLEmailAddressAttribute
   */
  public function setSimpleSAMLEmailAddressAttribute($simpleSAMLEmailAddressAttribute) {
    $this->_simpleSAMLEmailAddressAttribute = $simpleSAMLEmailAddressAttribute;
  }
  
  /**
   * get publicKeyCertificatePath
   * @return string
   */
  public function getPublicKeyCertificatePath() {
    return $this->_publicKeyCertificatePath;
  }
  
  /**
   * set publicKeyCertificatePath
   * @var string $publicKeyCertificatePath
   */
  public function setPublicKeyCertificatePath($publicKeyCertificatePath) {
    $this->_publicKeyCertificatePath = $publicKeyCertificatePath;
  }
  
  /**
   * get adminDirectoryClass
   * @return string
   */
  public function getAdminDirectoryClass() {
    return $this->_adminDirectoryClass;
  }
  
  /**
   * set adminDirectoryClass
   * @var string $adminDirectoryClass
   */
  public function setAdminDirectoryClass($adminDirectoryClass) {
    $this->_adminDirectoryClass = $adminDirectoryClass;
  }  
  
  /**
   * get LdapHostname
   * @return string
   */
  public function getLdapHostname() {
    return $this->_ldapHostname;
  }
  
  /**
   * set ldapHostname
   * @var string $ldapHostname
   */
  public function setLdapHostname($ldapHostname) {
    $this->_ldapHostname = $ldapHostname;
  }  
  
  /**
   * get LdapPort
   * @return string
   */
  public function getLdapPort() {
    return $this->_ldapPort;
  }
  
  /**
   * set ldapPort
   * @var string $ldapPort
   */
  public function setLdapPort($ldapPort) {
    $this->_ldapPort = $ldapPort;
  }  
  
  /**
   * get LdapBindRdn
   * @return string
   */
  public function getLdapBindRdn() {
    return $this->_ldapBindRdn;
  }
  
  /**
   * set ldapBindRdn
   * @var string $ldapBindRdn
   */
  public function setLdapBindRdn($ldapBindRdn) {
    $this->_ldapBindRdn = $ldapBindRdn;
  }  
  
  /**
   * get LdapBindPassword
   * @return string
   */
  public function getLdapBindPassword() {
    return $this->_ldapBindPassword;
  }
  
  /**
   * set ldapBindPassword
   * @var string $ldapBindPassword
   */
  public function setLdapBindPassword($ldapBindPassword) {
    $this->_ldapBindPassword = $ldapBindPassword;
  }  
  
  /**
   * get LdapUsernameAttribute
   * @return string
   */
  public function getLdapUsernameAttribute() {
    return $this->_ldapUsernameAttribute;
  }
  
  /**
   * set ldapUsernameAttribute
   * @var string $ldapUsernameAttribute
   */
  public function setLdapUsernameAttribute($ldapUsernameAttribute) {
    $this->_ldapUsernameAttribute = $ldapUsernameAttribute;
  }  
  
  /**
   * get LdapFirstNameAttribute
   * @return string
   */
  public function getLdapFirstNameAttribute() {
    return $this->_ldapFirstNameAttribute;
  }
  
  /**
   * set ldapFirstNameAttribute
   * @var string $ldapFirstNameAttribute
   */
  public function setLdapFirstNameAttribute($ldapFirstNameAttribute) {
    $this->_ldapFirstNameAttribute = $ldapFirstNameAttribute;
  }  
  
  /**
   * get LdapLastNameAttribute
   * @return string
   */
  public function getLdapLastNameAttribute() {
    return $this->_ldapLastNameAttribute;
  }
  
  /**
   * set ldapLastNameAttribute
   * @var string $ldapLastNameAttribute
   */
  public function setLdapLastNameAttribute($ldapLastNameAttribute) {
    $this->_ldapLastNameAttribute = $ldapLastNameAttribute;
  }  
  
  /**
   * get LdapEmailAddressAttribute
   * @return string
   */
  public function getLdapEmailAddressAttribute() {
    return $this->_ldapEmailAddressAttribute;
  }
  
  /**
   * set ldapEmailAddressAttribute
   * @var string $ldapEmailAddressAttribute
   */
  public function setLdapEmailAddressAttribute($ldapEmailAddressAttribute) {
    $this->_ldapEmailAddressAttribute = $ldapEmailAddressAttribute;
  }  
  
  /**
   * get LdapSearchBase
   * @return string
   */
  public function getLdapSearchBase() {
    return $this->_ldapSearchBase;
  }
  
  /**
   * set ldapSearchBase
   * @var string $ldapSearchBase
   */
  public function setLdapSearchBase($ldapSearchBase) {
    $this->_ldapSearchBase = $ldapSearchBase;
  }

}
