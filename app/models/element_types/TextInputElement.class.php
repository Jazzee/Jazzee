<?php
/**
 * TextInput Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class TextInputElement extends ApplyElement {
  public function addToField(Form_Field $field){
    $element = $field->newElement('TextInput', 'el' . $this->element->id);
    $element->label = $this->element->title;
    $element->instructions = $this->element->instructions;
    $element->format = $this->element->format;
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
    $elementAnswer = new Entity\ElementAnswer;
    $elementAnswer->setElement($this->element);
    $elementAnswer->setPosition(0);
    $elementAnswer->setEShortString($this->value);
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