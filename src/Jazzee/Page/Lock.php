<?php
namespace Jazzee\Page;
/**
 * Test the application for completness and lock it
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
class Lock implements \Jazzee\Interfaces\Page, \Jazzee\Interfaces\FormPage {
  
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
  * Contructor
  * 
  * @param \Jazzee\Entity\ApplicationPage $applicationPage
  */
  public function __construct(\Jazzee\Entity\ApplicationPage $applicationPage){
    $this->_applicationPage = $applicationPage;
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
  
  public function getForm(){
    $form = new \Foundation\Form;
    $form->setCSRFToken($this->_controller->getCSRFToken());
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    
    $element = $field->newElement('RadioList', 'lock');
    $element->setLabel('Do you wish to lock your application?');
    $element->newItem(0,'No');
    $element->newItem(1,'Yes');
    
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit','Submit Application');
    return $form;
  }
  
  /**
   * Test each page to see if it is complete
   * @param FormInput $input
   * @return bool
   */
  public function validateInput($input){
    if(!$input = $this->getForm()->processInput($input)) return false;
    if(!$input->get('lock')){
      $this->_form->getElementByName('lock')->addMessage('You must answer yes to submit your application.');
      return false;
    }
    $error = false;
    foreach($this->_applicant->getApplication()->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $page){
      if($page != $this->_applicationPage){ //dont check the lock page (this page), it will never be complete
        if($page->getJazzeePage()->getStatus() == self::INCOMPLETE){
          $error = true;
          $this->_controller->addMessage('error', 'You have not completed the ' . $page->getTitle() . ' page');
        }
      }
    }
    return !$error;
  }
  
  public function newAnswer($input){
    $this->_applicant->lock();
    $this->_controller->getEntityManager()->persist($this->_applicant);
    $this->_controller->addMessage('success', 'Your application has been submitted.');
    $this->_controller->redirectUrl($this->_controller->getActionPath());
    
  }
  
  /**
   * Lock Doesn't update answers
   * @param type $input
   * @param type $answerId
   * @return boolean 
   */
  public function updateAnswer($input, $answerId){
    return false;
  }
  
  /**
   * Lock Doesn't delete answers
   * @param type $answerId
   * @return boolean 
   */
  public function deleteAnswer($answerId){
    return false;
  }
  
  public function fill($answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      foreach($this->_applicationPage->getPage()->getElements() as $element){
        $element->getJazzeeElement()->setController($this->_controller);
        $value = $element->getJazzeeElement()->formValue($answer);
        if($value) $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
      }
      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }
  
  /**
   * No Special setup
   * @return null
   */
  public function setupNewPage(){
    return;
  }
  
  public static function applyPageElement(){
    return 'Standard-apply_page';
  }
  
  public static function pageBuilderScriptPath(){
    return 'resource/scripts/page_types/JazzeePageLock.js';
  }
  
  /**
   * Lock Pages are always incomplete
   */
  public function getStatus(){
    if($this->_applicant->isLocked()) return self::COMPLETE;
    return self::INCOMPLETE;
  }
  
  /**
   * By default just set the varialbe dont check it
   * @param string $name
   * @param string $value 
   */
  public function setVar($name, $value){
    $var = $this->_applicationPage->getPage()->setVar($name, $value);
    $this->_controller->getEntityManager()->persist($var);
  }
  
}
?>