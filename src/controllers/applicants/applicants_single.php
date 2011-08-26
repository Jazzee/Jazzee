<?php
/**
 * View an applicant
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsSingleController extends \Jazzee\AdminController {
  const TITLE = 'Single Applicant';
  const PATH = 'applicants/single';
  
  const ACTION_INDEX = 'View';
  const ACTION_PDF = 'Print as PDF';
  const ACTION_UPDATEBIO = 'Edit Account';
  const ACTION_EXTENDDEADLINE = 'Extend Deadline';
  const ACTION_LOCK = 'Lock';
  const ACTION_UNLOCK = 'UnLock';
  const ACTION_ADDTAG = 'Tag';
  const ACTION_REMOVETAG = 'Remove Tag';
  const ACTION_ATTACHAPPLICANTPDF = 'Attach PDF';
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
  const ACTION_UNDOACCEPTOFFER = 'Undo Accept Offer';
  const ACTION_UNDODECLINEOFFER = 'Undo Decline Offer';
  const ACTION_SETTLEPAYMENT = 'Settle Payment';
  const ACTION_REFUNDPAYMENT = 'Refund Payment';
  const ACTION_REJECTPAYMENT = 'Reject Payment';
  
  
  /**
   * Add the required JS
   */
  protected function setUp(){
    parent::setUp();
    $this->layout = 'json';
    $this->setLayoutVar('status', 'error');  //default to an error
    $this->addScript($this->path('resource/foundation/scripts/form.js'));
    $this->addScript($this->path('resource/scripts/classes/Status.class.js'));
    $this->addScript($this->path('resource/scripts/classes/AuthenticationTimeout.class.js'));
    $this->addScript($this->path('resource/scripts/classes/Applicant.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/applicants_single.controller.js'));
    \Foundation\VC\Config::addElementViewPath(__DIR__ . '/../../views/applicants/applicants_single/elements/');
  }
  
  /**
   * Javascript does the display work
   * @param integer $id the applicants id
   */
  public function actionIndex($id){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'wide';
  }
  
  /**
   * Refresh evverything
   * @param integer $applicantId
   */
  public function actionRefresh($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $result = array(
     'bio' => $this->getBio($applicant),
     'actions' => $this->getActions($applicant),
     'decisions' => $this->getDecisions($applicant),
     'tags' => $this->getTags($applicant),
     'pages' => $this->getPages($applicant)
    );
    $this->layout = 'json'; //set the layout back since getPages changes it
    $this->setVar('result', $result);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Get bio
   * @param \Jazzee\Entity\Applicant $applicant
   * @return array
   */
  protected function getBio(\Jazzee\Entity\Applicant $applicant){
    $bio = array(
     'name' => $applicant->getFullName(),
     'email' => $applicant->getEmail(),
     'allowEdit' => $this->checkIsAllowed($this->controllerName, 'updateBio')
    );
    return $bio;
  }
  
  
  /**
   * Get an applicants action status
   * @param \Jazzee\Entity\Applicant $applicant
   */
  protected function getActions(\Jazzee\Entity\Applicant $applicant){
    $actions = array(
      'createdAt'=>$applicant->getCreatedAt(),
      'updatedAt'=>$applicant->getUpdatedAt(),
      'lastLogin'=>$applicant->getLastLogin(),
      'deadlineExtension'=>$applicant->getDeadlineExtension(),
      'allowExtendDeadline' => $this->checkIsAllowed($this->controllerName, 'extendDeadline')
    );
    return $actions;
  }
  
  /**
   * Get an applicants tags
   * @param \Jazzee\Entity\Applicant $applicant
   */
  protected function getTags(\Jazzee\Entity\Applicant $applicant){
    $tags = array(
      'tags'=>array(),
      'allowAdd' => $this->checkIsAllowed($this->controllerName, 'addTag'),
      'allowRemove' => $this->checkIsAllowed($this->controllerName, 'removeTag'),
      'allTags' => array()
    );
    if($this->checkIsAllowed($this->controllerName, 'addTag')){
      foreach($this->_em->getRepository('\Jazzee\Entity\Tag')->findAll() as $tag) $tags['allTags'][] = $tag->getTitle();
    }
    foreach($applicant->getTags() as $tag){
      $tags['tags'][] = array(
        'id'=> $tag->getId(),
        'title'=>$tag->getTitle()
      );
    }
    return $tags;
  }
  
  
  /**
   * Get application pages
   * @param \Jazzee\Entity\Applicant $applicant
   */
  protected function getPages(\Jazzee\Entity\Applicant $applicant){
    $pages = array(
      'pages'=>array(),
      'allowAddAnswer' => $this->checkIsAllowed($this->controllerName, 'addAnswer'),
      'allowEditAnswer' => $this->checkIsAllowed($this->controllerName, 'editAnswer'),
      'allowDeleteAnswer' => $this->checkIsAllowed($this->controllerName, 'deleteAnswer')
    );
    $applicationPages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$applicant->getApplication()->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc'));
    foreach($applicationPages as $applicationPage){
      if($applicationPage->getJazzeePage()->showReviewPage()){
        $params = array($applicant->getId(), $applicationPage->getPage()->getId());
        
        $content = $this->getActionOutput('refreshPage', $params);
        $content = str_replace(array("\n", "\r"), '', $content);
        $pages['pages'][] = array(
          'id' => $applicationPage->getPage()->getId(),
          'content' => utf8_encode($content)
        );
      }
    }
    
    return $pages;
  }
  
  
  /**
   * Get an applicants decisions status
   * @param \Jazzee\Entity\Applicant $applicant
   */
  protected function getDecisions(\Jazzee\Entity\Applicant $applicant){
    $status = '';
    if($applicant->getDecision()) $status = $applicant->getDecision()->status();
    switch($status){
      case '': $status = 'No Decision'; break;
      case 'nominateAdmit': $status = 'Nominated for Admission'; break; 
      case 'nominateDeny': $status = 'Nominated for Deny'; break; 
      case 'finalDeny': $status = 'Denied'; break; 
      case 'finalAdmit': $status = 'Admited'; break; 
      case 'acceptOffer': $status = 'Accepted'; break; 
      case 'declineOffer': $status = 'Declined'; break;
    }
    if($applicant->isLocked()){
      $decisions = array('status'=>$status);
      foreach(array('nominateAdmit', 'undoNominateAdmit', 'nominateDeny', 'undoNominateDeny', 'finalAdmit', 'finalDeny', 'undoFinalAdmit', 'undoFinalDeny', 'acceptOffer', 'declineOffer', 'undoAcceptOffer', 'undoDeclineOffer') as $type){
        $decisions["allow{$type}"] = ($this->checkIsAllowed($this->controllerName, $type) && $applicant->getDecision()->can($type));
      }
    }
    $decisions['allowUnlock'] = $this->checkIsAllowed($this->controllerName, 'unlock');
    $decisions['allowLock'] = $this->checkIsAllowed($this->controllerName, 'lock');
    $decisions['isLocked'] = $applicant->isLocked();
    return $decisions;
  }
  
  /**
   * Update Biography
   * @param integer $applicantId
   */
  public function actionUpdateBio($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/updateBio"));
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
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $applicant->setFirstName($input->get('firstName'));
        $applicant->setMiddleName($input->get('middleName'));
        $applicant->setLastName($input->get('lastName'));
        $applicant->setSuffix($input->get('suffix'));
        $applicant->setEmail($input->get('email'));
        
        $this->_em->persist($applicant);
        $this->setLayoutVar('status', 'success');
        $this->setVar('result', array('bio'=> $this->getBio($applicant)));
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
  public function actionRefreshPage($applicantId, $pageId){
    $this->layout = 'blank';
    $applicant = $this->getApplicantById($applicantId);
    $page = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page'=>$pageId, 'application'=>$this->_application->getId()));
    $this->setVar('variables', array('page'=>$page,'applicant'=>$applicant));
    $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-applicants-single-page');
    $this->setVar('element', $elementName);
    $this->loadView($this->controllerName . '/element');
  }
  
  /**
   * Nominate and applicant for admission
   * @param integer $applicantId
   */
  public function actionNominateAdmit($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$applicant->isLocked()) throw new \Jazzee\Exception('Tried to nominate an applicant that was not locked');
    $applicant->getDecision()->nominateAdmit();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Undo Nominate an applicant for admission
   * @param integer $applicantId
   */
  public function actionUndoNominateAdmit($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoNominateAdmit();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Final Admit applicant
   * @param integer $applicantId
   */
  public function actionFinalAdmit($applicantId){
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
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $applicant->getDecision()->finalAdmit();
        $applicant->getDecision()->setOfferResponseDeadline($input->get('offerResponseDeadline'));
        if($input->get('sendMessage')){
          $thread = new \Jazzee\Entity\Thread();
          $thread->setSubject('Admission Decision');
          $thread->setApplicant($applicant);
          
          $message = new \Jazzee\Entity\Message();
          $message->setSender(\Jazzee\Entity\Message::PROGRAM);
          $text = $this->_application->getAdmitLetter();
          $search = array(
           '%Admit_Date%',
           '%Applicant_Name%',
           '%Offer_Response_Deadline%'
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
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('result', array('decisions'=> $this->getDecisions($applicant)));
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  /**
   * Undo Final Admit
   * @param integer $applicantId
   */
  public function actionUndoFinalAdmit($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoFinalAdmit();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Tag an applicant
   * @param integer $applicantID
   */
  public function actionAddTag($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $tag = $this->_em->getRepository('\Jazzee\Entity\Tag')->findOneBy(array('title'=> $this->post['tagTitle']));
    if(!$tag){
      $tag = new \Jazzee\Entity\Tag();
      $tag->setTitle($this->post['tagTitle']);
      $this->_em->persist($tag);
    }
    $applicant->addTag($tag);
    $this->_em->persist($applicant);
    $this->_em->flush(); //flush here so the tag ID will be available
    $this->setVar('result', array('tags'=>$this->getTags($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Remove a tag from an applicant
   * @param integer $applicantID
   */
  public function actionRemoveTag($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $tag = $this->_em->getRepository('\Jazzee\Entity\Tag')->find($this->post['tagId']);
    $applicant->removeTag($tag);
    $this->_em->persist($applicant);
    $this->setVar('result', array('tags'=>$this->getTags($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Nominate and applicant for deny
   * @param integer $applicantId
   */
  public function actionNominateDeny($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$applicant->isLocked()) throw new \Jazzee\Exception('Tried to nominate an applicant that was not locked');
    $applicant->getDecision()->nominateDeny();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Undo Nominate an applicant for deny
   * @param integer $applicantId
   */
  public function actionUndoNominateDeny($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoNominateDeny();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Final Deny applicant
   * @param integer $applicantId
   */
  public function actionFinalDeny($applicantId){
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
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $applicant->getDecision()->finalDeny();
        if($input->get('sendMessage')){
          $thread = new \Jazzee\Entity\Thread();
          $thread->setSubject('Admission Decision');
          $thread->setApplicant($applicant);
          
          $message = new \Jazzee\Entity\Message();
          $message->setSender(\Jazzee\Entity\Message::PROGRAM);
          $text = $this->_application->getDenyLetter();
          $search = array(
           '%Deny_Date%',
           '%Applicant_Name%'
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
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('result', array('decisions'=> $this->getDecisions($applicant)));
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  /**
   * Undo Final Deny
   * @param integer $applicantId
   */
  public function actionUndoFinalDeny($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoFinalDeny();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Undo Accept Offer
   * @param integer $applicantId
   */
  public function actionUndoAcceptOffer($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoAcceptOffer();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Undo Decline Offer
   * @param integer $applicantId
   */
  public function actionUndoDeclineOffer($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $applicant->getDecision()->undoDeclineOffer();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Unlock an application
   * @param integer $applicantId
   */
  public function actionUnlock($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $applicant->unlock();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Lock an application
   * @param integer $applicantId
   */
  public function actionLock($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $applicant->lock();
    $this->_em->persist($applicant);
    $this->setVar('result', array('decisions'=>$this->getDecisions($applicant)));
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Extend Applicant Deadline
   * @param integer $applicantId
   */
  public function actionExtendDeadline($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/extendDeadline"));
    $field = $form->newField();
    $field->setLegend('Extend deadline for ' . $applicant->getFirstName() . ' ' . $applicant->getLastName());

    $element = $field->newElement('DateInput', 'deadline');
    $element->setLabel('New Deadline');
    $element->setFormat('Leave blank to remove the extension');
    if($applicant->getDeadlineExtension()) $element->setValue($applicant->getDeadlineExtension()->format('c'));
    $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, $applicant->getApplication()->getClose()->format('c')));
    $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, 'now'));
    
    $form->newButton('submit', 'Extend Deadline');
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        if($input->get('deadline')){
          $applicant->setDeadlineExtension($input->get('deadline'));
        } else {
          $applicant->removeDeadlineExtension();
        }
        $this->_em->persist($applicant);
        $this->setLayoutVar('status', 'success');
      }
    }
    $this->setVar('result', array('actions'=> $this->getActions($applicant)));
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  /**
   * Add an answer
   * @param integer $applicantId
   * @param integer $pageId
   */
  public function actionAddAnswer($applicantId, $pageId){
    $applicant = $this->getApplicantById($applicantId);
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page'=>$pageId, 'application'=>$this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $pageEntity->getJazzeePage()->validateInput($this->post)){
        $pageEntity->getJazzeePage()->newAnswer($input);
        $this->setLayoutVar('status', 'success');
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
  public function actionEditAnswer($applicantId, $answerId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$answer = $applicant->findAnswerById($answerId))  throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page'=>$answer->getPage()->getId(), 'application'=>$this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    $pageEntity->getJazzeePage()->fill($answerId);
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $pageEntity->getJazzeePage()->validateInput($this->post)){
        $pageEntity->getJazzeePage()->updateAnswer($input, $answerId);
        $this->setLayoutVar('status', 'success');
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
  public function actionDeleteAnswer($applicantId, $answerId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$answer = $applicant->findAnswerById($answerId))  throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page'=>$answer->getPage()->getId(), 'application'=>$this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    $pageEntity->getJazzeePage()->fill($answerId);
    $pageEntity->getJazzeePage()->deleteAnswer($answerId);
    $this->setVar('result', true);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Attach PDF to answer
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionAttachAnswerPdf($applicantId, $answerId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$answer = $applicant->findAnswerById($answerId))  throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/single/{$applicantId}/attachAnswerPdf/{$answerId}"));
    $field = $form->newField();
    $field->setLegend('Attach PDF');
    
    $element = $field->newElement('FileInput', 'pdf');
    $element->setLabel('File');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\PDF($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));
        
    $form->newButton('submit', 'Attach PDF to Answer');
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $attachment = new \Jazzee\Entity\Attachment();
        $attachment->setApplicant($applicant);
        $attachment->setAnswer($answer);
        $attachment->setAttachment($input->get('pdf'));
        $this->_em->persist($attachment);
        //persist the applicant and answer to catch the last update  
        $this->_em->persist($applicant); 
        $this->_em->persist($answer); 
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
  public function actionDeleteAnswerPdf($applicantId, $answerId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$answer = $applicant->findAnswerById($answerId))  throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    
    if($attachment = $answer->getAttachment()){
      $this->_em->remove($attachment);
      $answer->markLastUpdate();
      $this->_em->persist($answer);
      $this->setLayoutVar('status', 'success');
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }
  
  /**
   * Settle a Payment
   * @param integer $applicantId
   * @param integer $answerId
   */
  public function actionSettlePayment($applicantId, $answerId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$answer = $applicant->findAnswerById($answerId))  throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    if(!$payment = $answer->getPayment())  throw new \Jazzee\Exception("Answer {$answerId} does not have a payment.");
    if($payment->getStatus() != \Jazzee\Entity\Payment::PENDING) throw new \Jazzee\Exception('Payment ' . $payment->getId() . ' is not pending so cannot be settled.');
    $form = $payment->getType()->getJazzeePaymentType()->getSettlePaymentForm($payment);
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $payment->getType()->getJazzeePaymentType()->settlePayment($payment, $input);
        $this->_em->persist($payment);
        foreach($payment->getVariables() as $var) $this->_em->persist($var);
        $this->setLayoutVar('status', 'success');
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
  public function actionRefundPayment($applicantId, $answerId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$answer = $applicant->findAnswerById($answerId))  throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    if(!$payment = $answer->getPayment())  throw new \Jazzee\Exception("Answer {$answerId} does not have a payment.");
    if($payment->getStatus() != \Jazzee\Entity\Payment::PENDING and $payment->getStatus() != \Jazzee\Entity\Payment::SETTLED) throw new \Jazzee\Exception('Payment ' . $payment->getId() . ' is not settled or pending so cannot be refunded.');
    $form = $payment->getType()->getJazzeePaymentType()->getRefundPaymentForm($payment);
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $payment->getType()->getJazzeePaymentType()->refundPayment($payment, $input);
        $this->_em->persist($payment);
        foreach($payment->getVariables() as $var) $this->_em->persist($var);
        $this->setLayoutVar('status', 'success');
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
  public function actionRejectPayment($applicantId, $answerId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$answer = $applicant->findAnswerById($answerId))  throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    if(!$payment = $answer->getPayment())  throw new \Jazzee\Exception("Answer {$answerId} does not have a payment.");
    if($payment->getStatus() != \Jazzee\Entity\Payment::PENDING and $payment->getStatus() != \Jazzee\Entity\Payment::SETTLED) throw new \Jazzee\Exception('Payment ' . $payment->getId() . ' is not settled or pending so cannot be rejected.');
    $form = $payment->getType()->getJazzeePaymentType()->getRejectPaymentForm($payment);
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $payment->getType()->getJazzeePaymentType()->rejectPayment($payment, $input);
        $this->_em->persist($payment);
        foreach($payment->getVariables() as $var) $this->_em->persist($var);
        $this->setLayoutVar('status', 'success');
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
  public function actionDo($applicantId, $what, $answerId){
    $applicant = $this->getApplicantById($applicantId);
    if(!$answer = $applicant->findAnswerById($answerId))  throw new \Jazzee\Exception("Answer {$answerId} does not belong to applicant {$applicantId}");
    $pageEntity = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page'=>$answer->getPage()->getId(), 'application'=>$this->_application->getId()));
    $pageEntity->getJazzeePage()->setApplicant($applicant);
    $pageEntity->getJazzeePage()->setController($this);
    if(method_exists($pageEntity->getJazzeePage(), $what)){
      $form = $pageEntity->getJazzeePage()->$what($answerId, $this->post, true);
      $form->setAction($this->path("applicants/single/{$applicantId}/do/{$what}/{$answerId}"));
      $this->setVar('form', $form);
      if(!empty($this->post)) $this->setLayoutVar('textarea', true);
    }
    $this->loadView($this->controllerName . '/form');
  }
  
  public function getActionPath(){
    return null;
  }
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null){
    //several views are controller by the complete action
    if(in_array($action, array('refresh', 'refreshPage'))) $action = 'index';
    if(in_array($action, array('do'))) $action = 'editAnswer';
    return parent::isAllowed($controller, $action, $user, $program, $application);
  }
}