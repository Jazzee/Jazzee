<?php
namespace Jazzee\Element;

/**
 * Checkbox List Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class CheckboxList extends AbstractElement
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementCheckboxList.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    $element = $field->newElement('CheckboxList', 'el' . $this->_element->getId());
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
    if (!empty($input)) {
      foreach ($input as $position => $value) {
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
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    foreach ($elementsAnswers as $elementAnswer) {
      $arr[] = $this->_element->getItemById($elementAnswer->getEInteger())->getValue();
    }

    return empty($arr) ? null : implode(', ', $arr);
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

  public function arrayDisplayValue(array $values)
  {
    $arr = array();
    foreach ($values as $value) {
      $arr[] = $value['value'];
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
        foreach ($elementsAnswers as $elementsAnswer) {
          $value = $this->_element->getItemById($elementsAnswer->getEInteger())->getValue();
          $name = $this->_element->getItemById($elementsAnswer->getEInteger())->getName();
          $id = $this->_element->getItemById($elementsAnswer->getEInteger())->getId();
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
    foreach ($elementsAnswers as $elementsAnswer) {
      if (preg_match($obj->pattern, $this->_element->getItemById($elementsAnswer->getEInteger())->getValue())) {
        return true;
      }
    }

    return false;
  }

}