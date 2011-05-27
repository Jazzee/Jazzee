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
}
?>