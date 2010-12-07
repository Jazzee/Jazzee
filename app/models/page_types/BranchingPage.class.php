<?php
/**
 * Branch a child page depending on an applicant input
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage apply
 */
class BranchingPage extends StandardPage {
  
  protected function makeForm(){
    $form = new Form;
    $field = $form->newField();
    $field->legend = $this->applicationPage->title;
    $field->instructions = $this->applicationPage->instructions;
    $element = $field->newElement('SelectList', 'branching');
    $element->label = $this->applicationPage->title;
    $element->addValidator('NotEmpty');
    foreach($this->applicationPage->Page->Children as $branch){
      $element->addItem($branch->id, $branch->title);
    }
    $form->newHiddenElement('level', 1);
    $form->newButton('submit', 'Next');
    return $form;
  }
  
  protected function branchingForm($branchingPageID){
    $this->applicationPage->leadingText .= "<a href='{$this->applicationPage->id}'>Undo</a>";
    $this->form->reset();
    $page = $this->applicationPage->Page->getChildById($branchingPageID);
    $field = $this->form->newField();
    $field->legend = $page->title;
    $field->instructions = $page->instructions;
    foreach($page->Elements as $e){
      $element = new $e->ElementType->class($e);
      $element->addToField($field);
    }
    $this->form->newHiddenElement('level', 2);
    $this->form->newHiddenElement('branching', $branchingPageID);
    $this->form->newButton('submit', 'Save');
    $this->form->newButton('reset', 'Clear Form');
  }
  
  public function validateInput($input){
    $this->branchingForm($input['branching']);
    if($input['level'] == 1) return false;
    return $this->form->processInput($input);
  }
  
  public function newAnswer($input){
    $a = $this->applicant->Answers->get(null);
    $a->pageID = $this->applicationPage->Page->id;
    foreach($this->applicationPage->Page->Children as $branch){
      $child = $a->Children->get(null); 
      $child->pageID = $branch->id;
    }
    $answer = new BranchingAnswer($a);
    $answer->update($input);
    $this->applicant->save();
    $this->form = $this->makeForm();
    $this->form->applyDefaultValues();
    return true;
  }
  
  public function updateAnswer($input, $answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new BranchingAnswer($a);
      $answer->update($input);
      $a->save();
      $this->form = $this->makeForm();
      $this->form->applyDefaultValues();
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
      $answer = new BranchingAnswer($a);
      $branchID = $answer->getActiveBranchPageId();
      $this->branchingForm($branchID);
      foreach($answer->getElements() as $id => $element){
        $value = $answer->getFormValueForElement($id);
        if($value) $this->form->elements['el' . $id]->value = $value;
      }
    }
  }
  
  public function getAnswers(){
    $answers = array();
    foreach($this->applicant->getAnswersForPage($this->applicationPage->Page->id) as $a){
      $answers[] = new BranchingAnswer($a);
    }
    return $answers;
  }  
}

/**
 * A single BranchingPage Applicant Answer
 */
class BranchingAnswer extends StandardAnswer {  
 /**
  * Contructor
  * Override StandardAnswer to create elements properly for every branch
  */
  public function __construct(Answer $answer){
    $this->answer = $answer;
    $allAnswersByElementId = array();
    foreach($this->answer->Children as $child){
      foreach($child->Elements as $answerElement){
        $allAnswersByElementId[$answerElement->elementID][] = $answerElement;
      }
    }
    foreach($this->answer->Page->Children as $branch){
      foreach($branch->Elements as $e){
        $this->elements[$e->id] = new $e->ElementType->class($e);
        if(!empty($allAnswersByElementId[$e->id])) $this->elements[$e->id]->setValueFromAnswer($allAnswersByElementId[$e->id]);
      }
    }
  }

  public function update(FormInput $input){
    //clear all the elements from all the answers
    foreach($this->answer->Children as $child){
      $child->Elements->delete();
    }
    $answer = $this->answer->getChildByPageId($input->branching);
    foreach($this->elements as $id => $element){
      $element->setValueFromInput($input->{'el'.$id});
      foreach($element->getAnswers() as $elementAnswer){
        $answer->Elements[] = $elementAnswer;
      }
    }
  }
  
  public function getElements(){
    $arr = array('branching' => $this->answer->Page->title);
    foreach($this->elements as $id => $element){
      $arr[$id] = $element->title;
    }
    return $arr;
  }

  public function getDisplayValueForElement($elementID){
    if($elementID == 'branching'){
      return $this->answer->Page->getChildById($this->getActiveBranchPageId())->title;
    }
    if(isset($this->elements[$elementID])){
      return $this->elements[$elementID]->displayValue();
    }
    return false;
  }
  
  public function getActiveBranchPageId(){
    foreach($this->answer->Children as $child){
      if($child->Elements->count() > 0) return $child->pageID;
    }
    return false;
  }
}
?>