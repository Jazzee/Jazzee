<?php

/**
 * View an applicant
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApplicantsSingleController extends \Jazzee\AdminController
{

  const TITLE = 'Single Applicant';
  const PATH = 'applicants/single';
  const ACTION_INDEX = 'View';
  const ACTION_PDF = 'Print as PDF';
  const ACTION_UPDATEBIO = 'Edit Account';
  const ACTION_ACTAS = 'Act as an applicant';
  const ACTION_MOVE = 'Move to another program';
  const ACTION_EXTENDDEADLINE = 'Extend Deadline';
  const ACTION_LOCK = 'Lock';
  const ACTION_UNLOCK = 'UnLock';
  const ACTION_ACTIVATE = 'Activate';
  const ACTION_DEACTIVATE = 'Deactivate';
  const ACTION_ADDTAG = 'Tag';
  const ACTION_REMOVETAG = 'Remove Tag';
  const ACTION_IGNOREDUPLICATE = 'Mark a duplicate applicant as ignored';
  const ACTION_ATTACHAPPLICANTPDF = 'Attach PDF';
  const ACTION_DELETEAPPLICANTPDF = 'Delete PDF';
  const ACTION_EDITANSWER = 'Edit Answer';
  const ACTION_DELETEANSWER = 'Delete Answer';
  const ACTION_ADDANSWER = 'Add Answer';
  const ACTION_ATTACHANSWERPDF = 'Attach PDF to Answer';
  const ACTION_DELETEANSWERPDF = 'Delete PDF attached to Answer';
  const ACTION_VERIFYANSWER = 'Verify Answer';
  const ACTION_NOMINATEADMIT = 'Nominate for Admission';
  const ACTION_NOMINATEDENY = 'Nominate for Deny';
  const ACTION_UNDONOMINATEADMIT = 'Undo Admit Nomination';
  const ACTION_UNDONOMINATEDENY = 'Undo Deny Nomination';
  const ACTION_FINALADMIT = 'Final Admit';
  const ACTION_FINALDENY = 'Final Deny';
  const ACTION_UNDOFINALADMIT = 'Undo Final Admit';
  const ACTION_UNDOFINALDENY = 'Undo Final Deny';
  const ACTION_ACCEPTOFFER = 'Accept Offer';
  const ACTION_DECLINEOFFER = 'Decline Offer';
  const ACTION_UNDOACCEPTOFFER = 'Undo Accept Offer';
  const ACTION_UNDODECLINEOFFER = 'Undo Decline Offer';
  const ACTION_SETTLEPAYMENT = 'Settle Payment';
  const ACTION_REFUNDPAYMENT = 'Refund Payment';
  const ACTION_REJECTPAYMENT = 'Reject Payment';
  const ACTION_VIEWAUDITLOG = 'View Applicant Audit Logs';
  const ACTION_EDITEXTERNALID = 'Edit an applicants external ID';

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->layout = 'json';
    $this->setLayoutVar('status', 'error');  //default to an error
    $this->addScript($this->path('resource/foundation/scripts/form.js'));
    $this->addScript($this->path('resource/scripts/classes/Applicant.class.js'));
    $this->addScript($this->path('resource/scripts/classes/DisplayChooser.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/applicants_single.controller.js'));
  }

  /**
   * Display single applicant
   * @param integer $applicantId
   */
  public function actionIndex($applicantId)
  {
    $this->layout = 'wide';
    $applicant = $this->getApplicantById($applicantId);
    $this->setVar('applicant', $applicant);
    $this->setVar('display', $this->_user->getMaximumDisplayForApplication($applicant->getApplication()));
  }

  /**
   * Get an applicants action status
   * @param \Jazzee\Entity\Applicant $applicant
   */
  protected function getActions(\Jazzee\Entity\Applicant $applicant)
  {
    $actions = array(
      'createdAt' => $applicant->getCreatedAt(),
      'updatedAt' => $applicant->getUpdatedAt(),
      'lastLogin' => $applicant->getLastLogin(),
      'deadlineExtension' => $applicant->getDeadlineExtension(),
      'externalId' => $applicant->getExternalId(),
      'allowExtendDeadline' => $this->checkIsAllowed($this->controllerName, 'extendDeadline'),
      'allowEditExternalId' => $this->checkIsAllowed($this->controllerName, 'editExternalId')
    );

    return $actions;
  }

  /**
   * Get an applicants tags
   * @param \Jazzee\Entity\Applicant $applicant
   */
  protected function getTags(\Jazzee\Entity\Applicant $applicant)
  {
    $tags = array(
      'tags' => array(),
      'allowAdd' => $this->checkIsAllowed($this->controllerName, 'addTag'),
      'allowRemove' => $this->checkIsAllowed($this->controllerName, 'removeTag'),
      'allTags' => array()
    );
    if ($this->checkIsAllowed($this->controllerName, 'addTag')) {
      foreach ($this->_em->getRepository('\Jazzee\Entity\Tag')->findByApplication($this->_application) as $tag) {
        $tags['allTags'][] = $tag->getTitle();
      }
    }
    foreach ($applicant->getTags() as $tag) {
      $tags['tags'][] = array(
        'id' => $tag->getId(),
        'title' => $tag->getTitle()
      );
    }

    return $tags;
  }

  /**
   * Get application pages
   * @param \Jazzee\Entity\Applicant $applicant
   */
  protected function getPages(\Jazzee\Entity\Applicant $applicant)
  {
    $pages = array(
      'pages' => array(),
      'allowAddAnswer' => $this->checkIsAllowed($this->controllerName, 'addAnswer'),
      'allowEditAnswer' => $this->checkIsAllowed($this->controllerName, 'editAnswer'),
      'allowDeleteAnswer' => $this->checkIsAllowed($this->controllerName, 'deleteAnswer')
    );
    $applicationPages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application' => $applicant->getApplication()->getId(), 'kind' => \Jazzee\Entity\ApplicationPage::APPLICATION), array('weight' => 'asc'));
    foreach ($applicationPages as $applicationPage) {
      if ($applicationPage->getJazzeePage()->showReviewPage()) {
        $params = array($applicant->getId(), $applicationPage->getPage()->getId());

        $content = $this->getActionOutput('refreshPage', $params);
        $content = str_replace(array("\n", "\r"), '', $content);
        $pages['pages'][] = array(
          'id' => $applicationPage->getPage()->getId(),
          'content' => $content
        );
      }
    }

    return $pages;
  }

  /**
   * Get an applicants decisions status
   * @param \Jazzee\Entity\Applicant $applicant
   */
  protected function getDecisions(\Jazzee\Entity\Applicant $applicant)
  {
    $status = '';
    if ($applicant->getDecision()) {
      $status = $applicant->getDecision()->status();
    }
    switch ($status) {
      case '':
        $status = 'No Decision';
        break;
      case 'nominateAdmit':
        $status = 'Nominated for Admission';
        break;
      case 'nominateDeny':
        $status = 'Nominated for Deny';
        break;
      case 'finalDeny':
        $status = 'Denied ' . ($applicant->getDecision()->getDecisionViewed() ? '(decision viewed ' . $applicant->getDecision()->getDecisionViewed()->format('c') . ')' : '(decision not viewed)');
        break;
      case 'finalAdmit':
        $status = 'Admited ' . ($applicant->getDecision()->getDecisionViewed() ? '(decision viewed ' . $applicant->getDecision()->getDecisionViewed()->format('c') . ')' : '(decision not viewed)');
        break;
      case 'acceptOffer': $status = 'Accepted';
        break;
      case 'declineOffer': $status = 'Declined';
        break;
    }
    if ($applicant->isLocked()) {
      $decisions = array('status' => $status);
      foreach (array('nominateAdmit', 'undoNominateAdmit', 'nominateDeny', 'undoNominateDeny', 'finalAdmit', 'finalDeny', 'undoFinalAdmit', 'undoFinalDeny', 'acceptOffer', 'declineOffer', 'undoAcceptOffer', 'undoDeclineOffer') as $type) {
        $decisions["allow{$type}"] = ($this->checkIsAllowed($this->controllerName, $type) && $applicant->getDecision()->can($type));
      }
    }
    $decisions['allowUnlock'] = $this->checkIsAllowed($this->controllerName, 'unlock');
    $decisions['allowLock'] = $this->checkIsAllowed($this->controllerName, 'lock');
    $decisions['isLocked'] = $applicant->isLocked();

    return $decisions;
  }

  /**
   * Get an applicants pdfs
   * @param \Jazzee\Entity\Applicant $applicant
   */
  public function getAttachments(\Jazzee\Entity\Applicant $applicant)
  {
    $attachments = array(
      'attachments' => array(),
      'allowAttach' => $this->checkIsAllowed($this->controllerName, 'attachApplicantPdf'),
      'allowDelete' => $this->checkIsAllowed($this->controllerName, 'deleteApplicantPdf')
    );
    foreach ($applicant->getAttachments() as $attachment) {
      $base = $applicant->getFullName() . '_attachment_' . $attachment->getId();
      //remove slashes in path to fix an apache issues with encoding slashes in redirects
      $base = str_replace(array('/', '\\'),'slash' , $base);
      $pdfName = $base . '.pdf';
      $pngName = $base . 'preview.png';
      \Jazzee\Globals::getFileStore()->createSessionFile($pdfName, $attachment->getAttachmentHash());
      if($attachment->getThumbnailHash() != null){
        \Jazzee\Globals::getFileStore()->createSessionFile($pngName, $attachment->getThumbnailHash());
        $thumbnailPath = \Jazzee\Globals::path('file/' . \urlencode($pngName));
      } else {
        $thumbnailPath = \Jazzee\Globals::path('resource/foundation/media/default_pdf_logo.png');
      }
      $attachments['attachments'][] = array(
        'id' => $attachment->getId(),
        'filePath' => $this->path('file/' . \urlencode($pdfName)),
        'previewPath' => $this->path('file/' . $thumbnailPath)
      );
    }

    return $attachments;
  }

  /**
   * Update Biography
   * @param integer $applicantId
   */
  public function actionUpdateBio($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicant->getId()}/updateBio"));
    $field = $form->newField();
    $field->setLegend('Edit ' . $applicant->getFirstName() . ' ' . $applicant->getLastName());

    $element = $field->newElement('TextInput', 'firstName');
    $element->setLabel('First Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->setValue($applicant->getFirstName());

    $element = $field->newElement('TextInput', 'middleName');
    $element->setLabel('Middle Name');
    $element->setValue($applicant->getMiddleName());

    $element = $field->newElement('TextInput', 'lastName');
    $element->setLabel('Last Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->setValue($applicant->getLastName());

    $element = $field->newElement('TextInput', 'suffix');
    $element->setLabel('Suffix');
    $element->setValue($applicant->getSuffix());

    $element = $field->newElement('TextInput', 'email');
    $element->setLabel('Email');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));
    $element->addFilter(new \Foundation\Form\Filter\Lowercase($element));
    $element->setValue($applicant->getEmail());

    $form->newButton('submit', 'Save Changes');
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $applicant->setFirstName($input->get('firstName'));
        $applicant->setMiddleName($input->get('middleName'));
        $applicant->setLastName($input->get('lastName'));
        $applicant->setSuffix($input->get('suffix'));
        $applicant->setEmail($input->get('email'));

        $this->_em->persist($applicant);
        $this->auditLog($applicant, 'Updated Bio');
        $this->setLayoutVar('status', 'success');
        $bio = array(
          'name' => $applicant->getFullName(),
          'email' => $applicant->getEmail(),
          'allowEdit' => $this->checkIsAllowed($this->controllerName, 'updateBio')
        );
        $this->setVar('result', array('bio' => $bio));
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }

  /**
   * Act as an applicant
   * Sets a session up as the applicant and opens a new window as that applicant
   * @param integer $applicantId
   */
  public function actionActas($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $store = $this->_session->getStore('apply', $this->_config->getApplicantSessionLifetime());
    $store->applicantID = $applicant->getId();
    $pages = $this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION);
    $link = $this->absoluteApplyPath('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/page/' . $pages[0]->getId());
    $this->setVar('result', array('link' => $link));
    $this->setLayoutVar('status', 'success');
    $this->auditLog($applicant, 'Became applicant');
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * move applicant
   * @param integer $applicantId
   */
  public function actionMove($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicant->getId()}/move"));
    $field = $form->newField();
    $field->setLegend('Move ' . $applicant->getFullName());

    $element = $field->newElement('SelectList', 'program');
    $element->setLabel('Program');
    $programs = $this->_em->getRepository('\Jazzee\Entity\Program')->findBy(array('isExpired' => false), array('name' => 'ASC'));
    $applications = array();
    foreach ($programs as $program) {
      $application = $this->_em->getRepository('\Jazzee\Entity\Application')->findOneBy(array('program' => $program->getId(), 'cycle' => $this->_cycle->getId()));
      if ($application != $this->_application and self::isAllowed($this->controllerName, 'move', $this->_user, $program, $application)) {
        $applications[$this->_cycle->getName()][$program->getId()] = $application;
        $element->newItem($program->getId(), $program->getName());
      }
    }
    $element->setValue($this->_program->getId());

    $form->newButton('submit', 'Move Applicant');
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $newApplication = $applications[$this->_cycle->getName()][$input->get('program')];
        $applicant->setApplication($newApplication);
        $this->_em->persist($applicant);
        $this->auditLog($applicant, 'Moved from ' . $this->_program->getName() . ' to ' . $newApplication->getProgram()->getName());
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }

  /**
   * Get content for a page
   * @param integer $applicantId
   * @param integer $pageId
   */
  public function actionRefreshPage($applicantId, $pageId)
  {
    $this->layout = 'blank';
    $applicant = $this->getApplicantById($applicantId);
    $page = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $pageId, 'application' => $this->_application->getId()));
    $this->setVar('variables', array('page' => $page, 'applicant' => $applicant, 'display'=>$this->_user->getMaximumDisplayForApplication($this->_application)));
    $class = $page->getPage()->getType()->getClass();
    $this->setVar('element', $class::applicantsSingleElement());
    $this->loadView($this->controllerName . '/element');
  }

  /**
   * Get content for the SIR page
   * @param integer $applicantId
   */
  public function actionRefreshSirPage($applicantId)
  {
    $this->layout = 'blank';
    $applicant = $this->getApplicantById($applicantId);
    if ($applicant->getDecision()->getAcceptOffer() and $pages = $applicant->getApplication()->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_ACCEPT)) {
      $page = $pages[0];
      $this->setVar('variables', array('page' => $page, 'applicant' => $applicant));
      $class = $page->getPage()->getType()->getClass();
      $this->setVar('element', $class::sirApplicantsSingleElement());
      $this->loadView($this->controllerName . '/element');

      return;
    }
    if ($applicant->getDecision()->getDeclineOffer() and $pages = $applicant->getApplication()->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_DECLINE)) {
      $page = $pages[0];
      $this->setVar('variables', array('page' => $page, 'applicant' => $applicant));
      $class = $page->getPage()->getType()->getClass();
      $this->setVar('element', $class::sirApplicantsSingleElement());
      $this->loadView($this->controllerName . '/element');

      return;
    }
    $this->loadView($this->controllerName . '/form'); //loads a blank since there is no form
  }

  /**
   * View the applicants audit log
   * Placeholder for authorization
   * @param integer $applicantId
   */
  public function actionViewAuditLog()
  {

  }

  /**
   * Nominate and applicant for admission
   * @param integer $applicantId
   */
  public function actionNominateAdmit($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$applicant->isLocked()) {
      throw new \Jazzee\Exception('Tried to nominate an applicant that was not locked');
    }
    $applicant->getDecision()->nominateAdmit();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Nominated for Admission');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Undo Nominate an applicant for admission
   * @param integer $applicantId
   */
  public function actionUndoNominateAdmit($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoNominateAdmit();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Undo Nominate Admit');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Final Admit applicant
   * @param integer $applicantId
   */
  public function actionFinalAdmit($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/finalAdmit"));
    $field = $form->newField();
    $field->setLegend('Admit ' . $applicant->getFirstName() . ' ' . $applicant->getLastName());

    $element = $field->newElement('DateInput', 'offerResponseDeadline');
    $element->setLabel('Offer Response Deadline');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, 'today'));

    $element = $field->newElement('RadioList', 'sendMessage');
    $element->setLabel('Send the applicant a notification?');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Admit Applicant');
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $applicant->getDecision()->finalAdmit();
        $applicant->getDecision()->setOfferResponseDeadline($input->get('offerResponseDeadline'));
        if ($input->get('sendMessage')) {
          $thread = new \Jazzee\Entity\Thread();
          $thread->setSubject('Admission Decision');
          $thread->setApplicant($applicant);

          $message = new \Jazzee\Entity\Message();
          $message->setSender(\Jazzee\Entity\Message::PROGRAM);
          $text = $this->_application->getAdmitLetter();
          $search = array(
            '_Admit_Date_',
            '_Applicant_Name_',
            '_Offer_Response_Deadline_'
          );
          $replace = array();
          $replace[] = $applicant->getDecision()->getFinalAdmit()->format('F jS Y');
          $replace[] = $applicant->getFullName();
          $replace[] = $applicant->getDecision()->getOfferResponseDeadline()->format('F jS Y g:ia');
          $text = str_ireplace($search, $replace, $text);
          $text = nl2br($text);
          $message->setText($text);
          $thread->addMessage($message);
          $this->_em->persist($thread);
          $this->_em->persist($message);
        }
        $this->_em->persist($applicant);
        $this->auditLog($applicant, 'Final Admit');
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }

  /**
   * Undo Final Admit
   * @param integer $applicantId
   */
  public function actionUndoFinalAdmit($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoFinalAdmit();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Undo Final Admit');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Tag an applicant
   * @param integer $applicantID
   */
  public function actionRefreshTags($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $this->setVar('result', array('tags' => $this->getTags($applicant)));
    $this->setLayoutVar('status', 'success');
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Tag an applicant
   * @param integer $applicantID
   */
  public function actionAddTag($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $tag = $this->_em->getRepository('\Jazzee\Entity\Tag')->findOneBy(array('title' => $this->post['tagTitle']));
    if (!$tag) {
      $tag = new \Jazzee\Entity\Tag();
      $tag->setTitle($this->post['tagTitle']);
      $this->_em->persist($tag);
    }
    $applicant->addTag($tag);
    $this->_em->persist($applicant);
    $this->_em->flush(); //flush here so the tag ID will be available
    $this->setVar('result', array('tags' => $this->getTags($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Remove a tag from an applicant
   * @param integer $applicantID
   */
  public function actionRemoveTag($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $tag = $this->_em->getRepository('\Jazzee\Entity\Tag')->find($this->post['tagId']);
    $applicant->removeTag($tag);
    $this->_em->persist($applicant);
    $this->setVar('result', array('tags' => $this->getTags($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Nominate and applicant for deny
   * @param integer $applicantId
   */
  public function actionNominateDeny($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$applicant->isLocked()) {
      throw new \Jazzee\Exception('Tried to nominate an applicant that was not locked');
    }
    $applicant->getDecision()->nominateDeny();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Nominate Deny');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Undo Nominate an applicant for deny
   * @param integer $applicantId
   */
  public function actionUndoNominateDeny($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoNominateDeny();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Undo Nominate Deny');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Final Deny applicant
   * @param integer $applicantId
   */
  public function actionFinalDeny($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/finalDeny"));
    $field = $form->newField();
    $field->setLegend('Deny ' . $applicant->getFirstName() . ' ' . $applicant->getLastName());

    $element = $field->newElement('RadioList', 'sendMessage');
    $element->setLabel('Send the applicant a notification?');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Deny Applicant');
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $applicant->getDecision()->finalDeny();
        if ($input->get('sendMessage')) {
          $thread = new \Jazzee\Entity\Thread();
          $thread->setSubject('Admission Decision');
          $thread->setApplicant($applicant);

          $message = new \Jazzee\Entity\Message();
          $message->setSender(\Jazzee\Entity\Message::PROGRAM);
          $text = $this->_application->getDenyLetter();
          $search = array(
            '_Deny_Date_',
            '_Applicant_Name_'
          );
          $replace = array();
          $replace[] = $applicant->getDecision()->getFinalDeny()->format('F jS Y');
          $replace[] = $applicant->getFullName();
          $text = str_ireplace($search, $replace, $text);
          $text = nl2br($text);
          $message->setText($text);
          $thread->addMessage($message);
          $this->_em->persist($thread);
          $this->_em->persist($message);
        }
        $this->_em->persist($applicant);
        $this->auditLog($applicant, 'Final Deny');
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }

  /**
   * Undo Final Deny
   * @param integer $applicantId
   */
  public function actionUndoFinalDeny($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoFinalDeny();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Undo Final Deny');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Accept Offer
   * @param integer $applicantId
   */
  public function actionAcceptOffer($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $accept = false;
    if ($pages = $this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_ACCEPT)) {
      $sirPage = $pages[0];
      $sirPage->getJazzeePage()->setApplicant($applicant);
      $sirPage->getJazzeePage()->setController($this);
      $form = $sirPage->getJazzeePage()->getForm();
      if (!empty($this->post)) {
        $this->setLayoutVar('textarea', true);
        if ($input = $sirPage->getJazzeePage()->validateInput($this->post)) {
          $accept = true;
          $sirPage->getJazzeePage()->newAnswer($input);
        }
        $form = $sirPage->getJazzeePage()->getForm();  //refresh the form for branching
      }
    } else {
      $form = new \Foundation\Form();
      $field = $form->newField();
      $field->setLegend('Accept Offer For ' . $applicant->getFirstName() . ' ' . $applicant->getLastName());
      $form->newButton('submit', 'Accept Offer');
      if (!empty($this->post)) {
        $this->setLayoutVar('textarea', true);
        if ($input = $form->processInput($this->post)) {
          $accept = true;
        }
      }
    }

    if ($accept) {
      $applicant->getDecision()->acceptOffer();
      $this->_em->persist($applicant);
      $this->auditLog($applicant, 'Accept Offer');
      $this->setLayoutVar('status', 'success');
      $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    }
    $form->setAction($this->path("applicants/single/{$applicantId}/acceptOffer"));
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Declien Offer Offer
   * @param integer $applicantId
   */
  public function actionDeclineOffer($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $decline = false;
    if ($pages = $this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_DECLINE)) {
      $sirPage = $pages[0];
      $sirPage->getJazzeePage()->setApplicant($applicant);
      $sirPage->getJazzeePage()->setController($this);
      $form = $sirPage->getJazzeePage()->getForm();
      if (!empty($this->post)) {
        $this->setLayoutVar('textarea', true);
        if ($input = $sirPage->getJazzeePage()->validateInput($this->post)) {
          $decline = true;
          $sirPage->getJazzeePage()->newAnswer($input);
        }
        $form = $sirPage->getJazzeePage()->getForm();  //refresh the form for branching
      }
    } else {
      $form = new \Foundation\Form();
      $field = $form->newField();
      $field->setLegend('Decline Offer For ' . $applicant->getFirstName() . ' ' . $applicant->getLastName());
      $form->newButton('submit', 'Decline Offer');
      if (!empty($this->post)) {
        $this->setLayoutVar('textarea', true);
        if ($input = $form->processInput($this->post)) {
          $decline = true;
        }
      }
    }

    if ($decline) {
      $applicant->getDecision()->declineOffer();
      $this->_em->persist($applicant);
      $this->auditLog($applicant, 'Decline Offer');
      $this->setLayoutVar('status', 'success');
      $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    }
    $form->setAction($this->path("applicants/single/{$applicantId}/declineOffer"));
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Undo Accept Offer
   * @param integer $applicantId
   */
  public function actionUndoAcceptOffer($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoAcceptOffer();
    if ($pages = $this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_ACCEPT)) {
      $applicationPage = $pages[0];
      $applicationPage->getJazzeePage()->setApplicant($applicant);
      $applicationPage->getJazzeePage()->setController($this);
      foreach ($applicant->findAnswersByPage($applicationPage->getPage()) as $answer) {
        $applicationPage->getJazzeePage()->deleteAnswer($answer->getId());
      }
    }
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Undo Accept Offer');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Undo Decline Offer
   * @param integer $applicantId
   */
  public function actionUndoDeclineOffer($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoDeclineOffer();
    if ($pages = $this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_DECLINE)) {
      $applicationPage = $pages[0];
      $applicationPage->getJazzeePage()->setApplicant($applicant);
      $applicationPage->getJazzeePage()->setController($this);
      foreach ($applicant->findAnswersByPage($applicationPage->getPage()) as $answer) {
        $applicationPage->getJazzeePage()->deleteAnswer($answer->getId());
      }
    }
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Undo Decline Offer');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Unlock an application
   * @param integer $applicantId
   */
  public function actionUnlock($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->unlock();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Unlock Application');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Lock an application
   * @param integer $applicantId
   */
  public function actionLock($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->lock();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Lock Application');
    $this->setVar('result', array('decisions' => $this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Activate an applicant
   * @param integer $applicantId
   */
  public function actionActivate($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->activate();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Activated Applicant');
    $this->setLayoutVar('status', 'success');
    $this->setVar('result', array('path' => $this->path('applicants/single/' . $applicant->getId()), 'message'=>$applicant->getFullName() . ' activated.'));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Deactivate an applicant
   * @param integer $applicantId
   */
  public function actionDeactivate($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $applicant->deactivate();
    $this->_em->persist($applicant);
    $this->auditLog($applicant, 'Deactivated Applicant');
    $this->setLayoutVar('status', 'success');
    $this->setVar('result', array(
      'path' => $this->path('applicants/single/' . $applicant->getId()),
      'message' => $applicant->getFullName() . ' deactivated.'
    ));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Extend Applicant Deadline
   * @param integer $applicantId
   */
  public function actionExtendDeadline($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/extendDeadline"));
    $field = $form->newField();
    $field->setLegend('Extend deadline for ' . $applicant->getFirstName() . ' ' . $applicant->getLastName());

    $element = $field->newElement('DateInput', 'deadline');
    $element->setLabel('New Deadline');
    $element->setFormat('Clear to remove the extension');
    if ($applicant->getDeadlineExtension()) {
      $element->setValue($applicant->getDeadlineExtension()->format('c'));
    }
    if($applicant->getApplication()->getClose()){
      $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, $applicant->getApplication()->getClose()->format('c')));
    }
    $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, 'now'));

    $form->newButton('submit', 'Extend Deadline');
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        if ($input->get('deadline')) {
          $applicant->setDeadlineExtension($input->get('deadline'));
          $this->auditLog($applicant, 'Extend Deadline to ' . $applicant->getDeadlineExtension()->format('c'));
        } else {
          $applicant->removeDeadlineExtension();
          $this->auditLog($applicant, 'Remove Deadline Extension');
        }
        $this->_em->persist($applicant);
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('result', array('actions' => $this->getActions($applicant)));
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }

  /**
   * Add an answer
   * @param integer $applicantId
   * @param integer $pageId
   */
  public function actionAddAnswer($applicantId, $pageId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $pageId, 'application' => $this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $pageEntity->getJazzeePage()->validateInput($this->post)) {
        $pageEntity->getJazzeePage()->newAnswer($input);
        $this->setLayoutVar('status', 'success');
        $this->auditLog($applicant, 'Added Answer to page ' . $pageEntity->getTitle());
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $form = $pageEntity->getJazzeePage()->getForm();
    $form->setAction($this->path("applicants/single/{$applicantId}/addAnswer/{$pageId}"));
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit an answer
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionIgnoreDuplicate($applicantId, $duplicateId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$duplicate = $applicant->getDuplicateById($duplicateId)) {
      throw new \Jazzee\Exception("Duplicate {$duplicateId} does not belong to applicant {$applicantId}");
    }
    $duplicate->ignore();
    $this->_em->persist($duplicate);
    $this->auditLog($applicant, 'Ignored Duplicate application for ' . $duplicate->getDuplicate()->getFullName());
    $duplicates = array();
    foreach ($applicant->getDuplicates() as $duplicate) {
      if (!$duplicate->isIgnored()) {
        $duplicates[] = array(
          'id' => $duplicate->getId(),
          'name' => $duplicate->getDuplicate()->getFullName(),
          'complete' => $duplicate->getDuplicate()->getPercentComplete() * 100,
          'program' => $duplicate->getDuplicate()->getApplication()->getProgram()->getName()
        );
      }
    }
    $this->setLayoutVar('status', 'success');
    $this->setVar('result', array('duplicates' => $duplicates));
    $this->addMessage('success', "Duplicate Ignored");
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Edit an answer
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionEditAnswer($applicantId, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $answer->getPage()->getId(), 'application' => $this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    $pageEntity->getJazzeePage()->fill($answerId);
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $pageEntity->getJazzeePage()->validateInput($this->post)) {
        $pageEntity->getJazzeePage()->updateAnswer($input, $answerId);
        $this->setLayoutVar('status', 'success');
        $this->auditLog($applicant, 'Edited answer ' . $answer->getId() . ' on page ' . $pageEntity->getTitle());
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $form = $pageEntity->getJazzeePage()->getForm();
    $form->setAction($this->path("applicants/single/{$applicantId}/editAnswer/{$answerId}"));
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Delete an answer
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionDeleteAnswer($applicantId, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $answer->getPage()->getId(), 'application' => $this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    $pageEntity->getJazzeePage()->fill($answerId);
    $pageEntity->getJazzeePage()->deleteAnswer($answerId);
    $this->auditLog($applicant, 'Deleted answer from page ' . $pageEntity->getTitle());
    $this->setVar('result', true);
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Attach PDF to answer
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionVerifyAnswer($applicantId, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/verifyAnswer/{$answerId}"));
    $field = $form->newField();
    $field->setLegend('Set Verification Status');

    $answerStatusTypes = array();
    foreach ($this->_em->getRepository('\Jazzee\Entity\AnswerStatusType')->findBy(array(), array('name' => 'ASC')) as $type) {
      $answerStatusTypes[$type->getId()] = $type;
    }
    //only set public status on pages where the status can be displayed
    if ($answer->getPage()->answerStatusDisplay()) {
      $element = $field->newElement('SelectList', 'publicStatus');
      $element->setLabel('Public Status');
      $element->setFormat('Visible to applicants');
      $element->newItem('', '');
      foreach ($answerStatusTypes as $id => $type) {
        $element->newItem($id, $type->getName());
      }
      if ($answer->getPublicStatus()) {
        $element->setValue($answer->getPublicStatus()->getId());
      }
    }

    $element = $field->newElement('SelectList', 'privateStatus');
    $element->setLabel('Private Status');
    $element->setFormat('Only visible to program');
    $element->newItem('', '');
    foreach ($answerStatusTypes as $id => $type) {
      $element->newItem($id, $type->getName());
    }
    if ($answer->getPrivateStatus()) {
      $element->setValue($answer->getPrivateStatus()->getId());
    }

    $form->newButton('submit', 'Verify Answer');
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $answer->clearPublicStatus();
        $answer->clearPrivateStatus();
        if ($input->get('publicStatus')) {
          $answer->setPublicStatus($answerStatusTypes[$input->get('publicStatus')]);
        }
        if ($input->get('privateStatus')) {
          $answer->setPrivateStatus($answerStatusTypes[$input->get('privateStatus')]);
        }
        $this->_em->persist($applicant);
        $this->_em->persist($answer);
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Attach PDF to answer
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionAttachAnswerPdf($applicantId, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/attachAnswerPdf/{$answerId}"));
    $field = $form->newField();
    $field->setLegend('Attach PDF');

    $element = $field->newElement('FileInput', 'pdf');
    $element->setLabel('File');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\PDF($element));
    if ($this->_config->getVirusScanUploads()) {
      $element->addValidator(new \Foundation\Form\Validator\Virusscan($element));
    }
    $element->addValidator(new \Foundation\Form\Validator\PDFNotEncrypted($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));

    if ($this->_config->getMaximumAdminFileUploadSize()) {
      $max = $this->_config->getMaximumAdminFileUploadSize();
    } else {
      $max = \Foundation\Utility::convertIniShorthandValue(\ini_get('upload_max_filesize'));
    }
    $element->addValidator(new \Foundation\Form\Validator\MaximumFileSize($element, $max));

    $form->newButton('submit', 'Attach PDF to Answer');
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $attachment = new \Jazzee\Entity\Attachment();
        $attachment->setApplicant($applicant);
        $attachment->setAnswer($answer);
        $attachment->setAttachment($input->get('pdf'));
        $this->_em->persist($attachment);
        //persist the applicant and answer to catch the last update
        $this->_em->persist($applicant);
        $this->_em->persist($answer);
        $this->_em->flush();  //flush early so the attachment gets a good id for the preview
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Delete PDF attached to answer
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionDeleteAnswerPdf($applicantId, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }

    if ($attachment = $answer->getAttachment()) {
      $this->_em->remove($attachment);
      $answer->markLastUpdate();
      $this->_em->persist($answer);
      $this->auditLog($applicant, 'Deleted PDF from answer ' . $answer->getId());
      $this->setLayoutVar('status', 'success');
    }
    $this->setVar('result', true);
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Attach PDF to applicant
   * @param integer $applicantId
   */
  public function actionAttachApplicantPdf($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/attachApplicantPdf"));
    $field = $form->newField();
    $field->setLegend('Attach PDF');

    $element = $field->newElement('FileInput', 'pdf');
    $element->setLabel('File');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\PDF($element));
    $element->addValidator(new \Foundation\Form\Validator\PDFNotEncrypted($element));
    if ($this->_config->getVirusScanUploads()) {
      $element->addValidator(new \Foundation\Form\Validator\Virusscan($element));
    }
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));

    if ($this->_config->getMaximumAdminFileUploadSize()) {
      $max = $this->_config->getMaximumAdminFileUploadSize();
    } else {
      $max = \Foundation\Utility::convertIniShorthandValue(\ini_get('upload_max_filesize'));
    }
    $element->addValidator(new \Foundation\Form\Validator\MaximumFileSize($element, $max));

    $form->newButton('submit', 'Attach PDF to Applicant');
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $attachment = new \Jazzee\Entity\Attachment();
        $attachment->setAttachment($input->get('pdf'));
        $applicant->addAttachment($attachment);
        $this->_em->persist($attachment);
        //persist the applicant and answer to catch the last update
        $this->_em->persist($applicant);
        $this->_em->flush();  //flush early so the attachment gets a good id for the preview
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('result', array('attachments' => $this->getAttachments($applicant)));
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Create a pdf from applicant
   * @param integer $applicantId
   * @param string $layout the format for the pdf file
   */
  public function actionPdf($applicantId, $layout)
  {
    $applicant = $this->getApplicantById($applicantId);
    $pdf = new \Jazzee\ApplicantPDF($this->_config->getPdflibLicenseKey(), $layout == 'landscape' ? \Jazzee\ApplicantPDF::USLETTER_LANDSCAPE : \Jazzee\ApplicantPDF::USLETTER_PORTRAIT, $this);
    $this->setVar('filename', $applicant->getFullName() . '.pdf');
    $this->setVar('blob', $pdf->pdf($applicant));
  }

  /**
   * Create a pdf from template
   * @param integer $applicantId
   * @param integer $templateId
   */
  public function actionPdftemplate($applicantId, $templateId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if($template = $this->_application->getTemplateById($templateId)){
      $pdf = new \Jazzee\TemplatePDF($this->_config->getPdflibLicenseKey(), $template, $this);
      $this->setVar('blob', $pdf->pdf($applicant));
      $this->setVar('filename', $applicant->getFullName() . '.pdf');
      $this->loadView('applicants_single/pdf');
    } else {
      $this->addMessage('error', 'That is not a valid template for this application.');
      $this->setLayoutVar('status', 'error');
    }
  }

  /**
   * Delete PDF attached to applicant
   * @param integer $applicantId
   * @param integer $attachmentId
   */
  public function actionDeleteApplicantPdf($applicantId, $attachmentId)
  {
    $applicant = $this->getApplicantById($applicantId);

    if ($attachment = $this->_em->getRepository('\Jazzee\Entity\Attachment')->findOneBy(array('id' => $attachmentId, 'applicant' => $applicant->getId()))) {
      $applicant->removeAttachment($attachment);
      $this->_em->remove($attachment);
      $applicant->markLastUpdate();
      $this->_em->persist($applicant);
      $this->auditLog($applicant, 'Deleted applicant PDF');
      $this->setLayoutVar('status', 'success');
    }
    $this->setVar('result', array('attachments' => $this->getAttachments($applicant)));
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Settle a Payment
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionSettlePayment($applicantId, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }
    if (!$payment = $answer->getPayment()) {
      throw new \Jazzee\Exception("Answer {$answerId} does not have a payment.");
    }
    if ($payment->getStatus() != \Jazzee\Entity\Payment::PENDING) {
      throw new \Jazzee\Exception('Payment ' . $payment->getId() . ' is not pending so cannot be settled.');
    }
    $form = $payment->getType()->getJazzeePaymentType($this)->getSettlePaymentForm($payment);
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $result = $payment->getType()->getJazzeePaymentType($this)->settlePayment($payment, $input);
        if ($result === true) {
          $this->_em->persist($payment);
          foreach ($payment->getVariables() as $var) {
            $this->_em->persist($var);
          }
          $this->auditLog($applicant, 'Settled Payment ' . $payment->getId());
          $this->setLayoutVar('status', 'success');
        } else {
          $fields = $form->getFields();
          $elements = $fields[0]->getElements();
          array_pop($elements)->addMessage($result);
          $this->setLayoutVar('status', 'error');
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $form->setAction($this->path("applicants/single/{$applicantId}/settlePayment/{$answerId}"));
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Refund Payment
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionRefundPayment($applicantId, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }
    if (!$payment = $answer->getPayment()) {
      throw new \Jazzee\Exception("Answer {$answerId} does not have a payment.");
    }
    if ($payment->getStatus() != \Jazzee\Entity\Payment::PENDING and $payment->getStatus() != \Jazzee\Entity\Payment::SETTLED) {
      throw new \Jazzee\Exception('Payment ' . $payment->getId() . ' is not settled or pending so cannot be refunded.');
    }
    $form = $payment->getType()->getJazzeePaymentType($this)->getRefundPaymentForm($payment);
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $result = $payment->getType()->getJazzeePaymentType($this)->refundPayment($payment, $input);
        if ($result === true) {
          $this->_em->persist($payment);
          foreach ($payment->getVariables() as $var) {
            $this->_em->persist($var);
          }
          $this->auditLog($applicant, 'Refunded Payment ' . $payment->getId());
          $this->setLayoutVar('status', 'success');
        } else {
          $fields = $form->getFields();
          $elements = $fields[0]->getElements();
          array_pop($elements)->addMessage($result);
          $this->setLayoutVar('status', 'error');
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $form->setAction($this->path("applicants/single/{$applicantId}/refundPayment/{$answerId}"));
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Reject Payment
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionRejectPayment($applicantId, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }
    if (!$payment = $answer->getPayment()) {
      throw new \Jazzee\Exception("Answer {$answerId} does not have a payment.");
    }
    if ($payment->getStatus() != \Jazzee\Entity\Payment::PENDING and $payment->getStatus() != \Jazzee\Entity\Payment::SETTLED) {
      throw new \Jazzee\Exception('Payment ' . $payment->getId() . ' is not settled or pending so cannot be rejected.');
    }
    $form = $payment->getType()->getJazzeePaymentType($this)->getRejectPaymentForm($payment);
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        $result = $payment->getType()->getJazzeePaymentType($this)->rejectPayment($payment, $input);
        if ($result === true) {
          $this->_em->persist($payment);
          foreach ($payment->getVariables() as $var) {
            $this->_em->persist($var);
          }
          $this->setLayoutVar('status', 'success');
          $this->auditLog($applicant, 'Rejected Payment ' . $payment->getId());
        } else {
          $fields = $form->getFields();
          $elements = $fields[0]->getElements();
          array_pop($elements)->addMessage($result);
          $this->setLayoutVar('status', 'error');
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $form->setAction($this->path("applicants/single/{$applicantId}/rejectPayment/{$answerId}"));
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Do something with an answer
   * Passes everything off to the page to perform a special action
   * @param integer $applicantId
   * @param string $what the special method name
   * @param integer $answerId
   */
  public function actionDo($applicantId, $what, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $answer->getPage()->getId(), 'application' => $this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    $method = 'do_' . $what;
    if (method_exists($pageEntity->getJazzeePage(), $method)) {
      $form = $pageEntity->getJazzeePage()->$method($answerId, $this->post);
      $form->setAction($this->path("applicants/single/{$applicantId}/do/{$what}/{$answerId}"));
      $this->setVar('form', $form);
      if (!empty($this->post)) {
        $this->setLayoutVar('textarea', true);
        $this->auditLog($applicant, 'Answer action ' . $what . ' on answer ' . $answerId);
      }
    }
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Do something with an answer not involving a form
   * Passes everything off to the page to perform a special action
   * @param integer $applicantId
   * @param string $what the special method name
   * @param integer $answerId
   */
  public function actionDoAction($applicantId, $what, $answerId)
  {
    $applicant = $this->getApplicantById($applicantId);
    if (!$answer = $applicant->findAnswerById($answerId)) {
      throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    }
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $answer->getPage()->getId(), 'application' => $this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    $method = 'do_' . $what;
    if (method_exists($pageEntity->getJazzeePage(), $method)) {
      $pageEntity->getJazzeePage()->$method($answerId);
      $this->auditLog($applicant, 'Answer action ' . $what . ' on answer ' . $answerId);
      $this->setLayoutVar('status', 'success');
    }
    $this->setVar('result', true);
    $this->loadView($this->controllerName . '/result');
  }

  /**
   * Do something with a page
   * Passes everything off to the page to perform a special action
   * @param integer $applicantId
   * @param string $what the special method name
   * @param integer $pageId
   */
  public function actionPageDo($applicantId, $what, $pageId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $pageId, 'application' => $this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    $method = 'do_' . $what;
    if (method_exists($pageEntity->getJazzeePage(), $method)) {
      $form = $pageEntity->getJazzeePage()->$method($this->post);
      $form->setAction($this->path("applicants/single/{$applicantId}/pageDo/{$what}/{$pageId}"));
      $this->setVar('form', $form);
      if (!empty($this->post)) {
        $this->setLayoutVar('textarea', true);
        $this->auditLog($applicant, 'Page action ' . $what . ' on page ' . $pageEntity->getTitle());
      }
    }
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Do something with an page not involving a form
   * Passes everything off to the page to perform a special action
   * @param integer $applicantId
   * @param string $what the special method name
   * @param integer $pageId
   */
  public function actionDoPageAction($applicantId, $what, $pageId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $pageId, 'application' => $this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    $method = 'do_' . $what;
    if (method_exists($pageEntity->getJazzeePage(), $method)) {
      $pageEntity->getJazzeePage()->$method();
      $this->setLayoutVar('status', 'success');
      $this->auditLog($applicant, 'Page action ' . $what . ' on page ' . $pageEntity->getTitle());
    }
    $this->setVar('result', true);
    $this->loadView($this->controllerName . '/result');
  }

  public function getActionPath()
  {
    return null;
  }

  /**
   * Log something in the audit log
   * @param \Jazzee\Entity\Applicant $applicant
   * @param type $string
   */
  protected function auditLog(\Jazzee\Entity\Applicant $applicant, $text)
  {
    $auditLog = new \Jazzee\Entity\AuditLog($this->_user, $applicant, $text);
    $this->_em->persist($auditLog);
  }

  /**
   * Edit an applicants external ID
   * @param integer $applicantId
   */
  public function actionEditExternalId($applicantId)
  {
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/editExternalId"));
    $field = $form->newField();
    $field->setLegend('Change External ID for ' . $applicant->getFirstName() . ' ' . $applicant->getLastName());

    $element = $field->newElement('TextInput', 'externalId');
    $element->setLabel('External ID');
    $element->setFormat('Clear to remove the id');
    $element->setValue($applicant->getExternalId());
    $element->addValidator(new \Foundation\Form\Validator\SpecialObject($element, array(
      'object' => $this->_application,
      'method' => 'validateExternalId',
      'errorMessage' => 'This is not a valid External ID for this program.'
    )));

    $form->newButton('submit', 'Apply');
    if (!empty($this->post)) {
      $this->setLayoutVar('textarea', true);
      if ($input = $form->processInput($this->post)) {
        if ($input->get('externalId')) {
          $applicant->setExternalId($input->get('externalId'));
          $this->auditLog($applicant, 'External ID set to ' . $applicant->getExternalId());
          $this->_em->persist($applicant);
          $this->setLayoutVar('status', 'success');
        } else {
          $applicant->setExternalId(null);
          $this->auditLog($applicant, 'Removed External ID');
          $this->_em->persist($applicant);
          $this->setLayoutVar('status', 'success');
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('result', array('actions' => $this->getActions($applicant)));
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }

  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    //several views are controller by the complete action
    if (in_array($action, array('refreshTags', 'refreshPage', 'refreshSirPage'))) {
      $action = 'index';
    }
    if (in_array($action, array('do', 'doAction', 'pageDo', 'doPageAction'))) {
      $action = 'editAnswer';
    }
    if (in_array($action, array('pdf', 'pdftemplate'))) {
      $action = 'pdf';
    }

    //require a working ApplicantPDF class
    if (in_array($action, array('pdf'))) {
      if (!\Jazzee\ApplicantPDF::isAvailable()) {
        return false;
      }
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}