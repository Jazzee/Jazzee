<?php
/**
 * List all applicants by status
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsDuplicatesController extends \Jazzee\AdminController {
  const MENU = 'Applicants';
  const TITLE = 'List Duplicates';
  const PATH = 'applicants/duplicates';
  
  const ACTION_INDEX = 'Show All Duplicates';
  
  const MIN_INTERVAL = 14000; //4 hours minus a bit
  
  /**
   * List all applicants
   */
  public function actionIndex(){
    $applicants = array();
    foreach($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%','%', $this->_application) as $applicant){
      $duplicates = $applicant->getDuplicates();
      if(count($duplicates))$applicants[] = $applicant;
    }
    $this->setVar('applicants', $applicants);
  }
  
  /**
   * Check for duplicate applicants
   * 
   * @param AdminCronController $cron
   */
  public static function runCron(AdminCronController $cron){
    if(time() - (int)$cron->getVar('applicantsDuplicatesLastRun') > self::MIN_INTERVAL){
      $cron->setVar('applicantsDuplicatesLastRun', time());
      $applicantsWithDuplicates = array();
      foreach($cron->getEntityManager()->getRepository('\Jazzee\Entity\Cycle')->findAll() as $cycle){
        foreach($cron->getEntityManager()->getRepository('\Jazzee\Entity\Applicant')->findByCycle($cycle) as $applicant){
          foreach($cron->getEntityManager()->getRepository('\Jazzee\Entity\Applicant')->findDuplicates($applicant) as $duplicateApplicant){
            if(!$cron->getEntityManager()->getRepository('\Jazzee\Entity\Duplicate')->findBy(array('applicant'=>$applicant->getId(), 'duplicate'=>$duplicateApplicant->getId()))){
              $duplicate = new \Jazzee\Entity\Duplicate;
              $duplicate->setApplicant($applicant);
              $duplicate->setDuplicate($duplicateApplicant);
              $cron->getEntityManager()->persist($duplicate);
            }
          }
        }
      }
      $cron->getEntityManager()->flush();
    }
  }
}