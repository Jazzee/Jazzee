<?php

namespace Jazzee\Element;

/**
 * Textarea Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Textarea extends AbstractElement
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementTextarea.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    $element = $field->newElement('Textarea', 'el' . $this->_element->getId());
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

    //restrict to 1M chars so the DB cant be filled with 4GB text dumps
    if (!$this->_element->getMax() or $this->_element->getMax() > 1000000) {
      $max = 1000000;
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
      $elementAnswer->setEText($input);
      $elementAnswers[] = $elementAnswer;
    }

    return $elementAnswers;
  }

  public function displayValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return nl2br(htmlentities($elementsAnswers[0]->getEText(), ENT_COMPAT, 'utf-8'));
    }

    return null;
  }

  public function formValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return $elementsAnswers[0]->getEText();
    }

    return null;
  }

  public function rawValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return $elementsAnswers[0]->getEText();
    }

    return null;
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

    return preg_match($obj->pattern, $elementsAnswers[0]->getEText());
  }

}