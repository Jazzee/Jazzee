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
    if($this->_ruleSet AND !is_null($input->{$this->_e->name})){
      if(strlen($input->{$this->_e->name}) > $this->_ruleSet){
        $this->addError('Input is too large.  Your input is: ' . (strlen($input->{$this->_e->name}) - $this->_ruleSet) . ' characters bigger than the maximum size of ' . $this->_ruleSet);
        return false;
      }
    }
    return true;
  }
  
  public function preRender(){
    $this->_e->format .=  "Maximum length: {$this->_ruleSet} characters.  ";
  }
}
?>
