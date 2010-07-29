<?php
/**
 * A Date Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_DateInputElement extends Form_InputElement{
  /**
   * Take a value array and use it to set the value attribute
   * @param array $values
   */
  public function setValue($value){
    $this->value = date('m/d/Y', strtotime($value));
  }
}
?>