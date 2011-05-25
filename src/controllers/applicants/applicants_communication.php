<?php
/**
 * Communicate with Applicants
 * View messages sent by applicants and send messages to individuals or groups
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsCommunicationController extends ApplicantsController {
  const MENU = 'Applicants';
  const TITLE = 'Communication';
  const PATH = 'applicants/communication';
  
//  
//  /**
//   * Add the required JS
//   */
//  public function setUp(){
//    parent::setUp();
//    $this->addScript('foundation/scripts/form.js');
//    $this->addScript('common/scripts/status.js');
//    $this->addScript('common/scripts/applicants_view.js');
//  }

  /**
   * Messages from applicants
   */
  public function actionIndex(){
    $threads = array();
    foreach($this->application->Applicants as $applicant){
      foreach($applicant->findCommunicationThreads() as $t){
        $threads[] = $t;
      }
    }
    $this->setVar('user', $this->user);
    $this->setVar('threads', $threads);
  }
  
  /**
   * Reply to a message
   * @param integer $id
   */
  public function actionReply($id){
    $form = new Form;
    $form->action = $this->path("applicants/communication/reply/{$id}");
    $field = $form->newField();
    $field->legend = 'Reply to Message';
    $element = $field->newElement('Textarea', 'text');
    $element->label = 'Your Reply';
    $element->addValidator('NotEmpty');
    $form->newButton('submit', 'Send');
    $this->setVar('form', $form);
    if($input = $form->processInput($this->post)){
      $parent = Doctrine::getTable('Communication')->find($id);
      $communication = $parent->Reply;
      $communication->sentBy = 'user';
      $communication->applicantID = $parent->Applicant->id;
      $communication->userID = $this->user->id;
      $communication->text = $input->text;
      $communication->save();
      $this->messages->write('success', 'Your message has been sent.');
      $this->redirect($this->path('applicants/communication/'));
    }
    
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Communication with Applicants';
    $auth->addAction('index', new ActionAuth('Show Messages'));
    $auth->addAction('reply', new ActionAuth('Reply to Messages'));
    return $auth;
  }
}