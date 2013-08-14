<?php
namespace Jazzee;

/**
 * JazzeeConfiguration is where any application configuration options shoudl be
 * stored.  Never read from the configuration file directly since this class can
 * be overrideen to provide defaults or ignore the file completly.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class JazzeeConfiguration
{

  /**
   * @var string
   * The mode allows you to limit access to the application easily.  Possible values are:
   * <ul>
   * <li><b>LIVE</b> the default everything running mode</li>
   * <li><b>APPLY_MAINTENANCE</b> which does not allow applicants or recommenders to access the system</li>
   * <li><b>MAINTENANCE which</b> prevents everyone from accessing the system</li>
   * </ul>
   */
  protected $_mode;

  /**
   * @var string
   * Message displayed to anyone who cannot access the system becuase of a mode setting
   */
  protected $_maintenanceModeMessage;

  /**
   * @var string
   * Message displayed to everyone on every page.  Useful for advertising future
   * downtime or any other significant system wide events.
   */
  protected $_broadcastMessage;

  /**
   * @var string
   * Provides information  to JAZZEE components about the current system state.  Possible values are:
   * <ul>
   * <li><b>PRODUCTION</b> the default live application status</li>
   * <li><b>PREVIEW</b> limits some functionality in a draft installation.  Useful for QA
   * where something like payments shouldn't work - but caching should still work and email should still go out</li>
   * <li><b>DEVELOPMENT</b> If you're working on Jazzee this is the status for you.  If redirects outgoing
   * email and limits caching</li>
   * </ul>
   */
  protected $_status;

  /**
   * @var string
   * The Database host name.  Defaults to 'localhost'
   */
  protected $_dbHost;

  /**
   * @var string
   * The database port
   */
  protected $_dbPort;

  /**
   * @var string
   * The database name
   */
  protected $_dbName;

  /**
   * @var string
   * The database user
   */
  protected $_dbUser;

  /**
   * @var string
   * The database password
   */
  protected $_dbPassword;

  /**
   * @var string
   * The database driver.  The allowed types can be found at the Doctrine Project website
   * @link http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#driver
   */
  protected $_dbDriver;

  /**
   * @var string
   * The database character set
   */
  protected $_dbCharset;

  /**
   * @var string
   * What to name the PHP session.  Defaults to 'JAZZEE'
   */
  protected $_sessionName;

  /**
   * @var string
   * The maximum session lifetime for an applicant in seconds.  Defaults to '0' which means applicants
   * stay logged in until they close their browser or logout manually
   */
  protected $_applicantSessionLifetime;

  /**
   * @var string
   * The maximum session lifetime for administrators.  Defaults to 7200 or two hours.
   */
  protected $_adminSessionLifetime;

  /**
   * @var string
   * The type of outgoing mail server we will be using defaults to php. Possible values are:
   * <ul>
   * <li><b>php</b> to use the builtin php mail() function</li>
   * <li><b>sendmail</b> to use the sendmail binary on the server</li>
   * <li><b>smtp</b> to use a remote smtp server</li>
   * <li><b>smtp+ssl</b> for a secure connection to a remote smtp server</li>
   * </ul>
   */
  protected $_mailServerType;

  /**
   * @var string
   * The hostname for the mailserver - only required for external smtp mailServerTypes
   */
  protected $_mailServerHost;

  /**
   * @var string
   *  The port for the mailserver - only required for external smtp mailServerTypes
   */
  protected $_mailServerPort;

  /**
   * @var string
   *  The username for the mailserver - only required for external smtp mailServerTypes
   */
  protected $_mailServerUsername;

  /**
   * @var string
   *  The password for the mailserver - only required for external smtp mailServerTypes
   */
  protected $_mailServerPassword;

  /**
   * @var string
   * If set all outgoing mail subject lines will be prefixed with this string
   */
  protected $_mailSubjectPrefix;

  /**
   * @var string
   * If no address is set for the outgoing message it will default to this address.
   * You should set this otherwise a system default like postmaster@local.nothing could
   * get sent.
   */
  protected $_mailDefaultFromAddress;

  /**
   * @var string
   * If no address is set for the outgoing message it will use this name.
   */
  protected $_mailDefaultFromName;

  /**
   * @var string
   * This should only be used in DEVELOPMENT environments.  It will send ALL outoing mail
   * to this address.  NOT the intended recipient.
   */
  protected $_mailOverrideToAddress;

  /**
   * @var string
   * The system path to the VAR directory.  Defaults to JAZZEESOURCE/var.  This directory
   * must be writable be the webserver.  It is where session data, temporary files, uploads,
   * and logs will get writtend to.
   */
  protected $_varPath;

  /**
   * @var string
   * The maximum size for applicant file uploads.  Programs will not be able to override this setting.
   * Defaults to the value of PHP's builtin upload_max_filesize which is generally pretty large
   * so you should set this to something sensible like 1M
   */
  protected $_maximumApplicantFileUploadSize;

  /**
   * @var string
   * The default size for applicant file uploads if one is not set.
   * Defaults to the value of PHP's builtin upload_max_filesize which is generally pretty large
   * so you should set this to something sensible like 1M
   */
  protected $_defaultApplicantFileUploadSize;

  /**
   * @var boolean
   * Is the applicant allowed to change their name
   */
  protected $_allowApplicantNameChange;

  /**
   * @var boolean
   * Is the applicant allowed to change their email address
   */
  protected $_allowApplicantEmailChange;

  /**
   * @var boolean
   * Is the applicant allowed to change their password
   */
  protected $_allowApplicantPasswordChange;

  /**
   * @var boolean
   * Is the applicant allowed to print thier application
   */
  protected $_allowApplicantPrintApplication;

  /**
   * @var string
   * The maximum size for administrator file uploads.
   * Defaults to the value of PHP's builtin upload_max_filesize which is generally pretty large
   * so you should set this to something sensible like 5M
   */
  protected $_maximumAdminFileUploadSize;

  /**
   * @var string
   * Authentication for administrators can be handled by several different methods.
   * Builtin options are:
   * <ul>
   * <li><b>Shibboleth</b> - for schools which have shibboleth IDPs.</li>
   * <li><b>SimpleSAML</b> - an easier to configure shibboleth SP.  If the webserver you are
   * using doesn't have shibboleth installed this may be the right choice for you.</li>
   * <li><b>OpenID</b> - This will allow anyone with a google, yahoo or other internet account to log in.</li>
   * <li><b>NoAuthentication</b> - only if Jazzee is in DEVELOPER status.  This allows the user to
   * pick ANY user account and login as them.</li>
   * </ul>
   */
  protected $_adminAuthenticationClass;

  /**
   * @var string
   * If Shibboleth is set as the adminAuthenticationClass this is the attribute name
   * we will use as the userName.  This is almost always the default of 'eppn'
   */
  protected $_shibbolethUsernameAttribute;

  /**
   * @var string
   * If Shibboleth is set as the adminAuthenticationClass this is the attribute name
   * we will use as the first name.  This is almost always the default of 'givenName'
   */
  protected $_shibbolethFirstNameAttribute;

  /**
   * @var string
   * If Shibboleth is set as the adminAuthenticationClass this is the attribute name
   * we will use as the last name.  This is almost always the default of 'sn'
   */
  protected $_shibbolethLastNameAttribute;

  /**
   * @var string
   * If Shibboleth is set as the adminAuthenticationClass this is the attribute name
   * we will use as the email address.  This is almost always the default of 'mail'
   */
  protected $_shibbolethEmailAddressAttribute;

  /**
   * @var string
   * If Shibboleth is set as the adminAuthenticationClass this is the url applicants will
   * be directed to in order to login.  This is almost always the default of '/Shibboleth.sso/Login'
   */
  protected $_shibbolethLoginUrl;

  /**
   * @var string
   * If Shibboleth is set as the adminAuthenticationClass this is the url applicants will
   * be directed to in order to logout.  This is almost always the default of '/Shibboleth.sso/Logout'
   */
  protected $_shibbolethLogoutUrl;

  /**
   * @var string
   * if NoAuthentication is set as the adminAuthenticationClass this restricts what
   * ip addresses can be used to authenticate.  Defaults to 127.0.0.1 (the localhost)
   */
  protected $_noAuthIpAddresses;

  /**
   * @var string
   * if ApiFormAuthentication is set as the adminAuthenticationClass this restricts what
   * ip addresses can be used to authenticate.  Defaults to 127.0.0.1 (the localhost)
   */
  protected $_apiFormAuthenticationIpAddresses;

  /**
   * @var string
   * If SimpleSAML is set as the adminAuthenticationClass this is the path to the
   * autoloader so it can be included when needed.
   */
  protected $_simpleSAMLIncludePath;

  /**
   * @var string
   * If SimpleSAML is set as th adminAuthenticationClass this is the IDP
   */
  protected $_simpleSAMLAuthenticationSource;

  /**
   * @var string
   * If SimpleSAML is set as the adminAuthenticationClass this is the attribute name
   * we will use as the userName.  This is almost always the default of 'eduPersonPrincipalName'
   */
  protected $_simpleSAMLUsernameAttribute;

  /**
   * @var string
   * If SimpleSAML is set as the adminAuthenticationClass this is the attribute name
   * we will use as the first name.  This is almost always the default of 'givenName'
   */
  protected $_simpleSAMLFirstNameAttribute;

  /**
   * @var string
   * If SimpleSAML is set as the adminAuthenticationClass this is the attribute name
   * we will use as the last name.  This is almost always the default of 'sn'
   */
  protected $_simpleSAMLLastNameAttribute;

  /**
   * @var string
   * If SimpleSAML is set as the adminAuthenticationClass this is the attribute name
   * we will use as the email address.  This is almost always the default of 'mail'
   */
  protected $_simpleSAMLEmailAddressAttribute;

  /**
   * @var string
   * The SSL public key certificate to use in encrypting data.  Only the public key
   * should reside on the Jazzee server as Jazzee has no method for decrypting data.
   */
  protected $_publicKeyCertificatePath;

  /**
   * @var string
   * Your reCaptch private key to use for new applicant accounts.
   * @link http://www.google.com/recaptcha
   */
  protected $_recaptchaPrivateKey;

  /**
   * @var string
   * Your reCaptch public key to use for new applicant accounts.
   * @link http://www.google.com/recaptcha
   */
  protected $_recaptchaPublicKey;

  /**
   * @var string
   * The class to use when looking up users.  If your campus has an LDAP directory you
   * should use Ldap so you can search for new users there.  Otherwise Local looks up users
   * who alrady have Jazzee accounts.  If your using OpenID for you adminAuthenticationClass
   * then Local is the only way to go.
   */
  protected $_adminDirectoryClass;

  /**
   * @var string
   * If Ldap is set as your adminDirectoryClass then this is the host name for you server
   * */
  protected $_ldapHostname;

  /**
   * @var string
   * If Ldap is set as your adminDirectoryClass then this is the port for you server
   */
  protected $_ldapPort;

  /**
   * @var string
   * If Ldap is set as your adminDirectoryClass then this is the bind RDN for you server
   */
  protected $_ldapBindRdn;

  /**
   * @var string
   * If Ldap is set as your adminDirectoryClass then this is the bind password for you server
   */
  protected $_ldapBindPassword;

  /**
   * @var string
   * If Ldap is set as your adminDirectoryClass then this is the attribute name
   * we will use as the username.  This is should match what will be returend in
   * for the shibbolethUserName
   */
  protected $_ldapUsernameAttribute;

  /**
   * @var string
   * If Ldap is set as your adminDirectoryClass then this is the attribute name
   * we will use as the first name.  This is almost always the default of 'givenName'
   */
  protected $_ldapFirstNameAttribute;

  /**
   * @var string
   * If Ldap is set as your adminDirectoryClass then this is the attribute name
   * we will use as the last name.  This is almost always the default of 'sn'
   */
  protected $_ldapLastNameAttribute;

  /**
   * @var string
   * If Ldap is set as your adminDirectoryClass then this is the attribute name
   * we will use as the email address.  This is almost always the default of 'mail'
   */
  protected $_ldapEmailAddressAttribute;

  /**
   * @var string
   * If Ldap is set as your adminDirectoryClass then this is the search base for
   * your directory.  Usually something like 'ou=people, dc=ucsf, dc=edu'
   */
  protected $_ldapSearchBase;

  /**
   *  @var string
   * If you want some advanced PDF functions you will have to purchase a PDFlib+PDI license
   * and enter your license key here.
   * @link http://www.pdflib.com/
   */
  protected $_pdflibLicenseKey;

  /**
   * Path to pdftk executable
   * @var string
   */
  protected $_pdftkPath;

  /**
   *  @var array
   * Hostnames or IP addresses which are allowed to hit the cron page and trigger
   * a run.  If you're using links to trigger cron from the webserver jazzee is on then
   * leaving this as the default 'localhost' is fine.
   */
  protected $_adminCronAllowed;

  /**
   *  @var boolean
   * Can the scramble command be run on the console
   */
  protected $_allowScramble;

  /**
   *  @var boolean
   * Should uploaded files be scanned for viruses
   */
  protected $_virusScanUploads;

    /**
     * Construct
     * Load data from the ini file
     */
    public function __construct()
    {
        $this->setDefaults();
        if($path = $this->getPath()){
            $arr = parse_ini_file($path);
            if (!empty($arr) and $arr !== false) {
                foreach ($arr as $name => $value) {
                  $setter = 'set' . \ucfirst($name);
                  if (!method_exists($this, $setter)) {
                    throw new Exception("Configuration variable ({$name}) found in file, but it is not a recognized option.");
                  }
                  $this->$setter($value);
                }
            }
        }
    }

  /**
   * Set the defaults for those options that have defaults
   */
  protected function setDefaults()
  {
    $defaults = array(
      'mode' => 'LIVE',
      'status' => 'PRODUCTION',
      'dbHost' => 'localhost',
      'sessionName' => 'JAZZEE',
      'adminSessionLifetime' => '7200',
      'applicantSessionLifetime' => '0',
      'mailServerType' => 'php',
      'maximumApplicantFileUploadSize' => \Foundation\Utility::convertIniShorthandValue(\ini_get('upload_max_filesize')),
      'defaultApplicantFileUploadSize' => \Foundation\Utility::convertIniShorthandValue('1m'),
      'maximumAdminFileUploadSize' => \Foundation\Utility::convertIniShorthandValue(\ini_get('upload_max_filesize')),
      'allowApplicantNameChange' => false,
      'allowApplicantEmailChange' => false,
      'allowApplicantPasswordChange' => false,
      'allowApplicantPrintApplication' => false,
      'varPath' => \realpath(__DIR__ . '/../../var'),
      'shibbolethUsernameAttribute' => 'eppn',
      'shibbolethFirstNameAttribute' => 'givenName',
      'shibbolethLastNameAttribute' => 'sn',
      'shibbolethEmailAddressAttribute' => 'mail',
      'shibbolethLoginUrl' => '/Shibboleth.sso/Login',
      'shibbolethLogoutUrl' => '/Shibboleth.sso/Logout',
      'noAuthIpAddresses' => '127.0.0.1',
      'simpleSAMLAuthenticationSource' => 'default-sp',
      'simpleSAMLUsernameAttribute' => 'eduPersonPrincipalName',
      'simpleSAMLFirstNameAttribute' => 'givenName',
      'simpleSAMLLastNameAttribute' => 'sn',
      'simpleSAMLEmailAddressAttribute' => 'mail',
      'publicKeyCertificatePath' => \realpath(__DIR__ . '/../../etc/publickey.crt'),
      'adminAuthenticationClass' => '\Jazzee\AdminAuthentication\OpenID',
      'adminDirectoryClass' => '\Jazzee\AdminDirectory\Local',
      'adminCronAllowed' => 'localhost',
      'ldapUsernameAttribute' => 'eppn',
      'ldapFirstNameAttribute' => 'givenName',
      'ldapLastNameAttribute' => 'sn',
      'ldapEmailAddressAttribute' => 'mail',
      'allowScramble' => false,
      'virusScanUploads' => true
    );
    foreach ($defaults as $name => $value) {
      $method = 'set' . ucfirst($name);
      if (!method_exists($this, $method)) {
        throw new Exception("Tried to set default value for {$name} but {$method} method does not exist.");
      }
      $this->$method($value);
    }
  }

  /**
   * Get the path to the configuration file
   *
   * This is here so it is easy to override this path or customize a path for
   * different environments.
   * @return string path
   */
  protected function getPath()
  {
    $defaultPath =  __DIR__ . '/../../etc/config.ini.php';
    if ($realPath = \realpath($defaultPath)) {
      if (!\is_readable($realPath)) {
        $perms = \substr(\sprintf('%o', \fileperms($realPath)), -4);
        $owner = \fileowner($realPath);
        $group = \filegroup($realPath);
        if (function_exists('posix_getpwuid')) {
          $arr = posix_getpwuid($owner);
          $owner = $arr['name'];
        }
        if (function_exists('posix_getgrgid')) {
          $arr = posix_getgrgid($group);
          $group = $arr['name'];
        }
        throw new Exception("The configuration file at {$realPath} is not readable.  The file is owned by user {$owner} and group {$group} and has permissions {$perms}.");
      } else {
        return $realPath;
      }
    }

    return false;
  }

  /**
   * get mode
   * @return string
   */
  public function getMode()
  {
    return $this->_mode;
  }

  /**
   * set mode
   * @var string mode
   */
  public function setMode($mode)
  {
    if (!in_array($mode, array('LIVE', 'APPLY_MAINTENANCE', 'MAINTENANCE'))) {
      throw new Exception("{$mode} is not a valid configuration setting for mode.");
    }
    $this->_mode = $mode;
  }

  /**
   * get maintenanceModeMessage
   * @return string
   */
  public function getMaintenanceModeMessage()
  {
    return $this->_maintenanceModeMessage;
  }

  /**
   * set maintenanceModeMessage
   * @var string $maintenanceModeMessage
   */
  public function setMaintenanceModeMessage($maintenanceModeMessage)
  {
    $this->_maintenanceModeMessage = $maintenanceModeMessage;
  }

  /**
   * get broadcastMessage
   * @return string
   */
  public function getBroadcastMessage()
  {
    return $this->_broadcastMessage;
  }

  /**
   * set broadcastMessage
   * @var string $broadcastMessage
   */
  public function setbroadcastMessage($broadcastMessage)
  {
    $this->_broadcastMessage = $broadcastMessage;
  }

  /**
   * get status
   * @return string
   */
  public function getStatus()
  {
    return $this->_status;
  }

  /**
   * set status
   * @var string status
   */
  public function setStatus($status)
  {
    if (!in_array($status, array('PRODUCTION', 'PREVIEW', 'DEVELOPMENT'))) {
      throw new Exception("{$status} is not a valid configuration setting for status.");
    }
    $this->_status = $status;
  }

  /**
   * get dbHost
   * @return string
   */
  public function getDbHost()
  {
    return $this->_dbHost;
  }

  /**
   * set dbHost
   * @var string dbHost
   */
  public function setDbHost($dbHost)
  {
    $this->_dbHost = $dbHost;
  }

  /**
   * get dbPort
   * @return string
   */
  public function getDbPort()
  {
    return $this->_dbPort;
  }

  /**
   * set dbPort
   * @var string dbPort
   */
  public function setDbPort($dbPort)
  {
    $this->_dbPort = $dbPort;
  }

  /**
   * get dbName
   * @return string
   */
  public function getDbName()
  {
    return $this->_dbName;
  }

  /**
   * set dbName
   * @var string dbName
   */
  public function setDbName($dbName)
  {
    $this->_dbName = $dbName;
  }

  /**
   * get dbUser
   * @return string
   */
  public function getDbUser()
  {
    return $this->_dbUser;
  }

  /**
   * set dbUser
   * @var string dbUser
   */
  public function setDbUser($dbUser)
  {
    $this->_dbUser = $dbUser;
  }

  /**
   * get dbPassword
   * @return string
   */
  public function getDbPassword()
  {
    return $this->_dbPassword;
  }

  /**
   * set dbPassword
   * @var string dbPassword
   */
  public function setDbPassword($dbPassword)
  {
    $this->_dbPassword = $dbPassword;
  }

  /**
   * get dbDriver
   * @return string
   */
  public function getDbDriver()
  {
    return $this->_dbDriver;
  }

  /**
   * set dbDriver
   * @var string dbDriver
   */
  public function setDbDriver($dbDriver)
  {
    $this->_dbDriver = $dbDriver;
  }

  /**
   * get sessionName
   * @return string
   */
  public function getSessionName()
  {
    return $this->_sessionName;
  }

  /**
   * set sessionName
   * @var string $sessionName
   */
  public function setSessionName($sessionName)
  {
    $this->_sessionName = $sessionName;
  }

  /**
   * get adminSessionLifetime
   * @return string
   */
  public function getAdminSessionLifetime()
  {
    return $this->_adminSessionLifetime;
  }

  /**
   * set adminSessionLifetime
   * @var string $lifetime
   */
  public function setAdminSessionLifetime($lifetime)
  {
    $this->_adminSessionLifetime = $lifetime;
  }

  /**
   * get Applicant SessionLifetime
   * @return string
   */
  public function getApplicantSessionLifetime()
  {
    return $this->_applicantSessionLifetime;
  }

  /**
   * set Applicant SessionLifetime
   * @var string $lifetime
   */
  public function setApplicantSessionLifetime($lifetime)
  {
    $this->_applicantSessionLifetime = $lifetime;
  }

  /**
   * get mailServerType
   * @return string
   */
  public function getMailServerType()
  {
    return $this->_mailServerType;
  }

  /**
   * set mailServerType
   * @var string mailServerType
   */
  public function setMailServerType($mailServerType)
  {
    $this->_mailServerType = $mailServerType;
  }

  /**
   * get mailServerHost
   * @return string
   */
  public function getMailServeHost()
  {
    return $this->_mailServerHost;
  }

  /**
   * set mailServerHost
   * @var string mailServerHost
   */
  public function setMailServerHost($mailServerHost)
  {
    $this->_mailServerHost = $mailServerHost;
  }

  /**
   * get mailServerPort
   * @return string
   */
  public function getMailServerPort()
  {
    return $this->_mailServerPort;
  }

  /**
   * set mailServerPort
   * @var string mailServerPort
   */
  public function setMailServerPort($mailServerPort)
  {
    $this->_mailServerPort = $mailServerPort;
  }

  /**
   * get mailServerUsername
   * @return string
   */
  public function getMailServerUsername()
  {
    return $this->_mailServerUsername;
  }

  /**
   * set mailServerUsername
   * @var string mailServerUsername
   */
  public function setMailServerUsername($mailServerUsername)
  {
    $this->_mailServerUsername = $mailServerUsername;
  }

  /**
   * get mailServerPassword
   * @return string
   */
  public function getMailServerPassword()
  {
    return $this->_mailServerPassword;
  }

  /**
   * set mailServerPassword
   * @var string mailServerPassword
   */
  public function setMailServerPassword($mailServerPassword)
  {
    $this->_mailServerPassword = $mailServerPassword;
  }

  /**
   * get mailSubjectPrefix
   * @return string
   */
  public function getMailSubjectPrefix()
  {
    return $this->_mailSubjectPrefix;
  }

  /**
   * set mailSubjectPrefix
   * @var string mailSubjectPrefix
   */
  public function setMailSubjectPrefix($mailSubjectPrefix)
  {
    $this->_mailSubjectPrefix = $mailSubjectPrefix;
  }

  /**
   * get mailDefaultFromAddress
   * @return string
   */
  public function getMailDefaultFromAddress()
  {
    return $this->_mailDefaultFromAddress;
  }

  /**
   * set mailDefaultFrom
   * @var string mailDefaultFromAddress
   */
  public function setMailDefaultFromAddress($mailDefaultFromAddress)
  {
    $this->_mailDefaultFromAddress = $mailDefaultFromAddress;
  }

  /**
   * get mailDefaultName
   * @return string
   */
  public function getMailDefaultFromName()
  {
    return $this->_mailDefaultFromName;
  }

  /**
   * set mailDefaultName
   * @var string mailDefaultName
   */
  public function setMailDefaultFromName($mailDefaultFromName)
  {
    $this->_mailDefaultFromName = $mailDefaultFromName;
  }

  /**
   * get mailOverrideToAddress
   * @return string
   */
  public function getMailOverrideToAddress()
  {
    return $this->_mailOverrideToAddress;
  }

  /**
   * set mailOverrideToAddress
   * @var string mailOverrideTo
   */
  public function setMailOverrideToAddress($mailOverrideToAddress)
  {
    $this->_mailOverrideToAddress = $mailOverrideToAddress;
  }

  /**
   * Get varPath
   *
   * @return string
   */
  public function getVarPath()
  {
    return $this->_varPath;
  }

  /**
   * set the var path and ensure it is correct
   * 
   * @var string $path
   */
  public function setVarPath($path)
  {
    if (!$realPath = \realpath($path) or !\is_dir($realPath) or !\is_writable($realPath)) {
      if ($realPath) {
        $path = $realPath; //nicer error message if the path exists
      }
      throw new Exception("{$path} is not readable by the webserver so we cannot use it as the 'var' directory");
    }

    $this->_varPath = $realPath;
  }

  /**
   * get recaptchaPrivateKey
   * @return string
   */
  public function getRecaptchaPrivateKey()
  {
    return $this->_recaptchaPrivateKey;
  }

  /**
   * set recaptchaPrivateKey
   * @var string $recaptchaPrivateKey
   */
  public function setRecaptchaPrivateKey($recaptchaPrivateKey)
  {
    $this->_recaptchaPrivateKey = $recaptchaPrivateKey;
  }

  /**
   * get recaptchaPublicKey
   * @return string
   */
  public function getRecaptchaPublicKey()
  {
    return $this->_recaptchaPublicKey;
  }

  /**
   * set recaptchaPublicKey
   * @var string $recaptchaPublicKey
   */
  public function setRecaptchaPublicKey($recaptchaPublicKey)
  {
    $this->_recaptchaPublicKey = $recaptchaPublicKey;
  }

  /**
   * get maximumApplicantFileUploadSize
   * @return string
   */
  public function getMaximumApplicantFileUploadSize()
  {
    return $this->_maximumApplicantFileUploadSize;
  }

  /**
   * set maximumApplicantFileUploadSize
   * @var string $maximumApplicantFileUploadSize
   */
  public function setMaximumApplicantFileUploadSize($maximumApplicantFileUploadSize)
  {
    if (\Foundation\Utility::convertIniShorthandValue($maximumApplicantFileUploadSize) > \Foundation\Utility::convertIniShorthandValue(\ini_get('upload_max_filesize'))) {
      throw new \Jazzee\Exception('Configured Applicant File Upload Size is larger than PHP upload_max_filesize');
    }
    $this->_maximumApplicantFileUploadSize = \Foundation\Utility::convertIniShorthandValue($maximumApplicantFileUploadSize);
  }

  /**
   * get defaultimumApplicantFileUploadSize
   * @return string
   */
  public function getDefaultApplicantFileUploadSize()
  {
    return $this->_defaultApplicantFileUploadSize;
  }

  /**
   * set defaultApplicantFileUploadSize
   * @var string $defaultApplicantFileUploadSize
   */
  public function setDefaultApplicantFileUploadSize($defaultApplicantFileUploadSize)
  {
    $size = \Foundation\Utility::convertIniShorthandValue($defaultApplicantFileUploadSize);
    if ($size > \Foundation\Utility::convertIniShorthandValue(\ini_get('upload_max_filesize'))) {
      throw new \Jazzee\Exception('Configured Applicant Default Upload Size is larger than PHP upload_max_filesize');
    }
    if ($size > $this->getMaximumApplicantFileUploadSize()) {
      throw new \Jazzee\Exception('Configured Applicant Default Upload Size is larger than Configuraed Maximum applicant puload size');
    }
    $this->_defaultApplicantFileUploadSize = \Foundation\Utility::convertIniShorthandValue($defaultApplicantFileUploadSize);
  }

  /**
   * get maximumAdminFileUploadSize
   * @return string
   */
  public function getMaximumAdminFileUploadSize()
  {
    return $this->_maximumAdminFileUploadSize;
  }

  /**
   * set maximumAdminFileUploadSize
   * @var string $maximumAdminFileUploadSize
   */
  public function setMaximumAdminFileUploadSize($maximumAdminFileUploadSize)
  {
    if (\Foundation\Utility::convertIniShorthandValue($maximumAdminFileUploadSize) > \Foundation\Utility::convertIniShorthandValue(\ini_get('upload_max_filesize'))) {
      throw new \Jazzee\Exception('Configured Admin File Upload Size is larger than PHP upload_max_filesize');
    }
    $this->_maximumAdminFileUploadSize = \Foundation\Utility::convertIniShorthandValue($maximumAdminFileUploadSize);
  }

  /**
   * get allowApplicantNameChange
   * @return string
   */
  public function getAllowApplicantNameChange()
  {
    return $this->_allowApplicantNameChange;
  }

  /**
   * set allowApplicantNameChange
   * @var string $value
   */
  public function setAllowApplicantNameChange($value)
  {
    $this->_allowApplicantNameChange = (bool)$value;
  }

  /**
   * get allowApplicantEmailChange
   * @return string
   */
  public function getAllowApplicantEmailChange()
  {
    return $this->_allowApplicantEmailChange;
  }

  /**
   * set allowApplicantEmailChange
   * @var string $value
   */
  public function setAllowApplicantEmailChange($value)
  {
    $this->_allowApplicantEmailChange = (bool)$value;
  }

  /**
   * get allowApplicantPasswordChange
   * @return string
   */
  public function getAllowApplicantPasswordChange()
  {
    return $this->_allowApplicantPasswordChange;
  }

  /**
   * set allowApplicantPasswordChange
   * @var string $value
   */
  public function setAllowApplicantPasswordChange($value)
  {
    $this->_allowApplicantPasswordChange = (bool)$value;
  }

  /**
   * get allowApplicantPrintApplication
   * @return string
   */
  public function getAllowApplicantPrintApplication()
  {
    return $this->_allowApplicantPrintApplication;
  }

  /**
   * set allowApplicantPrintApplication
   * @var string $value
   */
  public function setAllowApplicantPrintApplication($value)
  {
    $this->_allowApplicantPrintApplication = (bool)$value;
  }

  /**
   * get adminAuthClass
   * @return string
   */
  public function getAdminAuthenticationClass()
  {
    return $this->_adminAuthClass;
  }

  /**
   * set adminAuthClass
   * @var string $adminAuthClass
   */
  public function setAdminAuthenticationClass($adminAuthClass)
  {
    $this->_adminAuthClass = $adminAuthClass;
  }

  /**
   * get shibbolethUsernameAttribute
   * @return string
   */
  public function getShibbolethUsernameAttribute()
  {
    return $this->_shibbolethUsernameAttribute;
  }

  /**
   * set shibbolethUsernameAttribute
   * @var string $shibbolethUsernameAttribute
   */
  public function setShibbolethUsernameAttribute($shibbolethUsernameAttribute)
  {
    $this->_shibbolethUsernameAttribute = $shibbolethUsernameAttribute;
  }

  /**
   * get shibbolethFirstNameAttribute
   * @return string
   */
  public function getShibbolethFirstNameAttribute()
  {
    return $this->_shibbolethFirstNameAttribute;
  }

  /**
   * set shibbolethFirstNameAttribute
   * @var string $shibbolethFirstNameAttribute
   */
  public function setShibbolethFirstNameAttribute($shibbolethFirstNameAttribute)
  {
    $this->_shibbolethFirstNameAttribute = $shibbolethFirstNameAttribute;
  }

  /**
   * get shibbolethLastNameAttribute
   * @return string
   */
  public function getShibbolethLastNameAttribute()
  {
    return $this->_shibbolethLastNameAttribute;
  }

  /**
   * set shibbolethLastNameAttribute
   * @var string $shibbolethLastNameAttribute
   */
  public function setShibbolethLastNameAttribute($shibbolethLastNameAttribute)
  {
    $this->_shibbolethLastNameAttribute = $shibbolethLastNameAttribute;
  }

  /**
   * get shibbolethEmailAddressAttribute
   * @return string
   */
  public function getShibbolethEmailAddressAttribute()
  {
    return $this->_shibbolethEmailAddressAttribute;
  }

  /**
   * set shibbolethEmailAddressAttribute
   * @var string $shibbolethEmailAddressAttribute
   */
  public function setShibbolethEmailAddressAttribute($shibbolethEmailAddressAttribute)
  {
    $this->_shibbolethEmailAddressAttribute = $shibbolethEmailAddressAttribute;
  }

  /**
   * get shibbolethLoginUrl
   * @return string
   */
  public function getShibbolethLoginUrl()
  {
    return $this->_shibbolethLoginUrl;
  }

  /**
   * set shibbolethLoginUrl
   * @var string $shibbolethLoginUrl
   */
  public function setShibbolethLoginUrl($shibbolethLoginUrl)
  {
    $this->_shibbolethLoginUrl = $shibbolethLoginUrl;
  }

  /**
   * get shibbolethLogoutUrl
   * @return string
   */
  public function getShibbolethLogoutUrl()
  {
    return $this->_shibbolethLogoutUrl;
  }

  /**
   * set shibbolethLogoutUrl
   * @var string $shibbolethLogoutUrl
   */
  public function setShibbolethLogoutUrl($shibbolethLogoutUrl)
  {
    $this->_shibbolethLogoutUrl = $shibbolethLogoutUrl;
  }

  /**
   * get noAuthIpAddresses
   * @return string
   */
  public function getNoAuthIpAddresses()
  {
    return $this->_noAuthIpAddresses;
  }

  /**
   * set noAuthIpAddresses
   * @var string $noAuthIpAddresses
   */
  public function setNoAuthIpAddresses($noAuthIpAddresses)
  {
    $this->_noAuthIpAddresses = $noAuthIpAddresses;
  }

  /**
   * get noAuthIpAddresses
   * @return string
   */
  public function getApiFormAuthenticationIpAddresses()
  {
    return $this->_apiFormAuthenticationIpAddresses;
  }

  /**
   * set noAuthIpAddresses
   * @var string $noAuthIpAddresses
   */
  public function setApiFormAuthenticationIpAddresses($addresses)
  {
    $this->_apiFormAuthenticationIpAddresses = $addresses;
  }

  /**
   * get simpleSAMLIncludePath
   * @return string
   */
  public function getSimpleSAMLIncludePath()
  {
    return $this->_simpleSAMLIncludePath;
  }

  /**
   * set simpleSAMLIncludePath
   * @var string $simpleSAMLIncludePath
   */
  public function setSimpleSAMLIncludePath($simpleSAMLIncludePath)
  {
    $this->_simpleSAMLIncludePath = $simpleSAMLIncludePath;
  }

  /**
   * get simpleSAMLAuthenticationSource
   * @return string
   */
  public function getSimpleSAMLAuthenticationSource()
  {
    return $this->_simpleSAMLAuthenticationSource;
  }

  /**
   * set simpleSAMLAuthenticationSource
   * @var string $simpleSAMLAuthenticationSource
   */
  public function setSimpleSAMLAuthenticationSource($simpleSAMLAuthenticationSource)
  {
    $this->_simpleSAMLAuthenticationSource = $simpleSAMLAuthenticationSource;
  }

  /**
   * get simpleSAMLUsernameAttribute
   * @return string
   */
  public function getSimpleSAMLUsernameAttribute()
  {
    return $this->_simpleSAMLUsernameAttribute;
  }

  /**
   * set simpleSAMLUsernameAttribute
   * @var string $simpleSAMLUsernameAttribute
   */
  public function setSimpleSAMLUsernameAttribute($simpleSAMLUsernameAttribute)
  {
    $this->_simpleSAMLUsernameAttribute = $simpleSAMLUsernameAttribute;
  }

  /**
   * get simpleSAMLFirstNameAttribute
   * @return string
   */
  public function getSimpleSAMLFirstNameAttribute()
  {
    return $this->_simpleSAMLFirstNameAttribute;
  }

  /**
   * set simpleSAMLFirstNameAttribute
   * @var string $simpleSAMLFirstNameAttribute
   */
  public function setSimpleSAMLFirstNameAttribute($simpleSAMLFirstNameAttribute)
  {
    $this->_simpleSAMLFirstNameAttribute = $simpleSAMLFirstNameAttribute;
  }

  /**
   * get simpleSAMLLastNameAttribute
   * @return string
   */
  public function getSimpleSAMLLastNameAttribute()
  {
    return $this->_simpleSAMLLastNameAttribute;
    ;
  }

  /**
   * set simpleSAMLLastNameAttribute
   * @var string $simpleSAMLLastNameAttribute
   */
  public function setSimpleSAMLLastNameAttribute($simpleSAMLLastNameAttribute)
  {
    $this->_simpleSAMLLastNameAttribute = $simpleSAMLLastNameAttribute;
  }

  /**
   * get simpleSAMLEmailAddressAttribute
   * @return string
   */
  public function getSimpleSAMLEmailAddressAttribute()
  {
    return $this->_simpleSAMLEmailAddressAttribute;
  }

  /**
   * set simpleSAMLEmailAddressAttribute
   * @var string $simpleSAMLEmailAddressAttribute
   */
  public function setSimpleSAMLEmailAddressAttribute($simpleSAMLEmailAddressAttribute)
  {
    $this->_simpleSAMLEmailAddressAttribute = $simpleSAMLEmailAddressAttribute;
  }

  /**
   * get publicKeyCertificatePath
   * @return string
   */
  public function getPublicKeyCertificatePath()
  {
    return $this->_publicKeyCertificatePath;
  }

  /**
   * set publicKeyCertificatePath
   * @var string $publicKeyCertificatePath
   */
  public function setPublicKeyCertificatePath($publicKeyCertificatePath)
  {
    $this->_publicKeyCertificatePath = $publicKeyCertificatePath;
  }

  /**
   * get adminDirectoryClass
   * @return string
   */
  public function getAdminDirectoryClass()
  {
    return $this->_adminDirectoryClass;
  }

  /**
   * set adminDirectoryClass
   * @var string $adminDirectoryClass
   */
  public function setAdminDirectoryClass($adminDirectoryClass)
  {
    $this->_adminDirectoryClass = $adminDirectoryClass;
  }

  /**
   * get LdapHostname
   * @return string
   */
  public function getLdapHostname()
  {
    return $this->_ldapHostname;
  }

  /**
   * set ldapHostname
   * @var string $ldapHostname
   */
  public function setLdapHostname($ldapHostname)
  {
    $this->_ldapHostname = $ldapHostname;
  }

  /**
   * get LdapPort
   * @return string
   */
  public function getLdapPort()
  {
    return $this->_ldapPort;
  }

  /**
   * set ldapPort
   * @var string $ldapPort
   */
  public function setLdapPort($ldapPort)
  {
    $this->_ldapPort = $ldapPort;
  }

  /**
   * get LdapBindRdn
   * @return string
   */
  public function getLdapBindRdn()
  {
    return $this->_ldapBindRdn;
  }

  /**
   * set ldapBindRdn
   * @var string $ldapBindRdn
   */
  public function setLdapBindRdn($ldapBindRdn)
  {
    $this->_ldapBindRdn = $ldapBindRdn;
  }

  /**
   * get LdapBindPassword
   * @return string
   */
  public function getLdapBindPassword()
  {
    return $this->_ldapBindPassword;
  }

  /**
   * set ldapBindPassword
   * @var string $ldapBindPassword
   */
  public function setLdapBindPassword($ldapBindPassword)
  {
    $this->_ldapBindPassword = $ldapBindPassword;
  }

  /**
   * get LdapUsernameAttribute
   * @return string
   */
  public function getLdapUsernameAttribute()
  {
    return $this->_ldapUsernameAttribute;
  }

  /**
   * set ldapUsernameAttribute
   * @var string $ldapUsernameAttribute
   */
  public function setLdapUsernameAttribute($ldapUsernameAttribute)
  {
    $this->_ldapUsernameAttribute = $ldapUsernameAttribute;
  }

  /**
   * get LdapFirstNameAttribute
   * @return string
   */
  public function getLdapFirstNameAttribute()
  {
    return $this->_ldapFirstNameAttribute;
  }

  /**
   * set ldapFirstNameAttribute
   * @var string $ldapFirstNameAttribute
   */
  public function setLdapFirstNameAttribute($ldapFirstNameAttribute)
  {
    $this->_ldapFirstNameAttribute = $ldapFirstNameAttribute;
  }

  /**
   * get LdapLastNameAttribute
   * @return string
   */
  public function getLdapLastNameAttribute()
  {
    return $this->_ldapLastNameAttribute;
  }

  /**
   * set ldapLastNameAttribute
   * @var string $ldapLastNameAttribute
   */
  public function setLdapLastNameAttribute($ldapLastNameAttribute)
  {
    $this->_ldapLastNameAttribute = $ldapLastNameAttribute;
  }

  /**
   * get LdapEmailAddressAttribute
   * @return string
   */
  public function getLdapEmailAddressAttribute()
  {
    return $this->_ldapEmailAddressAttribute;
  }

  /**
   * set ldapEmailAddressAttribute
   * @var string $ldapEmailAddressAttribute
   */
  public function setLdapEmailAddressAttribute($ldapEmailAddressAttribute)
  {
    $this->_ldapEmailAddressAttribute = $ldapEmailAddressAttribute;
  }

  /**
   * get LdapSearchBase
   * @return string
   */
  public function getLdapSearchBase()
  {
    return $this->_ldapSearchBase;
  }

  /**
   * set ldapSearchBase
   * @var string $ldapSearchBase
   */
  public function setLdapSearchBase($ldapSearchBase)
  {
    $this->_ldapSearchBase = $ldapSearchBase;
  }

  /**
   * get pdflibLicenseKey
   * @return string
   */
  public function getPdflibLicenseKey()
  {
    return $this->_pdflibLicenseKey;
  }

  /**
   * set pdflibLIcenseKey
   * @var string $pdflibLicenseKey
   */
  public function setPdflibLicenseKey($pdflibLicenseKey)
  {
    $this->_pdflibLicenseKey = $pdflibLicenseKey;
  }

  /**
   * get pdftkPath
   * @return string
   */
  public function getPdftkPath()
  {
    return $this->_pdftkPath;
  }

  /**
   * set pdftkPath
   * @var string $pdftkPath
   */
  public function setPdftkPath($pdftkPath)
  {
    $this->_pdftkPath = $pdftkPath;
  }

  /**
   * get adminCronAllowed
   * @return string
   */
  public function getAdminCronAllowed()
  {
    return $this->_adminCronAllowed;
  }

  /**
   * set adminCronAllowed
   * @var string $adminCronAllowed
   */
  public function setAdminCronAllowed($adminCronAllowed)
  {
    $this->_adminCronAllowed = $adminCronAllowed;
  }

  /**
   * get allowScramble
   * @return string
   */
  public function getAllowScramble()
  {
    return $this->_allowScramble;
  }

  /**
   * set allowScramble
   * @var string $allowScramble
   */
  public function setAllowScramble($allowScramble)
  {
    $this->_allowScramble = (bool)$allowScramble;
  }

  /**
   * get virusScanUploads
   * @return string
   */
  public function getVirusScanUploads()
  {
    return $this->_virusScanUploads;
  }

  /**
   * set virusScanUploads
   * @var string $allowScramble
   */
  public function setVirusScanUploads($virusScanUploads)
  {
    $this->_virusScanUploads = (bool)$virusScanUploads;
  }

  /**
   * Get the path to the jazzee source
   * @return string
   */
  public static function getSourcePath()
  {
    return realpath(__DIR__ . '/../..');
  }

}
