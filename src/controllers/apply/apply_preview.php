<?php

/**
 * Special function for previewing applications
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplyPreviewController extends \Jazzee\AuthenticatedApplyController
{

  public function beforeAction()
  {
    parent::beforeAction();
    if(!$this->isPreviewMode()){
      throw new \Jazzee\Exception("Applicant #{$this->_applicant->getId()} {$this->_applicant->getFullName()} attempted to acces apply preview functions when not in preview mode.", E_USER_ERROR);
    }
  }

  /**
   * Lock the preview app
   */
  public function actionLock()
  {
    $this->_applicant->lock();
    $this->_em->persist($this->_applicant);
    $this->addMessage('success', 'Your preview application was locked.');
    $this->redirectApplyFirstPage();
  }

  /**
   * Lock the preview app
   */
  public function actionUnlock()
  {
    $this->_applicant->unLock();
    $this->_em->persist($this->_applicant);
    $this->addMessage('success', 'Your preview application was unlocked.');
    $this->redirectApplyFirstPage();
  }

}