<?php
/**
 * Accept and Match user scores to those recieved from ETS
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
use Entity\GREScore;
class ETSMatchPage extends StandardPage {

  /**
   * These fixedIDs make it easy to find the element we are looking for
   * @const integer
   */
  const FID_TEST_TYPE = 2;
  const FID_REGISTRATION_NUMBER = 4;
  const FID_TEST_DATE = 6;
  
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
      return true;
    }
  }
  
  public function fill($answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new ETSAnswer($a);
      foreach($answer->getElements() as $id => $element){
        $value = $answer->getFormValueForElement($id);
        if($value) $this->form->elements['el' . $id]->value = $value;
      }
    }
  }
  
  public function getAnswers(){
    $answers = array();
    foreach($this->applicant->getAnswersForPage($this->applicationPage->Page->id) as $a){
      $answers[] = new ETSAnswer($a);
    }
    return $answers;
  }
  
/**
   * Create the ets match form
   * @param Entity\Page $page
   */
  public static function setupNewPage(Entity\Page $page){
    $em = JazzeeController::getEntityManager();
    $types = $em->getRepository('Entity\ElementType')->findAll();
    $elementTypes = array();
    foreach($types as $type){
      $elementTypes[$type->getClass()] = $type;
    };
    $count = 1;

    $element = new Entity\Element;
    $element->setPage($page);
    $element->setType($elementTypes['RadioListElement']);
    $element->setTitle('Test Type');
    $element->required();
    $element->setWeight(1);
    $element->setFixedId(ETSMatchPage::FID_TEST_TYPE);
    $em->persist($element);
    
    $item = new Entity\ElementListItem;
    $item->setElement($element);
    $item->setValue('GRE/GRE Subject');
    $item->setWeight(1);
    $em->persist($item);
    
    $item = new Entity\ElementListItem;
    $item->setElement($element);
    $item->setValue('TOEFL');
    $item->setWeight(2);
    $em->persist($item);
    
    $element = new Entity\Element;
    $element->setPage($page);
    $element->setType($elementTypes['RadioListElement']);
    $element->setTitle('ETS Registration Number');
    $element->setFormat('no leading zeros');
    $element->required();
    $element->setWeight(2);
    $element->setFixedId(ETSMatchPage::FID_REGISTRATION_NUMBER);
    $em->persist($element);
    
    $element = new Entity\Element;
    $element->setPage($page);
    $element->setType($elementTypes['ShortDateElement']);
    $element->setTitle('Test Date');
    $element->required();
    $element->setWeight(3);
    $element->setFixedId(ETSMatchPage::FID_TEST_DATE);
    $em->persist($element);
    
    
  }
  
}

/**
 * ETS Answers
 */
class ETSAnswer extends StandardAnswer {
   
  public function update(FormInput $input){
    $this->answer->Score->scoreType = $input->scoreType;
    $this->answer->Score->registrationNumber = $input->registrationNumber;
    $this->answer->Score->testMonth = date('n', strtotime($input->testDate));
    $this->answer->Score->testYear = date('Y', strtotime($input->testDate));
    $this->answer->Score->scoreID = null; //reset the score ID in case we already had a match
    $this->answer->Score->makeMatch();
  }
  
  public function getElements(){
    return array(
      'scoreType' => 'Test Type',
      'registrationNumber' => 'ETS Registration Number',
      'testDate' => 'Test Date'
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
    } else if($name == 'testDate'){
      return date('F Y', strtotime($this->answer->Score->testMonth . '/1/' . $this->answer->Score->testYear));
    } else {
      return $this->answer->Score->$name;
    }
  }
  
  public function getFormValueForElement($name){
    if($name == 'testDate'){
      return "{$this->answer->Score->testYear}-{$this->answer->Score->testMonth}";
    } else {
      return $this->answer->Score->$name;
    }
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
    $arr = parent::applicantStatus();
    if($this->answer->Score->Score){
      $arr['Score Status'] = 'ETS Score recieved for test taken on ' . date('m/d/Y', strtotime($this->answer->Score->Score->testDate));
    } else {
      $arr['Score Status'] = 'This score has not been matched to one sent from ETS';
    }
    return $arr;
  }
}
?>