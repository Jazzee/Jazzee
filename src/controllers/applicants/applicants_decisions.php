<?php
/**
 * Decide on applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsDecisionsController extends ApplicantsController {
  const MENU = 'Applicants';
  const TITLE = 'Decisions';
  const PATH = 'applicants/decisions';
  
  protected $layout = 'json';
  
    
  /**
   * Add the required JS
   */
  protected function setUp(){
    parent::setUp();
    $this->addScript('common/scripts/status.js');
    $this->addScript('common/scripts/decisions.js');
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
    
    foreach($this->application->findLockedApplicants() AS $applicant){
      if($applicant->Decision->finalDeny){
        $list['finalDeny'][] = $applicant;
      } else if($applicant->Decision->finalAdmit){
        $list['finalAdmit'][] = $applicant;
      } else if($applicant->Decision->nominateDeny){
        $list['nominateDeny'][] = $applicant;
      } else if($applicant->Decision->nominateAdmit){
        $list['nominateAdmit'][] = $applicant;
      } else {
        $list['noDecision'][] = $applicant;
      }
    }
    $this->setVar('list', $list);
    $this->layout = 'wide';
  }
  
  /**
   * Nominate an applicant for admission
   */
  public function actionPreliminaryDecision(){
    $count = array('admit'=>0, 'deny'=>0);
    foreach($this->post['deny'] as $id){
      $applicant = $this->getApplicantById($id);
      $count['deny']++;
      $applicant->Decision->nominateDeny();
      $applicant->save();
    }
    foreach($this->post['admit'] as $id){
      $applicant = $this->getApplicantById($id);
      $count['admit']++;
      $applicant->Decision->nominateAdmit();
      $applicant->save();
    }
    $message = '';
    if($count['admit']) $message .= "{$count['admit']} applicant(s) nominated for admit.  ";
    if($count['deny']) $message .= "{$count['deny']} applicant(s) nominated for deny.";
    if($message)  $this->messages->write('success', $message);
    $this->redirect($this->path('applicants/decisions'));
    exit();
  }

  /**
   * Final Deny and applicant or undo a preliminary decision
   */
  public function actionFinalDeny(){
    $count = array('undo'=>0, 'deny'=>0);
    foreach($this->post['undo'] as $id){
      $applicant = $this->getApplicantById($id);
      $count['undo']++;
      $applicant->Decision->undoNominateDeny();
      $applicant->save();
    }
    foreach($this->post['deny'] as $id){
      $applicant = $this->getApplicantById($id);
      $count['deny']++;
      $applicant->Decision->finalDeny();
      $this->notifyApplicantStatusUpdate($applicant);
      $applicant->save();
    }
    $message = '';
    if($count['undo']) $message .= "{$count['undo']} applicant(s) changed to no decision.  ";
    if($count['deny']) $message .= "{$count['deny']} applicant(s) denied.";
    if($message)  $this->messages->write('success', $message);
    $this->redirect($this->path('applicants/decisions'));
    exit();
  }
  

  /**
   * Final Admit and applicant or undo a preliminary decision
   */
  public function actionFinalAdmit(){
    $count = array('undo'=>0, 'admit'=>0);
    foreach($this->post['undo'] as $id){
      $applicant = $this->getApplicantById($id);
      $count['undo']++;
      $applicant->Decision->undoNominateAdmit();
      $applicant->save();
    }
    if(!empty($this->post['admit'])){
      if(!isset($this->post['sirdeadline']) OR empty($this->post['sirdeadline'])){
        $this->messages->write('error', 'You must specify a SIR deadline if you are admiting applicants.');
        $this->redirect($this->path('applicants/decisions'));
        exit();
      }
      if(!$timestamp = strtotime($this->post['sirdeadline']) OR $timestamp <= time()){
        $this->messages->write('error', 'You must specify a valid future date for the SIR deadline if you are admiting applicants.');
        $this->redirect($this->path('applicants/decisions'));
        exit();
      }
      $sirDeadline = date('Y-m-d H:i:s', $timestamp);
      foreach($this->post['admit'] as $id){
        $applicant = $this->getApplicantById($id);
        $count['admit']++;
        $applicant->Decision->finalAdmit();
        $applicant->Decision->offerResponseDeadline = $sirDeadline;
        $this->notifyApplicantStatusUpdate($applicant);
        $applicant->Decision->decisionLetterSent();
        $applicant->save();
      }
    }
    $message = '';
    if($count['undo']) $message .= "{$count['undo']} applicant(s) changed to no decision.  ";
    if($count['admit']) $message .= "{$count['admit']} applicant(s) admitted.";
    if($message)  $this->messages->write('success', $message);
    $this->redirect($this->path('applicants/decisions'));
    exit();
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Admission Decisions';
    $auth->addAction('index', new ActionAuth('List Applicants and their admission status'));
    $auth->addAction('preliminaryDecision', new ActionAuth('Make preliminary Admit/Deny Decisions'));
    $auth->addAction('finalDeny', new ActionAuth('Make Final Decision to Deny Applicant'));
    $auth->addAction('finalAdmit', new ActionAuth('Make Final Decision to Admit Applicant'));
    return $auth;
  }
}