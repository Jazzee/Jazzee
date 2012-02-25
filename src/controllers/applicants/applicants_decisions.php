<?php
ini_set('memory_limit', '1g');
set_time_limit('120');
/**
 * Decide on applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsDecisionsController extends \Jazzee\AdminController {
  const MENU = 'Applicants';
  const TITLE = 'Decisions';
  const PATH = 'applicants/decisions';
  
  const ACTION_INDEX = 'List applicant admission status';
  const ACTION_NOMINATEADMIT = 'Nominate for Admission';
  const ACTION_NOMINATEDENY = 'Nominate for Deny';
  const ACTION_FINALADMIT = 'Final Admit';
  const ACTION_FINALDENY = 'Final Deny';
  
  /**
   * Add the required JS
   */
  protected function setUp(){
    parent::setUp();
    $this->addScript($this->path('resource/scripts/controllers/applicants_decisions.controller.js'));
  }
  
  /**
   * Build the blank page
   */
  public function actionIndex(){
    $list = array(
      'noDecision' => array(),
      'finalDeny' => array(),
      'finalAdmit' => array(),
      'nominateDeny' => array(),
      'nominateAdmit' => array()
    );
    foreach($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant){
      if($applicant->isLocked()){
        if($applicant->getDecision()->getFinalDeny()){
          $list['finalDeny'][] = $applicant;
        } else if($applicant->getDecision()->getFinalAdmit()){
          $list['finalAdmit'][] = $applicant;
        } else if($applicant->getDecision()->getNominateDeny()){
          $list['nominateDeny'][] = $applicant;
        } else if($applicant->getDecision()->getNominateAdmit()){
          $list['nominateAdmit'][] = $applicant;
        } else {
          $list['noDecision'][] = $applicant;
        }
      }
    }
    $this->setVar('list', $list);
  }
  
  /**
   * Nominate an applicant for admission
   */
  public function actionNominateAdmit(){
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/decisions/nominateAdmit'));
    $field = $form->newField();
    $field->setLegend('Nominate Applicants for admission');
    
    $element = $field->newElement('CheckboxList','applicants');
    $element->setLabel('Select applicants to nominate');
    foreach($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant){
      if($applicant->isLocked() AND $applicant->getDecision()->can('nominateAdmit')) $element->newItem($applicant->getId(), $applicant->getLastName() . ', ' . $applicant->getFirstName());
    }

    $form->newButton('submit', 'Submit');
    if($input = $form->processInput($this->post)){
      $count = 0;
      foreach($input->get('applicants') as $id){
        $applicant = $this->getApplicantById($id);
        $applicant->getDecision()->nominateAdmit();
        $this->_em->persist($applicant);
        $count ++;
        if($count > 100){
          $this->_em->flush();
          $count = 0;
        }
      }
      $this->addMessage('success', count($this->post['applicants']) . ' applicant(s) nominated for admit.');
      $this->redirectPath('applicants/decisions');
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_decisions/form');
  }
  
  /**
   * Nominate an applicant for deny
   */
  public function actionNominateDeny(){
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/decisions/nominateDeny'));
    $field = $form->newField();
    $field->setLegend('Nominate Applicants for deny');
    
    $element = $field->newElement('CheckboxList','applicants');
    $element->setLabel('Select applicants to nominate');
    foreach($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant){
      if($applicant->isLocked() AND $applicant->getDecision()->can('nominateDeny')) $element->newItem($applicant->getId(), $applicant->getLastName() . ', ' . $applicant->getFirstName());
    }

    $form->newButton('submit', 'Submit');
    if($input = $form->processInput($this->post)){   
      $count = 0;
      foreach($input->get('applicants') as $id){
        $applicant = $this->getApplicantById($id);
        $applicant->getDecision()->nominateDeny();
        $this->_em->persist($applicant);
        $count ++;
        if($count > 100){
          $this->_em->flush();
          $count = 0;
        }
      }
      $this->addMessage('success', count($this->post['applicants']) . ' applicant(s) nominated for deny.');
      $this->redirectPath('applicants/decisions');
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_decisions/form');
  }
  
  /**
   * Admit Applicants
   */
  public function actionFinalAdmit(){
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/decisions/finalAdmit'));
    $field = $form->newField();
    $field->setLegend('Admit Applicants');
    
    $element = $field->newElement('CheckboxList','applicants');
    $element->setLabel('Select applicants to admit');
    foreach($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant){
      if($applicant->isLocked() AND $applicant->getDecision()->can('finalAdmit')) $element->newItem($applicant->getId(), $applicant->getLastName() . ', ' . $applicant->getFirstName());
    }
    
    $element = $field->newElement('DateInput', 'offerResponseDeadline');
    $element->setLabel('Offer Response Deadline');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, 'today'));
    
    $element = $field->newElement('RadioList', 'sendMessage');
    $element->setLabel('Send the applicant a notification?');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Submit');
    if($input = $form->processInput($this->post)){   
      $count = 0;
      foreach($input->get('applicants') as $id){
        $applicant = $this->getApplicantById($id);
        $applicant->getDecision()->finalAdmit();
        $applicant->getDecision()->setOfferResponseDeadline($input->get('offerResponseDeadline'));
        if($input->get('sendMessage')){
          $thread = new \Jazzee\Entity\Thread();
          $thread->setSubject('Admission Decision');
          $thread->setApplicant($applicant);
          
          $message = new \Jazzee\Entity\Message();
          $message->setSender(\Jazzee\Entity\Message::PROGRAM);
          $text = $this->_application->getAdmitLetter();
          $search = array(
           '_Admit_Date_',
           '_Applicant_Name_',
           '_Offer_Response_Deadline_'
          );
          $replace = array();
          $replace[] = $applicant->getDecision()->getFinalAdmit()->format('F jS Y');
          $replace[] = $applicant->getFullName();
          $replace[] = $applicant->getDecision()->getOfferResponseDeadline()->format('F jS Y g:ia');
          $text = str_ireplace($search, $replace, $text);
          $text = nl2br($text);
          $message->setText($text);
          $thread->addMessage($message);
          $this->_em->persist($thread);
          $this->_em->persist($message);
        }
        $this->_em->persist($applicant);
        $count ++;
        if($count > 100){
          $this->_em->flush();
          $count = 0;
        }
      }
      $this->addMessage('success', count($this->post['applicants']) . ' applicant(s) admited.');
      $this->redirectPath('applicants/decisions');
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_decisions/form');
  }
  
  /**
   * Deny Applicants
   */
  public function actionFinalDeny(){
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/decisions/finalDeny'));
    $field = $form->newField();
    $field->setLegend('Deny Applicants');
    
    $element = $field->newElement('CheckboxList','applicants');
    $element->setLabel('Select applicants to deny');
    foreach($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant){
      if($applicant->isLocked() AND $applicant->getDecision()->can('finalDeny')) $element->newItem($applicant->getId(), $applicant->getLastName() . ', ' . $applicant->getFirstName());
    }
    
    $element = $field->newElement('RadioList', 'sendMessage');
    $element->setLabel('Send the applicant a notification?');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Submit');
    if($input = $form->processInput($this->post)){   
      $count = 0;
      foreach($input->get('applicants') as $id){
        $applicant = $this->getApplicantById($id);
        $applicant->getDecision()->finalDeny();
        if($input->get('sendMessage')){
          $thread = new \Jazzee\Entity\Thread();
          $thread->setSubject('Admission Decision');
          $thread->setApplicant($applicant);
          
          $message = new \Jazzee\Entity\Message();
          $message->setSender(\Jazzee\Entity\Message::PROGRAM);
          $text = $this->_application->getDenyLetter();
          $search = array(
           '_Deny_Date_',
           '_Applicant_Name_'
          );
          $replace = array();
          $replace[] = $applicant->getDecision()->getFinalDeny()->format('F jS Y');
          $replace[] = $applicant->getFullName();
          $text = str_ireplace($search, $replace, $text);
          $text = nl2br($text);
          $message->setText($text);
          $thread->addMessage($message);
          $this->_em->persist($thread);
          $this->_em->persist($message);
        }
        $this->_em->persist($applicant);
        $count ++;
        if($count > 100){
          $this->_em->flush();
          $count = 0;
        }
      }
      $this->addMessage('success', count($this->post['applicants']) . ' applicant(s) denied.');
      $this->redirectPath('applicants/decisions');
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_decisions/form');
  }
}