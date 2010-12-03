<?php
/**
 * HiddenInput Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class HiddenInputElement extends ApplyElement {
  public function addToField(Form_Field $field){
    $element = $field->newElement('HiddenInput', 'el' . $this->element->id);
    $element->value = $this->element->defaultValue;
    if($this->element->required){
      $element->addValidator('NotEmpty');
    }
    return $element;
  }
  
  public function setValueFromInput($input){
    $this->value = $input;
  }
  
  public function setValueFromAnswer($answers){
    if(isset($answers[0]))
      $this->value = $answers[0]->eShortString;
  }
  
  public function getAnswers(){
    if(is_null($this->value)) return array();
    $elementAnswer = new ElementAnswer;
    $elementAnswer->elementID = $this->element->id;
    $elementAnswer->position = 0;
    $elementAnswer->eShortString = $this->value;
    return array($elementAnswer);
  }
  
  public function displayValue(){
    return (string)$this->value;
  }
  
  public function formValue(){
    return (string)$this->value;
  }
}
?>