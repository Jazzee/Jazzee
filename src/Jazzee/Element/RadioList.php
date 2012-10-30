<?php

namespace Jazzee\Element;

/**
 * Radio List Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class RadioList extends AbstractElement
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementRadioList.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    $element = $field->newElement('RadioList', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
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
    if (!is_null($input)) {
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEInteger($input);
      $elementAnswers[] = $elementAnswer;
    }

    return $elementAnswers;
  }

  public function displayValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return $this->_element->getItemById($elementsAnswers[0]->getEInteger())->getValue();
    }

    return null;
  }
  
  protected function arrayValue(array $elementAnswer)
  {
    $item = $this->_element->getItemById($elementAnswer['eInteger']);
    $value = array(
      'value' => $item->getValue(),
      'name' => $item->getName(),
      'id' => $item->getId(),
    );

    return $value;
  }

  public function formValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return $elementsAnswers[0]->getEInteger();
    }

    return null;
  }

  /**
   * Get the answer value as an xml element
   * @param \DomDocument $dom
   * @param \Jazzee\Entity\Answer $answer
   * @param integer $version
   * @return \DomElement
   */
  public function getXmlAnswer(\DomDocument $dom, \Jazzee\Entity\Answer $answer, $version)
  {
    $eXml = $dom->createElement('element');
    $eXml->setAttribute('elementId', $this->_element->getId());
    $eXml->setAttribute('title', htmlentities($this->_element->getTitle(), ENT_COMPAT, 'utf-8'));
    $eXml->setAttribute('name', htmlentities($this->_element->getName(), ENT_COMPAT, 'utf-8'));
    $eXml->setAttribute('type', htmlentities($this->_element->getType()->getClass(), ENT_COMPAT, 'utf-8'));
    $eXml->setAttribute('weight', $this->_element->getWeight());

    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    switch ($version) {
      case 1:
        if ($value = $this->rawValue($answer)) {
          $eXml->appendChild($dom->createCDATASection(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value)));
        }
        break;
      case 2:
        $value = null;
        if (isset($elementsAnswers[0])) {
          $value = $this->_element->getItemById($elementsAnswers[0]->getEInteger())->getValue();
          $name = $this->_element->getItemById($elementsAnswers[0]->getEInteger())->getName();
          $id = $this->_element->getItemById($elementsAnswers[0]->getEInteger())->getId();
        }
        if ($value) {
          $vXml = $dom->createElement('value');
          $vXml->setAttribute('valueId', $id);
          $vXml->setAttribute('name', htmlentities($name, ENT_COMPAT, 'utf-8'));
          $vXml->appendChild($dom->createCDATASection(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value)));
          $eXml->appendChild($vXml);
        }
        break;
    }

    return $eXml;
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

    return preg_match($obj->pattern, $this->_element->getItemById($elementsAnswers[0]->getEInteger())->getValue());
  }

}