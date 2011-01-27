<?php
/**
 * Base controller for all authenticated applicants controllers
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
abstract class ApplicantsController extends AdminController{
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    if($programID AND $cycleID AND $user)  return $user->isAllowed($controller, $action, $programID);
    return false;
  }
  
  /**
   * Notify and applicant that their status has changed
   * Several controllers (ApplicantsView, ApplicantsDecisions) can change an applicants status (decisions, sir, answer status)
   * Use this method to send them notifications 
   * @param Applicant $applicant
   */
  protected function notifyApplicantStatusUpdate(Applicant $applicant){
    $mail = JazzeeMail::getInstance();
    $message = new EmailMessage;
    $message->to($applicant->email, "{$applicant->firstName} {$applicant->lastName}");
    $message->from($applicant->Application->contactEmail, $applicant->Application->contactName);
    $message->subject = 'Application Status';
    $message->body = "We have updated your application status.  In order to protect your privacy you must login to see these changes.  " . $mail->path("apply/{$applicant->Application->Program->shortName}/{$applicant->Application->Cycle->name}/applicant/login");
    $mail->send($message);
  }
}

?>