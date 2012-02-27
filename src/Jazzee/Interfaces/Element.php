<?php
namespace Jazzee\Interfaces;
/**
 * Interface for Jazzee Elements
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage elements
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
   * Get the pdf value of the element
   * 
   * @param \Jazzee\Entity\Answer $answer
   * @param \Jazzee\ApplicantPDF $pdf
   * @return mixed
   */
  function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf);
}
?>