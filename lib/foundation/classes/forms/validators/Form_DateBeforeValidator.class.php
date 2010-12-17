<?php
/**
 * Check to see if the date entered is before a specific date
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_DateBeforeValidator extends Form_Validator{
  public function validate(FormInput $input){
    if(!is_null($input->{$this->e->name}) AND strtotime($input->{$this->e->name}) > strtotime($this->rulesSet)){
      $this->addError('Date must be before ' . $this->ruleSet);
      return false;
    }
    return true;
  }
}
?>
