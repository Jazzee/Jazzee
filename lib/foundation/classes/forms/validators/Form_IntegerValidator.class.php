<?php
/**
 * Check to see if the value is a number
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_IntegerValidator extends Form_Validator{
  public function validate(FormInput $input){
    if(!is_null($input->{$this->e->name}) AND !filter_Var($input->{$this->e->name}, FILTER_VALIDATE_INT)){
      $this->addError('An integer is required for this field');
      return false;
    }
    return true;
  }
}
?>
