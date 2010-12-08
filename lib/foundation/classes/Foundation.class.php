<?php
/**
 * Setup Foundation
 * All Foundation classes indluce this file.  Use this to place
 * global functions here and store special classes.
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */

 
/**
 * Parent class for all Foundation classes.  Can be used to add cross class functionality
*/
class Foundation {
  /*
   * Hold foundation attributes
   * @var array
   */
  private static $_foundationAttr = array();
  
  /**
   * Attribute Constants
   */
  const ATTR_FORM_VIEW_PATH           = 2;
  
  /**
   * Modify Foundaton attributes
   * @param int $attribute on of the attribute constants
   * @param mixed $value
   * @return null
   */
  static function setAttribute($attribute, $value){
    self::$_foundationAttr[$attribute] = $value;
  }
  
  /**
   * Retrieve Foundaton attributes
   * @param int $attribute
   * @return mixed|false if not set
   */
  static function getAttribute($attribute){
    return isset(self::$_foundationAttr[$attribute])?self::$_foundationAttr[$attribute]:false;
  }
}

/**
 * Declare an exception class for Foundation
 * Create a default exception handler to allow functionality 
 * to be added later
*/
class Foundation_Exception extends RuntimeException{
  /**
   * A nice message for the user
   * Allows any exception handler to output somethign nice to the user
   * @var string
   */
  private $_uMessage = 'An error has occurred and we could not recover.  Please try your request again.';
  
  /**  
   * Override the default Exception class constructor
   * Allows nice usermessages to be part of the exception
   * 
   * @param string $sMessage a System (programmer) message
   * @param int $code the user specified level
   * @param a nice message to display to users
   */
  public function __construct($sMessage = NULL, $code = E_ERROR, $uMessage = NULL){
    if ($uMessage !== NULL){
      $this->_uMessage = $uMessage;
    }
    parent::__construct($sMessage, $code);
  }
  
  /**  
   * Returns the Nice user message
   * 
   * @return string contents of SELF::uMessage
   */
  public function getUserMessage(){
    return $this->_uMessage;
  }
  
  /**
   * Handle a pear error
   * @param Pear_Error $error
   */
  public static function pearError($error){
    throw new Foundation_Exception('Pear Error: ' . $error->getMessage(), $error->getCode());
  }
  
}
//define PHP 5.3 errors not included in < 5.3
if(!defined('E_RECOVERABLE_ERROR')){
  define('E_RECOVERABLE_ERROR',4096);
}
if(!defined('E_DEPRECATED')){
  define('E_DEPRECATED',8192);
}
if(!defined('E_USER_DEPRECATED')){
  define('E_USER_DEPRECATED',16384);
}
/**
 * Handle PEAR errors with a foundation exception
 */
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array('Foundation_Exception', 'pearError'));
?>
