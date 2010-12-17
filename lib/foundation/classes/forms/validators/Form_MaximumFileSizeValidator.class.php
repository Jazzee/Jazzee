<?php
/**
 * Check that the file size is below the maximum
 * Also set the FileInputElement::maxSize value
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_MaximumFileSizeValidator extends Form_Validator{
  
  /**
   * Do our own constructor so we can set the maxfilesize
   * @param Form_Element $e the element we are validating
   * @param mixed $ruleSet rules set to use might be an array of rules or just a value to match
   * @param Form_ValidatorSet $set the set we belogn to
   */
  public function  __construct(Form_Element $e, $ruleSet, Form_ValidatorSet $set){
    parent::__construct($e,$ruleSet,$set);
    $this->e->maxSize = $this->ruleSet;
  }
  
  public function validate(FormInput $input){
    if($this->ruleSet AND !is_null($input->{$this->e->name})){
      $fileArr = $input->{$this->e->name};
      if($fileArr['size'] > $this->ruleSet){
        $this->addError('File is too large.  Your file is: ' . convertBytesToString($fileArr['size'] - $this->ruleSet) . ' bigger than the maximum size of ' . convertBytesToString($this->ruleSet, 0));
        return false;
      }
    }
    return true;
  }
  
  public function preRender(){
    $this->e->maxSize = $this->ruleSet;
    $this->e->format .=  'Maximum file size: ' . convertBytesToString($this->ruleSet, 0);
  }
}
?>
