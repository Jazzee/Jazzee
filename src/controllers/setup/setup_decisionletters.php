<?php
/**
 * Setup the Decision Letters
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class SetupDecisionlettersController extends \Jazzee\AdminController {
  const MENU = 'Setup';
  const TITLE = 'Decision Letters';
  const PATH = 'setup/decisionletters';
  
  const ACTION_INDEX = 'View Letters';
  const ACTION_EDITADMITLETTER = 'Edit Admit Letter';
  const ACTION_EDITDENYLETTER = 'Edit Deny Letter';
  
  
  
  /**
   * View Decision Letters
   */
  public function actionIndex(){
    $now = new DateTime('now');
    $text = $this->_application->getAdmitLetter();
    $search = array(
     '%Admit_Date%',
     '%Applicant_Name%',
     '%Offer_Response_Deadline%'
    );
    $replace = array();
    $replace[] = $now->format('F jS Y');
    $replace[] = 'John Smith';
    $replace[] = $now->format('F jS Y g:ia');
    $text = str_ireplace($search, $replace, $text);
    
    $text = nl2br($text);
    $this->setVar('admitLetter', $text);  
    
    $text = $this->_application->getDenyLetter();
    $search = array(
     '%Deny_Date%',
     '%Applicant_Name%'
    );
    $replace = array();
    $replace[] = $now->format('F jS Y');
    $replace[] = 'John Smith';
    $text = str_ireplace($search, $replace, $text);
    
    $text = nl2br($text);
    $this->setVar('denyLetter', $text);
  }
  
  /**
   * Edit Admit Letter
   */
  public function actionEditAdmitLetter(){
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/decisionletters/editAdmitLetter"));
    $field = $form->newField();
    $field->setLegend('Edit Admit Letter');
    
    $field->setInstructions('These tokens will be replaced in the text: %Admit_Date%, %Applicant_Name%, %Offer_Response_Deadline%');
    
    $element = $field->newElement('Textarea','admitLetter');
    $element->setLabel('Content');
    $element->setValue($this->_application->getAdmitLetter());
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $form->newButton('submit', 'Save');
    
    if($input = $form->processInput($this->post)){
      $this->_application->setAdmitLetter($input->get('admitLetter'));
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Letter saved.');
      $this->redirectPath('setup/decisionletters');
    }
    
    $this->setVar('form', $form);
  }
  
  /**
   * Edit Deny Letter
   */
  public function actionEditDenyLetter(){
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/decisionletters/editDenyLetter"));
    $field = $form->newField();
    $field->setLegend('Edit Deny Letter');
    
    $field->setInstructions('These tokens will be replaced in the text: %Deny_Date%, %Applicant_Name%');
    
    $element = $field->newElement('Textarea','denyLetter');
    $element->setLabel('Content');
    $element->setValue($this->_application->getDenyLetter());
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $form->newButton('submit', 'Save');
    
    if($input = $form->processInput($this->post)){
      $this->_application->setDenyLetter($input->get('denyLetter'));
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Letter saved.');
      $this->redirectPath('setup/decisionletters');
    }
    
    $this->setVar('form', $form);
  }
  
}