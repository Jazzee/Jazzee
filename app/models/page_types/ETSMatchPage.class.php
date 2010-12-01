<?php
/**
 * Accept and Match user scores to those recieved from ETS
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class ETSMatchPage extends StandardPage {

  /**
   * Create the ETS form
   * @return Form
   */
  protected function makeForm(){
    $form = new Form;
    $field = $form->newField();
    $field->legend = $this->applicationPage->title;
    $field->instructions = $this->applicationPage->instructions;
    
    $e = $field->newElement('RadioList', 'scoreType');
    $e->label = 'Test Type';
    $e->addValidator('NotEmpty');
    $e->addItem('gre', 'GRE/GRE Subject');
    $e->addItem('toefl', 'TOEFL');
    
    $e = $field->newElement('TextInput', 'registrationNumber');
    $e->label = 'ETS Registration Number';
    $e->format = 'no leading zeros';
    $e->addValidator('NotEmpty');
    $e->addValidator('Integer');
    $e->addFilter('PHPSanitize', FILTER_SANITIZE_NUMBER_INT);
    
    $form->newButton('submit', 'Save');
    $form->newButton('reset', 'Clear Form');
    return $form;
  }
  
  public function newAnswer($input){
    $a = new Answer;
    $a->pageID = $this->applicationPage->Page->id;
    $this->applicant['Answers'][] = $a;
    $answer = new ETSAnswer($a);
    $answer->update($input);
    $this->applicant->save();
    $this->form->applyDefaultValues();
    return true;
  }
  
  public function updateAnswer($input, $answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new ETSAnswer($a);
      $answer->update($input);
      $a->save();
      $this->form->applyDefaultValues();
    }
  }
  
  public function fill($answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new ETSAnswer($a);
      $this->form->elements['scoreType']->value = $answer->getFormValueForElement('scoreType');
      $this->form->elements['registrationNumber']->value = $answer->getFormValueForElement('registrationNumber');
    }
  }
  
  public function getAnswers(){
    $answers = array();
    foreach($this->applicant->getAnswersForPage($this->applicationPage->Page->id) as $a){
      $answers[] = new ETSAnswer($a);
    }
    return $answers;
  }
  
}

/**
 * ETS Answers
 */
class ETSAnswer extends StandardAnswer {
   
  public function update(FormInput $input){
    $this->answer->Score->scoreType = $input->scoreType;
    $this->answer->Score->registrationNumber = $input->registrationNumber;
    $this->answer->Score->scoreID = null;
    $this->answer->Score->makeMatch();
  }
  
  public function getElements(){
    return array(
      'scoreType' => 'Test Type',
      'registrationNumber' => 'ETS Registration Number'
    );
  }
  
  public function getDisplayValueForElement($name){
    if($name == 'scoreType'){
      switch($this->answer->Score->scoreType){
        case 'gre':
          return 'GRE/GRE Subject';
        case 'toefl':
          return 'TOEFL';
      }
    } else {
      return $this->answer->Score->$name;
    }
  }
  
  public function getFormValueForElement($name){
    return $this->answer->Score->$name;
  }
  
  public function applyStatus(){
    $arr = parent::applyStatus();
    if($this->answer->Score->Score){
      $arr['Score Status'] = 'ETS Score recieved for test taken on ' . date('m/d/Y', strtotime($this->answer->Score->Score->testDate));
    } else {
      $arr['Score Status'] = 'This score has not been matched to one sent from ETS';
    }
    return $arr;
  }
  
  public function applicantStatus(){
    $arr = parent::applyStatus();
    if($this->answer->Score->Score){
      $arr['Score Status'] = 'ETS Score recieved for test taken on ' . date('m/d/Y', strtotime($this->answer->Score->Score->testDate));
    } else {
      $arr['Score Status'] = 'This score has not been matched to one sent from ETS';
    }
    return $arr;
  }
}
?>