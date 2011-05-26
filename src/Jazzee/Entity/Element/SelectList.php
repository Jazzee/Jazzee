<?php
namespace Jazzee\Entity\Element;
/**
 * Select List Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class SelectList extends AbstractElement {
  
  public function addToField(\Foundation\Form\Field $field){
    $element = $field->newElement('SelectList', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if($this->_element->isRequired()){
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    } else {
      //only put a blank if it isn't required
      $element->newItem('', '');
    }
    foreach($this->_element->getListItems() as $item){
      if($item->isActive()) $element->newItem($item->getId(), $item->getValue());
    }
    return $element;
  }
  
  public function setValueFromInput($input){
    $this->value = $input;
  }
  
  public function setValueFromAnswer(\Jazzee\Entity\Answer $answer){
    if(isset($answers[0]))
      $this->value = $answers[0]->eInteger;
  }
  
  public function getAnswers(){
    if(is_null($this->value)) return array();
    $elementAnswer = new Entity\ElementAnswer;
    $elementAnswer->setElement($this->element);
    $elementAnswer->setPosition(0);
    $elementAnswer->setEInteger($this->value);
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