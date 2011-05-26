<?php
namespace Jazzee\Entity\Page;
/**
 * The Standard Application Page
 * 
 * Unless special functionaility is required all pages are of this type
 */
class Standard extends AbstractPage {
  
  /**
   * 
   * @see Jazzee\Page.AbstractPage::makeForm()
   */
  protected function makeForm(){
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    foreach($this->_applicationPage->getPage()->getElements() as $element){
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newButton('submit', 'Save');
    $form->newButton('reset', 'Clear Form');
    return $form;
  }
  
  
  public function newAnswer($input){
    $a = new Answer;
    $a->pageID = $this->applicationPage->Page->id;
    $this->applicant['Answers'][] = $a;
    $answer = new StandardAnswer($a);
    $answer->update($input);
    $this->applicant->save();
    $this->form->applyDefaultValues();
    return true;
  }
  
  public function updateAnswer($input, $answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new StandardAnswer($a);
      $answer->update($input);
      $a->save();
      $this->form->applyDefaultValues();
      return true;
    }
  }

  public function deleteAnswer($answerID){
    if(($key = array_search($answerID, $this->applicant->Answers->getPrimaryKeys())) !== false){
      $this->applicant->Answers->remove($key);
      $this->applicant->save();
      return true;
    }
    return false;
  }
  
  
  public function fill($answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new StandardAnswer($a);
      foreach($answer->getElements() as $id => $element){
        $value = $answer->getFormValueForElement($id);
        if($value) $this->form->elements['el' . $id]->value = $value;
      }
    }
  }
  
  public function getAnswers(){
    return array();
    $answers = array();
    foreach($this->applicant->getAnswersForPage($this->applicationPage->Page->id) as $a){
      $answers[] = new StandardAnswer($a);
    }
    return $answers;
  }
  
  public function getStatus(){
    return self::INCOMPLETE;
    if(count($this->getAnswers()) < $this->applicationPage->min){
      return self::INCOMPLETE;
    } else {
      return self::COMPLETE;
    }
  }
  
}