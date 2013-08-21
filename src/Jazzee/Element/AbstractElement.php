<?php
namespace Jazzee\Element;

/**
 * The Abstract Application Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
abstract class AbstractElement implements \Jazzee\Interfaces\Element, \Jazzee\Interfaces\XmlElement, \Jazzee\Interfaces\PdfElement
{

  /**
   * The Element entity
   * @var \Jazzee\Entity\Element
   */
  protected $_element;

  /**
   * The controller that is using this
   * @var \Jazzee\Controller
   */
  protected $_controller;

  public function __construct(\Jazzee\Entity\Element $element)
  {
    $this->_element = $element;
  }

  public function setController(\Jazzee\Controller $controller)
  {
    $this->_controller = $controller;
  }

  public function rawValue(\Jazzee\Entity\Answer $answer)
  {
    return html_entity_decode($this->displayValue($answer));
  }

  public function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf)
  {
    return $this->rawValue($answer);
  }

  public function pdfValueFromArray(array $answerData, \Jazzee\ApplicantPDF $pdf){
    foreach($answerData['elements'] as $arr){
      if($arr['id'] == $this->_element->getId()){
        return $arr['displayValue'];
      }
    }

    return null;
  }

  /**
   * Abstract element kills all queries
   */
  public function testQuery(\Jazzee\Entity\Answer $answer, \stdClass $obj)
  {
    return false;
  }

  /**
   * Compare an element to another element
   *
   *
   * @return array
   */
  public function compareWith(\Jazzee\Entity\Element $element)
  {
    $differences = array(
      'different' => false,
      'title' => $this->_element->getTitle(),
      'properties' => array(),
      'thisListItems' => array(),
      'otherListItems' => array()
    );
    $arr = array(
      'title' => 'Title',
      'name' => 'Name',
      'format' => 'Format',
      'min' => 'Minimum Value',
      'max' => 'Maximum Value',
      'instructions' => 'Instructions',
      'defaultValue' => 'Default Value'
    );
    foreach ($arr as $name => $niceName) {
      $func = 'get' . ucfirst($name);
      if ($this->_element->$func() != $element->$func()) {
        $differences['different'] = true;
        $differences['properties'][] = array(
          'name' => $niceName,
          'type' => 'textdiff',
          'this' => $this->_element->$func(),
          'other' => $element->$func()
        );
      }
    }

    foreach ($this->_element->getListItems() as $item) {
      $differences['thisListItems'][] = $item->getValue();
    }
    foreach ($element->getListItems() as $item) {
      $differences['otherListItems'][] = $item->getValue();
    }

    return $differences;
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
    if ($value = $this->rawValue($answer)) {
      switch ($version) {
        case 1:
          $eXml->appendChild($dom->createCDATASection(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value)));
          break;
        case 2:
          $vXml = $dom->createElement('value');
          $vXml->appendChild($dom->createCDATASection(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value)));
          $eXml->appendChild($vXml);
          break;
      }
    }
    
    return $eXml;
  }
  
  /**
   * Format element answer data into an array
   * 
   * @param array $elementAnswers
   * 
   * @return array
   */
  public function formatApplicantArray(array $elementAnswers)
  {
    $arr = array(
      'id' => $this->_element->getId(),
      'title' => $this->_element->getTitle(),
      'name' => $this->_element->getName(),
      'type' => $this->_element->getType()->getClass(),
      'weight' => $this->_element->getWeight(),
      'values' => array()
    );
    foreach($elementAnswers as $elementAnswer){
      $arr['values'][] = $this->arrayValue($elementAnswer);
    }
    $arr['displayValue'] = $this->arrayDisplayValue($arr['values']);

    return $arr;
  }
  
  /**
   * Format a single element answer into an array
   * 
   * @param array $elementAnswer
   * 
   * @return array
   */
  abstract protected function arrayValue(array $elementAnswer);
  
  /**
   * Format values into a display value
   * 
   * @param array $values
   * 
   * @return string
   */
  protected function arrayDisplayValue(array $values)
  {
    if (isset($values[0])) {
      return $values[0]['value'];
    }

    return '';
  }

  /**
   * Default to an empty list of configuration variables
   * @param \Jazzee\Configuration $configuration
   * @return array
   */
  public static function getConfigurationVariables(\Jazzee\Configuration $configuration)
  {
    return array();
  }

  /**
   * By default no special processing needs to take place when removing element answers
   * @param \Jazzee\Entity\Answer $answer
   */
  public function removeElementAnswer(\Jazzee\Entity\ElementAnswer $elementAnswer)
  {
    return;
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
          $values[] = $arr2['value'];
        }
      }
    }

    return implode("\n", $values);
  }
}