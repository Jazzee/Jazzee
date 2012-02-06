<?php
namespace Jazzee\Element;
/**
 * Select List Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class SelectList extends AbstractElement {
  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementSelectList.js';
  
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
  
  public function getElementAnswers($input){
    $elementAnswers = array();
    if(!is_null($input)){
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEInteger($input);
      $elementAnswers[] = $elementAnswer;
    }
    return $elementAnswers;
  }
  
  public function displayValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return $this->_element->getItemById($elementsAnswers[0]->getEInteger())->getValue();
    }
    return null;
  }
  
  public function formValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return $elementsAnswers[0]->getEInteger();
    }
    return null;
  }
}
?>