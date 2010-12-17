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
    if($this->ruleSet AND !is_null($input->{$this->e->name})){
      if(strlen($input->{$this->e->name}) < $this->ruleSet){
        $this->addError('Input is too small.  Your input is: ' . ($this->ruleSet - strlen($input->{$this->e->name})) . ' characters smaller than the minimum size of ' . $this->ruleSet);
        return false;
      }
    }
    return true;
  }
  
  public function preRender(){
    $this->e->format .=  "Minimum length: {$this->ruleSet} characters.  ";
  }
}
?>
