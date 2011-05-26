<?php
namespace Jazzee\Entity\Element;
/**
 * TextInput Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class TextInput extends AbstractElement {
  public function addToField(\Foundation\Form\Field $field){
    $element = $field->newElement('TextInput', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if($this->_element->isRequired()){
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    return $element;
  }
  
  public function setValueFromInput($input){
    $this->value = $input;
  }
  
  public function setValueFromAnswer(\Jazzee\Entity\Answer $answer){
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