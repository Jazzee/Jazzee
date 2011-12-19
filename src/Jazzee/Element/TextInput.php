<?php
namespace Jazzee\Element;
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
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    return $element;
  }
  
  public function getElementAnswers($input){
    $elementAnswers = array();
    if(!is_null($input)){
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEShortString($input);
      $elementAnswers[] = $elementAnswer;
    }
    return $elementAnswers;
  }
  
  public function displayValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return htmlentities($elementsAnswers[0]->getEShortString(),ENT_COMPAT,'utf-8');
    }
    return null;
  }
  
  public function formValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return $elementsAnswers[0]->getEShortString();
    }
    return null;
  }
  
  public function rawValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return $elementsAnswers[0]->getEShortString();
    }
    return null;
  }
}
?>