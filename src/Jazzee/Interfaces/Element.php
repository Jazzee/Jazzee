<?php
namespace Jazzee\Interfaces;

/**
 * Interface for Jazzee Elements
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface Element
{
  /**
   * The path to the pagebuilder javascript
   */

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElement.js';

  /**
   * Constructor
   *
   * @param \Jazzee\Entity\Element $element
   */
  function __construct(\Jazzee\Entity\Element $element);

  /**
   * Set the controller
   *
   * @param  \Jazzee\Controller $controller
   */
  function setController(\Jazzee\Controller $controller);

  /**
   * Add a form element to the supplied field
   * @param \Foundation\Form\Field $field
   * @return \Foundation\Form\Element
   */
  function addToField(\Foundation\Form\Field $field);

  /**
   * Create element answers and attach them to the answer
   *
   * @param mixed $input
   * @return array \Jazzee\Entity\AnswerElement
   */
  function getElementAnswers($input);

  /**
   * Remove element answers
   *
   * @param \Jazzee\Entity\ElementAnswer $elementAnswer
   */
  function removeElementAnswer(\Jazzee\Entity\ElementAnswer $elementAnswer);

  /**
   * Get the value of the element formated for display
   *
   * @param \Jazzee\Entity\Answer $answer
   * @return string
   */
  function displayValue(\Jazzee\Entity\Answer $answer);

  /**
   * Get the value of the element that \Foundation\Form\Element will accept
   *
   * @param \Jazzee\Entity\Answer $answer
   * @return mixed
   */
  function formValue(\Jazzee\Entity\Answer $answer);

  /**
   * Get the raw value of the element
   *
   * @param \Jazzee\Entity\Answer $answer
   * @return mixed
   */
  function rawValue(\Jazzee\Entity\Answer $answer);

  /**
   * Test a query
   * Checks if answer meets query parameters
   * @param \Jazzee\Entity\Answer $answer
   * @param \stdClass $query
   * @returns boolean
   */
  public function testQuery(\Jazzee\Entity\Answer $answer, \stdClass $obj);
  
  /**
   * Format element answer data into an array
   * 
   * @param array $elementAnswers
   * 
   * @return array
   */
  public function formatApplicantArray(array $elementAnswers);

  /**
   * Compare an element to another element
   *
   *
   * @return array
   */
  public function compareWith(\Jazzee\Entity\Element $element);

  /**
   * Get Configuration Variables
   * Allows an element to list any special configuration information for the page builder
   * @param \Jazzee\Configuration $configuration
   * @return array
   */
  public static function getConfigurationVariables(\Jazzee\Configuration $configuration);
}