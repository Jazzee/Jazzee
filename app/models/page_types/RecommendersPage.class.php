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
  
  public function getTabs(){
    $tabs = parent::getTabs();
    
    $tab = new PageCreatorTab('Email Text', 'form');
    $tab->method = 'editEmail';
    $form = new Form;
    $field = $form->newField(array('legend'=>"Edit {$this->applicationPage->title} Email Text"));
    $element = $field->newElement('Textarea','recommenderEmail');
    $element->label = 'Recommender Email';
    $element->instructions = "The text of the email request sent to each recommender.";
    $format = 'Special Text replacements <br />' .
        '%APPLICANT_NAME% - Applicants full name <br />' .
        '%DEADLINE% - Deadline for completing the recommendation <br />' .
        '%LINK% - Link to the recommendation <br />' .
        '%PROGRAM_CONTACT_NAME% - Name of program contact <br />' .
        '%PROGRAM_CONTACT_EMAIL% - Contact Email <br />' .
        '%PROGRAM_CONTACT_PHONE% - Contact Phone<br />';
    foreach($this->applicationPage->Page->Elements as $e){
      $format .= '%' . str_replace(' ', '_',strtoupper($e->title)) . "% - Value of the {$e->title} field <br />";
    }
    $element->format = $format;
    $element->value = $this->applicationPage->RecommendationPage->recommenderEmail;
    $element->addValidator('NotEmpty');
    $form->newButton('submit', 'Save');
    $tab->setForm($form);
    $tabs['email'] = $tab;
    
    $tab = new PageCreatorTab('Recommendation Page', 'form');
    $tab->method = 'editLorProperties';
    $recPage = $this->applicationPage->RecommendationPage->LORPage;
    $form = new Form;
    $field = $form->newField(array('legend'=>"Recommendation Form"));
    $element = $field->newElement('TextInput','title');
    $element->label = 'Title';
    $element->addValidator('NotEmpty');
    $element->value = $recPage->title;
    
    $element = $field->newElement('DateInput','deadline');
    $element->label = 'Deadline';
    $element->addValidator('NotEmpty');
    $element->addFilter('DateFormat', 'Y-m-d H:i:s');
    $element->value = $this->applicationPage->RecommendationPage->deadline;
    
    $element = $field->newElement('Textarea','instructions');
    $element->label = 'Instructions for Recommender';
    $element->value = $recPage->instructions;
    
    $form->newButton('submit', 'Save');
    
    $tab->setForm($form);
    $tabs['lorProperties'] = $tab;
    
    //only do elements if the LORPage has been setup
    if($this->applicationPage->RecommendationPage->LORPage->id){
      $tab = new PageCreatorTab('Recommendation Elements', 'elements');
      $tab->elementPageID = $this->applicationPage->RecommendationPage->LORPage->id;
      $elements = array();
      foreach($this->applicationPage->RecommendationPage->LORPage->Elements AS $element){
        $tab->addElement($element);
      }
      $tabs['lorElements'] = $tab;
    }
    return $tabs;
  }
  
  public function editEmail(FormInput $input){
    $this->applicationPage->RecommendationPage->recommenderEmail = $input->recommenderEmail;
    $this->applicationPage->save();
  }
  
  public function editLorProperties(FormInput $input){
    //I have no idea why this is necessary, but the relationship between RecommendationPage and LORPage 
    //does not want to create itself for some reason
    if($this->applicationPage->RecommendationPage->LORPage->exists()){
      $lorPage = $this->applicationPage->RecommendationPage->LORPage;
    } else {
      $lorPage = new Page;
      $this->applicationPage->RecommendationPage->LORPage = $lorPage;
    }
    $lorPage->title = $input->title;
    $this->applicationPage->RecommendationPage->deadline = $input->deadline;
    $lorPage->instructions = $input->instructions;
    $this->applicationPage->save();
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