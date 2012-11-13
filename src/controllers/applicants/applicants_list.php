<?php

/**
 * List all applicants by status
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsListController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'List by Tag';
  const PATH = 'applicants/list';
  const ACTION_INDEX = 'All Applicants';

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/jquery.tagcloud.js'));
    $this->addScript($this->path('resource/scripts/controllers/applicants_list.controller.js'));
  }

  /**
   * List all applicants
   */
  public function actionIndex()
  {
    $tags = array();
    $tags['Accepted'] = array();
    $tags['Admitted'] = array();
    $tags['Denied'] = array();
    $tags['Declined'] = array();
    $tags['Locked'] = array();
    $tags['Paid'] = array();
    $notLocked = array();
    foreach ($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant) {
      if ($applicant->isLocked()) {
        $tags['Locked'][] = $applicant;
      } else {
        $notLocked[] = $applicant;
      }
      if ($applicant->hasPaid()) {
        $tags['Paid'][] = $applicant;
      }
      if ($applicant->getDecision() and $applicant->getDecision()->getAcceptOffer()) {
        $tags['Accepted'][] = $applicant;
      }
      if ($applicant->getDecision() and $applicant->getDecision()->getFinalAdmit()) {
        $tags['Admitted'][] = $applicant;
      }
      if ($applicant->getDecision() and $applicant->getDecision()->getDeclineOffer()) {
        $tags['Declined'][] = $applicant;
      }
      if ($applicant->getDecision() and $applicant->getDecision()->getFinalDeny()) {
        $tags['Denied'][] = $applicant;
      }
      foreach ($applicant->getTags() as $tag) {
        if (!isset($tags[$tag->getTitle()])) {
          $tags = array($tag->getTitle() => array()) + $tags;
        }
        $tags[$tag->getTitle()][] = $applicant;
      }
    }
    uksort($tags, "strnatcasecmp");
    $tags['Not Locked'] = $notLocked;
    $this->setVar('tags', $tags);
  }

}