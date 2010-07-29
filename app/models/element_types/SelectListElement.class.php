<?php
/**
 * Select List Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class SelectListElement extends ApplyElement {
  public function addToField(Form_Field $field){
    $element = $field->newElement('SelectList', 'el' . $this->element->id);
    $element->label = $this->element->title;
    $element->instructions = $this->element->instructions;
    $element->format = $this->element->format;
    $element->value = $this->element->defaultValue;
    if($this->element->required){
      $element->addValidator('NotEmpty');
    }
    $element->addItem(false, '');
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
  
  public function hasListItems(){
    return true;
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
    
    $element = $field->newElement('RadioList','defaultValue');
    $element->label = 'Default Value';
    $element->addValidator('NotEmpty');
    $element->addItem(0, 'No Default');
    foreach($this->element->ListItems as $item){
      $element->addItem($item->id, $item->value);
    }
    $element->value = $this->element->defaultValue;
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  public function setProperties(FormInput $input){
    $this->element->title = $input->title;
    $this->element->instructions = $input->instructions;
    $this->element->required = $input->required;
    $this->element->defaultValue = $input->defaultValue;
    $this->element->save();
  }
}
?>