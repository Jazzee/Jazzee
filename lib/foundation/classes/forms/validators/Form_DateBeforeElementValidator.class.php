<?php
/**
 * Check to see if the date entered is before the date in another element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_DateBeforeElementValidator extends Form_Validator{
  public function validate(FormInput $input){
    if(!is_null($input->{$this->e->name}) AND strtotime($input->{$this->e->name}) >= strtotime($input->{$this->ruleSet})){
      $this->addError('Must be before date in ' . $this->e->field->form->elements[$this->ruleSet]->label);
      return false;
    }
    return true;
  }
}
?>
