<?php
/**
 * Check that the input string is below the maximum length
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_MaximumLengthValidator extends Form_Validator{
  public function validate(FormInput $input){
    if($this->ruleSet AND !is_null($input->{$this->e->name})){
      if(strlen($input->{$this->e->name}) > $this->ruleSet){
        $this->addError('Input is too large.  Your input is: ' . (strlen($input->{$this->e->name}) - $this->ruleSet) . ' characters bigger than the maximum size of ' . $this->ruleSet);
        return false;
      }
    }
    return true;
  }
  
  public function preRender(){
    $this->e->format .=  "Maximum length: {$this->ruleSet} characters.  ";
  }
}
?>
