<?php
/**
 * A single email message
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
require_once('Foundation.class.php');
class EmailMessage{  
  /**  
   * Address the email is from
   * @var string
   */
  private $_from;
  
  /**
   * Array of addresses to send the email to
   * @var array
   */
  private $_to = array();
  
  /**
   * Array of addresses to cc the email to
   * @var array
   */
  private $_cc = array();
  
  /**
   * @var string
   */
  public $subject;
  
  /**
   * @var string
   */
  public $body;
  
  /**
   * Convert an address/name pair into a well formed address
   */
  protected function makeAddress($address, $name){
    return trim("{$name} <{$address}>");
  }
  
  /**
   * Set the From address header
   */
  public function from($address, $name=''){
    $this->_from = $this->makeAddress($address, $name);
  }
  
  /**
   * Add a recipient
   */
  public function to($address, $name){
    $this->_to[] = $this->makeAddress($address, $name);
  }
  
  /**
   * Add a carbon copy
   */
  public function cc($address, $name){
    $this->_cc[] = $this->makeAddress($address, $name);
  }
  
  /**
   * Get an array of all the recipients
   * @return array
   */
  public function recipients(){
    return $this->_to;
  }
  
  /**
   * Make an array of all the headers
   * @return array all the headers
   */
  public function headers(){
    $headers = array(
      'From' => $this->_from,
      'Subject' => $this->subject
    );
    return $headers;
  }
}
?>
