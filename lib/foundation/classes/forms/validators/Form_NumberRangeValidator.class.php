<?php
/**
 * Check that a number is within a range
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_NumberRangeValidator extends Form_Validator{
  public function validate(FormInput $input){
    if(!is_array($this->_ruleSet) OR !isset($this->_ruleSet[0]) OR !isset($this->_ruleSet[1])){
      throw new Foundation_Exception("The ruleset for NumberRange must be an array with two elements.");
    }
    
    if(
      !is_null($input->{$this->_e->name}) AND
      ($input->{$this->_e->name} < $this->_ruleSet[0]
      OR $input->{$this->_e->name} > $this->_ruleSet[1])
    ){
      $this->addError("Value must be between {$this->_ruleSet[0]} and {$this->_ruleSet[1]}");
      return false;
    }
    return true;
  }
  
  public function preRender(){
    $this->_e->format .=  "Between {$this->_ruleSet[0]} and {$this->_ruleSet[1]}.  ";
  }
}
?>
