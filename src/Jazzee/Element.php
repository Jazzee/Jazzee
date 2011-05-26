<?php
namespace Jazzee;
/**
 * Interface for Jazzee Elements
 */
interface Element 
{
  /**
   * Add a form element to the supplied field
   * @param \Foundation\Form\Field $field
   * @return \Foundation\Form\Element
   */
  function addToField(\Foundation\Form\Field $field);
  
  /**
   * Store input as an ElementAnswer
   * @param mixed $input
   */
  function setValueFromInput($input);
  
  /**
   * Set the value from an ApplyAnswer Object
   * @param \Jazzee\Entity\Answer $answer
   */
  function setValueFromAnswer(\Jazzee\Entity\Answer $answer);
  
  /**
   * Get an array of ElementAnswers from the store value
   * @return array \Jazzee\Entity\ElementAnswer
   */
  function getAnswers();
  
  /**
   * Get the value of the element formated for display
   * @return string
   */
  function displayValue();
  
  /**
   * Get the value of the element that \Foundation\Form\Element will accept
   * @return mixed
   */
  function formValue();
}
?>