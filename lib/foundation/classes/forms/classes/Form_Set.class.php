<?php
/**
 * Abstract class to define sets and their iteration methods
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
abstract class Form_Set{
  /**
   * Holds the set
   * @var array
   */
  private $_set;
  
  /**
   * Current postiion in the $_set array
   * @var int
   */
  private $_key;
  
  public function __construct(){
    $this->_set = array();
    $this->_key = 0;
  }
  
  /**
   * Reset the set
   */
  public function reset(){
    $this->_key = 0;
  }
  
  /**
   * Get the next item
   * @return mixed|false if there isnt another item
   */
  public function next(){
    if(empty($this->_set)) return false;
    if($this->_key >= count($this->_set)){
      $this->reset();
      return false;
    }
    return $this->_set[$this->_key++];
  }
  
  /**
   * Get the previous item
   * @return mixed
   */
  public function previous(){
    if(0 == $this->_key){
      $this->_key = count($this->_set)-1;
    }
    return $this->next();
  }
  
  /**
   * Add somethign to the set
   * @param mixed $obj the object to add
   */
  protected function add($obj){
    $this->_set[] = $obj;
  }
}
?>
