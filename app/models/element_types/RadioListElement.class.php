<?php
/**
 * Radio List Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class RadioListElement extends ApplyElement {
  public function addToField(Form_Field $field){
    $element = $field->newElement('RadioList', 'el' . $this->element->id);
    $element->label = $this->element->title;
    $element->instructions = $this->element->instructions;
    $element->format = $this->element->format;
    $element->value = $this->element->defaultValue;
    if($this->element->required){
      $element->addValidator('NotEmpty');
    }
    foreach($this->element->ListItems as $item){
      $element->addItem($item->id, $item->value);
    }
    return $element;
  }
  
  public function setValueFromInput($input){
    $this->value = $input;
  }
  
  public function setValueFromAnswer($answers){
    if(isset($answers[0]))
      $this->value = $answers[0]->eInteger;
  }
  
  public function getAnswers(){
    if(is_null($this->value)) return array();
    $elementAnswer = new ElementAnswer;
    $elementAnswer->elementID = $this->element->id;
    $elementAnswer->position = 0;
    $elementAnswer->eInteger = $this->value;
    return array($elementAnswer);
  }
  
  public function displayValue(){
    $keys = $this->element->ListItems->getPrimaryKeys();
    if(($key = array_search($this->value, $keys)) !== false)
      return (string)$this->element->ListItems->get($key)->value;
    return null;
  }
  
  public function formValue(){
    return (integer)$this->value;
  }
}
?>