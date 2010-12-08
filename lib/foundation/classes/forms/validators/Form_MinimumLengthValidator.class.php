<?php
/**
 * Check that the input string is at least the minimum length
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_MinimumLengthValidator extends Form_Validator{
  public function validate(FormInput $input){
    if($this->_ruleSet AND !is_null($input->{$this->_e->name})){
      if(strlen($input->{$this->_e->name}) < $this->_ruleSet){
        $this->addError('Input is too small.  Your input is: ' . ($this->_ruleSet - strlen($input->{$this->_e->name})) . ' characters smaller than the minimum size of ' . $this->_ruleSet);
        return false;
      }
    }
    return true;
  }
  
  public function preRender(){
    $this->_e->format .=  "Minimum length: {$this->_ruleSet} characters.  ";
  }
}
?>
