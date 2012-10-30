<?php

namespace Jazzee\Element;

/**
 * ShortDate Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ShortDate extends AbstractElement
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementShortDate.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    $element = $field->newElement('ShortDateInput', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if ($this->_element->isRequired()) {
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    $filter = new \Foundation\Form\Filter\DateFormat($element, 'c');
    $element->addFilter($filter);

    return $element;
  }

  public function getElementAnswers($input)
  {
    $elementAnswers = array();
    if (!is_null($input)) {
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEDate($input);
      $elementAnswers[] = $elementAnswer;
    }

    return $elementAnswers;
  }

  public function displayValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return $elementsAnswers[0]->getEDate()->format('F Y');
    }

    return null;
  }
  
  protected function arrayValue(array $elementAnswer){
    $value = array(
      'value' => $elementAnswer['eDate']->format('F Y')
    );

    return $value;
  }

  public function formValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return $elementsAnswers[0]->getEDate()->format('Y-n-j');
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
    $match = false;
    $date = $elementsAnswers[0]->getEDate();
    if (isset($obj->after)) {
      if ($date > new \DateTime($obj->after)) {
        $match = true;
      } else {
        return false;
      }
    }
    if (isset($obj->before)) {
      if ($date < new \DateTime($obj->before)) {
        $match = true;
      } else {
        return false;
      }
    }

    return $match;
  }

}