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
    if(!is_null($input->{$this->e->name}) AND $input->{$this->e->name} != $input->{$this->ruleSet}){
      $this->addError('Does not match ' . $this->e->field->form->elements[$this->ruleSet]->label);
      return false;
    }
    return true;
  }
}
?>