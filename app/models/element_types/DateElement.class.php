<?php
/**
 * Date Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class DateElement extends ApplyElement {
  public function addToField(Form_Field $field){
    $element = $field->newElement('DateInput', 'el' . $this->element->id);
    $element->label = $this->element->title;
    $element->instructions = $this->element->instructions;
    $element->format = $this->element->format;
    if(!empty($this->element->defaultValue))
      $element->value = $this->element->defaultValue;
    if($this->element->required){
      $element->addValidator('NotEmpty');
    }
    $element->addFilter('DateFormat', 'Y-m-d H:i:s');
    $element->addValidator('Date');
    return $element;
  }
  
  public function setValueFromInput($input){
    $this->value = $input;
  }
  
  public function setValueFromAnswer($answers){
    if(isset($answers[0]))
      $this->value = $answers[0]->eDate;
  }
  
  public function getAnswers(){
    if(is_null($this->value)) return array();
    $elementAnswer = new ElementAnswer;
    $elementAnswer->elementID = $this->element->id;
    $elementAnswer->position = 0;
    $elementAnswer->eDate = $this->value;
    return array($elementAnswer);
  }
  
  public function displayValue(){
    return date('m/d/Y', strtotime($this->value));
  }
  
  public function formValue(){
    return date('m/d/Y', strtotime($this->value));
  }
}
?>