<?php
namespace Jazzee\Interfaces;

/**
 * FormPage interface
 * For any page with a form
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface FormPage
{

  /**
   * Get the form for the page
   */
  public function getForm();

  /**
   * Validate user input
   *
   * @param array $postData straight post data from the form
   * @return \Foundation\Form\Input on success false on failure
   */
  function validateInput($input);

  /**
   * Create a new answer from input
   * @param \Foundation\Form\Input $input
   * @return bool
   */
  function newAnswer($input);

  /**
   * Update an answer from input
   * @param \Foundation\Form\Input $input
   * @param integer $answerID
   * @return bool
   */
  function updateAnswer($input, $answerID);

  /**
   * Delete an answer
   * @param integer $answerID
   * @return bool
   */
  function deleteAnswer($answerID);

  /**
   * Fill the form with data from an answer
   * @param integer $answerID
   */
  function fill($answerID);
}