<?php
namespace Jazzee;
/**
 * Jazzee base controller
 * @package jazzee
 */

class JazzeeController extends PageController
{
  /**
   * Holds the session
   * @var \Foundation\Session
   */
  protected $_session;
  
  /**
   *  @var \Jazzee\Configuration
   */
  protected $_config;
  
  /**
   *  @var \Foundation\Configuration
   */
  protected $_foundationConfig;
  
  /**
   * @var \Foundation\Cache
   */
  protected $_cache;
  
  /**
   * Holds the EmailServer class for sending messages
   * @var \Foundation\Mail\Server
   */
  protected $_mailServer;
  
  /**
   * Holds the Doctrine EntityManager
   * @var \Doctrine\ORM\EntityManager
   */
  protected $_em;
  
  /**
   * Absolute server path
   * @var string
   */
  protected $_serverPath;
  
  /**
   * Constructor
   * Set up configuration containers
   * Start session handling 
   * Setup error processing and email
   */
  public function __construct(){
    $this->setupConfiguration();
    $this->setupVarPath();
    $this->setupDoctrine(); 
    $this->setupSession();
    //$this->setupLogging();
  }
  
  /**
   * Before any action is taken do some basic setup
   * Look for out of bounds file uploads
   * Crate a navigation instance
   * Create the default layout varialbes so the layout doesn't have to guess if they are available
   * @return null
   */
  protected function beforeAction(){
    parent::beforeAction();
    /*
      When the php post_max_size attribute is exceed the POST array is blanked.
      So a check has to be done using the CONTENT_LENGTH superglobal against the post_max_size value on every request
	  */
    if(!empty($_SERVER['CONTENT_LENGTH'])){
      $max = \convertIniShorthandValue(\ini_get('post_max_size'));
      if($_SERVER['CONTENT_LENGTH'] > $max){
        $this->addMessage('error', 'Your input has exceeded the maximum allowed size.  If you are trying to upload a file it is too large.  Please reduce your file size and try again');
      }
    }
    
    //add jquery
    $this->addScript($this->path('resource/foundation/scripts/jquery.js'));
    $this->addScript($this->path('resource/foundation/scripts/jqueryui.js'));
    $this->addScript($this->path('resource/foundation/scripts/jquery.json.js'));
    $this->addScript($this->path('resource/foundation/scripts/jquery.cookie.js'));
    
  }
  
  /**
   * Flush the EntityManager to persist all changes
   */
  protected function afterAction(){
    $this->_em->flush();
  }
  
  /**
   * Create a good path even if modrewrite is not present
   * @param string $path
   * @return string
   */
  public function path($path){
    $prefix = $this->_serverPath . rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\.');
    return $prefix . '/' . $path;
  }

  /**
   * Call any after action properties, redirect, and exit
   * @param string $path
   */
  public function redirectPath($path){
    $this->redirect($this->path($path));
    $this->afterAction();
    exit(0);
  }
  
  /**
   * Add a message for the user
   * @param string $type
   * @param string $text
   */
  public function addMessage($type, $text){
    if(isset($this->_session->getStore('messages')->messages)) $messages = $this->_session->getStore('messages')->messages;
    else $messages = array();
    $messages[] = array('type'=>$type, 'text'=>$text);
    $this->_session->getStore('messages')->messages = $messages;
  }
  
  /**
   * Get messages
   * @return array
   */
  public function getMessages(){
    $messages = array();
    if(isset($this->_session->getStore('messages')->messages)){
      $messages = $this->_session->getStore('messages')->messages;
      $this->_session->getStore('messages')->messages = array();
    } 
    return $messages;
  }
  
  /**
   * Send Email
   * 
   * @param string $toAddress Email Address
   * @param string $toName
   * @param string $fromAddress
   * @param string $fromName
   * @param string $subject
   * @param string $body
   * 
   * @return boolean true on success false if error
   */
  public function sendEmail($toAddress, $toName, $fromAddress, $fromName, $subject, $body){
    if(!isset($this->_mailServer)) $this->_mailServer = new \Foundation\Mail\Server($this->_foundationConfig);
    $message = new \Foundation\Mail\Message($this->_foundationConfig);
    $message->addTo($toAddress, $toName);
    $message->setFrom($fromAddress, $fromName);
    $message->setSubject($subject);
    $message->setBody($body);
    return $this->_mailServer->send($message);
  }
  
  /**
   * Get the path to the var directory
   * @return string
   */
  protected function getVarPath(){
    $path = $this->_config->getVarPath()?$this->_config->getVarPath():__DIR__ . '/../../var';
    if(!$realPath = \realpath($path) or !\is_dir($realPath) or !\is_writable($realPath)){
      if($realPath) $path = $realPath; //nicer error message if the path exists
      throw new Exception("{$path} is not readable by the webserver so we cannot use it as the 'var' directory");
    }
    return $realPath;
  }
  
  /**
   * Setup configuration
   * 
   * Load config.ini.php
   * translate to foundation config
   * create absolute path
   * set defautl timezone
   */
  protected function setupConfiguration(){
    $this->_config = new \Jazzee\Configuration();
    
    $this->_foundationConfig = new \Foundation\Configuration();
    if($this->_config->getStatus() == 'DEVELOPMENT'){
      $this->_foundationConfig->setCacheType('array');
    } else {
      $this->_foundationConfig->setCacheType('apc');
    }
    $this->_foundationConfig->setMailSubjectPrefix($this->_config->getMailSubjectPrefix());
    $this->_foundationConfig->setMailDefaultFromAddress($this->_config->getMailDefaultFromAddress());
    $this->_foundationConfig->setMailDefaultFromName($this->_config->getMailDefaultFromName());
    $this->_foundationConfig->setMailOverrideToAddress($this->_config->getMailOverrideToAddress());
    $this->_foundationConfig->setMailOverrideToName($this->_config->getMailOverrideToName());
    $this->_foundationConfig->setMailServerType($this->_config->getMailServerType());
    $this->_foundationConfig->setMailServerHost($this->_config->getMailServeHostr());
    $this->_foundationConfig->setMailServerPort($this->_config->getMailServerPort());
    $this->_foundationConfig->setMailServerUsername($this->_config->getMailServerUsername());
    $this->_foundationConfig->setMailServerPassword($this->_config->getMailServerPassword());
    
    
    $this->_cache = new \Foundation\Cache('Jazzee',$this->_foundationConfig);
    
    \Foundation\VC\Config::setCache($this->_cache);
    
    if((empty($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] == 'off') AND !$this->_config->getForceSSL()){
      $protocol = 'http';
    } else {
      $protocol = 'https';
    }
    
    $this->_serverPath = $protocol . '://' .  $_SERVER['SERVER_NAME'];
    
    //set the default timezone
    date_default_timezone_set($this->_config->getTimezone());
  }
  
  /**
   * Setup Doctrine ORM
   */
  protected function setupDoctrine(){
    //setup doctrine
    $doctrineConfig = new \Doctrine\ORM\Configuration();

    //We use different caching and proxy settings in Development status
    if($this->_config->getStatus() == 'DEVELOPMENT'){
      $doctrineConfig->setAutoGenerateProxyClasses(true);
      $doctrineConfig->setProxyDir($this->getVarPath() . '/tmp');
      $cache = new \Doctrine\Common\Cache\ArrayCache;
    } else {
      $doctrineConfig->setAutoGenerateProxyClasses(false);
      $doctrineConfig->setProxyDir(__DIR__ . '/Jazzee/Entity/Proxy');
      if(!extension_loaded('apc')) throw new Exception('APC cache is required, but was not available.');
      $cache = new \Doctrine\Common\Cache\ApcCache;
    }
    $driver = $doctrineConfig->newDefaultAnnotationDriver(array(__DIR__."/Jazzee/Entity"));
    $doctrineConfig->setMetadataDriverImpl($driver);
    
    $doctrineConfig->setProxyNamespace('Jazzee\Entity\Proxy');
    $doctrineConfig->setMetadataCacheImpl($cache);
    $doctrineConfig->setQueryCacheImpl($cache);
    
    $connectionParams = array(
      'dbname' => $this->_config->getDbName(),
      'user' => $this->_config->getDbUser(),
      'password' => $this->_config->getDbPassword(),
      'host' => $this->_config->getDbHost(),
      'port' => $this->_config->getDbPort(),
      'driver' => $this->_config->getDbDriver(),
    );
    
    $this->_em = \Doctrine\ORM\EntityManager::create($connectionParams, $doctrineConfig);
    
  }
  
  /**
   * Get the entity manager
   * 
   * @return \Doctrine\ORM\EntityManager
   */
  public function getEntityManager(){
    return $this->_em;  
  }
  
  /**
   * Setup the var directories
   */
  protected function setupVarPath(){
    $var = $this->getVarPath();
    //check to see if all the directories exist and are writable
    $varDirectories = array('log','session','tmp','uploads');
    foreach($varDirectories as $dir){
      $path = $var . '/' . $dir;
      if(!is_dir($path)){
        if(!mkdir($path)){
          throw new Exception("Tried to create 'var/{$dir}' directory but {$path} is not writable by the webserver");
        }
      }
      if(!is_writable($path)){
        throw new Exception("Invalid path to 'var/{$dir}' {$path} is not writable by the webserver");
      }
    }
  }
  
  /**
   * Setup Sessions
   */
  protected function setupSession(){
    //setup the session based on the configuration
    $this->_session = new \Foundation\Session();
    
    //if the session name variable is empty then there is no way to login and fix it so look for an empty session name and default to the ini value if it is blank
    $this->_session->setConfigVariable('name', $this->_config->getSessionName()?ini_get('session.name'):$this->_config->getSessionName());
    //cookies last forever (until browser is closed) which takes the users local clock out of the picture
    //Timeouts are handled By Session internally by expiring the Session_Store
    $this->_session->setConfigVariable('cookie_lifetime', 0);
    //since files are stored in sessions destroy any files after one day
    $this->_session->setConfigVariable('gc_maxlifetime', 86400);
    $this->_session->setConfigVariable('use_only_cookies', true);
    $this->_session->setConfigVariable('hash_function', 1);
    $this->_session->setConfigVariable('save_path', $this->getVarPath() . '/session/');
    if($this->_config->getForceSSL() OR (!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on')){
      $this->_session->setConfigVariable('cookie_secure', true);
    }
    $this->_session->setConfigVariable('cookie_path', rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\.') . '/');
    //browsers give inconsisten results when the domain is used to set the cookie, instead use an empty string to restrict the cookie to this domain
    $this->_session->setConfigVariable('cookie_domain', '');
    $this->_session->start();
  }
  
  /**
   * Setup logging
   */
  protected function setupLoggind(){
    //create an access log with browser information
    $accessLog = Log::singleton('file', VAR_ROOT . '/log/access_log', '', array('lineFormat'=>'%{timestamp} %{message}'),PEAR_LOG_INFO);
    $accessMessage ="[{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} {$_SERVER['SERVER_PROTOCOL']}] " .
      '[' . (!empty($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'-') . '] ' .
      '[' . (!empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'-') . ']';
    $accessLog->log($accessMessage, PEAR_LOG_INFO);
    
    //Add an observer to catch all generated errors and put them in the error_log
    Error::getInstance()->attach(
      new PearLogObserver(
        Log::singleton('file',VAR_ROOT . '/log/error_log')
      ),E_ALL ^ (E_NOTICE | E_USER_NOTICE)
    );
    
    //special log for Strict errors which are the results of depriciation mostly
    Error::getInstance()->attach(
      new PearLogObserver(
        Log::singleton('file',VAR_ROOT . '/log/strict_log')
      ),E_DEPRECATED | E_USER_DEPRECATED | E_STRICT | E_NOTICE | E_USER_NOTICE
    );
    
    //Also direct all errors out to PHP error_log function where the belong
    Error::getInstance()->attach(
      new PearLogObserver(
        Log::factory('error_log',PEAR_LOG_TYPE_SYSTEM,'Jazzee Error')
      ),E_ALL
    );
    
    //In developemnt log errors to screen
    if($this->config->status == 'DEVELOPMENT'){
      Error::getInstance()->attach(
        new PearLogObserver(
          Log::factory('display','','', array(),PEAR_LOG_DEBUG)
        ),E_ALL
      );
    }
    
    //Set Error to be the default error handler
    set_error_handler(array(Error::getInstance(), 'register'));
    
    //set Error to handle uncaught exceptions
    set_exception_handler(array(Error::getInstance(), 'exception'));
  }
}