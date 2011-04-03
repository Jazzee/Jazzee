<?php
/**
 * ApplyPage is the base class for all application pages
 * Includes functionality for the pages builder and answers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
 
/**
 * The Abstract Application Page
 * All Pages must extend this class
 */
abstract class ApplyPage {
  /**
   * Status constants
   */
  const INCOMPLETE = 0;
  const COMPLETE = 1;
  const SKIPPED = 2;
  
  /**
   * Does this page contain any answers or is it a Text or Lock page which doesn't
   * show anything usefull to reviewers
   */
  const SHOW_PAGE = true;
  
 /**
  * The ApplicationPage model
  * @var ApplicationPage $_page
  */
  protected $applicationPage;
  
  /**
   * The Applicant
   */
  protected $applicant;
  
  /**
   * The form for the page
   * @var Form
   */
  protected $form;
  
 /**
  * Contructor
  * @param ApplicationPage $applicationPage
  * @param Applicant $applicant
  */
  public function __construct(ApplicationPage $applicationPage, Applicant $applicant = null){
    $this->applicationPage = $applicationPage;
    $this->applicant = $applicant;
    $this->form = $this->makeForm();
  }
  
  /**
   * Passthrough unset get request to the $page object
   * @param $name
   */
  public function __get($name){
    $method = "get{$name}";
    if(method_exists($this, $method)){
      return $this->$method();
    }
    return $this->applicationPage->$name;
  }
  
  /**
   * Get the form for the page
   */
  public function getForm(){
    return $this->form;
  }
  
  /**
   * Make the form for the page
   * @return Form or false if no form
   */
  abstract protected function makeForm();
  
  /**
   * Validate user input
   * @param array $input
   * @return array on success false on failure
   */
  abstract public function validateInput($input);

  /**
   * Create a new answer from input
   * @param mixed $input
   * @return bool
   */
  abstract public function newAnswer($input);
  
  /**
   * Update an answer from input
   * @param mixed $input
   * @param integer $answerID
   * @return bool
   */
  abstract public function updateAnswer($input, $answerID);
  
  /**
   * Delete an answer
   * @param integer $answerID
   * @return bool
   */
  abstract public function deleteAnswer($answerID);
  
  /**
   * Skip this page
   */
  abstract public function skip();
  
  /**
   * Unskip this page
   */
  abstract public function unSkip();
  
  /**
   * Fill the form with elements of an answer
   * @param integer $answerID
   */
  abstract public function fill($answerID);
  
  /**
   * Get the current answers
   * @return array
   */
  abstract public function getAnswers();

  /**
   * Get the current status
   * @return integer self::INCOMPLETE | self::COMPLETE | self:SKIPPED
   */
  abstract public function getStatus();
  
  /**
   * Perform some setup actions on a new page
   * @param Page $page
   */
  public static function setupNewPage(Page $page){}
}

?>