<?php
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
  
  /**
   * @const string format for dates in display
   */
  const LONG_DATE_FORMAT = 'm/d/Y';
  
  /**
   * Add the required JS
   */
  protected function setUp(){
    parent::setUp();
    $this->layout = 'json';
    $this->addScript($this->path('resource/scripts/status.js'));
    $this->addScript($this->path('resource/scripts/decisions.js'));
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
    foreach($this->_application->getApplicants() AS $applicant){
      if($applicant->isLocked()){
        if($applicant->getDecision()->getFinalDeny()){
          $list['finalDeny'][] = $applicant;
        } else if($applicant->getDecision()->getFinalAdmit()){
          $list['finalAdmit'][] = $applicant;
        } else if($applicant->getDecision()->getNominateDeny()){
          $list['nominateDeny'][] = $applicant;
        } else if($applicant->getDecision()->geNominateAdmit()){
          $list['nominateAdmit'][] = $applicant;
        } else {
          $list['noDecision'][] = $applicant;
        }
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
    if($message)  $this->addMessage('success', $message);
    $this->redirect($this->path('admin/applicants/decisions'));
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
    if($message)  $this->addMessage('success', $message);
    $this->redirect($this->path('admin/applicants/decisions'));
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
        $this->addMessage('error', 'You must specify a SIR deadline if you are admiting applicants.');
        $this->redirect($this->path('admin/applicants/decisions'));
        exit();
      }
      if(!$timestamp = strtotime($this->post['sirdeadline']) OR $timestamp <= time()){
        $this->addMessage('error', 'You must specify a valid future date for the SIR deadline if you are admiting applicants.');
        $this->redirect($this->path('admin/applicants/decisions'));
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
    if($message)  $this->addMessage('success', $message);
    $this->redirect($this->path('admin/applicants/decisions'));
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