<?php
/**
 * TextArea Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class TextareaElement extends ApplyElement {
  public function addToField(Form_Field $field){
    $element = $field->newElement('Textarea', 'el' . $this->element->id);
    $element->label = $this->element->title;
    $element->instructions = $this->element->instructions;
    $element->format = $this->element->format;
    $element->value = $this->element->defaultValue;
    if($this->element->required){
      $element->addValidator('NotEmpty');
    }
    if($this->element->min){
      $element->addValidator('MinimumLength', (int)$this->element->min);
    }
    if($this->element->max){
      $element->addValidator('MaximumLength', (int)$this->element->max);
    }
    
    return $element;
  }
  
  public function setValueFromInput($input){
    $this->value = $input;
  }
  
  public function setValueFromAnswer($answers){
    if(isset($answers[0]))
      $this->value = $answers[0]->eLongString;
  }
  
  public function getAnswers(){
    if(is_null($this->value)) return array();
    $elementAnswer = new ElementAnswer;
    $elementAnswer->elementID = $this->element->id;
    $elementAnswer->position = 0;
    $elementAnswer->eLongString = $this->value;
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