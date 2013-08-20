<?php

namespace Jazzee\Element;

/**
 * TextInput Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class TextInput extends AbstractElement
{
  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementTextInput.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    $element = $field->newElement('TextInput', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if ($this->_element->isRequired()) {
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    if ($this->_element->getMin()) {
      $validator = new \Foundation\Form\Validator\MinimumLength($element, (int) $this->_element->getMin());
      $element->addValidator($validator);
    }
    //restrict to 255 for DB
    if (!$this->_element->getMax() or $this->_element->getMax() > 255) {
      $max = 255;
    } else {
      $max = (int) $this->_element->getMax();
    }
    $validator = new \Foundation\Form\Validator\MaximumLength($element, $max);
    $element->addValidator($validator);
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    return $element;
  }

  public function getElementAnswers($input)
  {
    $elementAnswers = array();
    if (!is_null($input)) {
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEShortString($input);
      $elementAnswers[] = $elementAnswer;
    }

    return $elementAnswers;
  }

  public function displayValue(\Jazzee\Entity\Answer $answer)
  {

    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return $elementsAnswers[0]->getEShortString();
    }

    return null;
  }
  
  protected function arrayValue(array $elementAnswer){

    $value = array(
      'value' => $elementAnswer['eShortString']
    );

    return $value;
  }

  public function formValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return \Foundation\Form\Filter\Safe::unFilter($elementsAnswers[0]->getEShortString());
    }

    return null;
  }

  public function rawValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return \Foundation\Form\Filter\Safe::unFilter($elementsAnswers[0]->getEShortString());
    }

    return null;
  }

  /**
   * Get the template pdf values of the element
   * Takes all the answers and returns a single string that sumerizes the data
   *
   * @param array $answers
   * @return string
   */
  public function pdfTemplateValue(array $answers)
  {
    $values = array();
    foreach($answers as $answer){
      $values[] = $this->rawValue($answer);
    }

    return implode("\n", $values);
  }

  /**
   * Get the template pdf values of the element from array data
   * Takes all the answers and returns a single string that sumerizes the data
   *
   * @param array $answers
   * @return string
   */
  function pdfTemplateValueFromArray(array $answers)
  {
    $values = array();
    foreach($answers as $answer){
      if(array_key_exists($this->_element->getId(), $answer['elements'])){
        $arr = $this->formatApplicantArray($answer['elements'][$this->_element->getId()]);
        foreach($arr['values'] as $arr2){
          $values[] = \Foundation\Form\Filter\Safe::unFilter($arr2['value']);
        }
      }
    }

    return implode("\n", $values);
  }

  /**
   * Perform a regular expression match on each value
   * @param \Jazzee\Entity\Answer $answer
   * @param \stdClass $obj
   * @return boolean
   */
  public function testQuery(\Jazzee\Entity\Answer $answer, \stdClass $obj)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (!isset($elementsAnswers[0])) {
      return false;
    }

    return preg_match($obj->pattern, $elementsAnswers[0]->getEShortString());
  }

}