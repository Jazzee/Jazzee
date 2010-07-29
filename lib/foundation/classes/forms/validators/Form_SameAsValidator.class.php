<?php
/**
 * Check to see if the value is identical to another value
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_SameAsValidator extends Form_Validator{
  public function validate(FormInput $input){
    if($input->{$this->_e->name} != $input->{$this->_ruleSet}){
      $this->addError('Does not match ' . $this->_e->field->form->elements[$this->_ruleSet]->label);
      return false;
    }
    return true;
  }
}
?>