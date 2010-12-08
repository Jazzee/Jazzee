<?php
/**
 * Obervable Error class
 * All errors are handled here and the observers like log, print, email etc do the rest
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage error
 */
require_once(dirname(__FILE__) . '/../Foundation.class.php');

class Error extends Foundation {
  /**
   * All of the observers
   * @var array
   */
  private $_observers = array();
    /**
   * Holds the singleton instance
   * @var mixed
   */
  private static $_instance = null;
  
  /**
   * Get a single instance of Error 
   * @return Error
   */
  public static function getInstance(){
    if(is_null(self::$_instance)){
      self::$_instance = new Error;
    }
    return self::$_instance;
  }
  /**
   * Private constructor to ensure singleton
   */
  private function __construct(){}
  
  /**
   * Destroy the singleton 
   */
  public static function destroy(){
    self::$_instance = NULL;
  } 
  
  /**
   * Register an error
   * Can be called directly or as the php error_handler
   * @param $errno integer PHP Error Constant for severity
   * @param $errstr string the actuall text of the error
   * @param $errfile string optional the filename that raised the error
   * @param $errline integer optional the line number that raised the error
   * @param $errcontext array optional points to the active symbol table at the point the error occurred. In other words, errcontext will contain an array of every variable that existed in the scope the error was triggered in. User error handler must not modify error context.
   * @return false to populate $php_errormsg and let any other handlers do their thing
   */
  public function register($errno, $errstr, $errfile = null, $errline = null, $errcontext = null){
    $message = new ErrorMessage($errno, $errstr, $errfile, $errline, $errcontext);
    
    //PHP5 implicity handles objects by reference so this foreach loop
    //will properly not copy each object
    foreach($this->_observers AS $severity => $arr){
      //Bitwise comparison to see if $s is contained in severity
      if(($severity & $errno) OR $severity == $errno){
        foreach($arr AS $observer){
          $observer->update($message);
        }
      }
    }
  }
  
  /**
   * Parse an exception and send it to self::register
   * @param $e Exception
   */
  public function exception(Exception $e){
    $this->register(E_USER_ERROR, 'Uncaught Exception: ' . $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
  }
  /**
   * Attach observers
   * @param $observer ErrorObserver the oberserving object
   * @param $severity integer PHP Error Constant for severity
   */
  public function attach(ErrorObserver $observer, $level){
    $this->_observers[$level][] = $observer;
  }
  
  /**
   * Detach observers
   * @param $observer ErrorObserver the oberserving object
   */
  public function detach(ErrorObserver $observer){
    foreach($this->_observers as $level => $arr){
      foreach($arr as $key => $object){
        if($observer === $object){
          unset($this->_observers[$level][$key]);
          return;
        }
      }
    }
  }
  
  
}
?>