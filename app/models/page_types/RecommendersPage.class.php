<?php
/**
 * Get recommendation information from applicants and send out invitations
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class RecommendersPage extends StandardPage {
  /**
   * The time to wait between sending emails to recommenders in seconds
   * @cons integer 2 weeks (86400 * 14)
   */
  const RECOMMENDATION_EMAIL_WAIT_TIME = 1209600;
  /**
   * Make the form for the page
   * Add the required email and waive rights element and then generate the rest dynamically
   * @param ApplicationPage $page
   * @return Form
   */
  protected function makeForm(){
    $form = new Form;
    $field = $form->newField();
    $field->legend = $this->applicationPage->title;
    $field->instructions = $this->applicationPage->instructions;
    
    $e = $field->newElement('TextInput', 'email');
    $e->label = 'Email Address';
    $e->addValidator('EmailAddress');
    $e->addValidator('NotEmpty');
    
    $e = $field->newElement('RadioList', 'waiveViewRight');
    $e->label = 'Do you waive your right to view this letter at a later time?';
    $e->addValidator('NotEmpty');
    $e->addItem(1, 'Yes');
    $e->addItem(0, 'No');
    foreach($this->applicationPage->Page->Elements as $e){
      $element = new $e->ElementType->class($e);
      $element->addToField($field);
    }
    $form->newButton('submit', 'Save');
    $form->newButton('reset', 'Clear Form');
    return $form;
  }
  
  public function newAnswer($input){
    $a = new Answer;
    $a->pageID = $this->applicationPage->Page->id;
    $a->Recommendation->recommendationPageID = $this->applicationPage->RecommendationPage->id;
    $this->applicant['Answers'][] = $a;
    $answer = new RecommendationAnswer($a);
    $answer->update($input);
    $this->applicant->save();
    $this->form->applyDefaultValues();
    return true;
  }
  public function updateAnswer($input, $answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new RecommendationAnswer($a);
      $answer->update($input);
      $a->save();
      $this->form->applyDefaultValues();
    }
  }
  
  /**
   * Send the invitaiton email
   * @param integer $answerID
   */
  public function sendEmail($answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      if(is_null($a->Recommendation->invitationSent) OR time() - strtotime($a->Recommendation->invitationSent) > self::RECOMMENDATION_EMAIL_WAIT_TIME){
        $answer = new RecommendationAnswer($a);
        $answer->sendEmail();
        $this->applicant->save();
        return true;
      }
      return false;
    }
  }

  public function fill($answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new RecommendationAnswer($a);
      foreach($answer->getElements() as $id => $element){
        $value = $answer->getFormValueForElement($id);
        if($id == 'email' OR $id =='waiveViewRight'){
          $key = $id;
        } else {
          $key = 'el' . $id;
        }
        if($value) $this->form->elements[$key]->value = $value;
      }
    }
  }
  
  public function getAnswers(){
    $answers = array();
    foreach($this->applicant->getAnswersForPage($this->applicationPage->Page->id) as $a){
      $answers[] = new RecommendationAnswer($a);
    }
    return $answers;
  }
}

/**
 * Answer for Recommendations
 */
class RecommendationAnswer extends StandardAnswer {

  public function update(FormInput $input){
    $this->answer->Recommendation->email = $input->email;
    $this->answer->Recommendation->waiveViewRight = $input->waiveViewRight;
    $this->answer->Elements->clear();
    foreach($this->elements as $id => $element){
      $element->setValueFromInput($input->{'el'.$id});
      foreach($element->getAnswers() as $elementAnswer){
        $this->answer->Elements[] = $elementAnswer;
      }
    }
  }
  
  public function getElements(){
    $arr = array(
      'email' => 'Email Address',
      'waiveViewRight' => 'Do you waive your right to view this letter at a later time?'
    );
    foreach($this->elements as $id => $element){
      $arr[$id] = $element->title;
    }
    return $arr;
  }

  public function getDisplayValueForElement($elementID){
    if($elementID == 'email'){
      return $this->answer->Recommendation->email;
    }
    if($elementID == 'waiveViewRight'){
      switch($this->answer->Recommendation->waiveViewRight){
        case true: return 'Yes';
        case false: return 'No';
        default: return null;
      }
    }
    if(isset($this->elements[$elementID])){
      return $this->elements[$elementID]->displayValue();
    }
    return false;
  }

  public function getFormValueForElement($elementID){
    if($elementID == 'email'){
      return $this->answer->Recommendation->email;
    }
    if($elementID == 'waiveViewRight'){
      return $this->answer->Recommendation->waiveViewRight;
    }
    if(isset($this->elements[$elementID])){
      return $this->elements[$elementID]->formValue();
    }
    return false;
  }
  
  public function applyTools($basePath){
    $arr = array();
    if(is_null($this->answer->Recommendation->invitationSent)){
      $arr = parent::applyTools($basePath);
      $arr['Send Invitation'] = "{$basePath}/do/sendEmail/{$this->answer->id}";
    } else if(time() - strtotime($this->answer->Recommendation->invitationSent) > RecommendersPage::RECOMMENDATION_EMAIL_WAIT_TIME){
      $arr['Resend Invitation'] = "{$basePath}/do/sendEmail/{$this->answer->id}";
    }
    return $arr;
  }
  
  public function applicantTools(){
    $arr = parent::applicantTools();
    return $arr;
  }
  
  public function applyStatus(){
    $arr = parent::applyStatus();
    if($this->answer->Recommendation->LORAnswer AND $this->answer->Recommendation->LORAnswer->exists()){
      $arr['Status'] = 'This recommendation was recieved on ' . date('l F jS Y g:ia', strtotime($this->answer->Recommendation->LORAnswer->updatedAt));
    } else if($this->answer->Recommendation->invitationSent){
      $arr['Invitation Sent'] = date('l F jS Y g:ia', strtotime($this->answer->Recommendation->invitationSent));
      $arr['Status'] = 'You cannot make changes to this recommendation becuase the invitation has already been sent.';
    }
    return $arr;
  }
  
  public function applicantStatus(){
    $arr = parent::applicantStatus();
    return $arr;
  }
  
  /**
   * Send invitation email to the recommender
   */
  public function sendEmail(){
    $mail = JazzeeMail::getInstance();
    $search = array(
     '%APPLICANT_NAME%',
     '%DEADLINE%',
     '%LINK%',
     '%PROGRAM_CONTACT_NAME%',
     '%PROGRAM_CONTACT_EMAIL%',
     '%PROGRAM_CONTACT_PHONE%'
    );
    $replace = array(
     $this->answer->Applicant->firstName,
     date('l F jS Y g:ia', strtotime($this->answer->Recommendation->RecommendationPage->deadline)),
     $mail->path('lor/' . $this->answer->Recommendation->urlKey),
     $this->answer->Applicant->Application->contactName,
     $this->answer->Applicant->Application->contactEmail,
     $this->answer->Applicant->Application->contactPhone
    );
    foreach($this->answer->Page->Elements as $e){
      $search[] = '%' . str_replace(' ', '_',strtoupper($e->title)) . '%';
      $replace[] = $this->getDisplayValueForElement($e->id);
    };
    $text = str_ireplace($search, $replace, $this->answer->Recommendation->RecommendationPage->recommenderEmail);
    if($this->answer->Recommendation->invitationSent){
      $text = 'This email was originally sent to you on ' . date('l F jS Y g:ia', strtotime($this->answer->Recommendation->invitationSent)) . ".  We are sending it again because we have not yet received your response.\n" . $text;
    }
    $message = new EmailMessage;
    $message->to($this->answer->Recommendation->email, '');
    $message->from($this->answer->Applicant->Application->contactEmail, $this->answer->Applicant->Application->contactName);
    $message->subject = 'Letter of Recommendation Request';
    $message->body = $text;
    if(!$mail->send($message)){
      return false;
    }
    $this->answer->Recommendation->invitationSent = date('Y-m-d H:i:s');
    $this->answer->Recommendation->save();
    return true;
  }
}
?>