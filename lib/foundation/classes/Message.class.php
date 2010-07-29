<?php
/**
 * Singleton for storing user Level messages
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
require_once('Foundation.class.php');

class Message extends Foundation {
  /**
   * Holds the instance for singleton
   * @var Message
   */
  private static $_instance = null;
  
  /**
   * Holds messages
   * @var array
   */
  private $_messages = array();
  
  /**
   * private constructor ensures singleton
   */
  private function __construct(){}
  
  /**
   * fetch a single instance of Message
   * @return Message 
   */
  public static function getInstance(){
    if(is_null(self::$_instance)){
      self::$_instance = new Message;
    }
    return self::$_instance;
  }
  
  /**
   * Load an instance of Message from elsewhere (like a saved session)
   * @param Message $class
   * @return Message
   */
  public static function loadInstance(Message $class){
    self::$_instance = $class;
    return self::$_instance;
  }
  
  /**
   * destroy the instance 
   */
  public static function destroyInstance(){
    self::$_instance = NULL;
  }
  
  /**
   * Add a message to the que
   * @param string $type the type of message (error|confirm|notice|etc)
   * @param string $message the content of the message
   * @param string $context applies the message to a specific context
   */
  public function write($type, $message){
    $this->_messages[] = array('type'=>$type, 'message'=>$message);
  }
  
  /**
   * Returns the first message from the que
   * @return array
   */
  public function read(){
    if(!empty($this->_messages)){
      return array_shift($this->_messages);
    }
    return false;
  }
  
  /**
   * retrieve all messages from the que
   * @return array
   */
  public function readAll(){
    $arr = $this->_messages;
    $this->_messages = array();
    return $arr;
  } 
}
 
?>
