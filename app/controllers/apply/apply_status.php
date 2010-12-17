<?php
/**
 * The status portal that is displayed to applicants once thier application is locked
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage apply
 */
 
class ApplyStatusController extends ApplyController {  
  /**
   * Status level constants 
   * Bitmask values so status levels can be compared
   */
  const DENIED = 1;
  const ADMITTED = 2;
  const DECLINED = 4;
  const ACCEPTED = 8;
  
  public function beforeAction(){
    parent::beforeAction();
    //if the applicant hasn't locked and the application isn't closed
    if(!$this->applicant->locked and strtotime($this->applicant->Application->close) > time()){
      $this->messages->write('notice', "You have not completed your application.");
      $this->redirect($this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/page/{$this->application['Pages']->getFirst()->id}"));
      $this->afterAction();
      die();
    }
    $status = 0;
    if($this->applicant->relatedExists('Decision')){
      if($this->applicant->Decision->finalDeny)
        $status = $status | ApplyStatusController::DENIED;
      if($this->applicant->Decision->finalAdmit)
        $status = $status | ApplyStatusController::ADMITTED;
      if($this->applicant->Decision->declineOffer)
        $status = $status | ApplyStatusController::DECLINED;
      if($this->applicant->Decision->acceptOffer)
        $status = $status | ApplyStatusController::ACCEPTED;
    }
    $this->setVar('status', $status);
    $this->setVar('applicant', $this->applicant);
  }
  
  /**
   * Display the page
   */
  public function actionIndex() {
    
  }
  
}
?>
