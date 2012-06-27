<?php

namespace Jazzee\Element;

/**
 * Ranking List Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class RankingList extends AbstractElement
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementRankingList.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    $element = $field->newElement('RankingList', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());

    $element->setRequiredItems($this->_element->getMin());
    $element->setTotalItems($this->_element->getMax());
    if ($this->_element->isRequired()) {
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    foreach ($this->_element->getListItems() as $item) {
      if ($item->isActive()) {
        $element->newItem($item->getId(), $item->getValue());
      }
    }

    return $element;
  }

  public function getElementAnswers($input)
  {
    $elementAnswers = array();
    foreach ($input as $position => $value) {
      if (!empty($value)) {
        $elementAnswer = new \Jazzee\Entity\ElementAnswer;
        $elementAnswer->setElement($this->_element);
        $elementAnswer->setPosition($position);
        $elementAnswer->setEInteger($value);
        $elementAnswers[] = $elementAnswer;
      }
    }

    return $elementAnswers;
  }

  public function displayValue(\Jazzee\Entity\Answer $answer)
  {
    $arr = array();
    $elementAnswers = $answer->getElementAnswersForElement($this->_element);
    foreach ($elementAnswers as $position => $elementAnswer) {
      $arr[] = \Foundation\Utility::ordinalValue($position + 1) . ' ' . $this->_element->getItemById($elementAnswer->getEInteger())->getValue();
    }

    return empty($arr) ? null : implode(', ', $arr);
  }

  public function formValue(\Jazzee\Entity\Answer $answer)
  {
    $arr = array();
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    foreach ($elementsAnswers as $elementsAnswer) {
      $arr[] = $this->_element->getItemById($elementsAnswer->getEInteger())->getId();
    }

    return $arr;
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
    foreach ($elementsAnswers as $elementsAnswer) {
      if (isset($obj->all)) {
        if (preg_match($obj->all, $this->_element->getItemById($elementsAnswer->getEInteger())->getValue())) {
          return true;
        }
      }
      if (isset($obj->{$elementsAnswer->getPosition()})) {
        if (preg_match($obj->{$elementsAnswer->getPosition()}, $this->_element->getItemById($elementsAnswer->getEInteger())->getValue())) {
          return true;
        }
      }
    }

    return false;
  }

}