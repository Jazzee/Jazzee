<?php
/**
 * Jazzee base controller
 * Using the beforeAction method we setup all the basic elements of the page
 * as well as sessions, messaging, and navigation
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 */
class JazzeeController extends Controller{
  
  /**
   * Holds the Message class for communicating with the applicant between page views
   * @var Message
   */
	protected $messages;
  
  /**
   * Holds the active session store
   * @var Session_Store
   */
  protected $session;
  
  /**
   * Holds the file session store
   * Contains FileContainer objects holding private blobs
   * @var Session_Store
   */
  protected $fileStore;
  
  /**
   * Holds the EmailServer class for sending messages
   * @var EmailServer
   */
  protected $mail;
  
  /**
   * Constructor
   * Set up configuration containers
   * Start session handling 
   * Setup error processing and email
   */
  public function __construct(){
    //load configuration file
    $c = new Config;
    $root = $c->parseConfig(SRC_ROOT . '/etc/config.ini.php', 'INICommented'); 
    if (PEAR::isError($root)) {
      $this->redirect(WWW_ROOT . '/index.php?url=install');
      exit();
    }
    $arr = $root->toArray();
    $this->configFile = $arr['root']['system'];
    
    //URL path for absolute links and email detect https and add it
    if($this->configFile['forceSSL']){
      $protocol = 'https';
    } else {
      $protocol = 'http';
      $url = (!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] != 'off')?'https':'http';
    }
    define('SERVER_URL', $protocol . '://' .  $_SERVER['SERVER_NAME']);
    
    //set the default timezone
    date_default_timezone_set($this->configFile['timezone']);
    
    //The var root is the base for storing logs, sessions, cache files, tmp and uploaded files
    define('VAR_ROOT',$this->configFile['varPath']?$this->configFile['varPath']:SRC_ROOT . '/var');
    
    if(!is_dir(VAR_ROOT)){
      throw new Jazzee_Exception(VAR_ROOT . ' is not readable by the webserver');
    }
    
    //check to see if all the directories exist and are writable
    $varDirectories = array('cache','log','session','tmp','uploads');
    foreach($varDirectories as $dir){
      $path = VAR_ROOT . '/' . $dir;
      if(!is_dir($path)){
        if(!mkdir($path)){
          throw new Jazzee_Exception("Tried to create {$dir} directory but {$path} is not writable by the webserver");
        }
      }
      if(!is_writable($path)){
        throw new Jazzee_Exception("Invalid path to var/{$dir} {$path} is not writable by the webserver");
      }
    }
    //setup database connection
    Doctrine_Manager::connection($this->configFile['dsn']);
    Doctrine::loadModels(APP_ROOT . '/models/doctrine');
    
    //Get database configuration options
    $this->config = new ConfigManager;
    
    //setup secure sessions
    $session = Session::getInstance();
    $session->set('name', $this->config->session_name);
    //cookies last forever (until browser is closed) which takes the users local clock out of the picture
    //Timeouts are handled By Session internally by expiring the Session_Store
    $session->set('cookie_lifetime', 0);
    //since files are stored in sessions destroy any files
    $session->set('gc_maxlifetime', $this->config->session_lifetime + 600);
    $session->set('use_only_cookies', true);
    $session->set('hash_function', 1);
    $session->set('save_path', VAR_ROOT . '/session/');
    if($this->configFile['forceSSL']){
      $session->set('cookie_secure', true);
    }
    $session->set('cookie_path', WWW_ROOT . '/');
    //browsers give inconsisten results when the domain is used to set the cookie, instead use an empty string to restrict the cookie to this domain
    $session->set('cookie_domain', '');
    $session->start();
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
    if($this->configFile['status'] == 'DEVELOPMENT'){
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
  
  /**
   * Before any action is taken do some basic setup
   * Create a session instance 
   * Create, or rescue, a Message instance
   * Crate a navigation instance
   * Create the default layout varialbes so the layout doesn't have to guess if they are availalbe
   * @return null
   */
  protected function beforeAction(){
    //start a guest session store by default which does not expire
    $this->session = Session::getInstance()->getStore('guest', 0);
    $this->fileStore = Session::getInstance()->getStore('files', $this->config->session_lifetime);
    $messageStore = Session::getInstance()->getStore('messages', 0); //messages live forever
    
    if(isset($messageStore->savedMessageClass)){
      $this->messages = Message::loadInstance($messageStore->savedMessageClass);
    } else {
      $this->messages = Message::getInstance();
    }
    
    /*
      When the php post_max_size attribute is exceed the POST array is blanked.
      So a check has to be done using the CONTENT_LENGTH superglobal against the post_max_size value oon every request
	*/
    if(!empty($_SERVER['CONTENT_LENGTH'])){
      $max = convertIniShorthandValue(ini_get('post_max_size'));
      if($_SERVER['CONTENT_LENGTH'] > $max){
        $this->messages->write('error', 'Your input has exceeded the maximum allowed size.  If you are trying to upload a file it is too large.  Please reduce your file size and try again');
      }
    }
    
    //required layout variables get default values
    $this->setLayoutVar('requiredCss', array());
    $this->setLayoutVar('requiredJs', array());
    $this->setLayoutVar('pageTitle', '');
    $this->setLayoutVar('layoutTitle', '');
    $this->setLayoutVar('layoutContentTop', '');
    $this->setLayoutVar('layoutContentFooter', '<p>This Application has been designed to meet current web standards in xhtml, css, and javascript in order to be accessible to everyone. If you notice a problem with the application or find it inaccessible in any way please let us know.</p>');
    $this->setLayoutVar('status', 'success'); //used in some json ajax requests
    
    //add jquery
    $this->addScript('foundation/scripts/jquery.js');
    $this->addScript('foundation/scripts/jqueryui.js');
    $this->addScript('foundation/scripts/jquery.json.js');
    $this->addScript('foundation/scripts/jquery.cookie.js');
    
    //yui css library
    $this->addCss('foundation/styles/reset-fonts-grids.css');
    $this->addCss('foundation/styles/base.css');
    
    //our css
    $this->addCss('common/styles/layout.css');
    $this->addCss('common/styles/style.css');
    
    //jquery's style info
    $this->addCss('foundation/styles/jquery/themes/smoothness/style.css');
  }
  
  /**
   * Clean up post-action
   * After the action was run and the views are rendered store any remaining messages
   * in the session so they can be displayed on the next page load
   * @return null
   */
  protected function afterAction(){
    $messageStore = Session::getInstance()->getStore('messages');
    $messageStore->savedMessageClass = $this->messages;
  }
  
  /**
   * Create a good path even if modrewrite is not present
   * @param string $path
   * @return string
   */
  public function path($path){
    if($this->config->pretty_urls){
      return WWW_ROOT . '/' . $path;
    } else {
      return WWW_ROOT . '/index.php?url=' . $path;
    }
    
  }
  
  /**
   * If there is no navigation
   * @return null
   */
  public function getNavigation(){
    return null;
  }
  
}
?>
