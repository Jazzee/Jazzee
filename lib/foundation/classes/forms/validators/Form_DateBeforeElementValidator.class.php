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
    if(!empty($input->{$this->_e->name}) AND strtotime($input->{$this->_e->name}) >= strtotime($input->{$this->_ruleSet})){
      $this->addError('Must be before date in ' . $this->_e->field->form->elements[$this->_ruleSet]->label);
      return false;
    }
    return true;
  }
}
?>
