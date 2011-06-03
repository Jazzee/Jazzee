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
  const ACTION_EDITACCOUNT = 'Edit Acount';
  const ACTION_EXTENDDEADLINE = 'Extend Deadline';
  const ACTION_LOCK = 'Lock Application';
  const ACTION_UNLOCK = 'UnLock Application';
  const ACTION_TAG = 'Tag Applicant';
  const ACTION_ATTACHAPPLICANTPDF = 'Attach PDF to Applicant';
  const ACTION_EDITANSWER = 'Edit Answer';
  const ACTION_DELETEANSWER = 'Delete Answer';
  const ACTION_ADDANSWER = 'Add Answer';
  const ACTION_ATTACHANSWERPDF = 'Attach PDF to Answer';
  const ACTION_VERIFYANSWER = 'Verify Answer';
  const ACTION_NOMINATEADMIT = 'Nominate for Admission';
  const ACTION_NOMINATEDENY = 'Nominate for Deny';
  const ACTION_UNDONOMINATEADMIT = 'Undo Admit Nomination';
  const ACTION_UNDONOMINATEDENY = 'Undo Deny Nomination';
  const ACTION_FINALADMIT = 'Final Admit';
  const ACTION_FINALDENY = 'Final Deny';
  const ACTION_NEWPAYMENT = 'Record Payment';
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
    $this->setVar('applicant', $applicant);
  }
  
  /**
   * Get an applicants action status
   */
  public function actionUpdateActions($id){
    $applicant = $this->getApplicantById($id);
    $actions = array(
      'createdAt'=>$applicant->getCreatedAt(),
      'updatedAt'=>$applicant->getUpdatedAt(),
      'lastLogin'=>$applicant->getLastLogin()
    );
    $actions['allowUnlock'] = $this->checkIsAllowed($this->controllerName, 'unlock');
    $actions['allowLock'] = $this->checkIsAllowed($this->controllerName, 'lock');
    $actions['isLocked'] = $applicant->isLocked();
    $this->setVar('result', $actions);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Get an applicants status
   */
  public function actionUpdateStatus($id){
    $applicant = $this->getApplicantById($id);
    $status = array(
      'status'=> 'Not Complete'
    );
    $this->setVar('result', $status);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Get an applicants tags
   */
  public function actionUpdateTags($id){
    $applicant = $this->getApplicantById($id);
    $tags = array();
    foreach($applicant->getTags() as $tag){
      $tags[] = array(
        'id' => $tag->getId(),
        'title' => $tag->getTitle()
      );
    }
    $this->setVar('result', $tags);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Update a page
   */
  public function actionUpdatePage($applicantId, $pageId){
    $this->layout = 'blank';
    $applicant = $this->getApplicantById($applicantId);
    $page = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page'=>$pageId, 'application'=>$this->_application->getId()));
    $this->setVar('variables', array('page'=>$page,'applicant'=>$applicant));
    $this->setVar('element', 'applicants-single-page');
    $this->loadView($this->controllerName . '/element');
  }
  
  /**
   * Update an answer
   */
  public function actionUpdateAnswer($applicantId, $answerId){
    $this->layout = 'blank';
    $applicant = $this->getApplicantById($applicantId);
    $answer = $this->_em->getRepository('\Jazzee\Entity\Answer')->findOneBy(array('id'=>$answerId, 'applicant'=>$applicant->getId()));
    $page = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page'=>$answer->getPage()->getId(), 'application'=>$this->_application->getId()));
    
    $this->setVar('variables', array('page'=>$page,'answer'=>$answer));
    $this->setVar('element', 'applicants-single-answer');
    $this->loadView($this->controllerName . '/element');
  }
  
  /**
   * PDF a single applicant
   * @param integer $id the applicant id
   * @param string $type the page orientation
   */
  public function actionPdf($applicantId, $type = 'portrait'){
    $applicant = $this->getApplicantById($applicantId);
    switch($type){
      case 'landscape':
        $orientation = \Jazzee\ApplicantPDF::USLETTER_LANDSCAPE;
        break;
      default:
        $orientation = \Jazzee\ApplicantPDF::USLETTER_PORTRAIT;
    }
    $key = '';
    $pdf = new \Jazzee\ApplicantPDF($orientation, $key);
    $blob = $pdf->pdf($applicant);
    header("Content-type: application/pdf");
    header("Content-Length: " . strlen($blob));
    header('Content-Disposition: inline; filename=' . $applicant->getLastName() .'-' . $applicant->getFirstName() . '-' . date('m-d-y') . '.pdf');
    print $blob; 
    exit();
  }
  
  /**
   * Edit the intial data entered by the applicant when creating an account
   * @param integer $applicantId
   */
  public function actionEditAccount($applicantId){
    $applicant = $this->getApplicantById($applicantId);
    $form = new \Foundation\Form();
    $form->setAction($this->path("admin/applicants/single/{$applicantId}/editAccount"));
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
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  
  /**
   * Nominate and applicant for admission
   * @param integer $id applicantID
   */
  public function actionNominateAdmit($id){
    $applicant = $this->getApplicantById($id);
    if($applicant->Decision->nominateDeny OR $applicant->Decision->finalAdmit OR $applicant->Decision->finalDeny)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to admit applicant {$id} who already has a deny or final status", E_USER_ERROR, 'A decision has already been recorded for that applicant');
    $applicant->Decision->nominateAdmit = date('Y-m-d H:i:s');
    $applicant->save();
    $this->redirectPath('applicants/single/byId/'.$applicant->id);
  }
  
  /**
   * Final Deny an applicant
   * @param integer $id applicantID
   */
  public function actionFinalAdmit($id){
    $applicant = $this->getApplicantById($id);
    if(!$applicant->Decision->nominateAdmit OR $applicant->Decision->finalAdmit OR $applicant->Decision->finalDeny)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to admit applicant {$id} who doesnt has a preliminary status or already final status", E_USER_ERROR, 'A decision has already been recorded for that applicant');
    $applicant->Decision->finalAdmit = date('Y-m-d H:i:s');
    $applicant->save();
    $this->redirectPath('applicants/single/byId/'.$applicant->id);
  }
  
  /**
   * Tag an applicant
   * @param integer $id applicantID
   */
  public function actionAddTag($id){
    $applicant = $this->getApplicantById($id);
    $applicant->addTag($this->post['tag']);
    $applicant->save();
    $this->redirectPath('applicants/single/byId/'.$applicant->id);
  }
  
  /**
   * Nominate and applicant for deny
   * @param integer $id applicantID
   */
  public function actionNominateDeny($id){
    $applicant = $this->getApplicantById($id);
    if($applicant->Decision->nominateAdmit OR $applicant->Decision->finalAdmit OR $applicant->Decision->finalDeny)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to deny applicant {$id} who already has a admit or final status", E_USER_ERROR, 'A decision has already been recorded for that applicant');
    $applicant->Decision->nominateDeny = date('Y-m-d H:i:s');
    $applicant->save();
    $this->redirectPath('applicants/single/byId/'.$applicant->id);
  }
  
  /**
   * Final Deny an applicant
   * @param integer $id applicantID
   */
  public function actionFinalDeny($id){
    $applicant = $this->getApplicantById($id);
    if(!$applicant->Decision->nominateDeny OR $applicant->Decision->finalAdmit OR $applicant->Decision->finalDeny)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to deny applicant {$id} who already has a final status", E_USER_ERROR, 'A decision has already been recorded for that applicant');
    $applicant->Decision->finalDeny = date('Y-m-d H:i:s');
    $applicant->save();
    $this->redirectPath('applicants/single/byId/'.$applicant->id);
  }
  
  /**
   * Settle a payment
   * @param integer $paymentId 
   */
  public function actionSettlePayment($paymentId){
    $payment = Doctrine::getTable('Payment')->find($paymentId);
    if(!$payment)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to settle payment {$paymentID} that doesn't exist", E_USER_ERROR, 'That payment is not valid');
    $applicant = $this->getApplicantById($payment->Applicant->id);
    $this->layout = 'json';
    $paymentType = new $payment->PaymentType->class($payment->PaymentType);
    
    $form = $paymentType->getSettlePaymentForm($payment);
    $form->action = $this->path("admin/applicants/single/settlePayment/{$payment->id}");
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        if($paymentType->settlePayment($payment, $input)){
          $this->addMessage('success', 'Payment Settled Successfully');
          $this->redirectPath('applicants/single/byId/'.$applicant->id);
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  /**
   * Refund a payment
   * @param integer $paymentId 
   */
  public function actionRefundPayment($paymentId){
    $payment = Doctrine::getTable('Payment')->find($paymentId);
    if(!$payment)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to settle payment {$paymentID} that doesn't exist", E_USER_ERROR, 'That payment is not valid');
    $applicant = $this->getApplicantById($payment->Applicant->id);
    $this->layout = 'json';
    $paymentType = new $payment->PaymentType->class($payment->PaymentType);
    
    $form = $paymentType->getRefundPaymentForm($payment);
    $form->action = $this->path("admin/applicants/single/refundPayment/{$payment->id}");
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        if($paymentType->refundPayment($payment, $input)){
          $this->addMessage('success', 'Payment Refunded Successfully');
          $this->redirectPath('applicants/single/byId/'.$applicant->id);
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  /**
   * Reject a payment
   * @param integer $paymentId 
   */
  public function actionRejectPayment($paymentId){
    $payment = Doctrine::getTable('Payment')->find($paymentId);
    if(!$payment)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to reject payment {$paymentID} that doesn't exist", E_USER_ERROR, 'That payment is not valid');
    $applicant = $this->getApplicantById($payment->Applicant->id);
    $this->layout = 'json';
    $paymentType = new $payment->PaymentType->class($payment->PaymentType);
    
    $form = $paymentType->getRejectPaymentForm($payment);
    $form->action = $this->path("admin/applicants/single/rejectPayment/{$payment->id}");
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        if($paymentType->rejectPayment($payment, $input)){
          $this->addMessage('success', 'Payment Rejected Successfully');
          $this->redirectPath('applicants/single/byId/'.$applicant->id);
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  /**
   * Undo a nomination decision
   * Can't be done for a final decision
   * @param integer $id applicantID
   */
  public function actionUndoNomination($id){
    $applicant = $this->getApplicantById($id);
    if($applicant->Decision->finalAdmit OR $applicant->Decision->finalDeny)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to undo nomination for applicant {$id} who already has a final status", E_USER_ERROR, 'A final decision has already been recorded for that applicant');
    $this->layout = 'json';
    $applicant->Decision->nominateAdmit = null;
    $applicant->Decision->nominateDeny = null;
    $applicant->save();
    $this->redirectPath('applicants/single/byId/'.$applicant->id);
  }
  
  /**
   * Unlock an application
   * @param integer $id
   */
  public function actionUnlock($id){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'json';
    $applicant->unlock();
    $applicant->save();
    $this->redirectPath('applicants/single/byId/'.$applicant->id);
  }
  
  /**
   * Lock an application
   * @param integer $id
   */
  public function actionLock($id){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'json';
    $applicant->lock();
    $applicant->save();
    $this->redirectPath('applicants/single/byId/'.$applicant->id);
  }
  
  /**
   * Extend the deadline for a single applicant
   * @param integer $id
   */
  public function actionExtendDeadline($id){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'json';
    $form = new \Foundation\Form();
    $form->action = $this->path("admin/applicants/view/extendDeadline/{$id}");
    $field = $form->newField(array('legend'=>"Extend Deadline for {$applicant->firstName} {$applicant->lastName}"));
    $element = $field->newElement('DateInput', 'deadline');
    $element->label = 'New Deadline';
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator('DateAfter', 'today');
    $element->addFilter('DateFormat', 'Y-m-d H:i:s');
    if($applicant->deadlineExtension){
      $element->value = $applicant->deadlineExtension;
    } else {
      $element->value = 'today';
    }
    $form->newButton('submit', 'Save');
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $applicant->deadlineExtension = $input->deadline;
        $applicant->save();
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  
  /**
   * Edit an answer
   * @param integer $id answerID
   */
  public function actionEditAnswer($id){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'json';
    $page = new $answer->Page->PageType->class($this->application->getApplicationPageByGlobalID($answer->Page->id), $applicant);
    $form = $page->getForm();
    $form->action = $this->path("admin/applicants/view/editAnswer/{$id}");
    $page->fill($id);
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        if($page->updateAnswer($input,$id)){
          $this->addMessage('success', 'Answer Updated Successfully');
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  /**
   * Add an answer
   * @param integer $applicantID
   * @param integer $pageID
   */
  public function actionAddAnswer($applicantID, $pageID){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'json';
    $page = new $page->Page->PageType->class($page, $applicant);
    $form = $page->getForm();
    $form->action = $this->path("admin/applicants/view/addAnswer/{$applicantID}/{$pageID}");
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        if($page->newAnswer($input)){
          $this->addMessage('success', 'Answer Saved Successfully');
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  /**
   * Delete an answer
   * @param integer $id answerID
   */
  public function actionDeleteAnswer($id){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'json';
    $page = new $answer->Page->PageType->class($this->application->getApplicationPageByGlobalID($answer->Page->id), $applicant);
    if($page->deleteAnswer($id)){
      $this->addMessage('success', 'Answer Deleted Successfully');
    }
    $this->redirectPath('applicants/single/byId/'.$applicant->id);
  }
  
  /**
   * Verify an answer
   * @param integer $id answerID
   */
  public function actionVerifyAnswer($id){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'json';
    $statusTypes = Doctrine::getTable('StatusType')->findAll();
    $form = new \Foundation\Form();
    $form->action = $this->path("admin/applicants/view/verifyAnswer/{$id}");
    $field = $form->newField(array('legend'=>"Verify Answer"));
    $element = $field->newElement('SelectList', 'publicStatus');
    $element->label = 'Public Status';
    $element->addItem(null, '');
    foreach($statusTypes as $type){
      $element->addItem($type->id, $type->name);
    }
    $element->value = $answer->publicStatus;
    
    $element = $field->newElement('SelectList', 'privateStatus');
    $element->label = 'Private Status';
    $element->addItem(null, '');
    foreach($statusTypes as $type){
      $element->addItem($type->id, $type->name);
    }
    $element->value = $answer->privateStatus;
    $form->newButton('submit', 'Save');
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $answer->publicStatus = $input->publicStatus;
        $answer->privateStatus = $input->privateStatus;
        $answer->save();
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  
  /**
   * Attach a PDF to an Answer
   * @param integer $id answerID
   */
  public function actionAttachAnswerPDF($id){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'json';
    $form = new \Foundation\Form();
    $form->action = $this->path("admin/applicants/view/attachAnswerPDF/{$id}");
    $field = $form->newField(array('legend'=>"Attach PDF"));
    $element = $field->newElement('FileInput', 'pdf');
    $element->label = 'PDF';

    $form->newButton('submit', 'Save');
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $answer->attachment = file_get_contents($input->pdf['tmp_name']);
        $answer->save();
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  
  /**
   * Attach a PDF to an Applicant
   * @param integer $id applicantID
   */
  public function actionAttachApplicantPDF($id){
    $applicant = $this->getApplicantById($id);
    $this->layout = 'json';
    $form = new \Foundation\Form();
    $form->action = $this->path("admin/applicants/view/attachApplicantPDF/{$id}");
    $field = $form->newField(array('legend'=>"Attach PDF to Applicant"));
    $element = $field->newElement('FileInput', 'pdf');
    $element->label = 'PDF';

    $form->newButton('submit', 'Attach');
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $attachment = $applicant->Attachments->get(null);
        $attachment->attachment = file_get_contents($input->pdf['tmp_name']);
        $applicant->save();
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
    $this->loadView('applicants_single/form');
  }
  

  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null){
    //several views are controller by the complete action
    if(in_array($action, array('updateBio', 'updateActions', 'updateStatus', 'updateTags', 'updatePage', 'updateAnswer'))) $action = 'index';
    return parent::isAllowed($controller, $action, $user, $program);
  }
}