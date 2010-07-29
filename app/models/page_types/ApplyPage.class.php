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
   * Show this page in data views?
   */
  abstract public function showPageData();
  
  /**
   * Get all the page creator tabs for the page
   * @return array of PageCreatorTabs
   */
  abstract public function getTabs();  
  
}


/**
 * Page creator tab
 * Makes it easy for different page types to create sets of tabs with different functions
 */
class PageCreatorTab {
  /**
   * The tabs title
   * @var string
   */
  public $title;
  
  /**
   * The type of tab
   * @var string
   */
  public $type;
  
  /**
   * The name of the method used to process the tab
   * @var string
   */
  public $processMethod;
  
  /**
   * If the type is a form this is the form
   * @var Form
   */
  protected $form;
  
  /**
   * If the type is Elements these are the current elements
   * @var array of Elements
   */
  protected $elements;
  
  /**
   * If the page is html this is the html
   */
  protected $html;
  
  /**
   * The PageID for new elements to be added to
   * @var integer
   */
  public $elementPageID;
  
  /**
   * Constructor 
   * @param string $name
   * @param string $title
   * @param string $type=
   */
  public function __construct($title, $type){
    $this->title = $title;
    $this->type = $type;
    $this->elements = array();
  }
  
  /**
   * Set the form
   * @param Form $form
   */
  public function setForm(Form $form){
    $this->form = $form;
  }
  
  /**
   * Add an element
   * @param Element $element
   */
  public function addElement(Element $element){
    $this->elements[] = $element;
  }
  
  /**
   * Set the html
   * @param string $html
   */
  public function setHTML($html){
    $this->html = $html;
  }
  
  /**
   * Get the form
   * @return Form
   */
  public function getForm(){
    return $this->form;
  }
  
  /**
   * Get the elements
   * @return array of Elements
   */
  public function getElements(){
    return $this->elements;
  }
  
  /**
   * Get the html
   * @return string
   */
  public function getHTML(){
    return $this->html;
  }
}
/**
 * A single Applicant Answer
 * Provides some nice helper classes for the views and pages to get their work donw
 * Allows for a sinlge point of override so special pages like LOR and ETS can add functionality
 */
abstract class ApplyAnswer {
  /**
   * Attempt to use a method to return unset get requests
   * @param $name
   */
  public function __get($name){
    $method = "get{$name}";
    if(method_exists($this, $method)){
      return $this->$method();
    }
    $trace = debug_backtrace();
    trigger_error(
        'Undefined property : ' . $name .
        ' in ' . $trace[0]['file'] .
        ' on line ' . $trace[0]['line'],
        E_USER_NOTICE);
  }
  /**
   * Get the ID of the answer 
   * @return integer
   */
  abstract public function getID();
  
  /**
   * Update the values in an answer 
   * @param FormInput $input
   */
  abstract public function update(FormInput $input);
  
  /**
   * Get a list of the elements
   * @return array
   */
  abstract public function getElements();
  
  /**
   * Get the display value for an element by ID
   * @param mixed $id
   * @return string
   */
  abstract public function getDisplayValueForElement($elementID);
  
  /**
   * Get the form value for an element by ID
   * @param mixed $id
   * @return string
   */
  abstract public function getFormValueForElement($elementID);
  
  /**
   * The tools for apply_page view
   * @param string $basePath
   * @return array of links 
   */
  abstract public function applyTools($basePath);
  
  /**
   * Tools for applicant_view view
   * @return array of links
   */
  abstract public function applicantTools();
  
  /**
   * The Status text for apply_page view
   * @return array of statuses
   */
  abstract public function applyStatus();
  
    /**
   * The Status text for applicants_view single
   * @return array of statuses
   */
  abstract public function applicantStatus();
  
  /**
   * Get the last update time
   * @return integer
   */
  abstract public function getUpdatedAt();

}

?>