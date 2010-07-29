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
  
  public function hasListItems(){
    return false;
  }
  
public function getPropertiesForm(){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Edit {$this->element->title} properties"));
    $element = $field->newElement('TextInput','title');
    $element->label = 'Title';
    $element->addValidator('NotEmpty');
    $element->value = $this->element->title;
    
    $element = $field->newElement('RadioList','required');
    $element->label = 'Is Element Required?';
    $element->addValidator('NotEmpty');
    $element->addItem(1,'Yes');
    $element->addItem(0, 'No');
    $element->value = (int)$this->element->required;
    
    $element = $field->newElement('TextInput','instructions');
    $element->label = 'Instructions';
    $element->value = $this->element->instructions;
    
    $element = $field->newElement('TextInput','format');
    $element->label = 'Format';
    $element->value = $this->element->format;
    
    $element = $field->newElement('TextInput','min');
    $element->addValidator('Integer');
    $element->label = 'Minimum Length';
    $element->format = 'Number of characters';
    $element->value = (int)$this->element->min;
    
    $element = $field->newElement('TextInput','max');
    $element->addValidator('Integer');
    $element->label = 'Maximum Length';
    $element->format = 'Number of characters';
    $element->value = (int)$this->element->max;
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  public function setProperties(FormInput $input){
    $this->element->title = $input->title;
    $this->element->instructions = $input->instructions;
    $this->element->required = $input->required;
    $this->element->format = $input->format;
    $this->element->min = $input->min;
    $this->element->max = $input->max;
    $this->element->save();
  }
}
?>