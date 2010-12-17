<?php
/**
 * Check usr input to make sure it was an option in the list
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package foundation
 * @subpackage forms
 */
class Form_ChoiceInListValidator extends Form_Validator{
  public function validate(FormInput $input){
    if(!is_array($input->{$this->e->name})) $arr = array($input->{$this->e->name});
    else $arr = $input->{$this->e->name};
    foreach($arr as $value){
      if(!array_key_exists($value, $this->e->getItems())){
        $this->addError('Your chose an invalid option');
        return false;
      }
    }
    return true;
  }
}
?>