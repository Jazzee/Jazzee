<?php
/**
 * View the applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage applicants
 */
class ApplicantsViewController extends ApplicantsController {
  
  /**
   * Add the required JS
   */
  public function setUp(){
    parent::setUp();
    $this->addScript('foundation/scripts/form.js');
    $this->addScript('common/scripts/status.js');
    $this->addScript('common/scripts/applicants_view.js');
  }
  /**
   * Display a search form and links to views
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("applicants/view");
    $field = $form->newField(array('legend'=>'Search Applicants'));
    $element = $field->newElement('TextInput','firstName');
    $element->label = 'First Name';
    $element = $field->newElement('TextInput','lastName');
    $element->label = 'Last Name';
    $element = $field->newElement('TextInput','applicantID');
    $element->label = 'Applicant ID';
    $form->newButton('submit', 'Search');
    if($input = $form->processInput($this->post)){
      $q = Doctrine_Query::create()
          ->select('*')
          ->from('Applicant');
      if($input->firstName)
          $q->andWhere('firstName like ?', $input->firstName . '%');
      if($input->lastName)
          $q->andWhere('lastName like ?', $input->lastName . '%');   
      if($input->applicantID)
          $q->andWhere('id = ?', $input->applicantID);
      $this->setVar('applicants', $q->execute());
      
    }
    
    $this->setVar('form', $form);
  }
 
  /**
   * List the applicants in the current program
   */
  public function actionList(){
    if($this->application)
      $this->setVar('applicants', $this->application->Applicants);
  }
  
  /**
   * View a single applicants
   * @param integer $id the applicant id
   */
  public function actionSingle($id){
    if(!$applicant = $this->application->getApplicantByID($id)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $pages = array();
    foreach($this->application->Pages as $page){
      if(class_exists($page->Page->PageType->class) AND is_subclass_of($page->Page->PageType->class, 'ApplyPage')){
        $pages[$page->id] = new $page->Page->PageType->class($page, $applicant);
      } else {
        throw new Jazzee_Exception("There is no {$page->Page->PageType->class} class available.  This page will not be displayed");
      }
    }
    $this->setVar('pages', $pages);
    $this->setVar('applicant', $applicant);
    $this->setVAR('fileStore', Session::getInstance()->getStore('files'));
  }
  
  /**
   * PDF a single applicant
   * @param integer $id the applicant id
   * @param string $type the page orientation
   */
  public function actionPdf($id, $type = 'portrait'){
    if(!$applicant = $this->application->getApplicantByID($id)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    switch($type){
      case 'landscape':
        $orientation = ApplicantPDF::USLETTER_LANDSCAPE;
        break;
      default:
        $orientation = ApplicantPDF::USLETTER_PORTRAIT;
    }
    $pdf = new ApplicantPDF($orientation, $this->config->pdflib_key);
    $blob = $pdf->pdf($applicant);
    header("Content-type: application/pdf");
    header("Content-Length: " . strlen($blob));
    header('Content-Disposition: inline; filename=' . "{$applicant->lastName}-{$applicant->firstName}-" . date('m-d-y') . '.pdf');
    print $blob; 
    exit();
  }
  
  /**
   * Edit the intial data entered by the applicant when creating an account
   * @param integer $id
   */
  public function actionEditApplicant($id){
    if(!$applicant = $this->application->getApplicantByID($id)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $this->layout = 'json';
    $form = new Form;
    $form->action = $this->path("applicants/view/editApplicant/{$id}");
    $field = $form->newField(array('legend'=>"Edit Applicant {$applicant->firstName} {$applicant->lastName}"));
    $element = $field->newElement('TextInput', 'firstName');
    $element->label = 'First Name';
    $element->addValidator('NotEmpty');
    $element->value = $applicant->firstName;
    
    $element = $field->newElement('TextInput', 'middleName');
    $element->label = 'Middle Name';
    $element->value = $applicant->middleName;
    
    $element = $field->newElement('TextInput','lastName');
    $element->label = 'Last Name';
    $element->addValidator('NotEmpty');
    $element->value = $applicant->lastName;
        
    $element = $field->newElement('TextInput', 'suffix');
    $element->label = 'Suffix';
    $element->format = 'Example: Jr., III';
    $element->value = $applicant->suffix;

    $element = $field->newElement('TextInput', 'email');
    $element->label = 'Email Address';
    $element->addValidator('NotEmpty');
    $element->addValidator('EmailAddress');
    $element->addFilter('Lowercase');
    $element->value = $applicant->email;
    
    $form->newButton('submit', 'Save Changes');
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        $applicant->firstName = $input->firstName;
        $applicant->middleName = $input->middleName;
        $applicant->lastName = $input->lastName;
        $applicant->suffix = $input->suffix;
        $applicant->email = $input->email;
        $applicant->save();
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
  }
  
  
  /**
   * Nominate and applicant for admission
   * @param integer $id applicantID
   */
  public function actionNominateAdmit($id){
    if(!$applicant = $this->application->getApplicantByID($id)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    if($applicant->Decision->nominateDeny OR $applicant->Decision->finalAdmit OR $applicant->Decision->finalDeny)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to admit applicant {$id} who already has a deny or final status", E_USER_ERROR, 'A decision has already been recorded for that applicant');
    $this->layout = 'json';
    $applicant->Decision->nominateAdmit = date('Y-m-d H:i:s');
    $applicant->save();
  }
  
  /**
   * Nominate and applicant for deny
   * @param integer $id applicantID
   */
  public function actionNominateDeny($id){
    if(!$applicant = $this->application->getApplicantByID($id)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    if($applicant->Decision->nominateAdmit OR $applicant->Decision->finalAdmit OR $applicant->Decision->finalDeny)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to deny applicant {$id} who already has a admit or final status", E_USER_ERROR, 'A decision has already been recorded for that applicant');
    $this->layout = 'json';
    $applicant->Decision->nominateDeny = date('Y-m-d H:i:s');
    $applicant->save();
  }
  
  /**
   * Undo a nomination decision
   * Can't be done for a final decision
   * @param integer $id applicantID
   */
  public function actionUndoNomination($id){
    if(!$applicant = $this->application->getApplicantByID($id)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    if($applicant->Decision->finalAdmit OR $applicant->Decision->finalDeny)
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to undo nomination for applicant {$id} who already has a final status", E_USER_ERROR, 'A final decision has already been recorded for that applicant');
    $this->layout = 'json';
    $applicant->Decision->nominateAdmit = null;
    $applicant->Decision->nominateDeny = null;
    $applicant->save();
  }
  
  /**
   * Unlock an application
   * @param integer $id
   */
  public function actionUnlock($id){
    if(!$applicant = $this->application->getApplicantByID($id)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $this->layout = 'json';
    $applicant->locked = null;
    $applicant->save();
  }
  
  /**
   * Extend the deadline for a single applicant
   * @param integer $id
   */
  public function actionExtendDeadline($id){
    if(!$applicant = $this->application->getApplicantByID($id)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $this->layout = 'json';
    $form = new Form;
    $form->action = $this->path("applicants/view/extendDeadline/{$id}");
    $field = $form->newField(array('legend'=>"Extend Deadline for {$applicant->firstName} {$applicant->lastName}"));
    $element = $field->newElement('DateInput', 'deadline');
    $element->label = 'New Deadline';
    $element->addValidator('NotEmpty');
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
  }
  
  
  /**
   * Edit an answer
   * @param integer $id answerID
   */
  public function actionEditAnswer($id){
    if(!$answer = Doctrine::getTable('Answer')->find($id) OR !$applicant = $this->application->getApplicantByID($answer->applicantID)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $this->layout = 'json';
    $page = new $answer->Page->PageType->class($this->application->getApplicationPageByGlobalID($answer->Page->id), $applicant);
    $form = $page->getForm();
    $form->action = $this->path("applicants/view/editAnswer/{$id}");
    $page->fill($id);
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        if($page->updateAnswer($input,$id)){
          $this->messages->write('success', 'Answer Updated Successfully');
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
  }
  
  /**
   * Add an answer
   * @param integer $applicantID
   * @param integer $pageID
   */
  public function actionAddAnswer($applicantID, $pageID){
    if(!$page = $this->application->getPageByID($pageID) OR !$applicant = $this->application->getApplicantByID($applicantID)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$applicantID} or page {$pageID} which is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $this->layout = 'json';
    $page = new $page->Page->PageType->class($page, $applicant);
    $form = $page->getForm();
    $form->action = $this->path("applicants/view/addAnswer/{$applicantID}/{$pageID}");
    if(!empty($this->post)){
      $this->setLayoutVar('textarea', true);
      if($input = $form->processInput($this->post)){
        if($page->newAnswer($input)){
          $this->messages->write('success', 'Answer Saved Successfully');
        }
      } else {
        $this->setLayoutVar('status', 'error');
      }
    }
    $this->setVar('form', $form);
  }
  
  /**
   * Delete an answer
   * @param integer $id answerID
   */
  public function actionDeleteAnswer($id){
    if(!$answer = Doctrine::getTable('Answer')->find($id) OR !$applicant = $this->application->getApplicantByID($answer->applicantID)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access applicant {$id} who is not in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $this->layout = 'json';
    $page = new $answer->ApplicationPage->Page->PageType->class($answer->ApplicationPage, $applicant);
    if($page->deleteAnswer($id)){
      $this->messages->write('success', 'Answer Deleted Successfully');
    }
  }
  
  /**
   * Verify an answer
   * @param integer $id answerID
   */
  public function actionVerifyAnswer($id){
    if(!$answer = Doctrine::getTable('Answer')->find($id) OR !$applicant = $this->application->getApplicantByID($answer->applicantID)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access answer {$id} which does not belong to an applicantt in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $this->layout = 'json';
    $statusTypes = Doctrine::getTable('StatusType')->findAll();
    $form = new Form;
    $form->action = $this->path("applicants/view/verifyAnswer/{$id}");
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
  }
  
  
  /**
   * Attach a PDF to an Answer
   * @param integer $id answerID
   */
  public function actionAttachAnswerPDF($id){
    if(!$answer = Doctrine::getTable('Answer')->find($id) OR !$applicant = $this->application->getApplicantByID($answer->applicantID)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to access answer {$id} which does not belong to an applicantt in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $this->layout = 'json';
    $form = new Form;
    $form->action = $this->path("applicants/view/attachAnswerPDF/{$id}");
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
  }
  
  /**
   * Attach a PDF to an Applicant
   * @param integer $id applicantID
   */
  public function actionAttachApplicantPDF($id){
    if(!$applicant = $this->application->getApplicantByID($id)){
      throw new Jazzee_Exception("{$this->user->firstName} {$this->user->lastName} (#{$this->user->id}) attempted to add a pdf to applicant {$id} which is not an applicant in their current program", E_USER_ERROR, 'That applicant does not exist or is not in your current program');
    }
    $this->layout = 'json';
    $form = new Form;
    $form->action = $this->path("applicants/view/attachApplicantPDF/{$id}");
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
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'View Applicants';
    $auth->addAction('index', new ActionAuth('Search Applicants'));
    $auth->addAction('list', new ActionAuth('List All Applicants'));
    $auth->addAction('single', new ActionAuth('View a Single Applicant'));
    $auth->addAction('edit', new ActionAuth('Edit Applicant Data'));
    $auth->addAction('attachAnswerPDF', new ActionAuth('Attach PDF to answers'));
    $auth->addAction('attachApplicantPDF', new ActionAuth('Attach PDF to an applicant'));
    $auth->addAction('verifyAnswer', new ActionAuth('Verify Applicant Answer'));
    $auth->addAction('unlock', new ActionAuth('Unlock an application'));
    $auth->addAction('extendDeadline', new ActionAuth('Extend the deadline for an applicant'));
    $auth->addAction('pdf', new ActionAuth('Generate PDF from applicant data'));
    $auth->addAction('nominateDecision', new ActionAuth('Nominate Applicant for Admission Decision'));
    return $auth;
  }
  
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    //All of these actions are controlled by the 'edit' role
    $editActions = array('editApplicant', 'editAnswer', 'deleteAnswer', 'addAnswer');
    if(in_array($action, $editActions))
      $action = 'edit';
    $nominateDecisionActions = array('nominateAdmit', 'nominateDeny', 'undoNomination');
    if(in_array($action, $nominateDecisionActions))
      $action = 'nominateDecision';
    $finalDecisionActions = array('finalAdmit', 'finalDeny');
    if(in_array($action, $finalDecisionActions))
      $action = 'finalDecision';
    if($programID AND $cycleID AND $user)  return $user->isAllowed($controller, $action, $programID);
    return false;
  }
}