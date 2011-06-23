<?php
namespace Jazzee\Entity\Element;
/**
 * Date Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class Date extends AbstractElement {
  public function addToField(\Foundation\Form\Field $field){
    $element = $field->newElement('DateInput', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    if($this->_element->getFormat()){
      $format = $this->_element->getFormat();
    } else {
      $format = 'mm/dd/yyyy eg ' . date('m/d/Y');
    }
    $element->setFormat($format);
    $element->setDefaultValue($this->_element->getDefaultValue());
    if($this->_element->isRequired()){
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    $filter = new \Foundation\Form\Filter\DateFormat($element, 'c');
    $element->addFilter($filter);
    return $element;
  }
  
  public function getElementAnswers($input){
    $elementAnswers = array();
    if(!is_null($input)){
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEDate($input);
      $elementAnswers[] = $elementAnswer;
    }
    return $elementAnswers;
  }
  
  public function displayValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return $elementsAnswers[0]->getEDate()->format('F jS Y');
    }
    return null;
  }
  
  public function formValue(\Jazzee\Entity\Answer $answer){
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if(isset($elementsAnswers[0])){
      return $elementsAnswers[0]->getEDate()->format('Y-n-j');
    }
    return null;
  }
}
?>