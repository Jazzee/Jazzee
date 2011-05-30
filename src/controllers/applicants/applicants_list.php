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
  
  const ACTION_INDEX = 'All Applicants';
  
  /**
   * List all applicants
   */
  public function actionIndex(){
    $tags = array();
    $tags['Locked'] = array();
    $tags['Not Locked'] = array();
    $tags['All Applicants'] = array();
    foreach($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant){
      $tags['All Applicants'][] = $applicant;
      if($applicant->isLocked()) $tags['Locked'][] = $applicant;
      else $tags['Not Locked'][] = $applicant;
      foreach($applicant->getTags() as $tag){
        if(!isset($tags[$tag->getTitle()])) $tags = array($tag->getTitle() => array()) + $tags;
        $tags[$tag->getTitle()][] = $applicant;
      }
    }
    $this->setVar('tags', $tags);
  }
}