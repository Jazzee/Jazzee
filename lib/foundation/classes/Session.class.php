<?php
/**
 * Singleton for handling sessions
 * Uses PHP $_SESSION
 * Hanles some default security and wrapper tasks
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
require_once('Foundation.class.php');

class Session extends Foundation {
  /**
   * Holds the instance for singleton
   * @var Session
   */
  private static $_instance = null;
  
  /**
   * Session configuration data
   * @var array
   */
  protected $config;
  
  private function __construct(){
    $this->config = array(
      'name' => null, //name of the session
      'use_only_cookies' => null, //force the use of cookies not passed session ID
      'hash_function' => null, //what hash function to use 0=md5 1=sha1
      'save_path' => null, //where to save the sessions defaults to system default
      'cookie_secure' => null, //force cookies to be sent over ssh only
      'gc_maxlifetime' => null, //how long to wait before garbage collection sessions
      'gc_probability' => null, //how long to wait before garbage collection sessions
      'gc_divisor' => null, //how long to wait before garbage collection sessions
      'cookie_lifetime' => null, //seconds for session timeout
      'cookie_path' => null, //domain path where the cookie will work
      'cookie_domain' => null, //domain where the cookie will work
    );
  }
  
  /**
   * Fetch a single instance of Session
   * @param string $limiter restrict data to this limiter 
   */
  public static function getInstance(){
    if(is_null(self::$_instance)){
      self::$_instance = new Session;
    }
    return self::$_instance;
  }
  
  /**
   * Delete all the session data and rebuild the containers
   */
  private function rebuildSession(){
    session_regenerate_id(TRUE);
    $_SESSION = array();
    $_SESSION['security'] = array(
        'user-agent' => md5(empty($_SERVER['HTTP_USER_AGENT'])?'':$_SERVER['HTTP_USER_AGENT']),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'start' => time()
    );
    $_SESSION['stores'] = array();
 }
 
  /**
   * Destroy the instance 
   */
  public static function destroyInstance(){
    self::$_instance->rebuildSession();
    self::$_instance = NULL;
  }
  
  /**
   * Set configuration variables to override the defaults
   * @param string $var the name of the config option
   * @param mixed $value the value to set
   */
  public function set($var, $value){
    $this->config[$var] = $value;
  }
  
    /**
   * Explicity begin a session
   * Sessions are only started when the config is all set.
   */
  public function start(){
    session_name($this->get('name'));
    ini_set("session.use_only_cookies", $this->get('use_only_cookies'));
    ini_set("session.cookie_secure", $this->get('cookie_secure'));
    ini_set("session.hash_function", $this->get('hash_function'));
    // create a private session directory so that another script
    // with a lower lifetime doesn't clean up our session
    $path = $this->get('save_path');
    if(
      !empty($path) AND 
      is_dir($path) AND 
      is_writable($path)
    ){
      ini_set('session.save_path', $this->get('save_path'));
    }
    //When to destroy sessions on the filesystem
    ini_set('session.gc_maxlifetime', $this->get('gc_maxlifetime'));
    ini_set('session.gc_probability', $this->get('gc_probability'));
    ini_set('session.gc_divisor', $this->get('gc_divisor'));
    
    //session_set_cookie_params  (lifetime,path,domain,secure)
    session_set_cookie_params($this->get('cookie_lifetime'),$this->get('cookie_path'),$this->get('cookie_domain'),$this->get('cookie_secure'));
    
    //the session_cache_limiter cache-control line is there to kill a bug in IE that causes the PDF not to be cached over ssl.  
    //these lines allow the caching and let the file be downloaded.  This bug doesn't seem to affect the preview 
    //it was present in IE6 and IE7
    session_cache_limiter ('must-revalidate');
    
    session_start();
    //do a very small check to see if the browser is the same as the originating browser
    //this canbe fooled easily, but provides a bit of security
    if(empty($_SESSION['security']) OR $_SESSION['security']['user-agent'] != md5(empty($_SERVER['HTTP_USER_AGENT'])?'':$_SERVER['HTTP_USER_AGENT'])){
      $this->rebuildSession();
    }
  }
  
  /**
   * Retrive confiuration option
   * attempts to find a user set option then loads the default
   * @param string $var the config to load
   */
  public function get($var){
    //if the user has set a config variable return it
    if(array_key_exists($var, $this->config) AND !is_null($this->config[$var])){
      return $this->config[$var];
    }
    return ini_get('session.' . $var);
  }
  
  /**
   * Retrieve a session store
   * Gets the store if it exists or created a new one if it doesn't
   * @param string $name
   * @return Session_Store
   */
  public function getStore($name, $lifetime = 0){
    if(array_key_exists($name, $_SESSION['stores'])){
      $_SESSION['stores'][$name]->setLifetime($lifetime);
      $_SESSION['stores'][$name]->touchActivity();
      return $_SESSION['stores'][$name];
    }
    $_SESSION['stores'][$name] = new Session_Store($lifetime);
    return $_SESSION['stores'][$name];
  }
}

/**
 * Stores the user data for a session
 */
class Session_Store{
  /**
   * Data Store
   * @var array
   */
  protected $data;
  
  /**
   * The last time activitity was recorded for this store
   * @var int
   */
  protected $lastActivity;
 
  /**
   * The last time the user was authenticated
   * @var int
   */
  protected $lastAuthentication;
  
  /**
   * Lifetime
   * @var int
   */
  protected $lifetime;
  
  /**
   * Constructor
   */
  public function __construct($lifetime){
    $this->data = array();
    $this->lastAuthentication = 0;
    $this->lifetime = $lifetime;
    $this->lastActivity = time(); //dont use touchActivity() causes an infinate loop if store is expired
  }
  
  /**
   * Expire the store
   */
  public function expire(){
    $this->__construct($this->lifetime);
  }
  
  /**
   * Check to see if the data should be expired
   * If so then destroy the store
   */
  protected function checkExpired(){
    if($this->lifetime AND (time() - $this->lastActivity) > $this->lifetime){
      $this->expire();
    } 
  }

  /**
   * Set the lifetime
   */
  public function setLifetime($lifetime){
    $this->lifetime = $lifetime;
  }
    
  /**
   * Update the lastActivity timestamp
   */
  public function touchActivity(){
    $this->checkExpired();
    $this->lastActivity = time();
  }
  
  /**
   * Update the lastAuthentication timestamp
   */
  public function touchAuthentication(){
    $this->lastAuthentication = time();
  }
  
  /**
   * Store data in the session
   * @param string $name the name of the data
   * @param mixed $value the value to store
   */
  public function __set($name, $value){
   $this->data[$name] = $value;
  }
  
  /**
   * Retrieve data stored in the session
   * @param string $name the name of the data
   */
  public function __get($name){
    $this->checkExpired();
    if(array_key_exists($name, $this->data)){
      return $this->data[$name];
    }
    $trace = debug_backtrace();
    trigger_error(
        'Undefined property : ' . $name .
        ' in ' . $trace[0]['file'] .
        ' on line ' . $trace[0]['line'],
        E_USER_NOTICE);
  }
  
  /**
   * Check if a property is set
   * @param string $name
   */
  public function __isset($name){
    $this->checkExpired();
    return isset($this->data[$name]);
  }
  
  /**
   * Unset a property
   * @param string $name
   */
  public function __unset($name){
    unset($this->data[$name]);
  }
}

?>