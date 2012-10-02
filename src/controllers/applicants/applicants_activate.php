<?php

/**
 * Activate deactivated applicants
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsActivateController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'View Deactivated';
  const PATH = 'applicants/activate';
  const ACTION_INDEX = 'View Applicants';
  const ACTION_ACTIVATE = 'Activate a deactivated applicant';

  /**
   * List all applicants
   */
  public function actionIndex()
  {
    $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findDeactivatedByApplication($this->_application);
    $this->setVar('applicants', $applicants);
  }

  /**
   * Activate an applicant
   *
   * @param integer $applicantId
   */
  public function actionActivate($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->activate();
    $this->_em->persist($applicant);
    $this->addMessage('success', $applicant->getFullName() . ' activated');
    $this->redirectPath('applicants/activate');
  }

}