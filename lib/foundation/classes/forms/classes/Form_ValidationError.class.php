<?php
/**
 * Validation Errors return this object
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_ValidationError {
  /**
   * The Message
   * @var string
   */
  private $_message = '';
  
  /**
   * Constructor
   * @param string $m the message text
   */
  public function __construct($m){
    $this->_message = $m;
  }
  
  /**
   * Return the message
   * @return string
   */
  public function getMessage(){
    return $this->_message;
  }
}
?>