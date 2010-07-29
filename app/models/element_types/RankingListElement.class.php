<?php
/**
 * Ranking List Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class RankingListElement extends ApplyElement {
 /**
  * RankingLists take multiple inputs as an array
  * @param array $value
  */
  protected $value = array();
  
  public function addToField(Form_Field $field){
    $element = $field->newElement('RankingList', 'el' . $this->element->id);
    $element->label = $this->element->title;
    $element->instructions = $this->element->instructions;
    $element->format = $this->element->format;
    $element->rankItems = $this->element->max;
    $element->minimumItems = $this->element->min;
    if($this->element->required){
      $element->addValidator('NotEmpty');
    }
    foreach($this->element->ListItems as $item){
      $element->addItem($item->id, $item->value);
    }
    return $element;
  }
  
  public function setValueFromInput($input){
    if(!is_null($input))
      $this->value = $input;
  }
  
  public function setValueFromAnswer($answers){
    $this->value = array();
    foreach($answers as $answerElement)
      $this->value[$answerElement->position] = $answerElement->eInteger;
  }
  
  public function getAnswers(){
    $return = array();
    foreach($this->value as $position => $value){
      if($value){
        $elementAnswer = new ElementAnswer;
        $elementAnswer->elementID = $this->element->id;
        $elementAnswer->position = $position;
        $elementAnswer->eInteger = $value;
        $return[] = $elementAnswer;
      }
    }
    return $return;
  }
  
  public function displayValue(){
    $keys = $this->element->ListItems->getPrimaryKeys();
    $arr = array();
    foreach($this->value as $rank => $value){
      if(($key = array_search($value, $keys)) !== false)
        $arr[] = ordinalValue($rank+1) . ' choice: ' . (string)$this->element->ListItems->get($key)->value;
    }
    return implode('<br />', $arr);
  }
  
  public function formValue(){
    return $this->value;
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
    
    $element = $field->newElement('TextInput','max');
    $element->addValidator('Integer');
    $element->label = 'Items to Rank';
    $element->value = (int)$this->element->max;
    
    $element = $field->newElement('TextInput','min');
    $element->addValidator('Integer');
    $element->label = 'Minimum Required';
    $element->value = (int)$this->element->min;
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  public function setProperties(FormInput $input){
    $this->element->title = $input->title;
    $this->element->instructions = $input->instructions;
    $this->element->required = $input->required;
    $this->element->defaultValue = $input->defaultValue;
    $this->element->min = $input->min;
    $this->element->max = $input->max;
    $this->element->save();
  }
}
?>