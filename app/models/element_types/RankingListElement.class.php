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
}
?>