<?php
/**
 * Check to see if the date entered is after a specific date
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_DateAfterValidator extends Form_Validator{
  public function validate(FormInput $input){
    if(!is_null($input->{$this->e->name}) AND strtotime($input->{$this->e->name}) <= strtotime($this->ruleSet)){
      $this->addError('Date must be after ' . $this->ruleSet);
      return false;
    }
    return true;
  }
}
?>
