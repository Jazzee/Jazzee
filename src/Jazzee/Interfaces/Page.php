<?php
namespace Jazzee\Interfaces;
/**
 * Page interface
 * Allows us to impelement a strategy pattern to load page types with thier Entities
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
interface Page 
{
  /**
   * Page status constants
   */
  const INCOMPLETE = 0;
  const COMPLETE = 1;
  const SKIPPED = 2;
  
  /**
   * Page Element constants 
   */
  
  /**
   * The element to load for apply_page view 
   */
  const APPLY_PAGE_ELEMENT = '';
  
  
  /**
   * The element to load for applicants_singe view 
   */
  const APPLICANTS_SINGLE_ELEMENT = '';
  
  
  /**
   * The element to load for apply_status view 
   */
  const APPLY_STATUS_ELEMENT = '';
  
  
  /**
   * The path to the pagebuilder javascript
   */
  const PAGEBUILDER_SCRIPT = 'resource/scripts/page_types/JazzeePage.js';
  
  /**
   * Set the controller
   * 
   * @param \Jazzee\Controller $controller
   */
  function setController(\Jazzee\Controller $controller);
  
  /**
   * Set the Applicant
   * 
   * @param \Jazzee\Entity\Applicant $applicant
   */
  function setApplicant(\Jazzee\Entity\Applicant $applicant);
  
  /**
   * Get the form for the page
   */
  public function getForm();
  
  /**
   * Show Review page
   * 
   * Does this page contain any answers or is it a Text or Lock page which doesn't
   * show anything usefull to reviewers
   * @return bool
   */
  public function showReviewPage();
  
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
  
  /**
   * Get the current answers
   * @return array \Jazzee\Answer
   */
  function getAnswers();
  
  /**
   * Get the current answers as an xml element
   * @param \DOMDocument $dom
   * @return array DOMElement
   */
  function getXmlAnswers(\DOMDocument $dom);
  
  /**
   * Get the CSV Headers
   * 
   * Some elements like branching need special attention for the header row
   * of a csv file
   * @return array
   */
  function getCsvHeaders();
  
  /**
   * Get the CSV Answers
   * 
   * CSV Answers have to be linear so we use a counter when asking for the answers
   * and return only the answer for that counter.  If there is no answer return a 
   * blank array with the right number of blank elements
   * @param int $position
   * @return array
   */
  function getCsvAnswer($position);

  /**
   * Get the current status
   * @return integer self::INCOMPLETE | self::COMPLETE | self:SKIPPED
   */
  function getStatus();
  
  /**
   * Setup a new page
   * 
   * Eg - create a form with fixed ids
   */
  function setupNewPage();
  
  /**
   * Set a page variable 
   * Do this here so we can check that the value is good
   * @param string $name
   * @param string $value
   * @throws \Jazzee\Exception
   */
  public function setVar($name, $value);
  
  /**
   * Build the pdf section for this page type
   * 
   * @param \Jazzee\ApplicantPDF
   */
  function renderPdfSection(\Jazzee\ApplicantPDF $pdf);
  
  /**
   * Test a query
   * Checks if the applicant meets the query parameters
   * @param \stdClass $query
   * @returns boolean
   */
  public function testQuery(\stdClass $obj);
}