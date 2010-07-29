<?php
/**
 * A Checkbox Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_CheckboxListElement extends Form_ListElement{
  /**
   * Constructor
   * Make $value and array
   */
  public function __construct(Form_Field $field){
    parent::__construct($field);
    $this->value = array();
  }
  
  /**
   * Set the value
   * Checkboxes use an array of values since multiple items can be checked
   * @param $value string|array
   */
  public function setValue($value){
    if(is_array($value)){
      foreach($value as $v){
        $this->value[] = $v;
      }
    } else {
      $this->value[] = $value;
    }
  }
  
}
?>