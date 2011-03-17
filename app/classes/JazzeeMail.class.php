<?php
/**
 * Singleton class for sending email so all the onfiguration can live in one place
 */
class JazzeeMail {
  /**
   * Holds the instance for singleton
   * @var Session
   */
  private static $_instance = null;
  
  /**
   * The EmailServer class
   * @var EmailServer
   */
  protected $server;
  
  /**
   * Constructor is protected to preserve singleton
   */
  protected function __construct() {
    $config = new ConfigManager;
    $config->addContainer(new IniConfigType(SRC_ROOT . '/etc/config.ini.php'));
    //setup the connection to the mail server
    $this->server = new EmailServer($config->mailServer);
    $this->server->defaultFrom($config->mailDefaultFrom, $config->mailDefaultName);
    
    //In development mode require the mailOverrideTo configruation item, but allow it in other status levels
    if($config->status == 'DEVELOPMENT' OR $config->mailOverrideTo) {
      if(!$config->mailOverrideTo)
        throw new Jazzee_Exception('In development mode mailOverrideTo must be set.');
      $this->server->overrideTo($config->mailOverrideTo);
    }
  }
  
  /**
   * Fetch a single instance of Session
   * @param string $limiter restrict data to this limiter 
   */
  public static function getInstance() {
    if(is_null(self::$_instance)) {
      self::$_instance = new JazzeeMail;
    }
    return self::$_instance;
  }
  
  /**
   * Send Email
   * @param EmailMessage $message
   * @return bool
   */
  public function send(EmailMessage $message) {
    return $this->server->send($message);
  }
  
  /**
   * Make an absolute path for sending emails
   * @param string $path
   * @return string
   */
  public function path($path){
    $config = new ConfigManager;
    if($config->pretty_urls){
      return SERVER_URL . WWW_ROOT . '/' . $path;
    } else {
      return SERVER_URL . WWW_ROOT . '/index.php?url=' . $path;
    }
  }
}
?>