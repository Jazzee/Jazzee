<?php
/**
 * A Short Date Element
 * Just the month and year
 * @author Jon Johnson <jon.johnson@ucsf.edu>f
 * @package foundation
 * @subpackage forms
 */
class Form_ShortDateInputElement extends Form_InputElement{
  /**
   * Take a value array and use it to set the value attribute
   * @param array $values
   */
  public function setValue($value){
    $this->value = date('F Y', strtotime($value));
  }
}
?>