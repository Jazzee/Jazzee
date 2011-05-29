<?php
/**
 * List all applicants by status
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsListController extends \Jazzee\AdminController {
  const MENU = 'Applicants';
  const TITLE = 'By Tag';
  const PATH = 'applicants/list';
  
  /**
   * List all applicants
   */
  public function actionIndex(){
    $tags = array();
    $tags['Locked'] = array();
    $tags['Not Locked'] = array();
    $tags['All Applicants'] = array();
    foreach($this->application->findApplicantsByName() as $applicant){
      $tags['All Applicants'][] = $applicant;
      if(!is_null($applicant->locked)) $tags['Locked'][] = $applicant;
      else $tags['Not Locked'][] = $applicant;
      foreach($applicant->Tags as $tag){
        if(!isset($tags[$tag->title])) $tags = array($tag->title => array()) + $tags;
        $tags[$tag->title][] = $applicant;
      }
    }
    $this->setVar('tags', $tags);
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'List Applicants';
    $auth->addAction('index', new ActionAuth('All Applicants'));
    return $auth;
  }
}