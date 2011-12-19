<?php
namespace Jazzee\Page;
/**
 * Test the application for completness and lock it
 */
class Lock extends Standard {
  
  protected function makeForm(){
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
    $form->newButton('reset', 'Clear Form');
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
  
  public function showReviewPage(){
    return false;
  }
  
  /**
   * TextPages are always complete
   */
  public function getStatus(){
    if($this->_applicant->isLocked()) return self::COMPLETE;
    return self::INCOMPLETE;
  }
  
}
?>