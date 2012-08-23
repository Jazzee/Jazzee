<?php
namespace Jazzee\Element;

/**
 * The Abstract Application Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
abstract class AbstractElement implements \Jazzee\Interfaces\Element
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
    foreach($arr as $name => $niceName){
      $func = 'get' . ucfirst($name);
      if($this->_element->$func() != $element->$func()){
        $differences['different'] = true;
        $differences['properties'][] = array(
          'name' => $niceName,
          'type' => 'textdiff',
          'this' => $this->_element->$func(),
          'other' => $element->$func()
        );
      }
    }

    foreach($this->_element->getListItems() as $item){
      $differences['thisListItems'][] = $item->getValue();
    }
    foreach($element->getListItems() as $item){
      $differences['otherListItems'][] = $item->getValue();
    }

    return $differences;
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

}