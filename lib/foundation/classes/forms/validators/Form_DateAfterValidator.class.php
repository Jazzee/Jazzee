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
    if(!empty($input->{$this->_e->name}) AND strtotime($input->{$this->_e->name}) <= strtotime($this->_ruleSet)){
      $this->addError('Date must be after ' . $this->_ruleSet);
      return false;
    }
    return true;
  }
}
?>
