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
   * Constructor
   * Set up configuration containers
   * Start session handling 
   * Setup error processing and email
   */
  public function __construct(){
    parent::__construct();
    $this->setupDoctrine(); 
    $this->setupSession();
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
      $doctrineConfig->setProxyDir(__DIR__ . '/Entity/Proxy');
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
    if(!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on'){
      $this->_session->setConfigVariable('cookie_secure', true);
    }
    $this->_session->setConfigVariable('cookie_path', rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\.') . '/');
    //browsers give inconsisten results when the domain is used to set the cookie, instead use an empty string to restrict the cookie to this domain
    $this->_session->setConfigVariable('cookie_domain', '');
    $this->_session->start();
  }
}