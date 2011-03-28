<?php
/**
 * List all applicants by status
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsListController extends ApplicantsController {
  const MENU = 'Applicants';
  const TITLE = 'By Status';
  const PATH = 'applicants/list';
  
  /**
   * List all applicants
   */
  public function actionIndex(){
    if($this->application)
      $this->setVar('applicants', $this->application->Applicants);
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'List Applicants';
    $auth->addAction('index', new ActionAuth('All Applicants'));
    return $auth;
  }
}