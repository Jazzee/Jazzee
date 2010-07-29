<?php
/**
 * Check to see if the value is empty
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_NotEmptyValidator extends Form_Validator{
  public function validate(FormInput $input){
    if(is_null($input->{$this->_e->name})){
      $this->addError('This field is required and you left it blank');
      return false;
    }
    return true;
  }
  
  public function preRender(){
    $this->_e->required = true;
  }
}
?>
