<?php
/**
 * Check to see if the element matches the regex
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_RegexValidator extends Form_Validator{
  public function validate(FormInput $input){
    if(!is_null($input->{$this->e->name}) AND !preg_match($this->ruleSet, $input->{$this->e->name})){
      $this->addError('Regex did not match');
      return false;
    }
    return true;
  }
}
?>