<?php

namespace Jazzee;

/**
 * Base controller for all authenticated application controllers
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AuthenticatedApplyController extends ApplyController
{

  /**
   * The applicant
   * @var \Jazzee\Entity\Applicant
   */
  protected $_applicant;

  /**
   * Check credentials and intialize members
   */
  public function beforeAction()
  {
    parent::beforeAction();
    if (!isset($this->_store->applicantID)) {
      //Not authenticated
      $this->addMessage('error', "You are not logged in or your session has expired.  Please log in again");
      $this->redirectApplyPath('applicant/login');
    }
    $this->_applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->find($this->_store->applicantID);
    //make sure the url path is the actual application
    if ($this->_application != $this->_applicant->getApplication()) {
      $this->redirectApplyPath('applicant/login');
    }

    foreach ($this->_pages as $applicationPage) {
      $applicationPage->getJazzeePage()->setApplicant($this->_applicant);
    }
    $this->setVar('applicant', $this->_applicant);
  }

}