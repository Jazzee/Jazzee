<?php
namespace Jazzee\Page;
/**
 * AbstractPage
 */
abstract class AbstractPage implements \Jazzee\Page {
 /**
  * The ApplicationPage Entity
  * @var \Jazzee\Entity\ApplicationPage
  */
  protected $_applicationPage;
    
  /**
   * Our controller
   * @var \Jazzee\Controller
   */
  protected $_controller;
  
  /**
   * The Applicant
   * @var \Jazzee\Entity\Applicant
   */
  protected $_applicant;
  
  /**
   * Our form
   * @var \Foundation\Form
   */
  protected $_form;
  
 /**
  * Contructor
  * 
  * @param \Jazzee\Entity\ApplicationPage $applicationPage
  */
  public function __construct(\Jazzee\Entity\ApplicationPage $applicationPage){
    $this->_applicationPage = $applicationPage;
  }
  
  /**
   * 
   * @see Jazzee.Page::getApplicationPage()
   */
  public function getApplicationPage(){
    return $this->_applicationPage;
  }
  
  /**
   * 
   * @see Jazzee.Page::setController()
   */
  public function setController(\Jazzee\Controller $controller){
    $this->_controller = $controller;
  }
  
  /**
   * 
   * @see Jazzee.Page::setApplicant()
   */
  public function setApplicant(\Jazzee\Entity\Applicant $applicant){
    $this->_applicant = $applicant;
  }
  
  /**
   * @see Jazzee.Page::getForm()
   */
  public function getForm(){
    if(is_null($this->_form)) $this->_form = $this->makeForm();
    return $this->_form;
  }
  
  /**
   * Make the form for the page
   * @return \Foundation\Form or false if no form
   */
  abstract protected function makeForm();
  
  /**
   * 
   * @see Jazzee.Page::validateInput()
   */
  public function validateInput($arr){
    return $this->form->processInput($arr);
  }
  
  /**
   * Most pages don't require any setup
   * @see Jazzee.Page::setupNewPage()
   */
  public function setupNewPage(){
    return;
  }
  
/**
   * (non-PHPdoc)
   * @see Jazzee.Page::showReviewPage()
   */
  public function showReviewPage(){
    return true;
  }
}

?>