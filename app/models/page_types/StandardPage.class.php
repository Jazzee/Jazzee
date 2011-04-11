<?php
/**
 * The Standard Application Page
 * Unless special functionaility is required all pages are of this type
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class StandardPage extends ApplyPage {
  
  /**
   * When we update the action path also update the path for the form
   * @see ApplyPage::setActionPath()
   */
  public function setActionPath($actionPath){
    parent::setActionPath($actionPath);
    $this->form->action = $actionPath;
  }
  
  /**
   * Create the form from the $page
   * @return Form
   */
  protected function makeForm(){
    $form = new Form;
    $form->action = $this->actionPath;
    $field = $form->newField();
    $field->legend = $this->applicationPage->title;
    $field->instructions = $this->applicationPage->instructions;
    foreach($this->applicationPage->Page->findElementsByWeight() as $e){
      $element = new $e->ElementType->class($e);
      $element->addToField($field);
    }
    $form->newButton('submit', 'Save');
    $form->newButton('reset', 'Clear Form');
    return $form;
  }
  
  public function validateInput($input){
    return $this->form->processInput($input);
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
  
  /**
   * Skip this page
   */
  public function skip(){}
  
  /**
   * Unskip this page
   */
  public function unSkip(){}
  
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
    $answers = array();
    foreach($this->applicant->getAnswersForPage($this->applicationPage->Page->id) as $a){
      $answers[] = new StandardAnswer($a);
    }
    return $answers;
  }
  
  public function getStatus(){
    if(count($this->getAnswers()) < $this->applicationPage->min){
      return self::INCOMPLETE;
    } else {
      return self::COMPLETE;
    }
  }
  
}

/**
 * A single StandardPage Applicant Answer
 */
class StandardAnswer implements ApplyAnswer {
 /**
  * The Answer model
  * @var Answer $answer
  */
  protected $answer;
  
  /**
   * The ApplyElements for an answer
   * @var array of ApplyElements
   */
  protected $elements = array();
  
 /**
  * Contructor
  * Store the answer and create the elements array
  */
  public function __construct(Answer $answer){
    $this->answer = $answer;
    $allAnswersByElementId = array();
    foreach($this->answer->Elements as $answerElement){
      $allAnswersByElementId[$answerElement->elementID][] = $answerElement;
    }
    foreach($this->answer->Page->findElementsByWeight() as $e){
      $this->elements[$e->id] = new $e->ElementType->class($e);
      if(!empty($allAnswersByElementId[$e->id])) $this->elements[$e->id]->setValueFromAnswer($allAnswersByElementId[$e->id]);
    }
  }

  public function getID(){
    return $this->answer->id;
  }

  public function update(FormInput $input){
    $this->answer->Elements->delete();
    foreach($this->elements as $id => $element){
      $element->setValueFromInput($input->{'el'.$id});
      foreach($element->getAnswers() as $elementAnswer){
        $this->answer->Elements[] = $elementAnswer;
      }
    }
  }
  
  public function getElements(){
    $arr = array();
    foreach($this->elements as $id => $element){
      $arr[$id] = $element->title;
    }
    return $arr;
  }

  public function getDisplayValueForElement($elementID){
    if(isset($this->elements[$elementID])){
      return $this->elements[$elementID]->displayValue();
    }
    return false;
  }

  public function getFormValueForElement($elementID){
    if(isset($this->elements[$elementID])){
      return $this->elements[$elementID]->formValue();
    }
    return false;
  }
  
  public function applyTools($basePath){
    return array(
      'Edit' => "{$basePath}/edit/{$this->answer->id}",
      'Delete' => "{$basePath}/delete/{$this->answer->id}",
    );
  }
  
  public function applicantTools(){
    $arr = array(
      array(
        'title' => 'Edit',
         'class' => 'editAnswer',
         'path' => "editAnswer/{$this->answer->id}"
       ),
       array(
        'title' => 'Delete',
         'class' => 'deleteAnswer',
         'path' => "deleteAnswer/{$this->answer->id}"
       ),
       array(
        'title' => 'Verify',
         'class' => 'verifyAnswer',
         'path' => "verifyAnswer/{$this->answer->id}"
       )
    );
    return $arr;
  }

  public function applyStatus(){
    $arr = array(
      'Last updated' => date('M d Y g:i a',$this->getUpdatedAt())
    );
    if($this->answer->publicStatus){
      $arr['Status'] = $this->answer->PublicStatus->name;
    }
    return $arr;
  }
  
  public function applicantStatus(){
    $arr = array(
      'Last updated' => date('M d Y g:i a',$this->getUpdatedAt())
    );
    if($this->answer->publicStatus){
      $arr['Public Status'] = $this->answer->PublicStatus->name;
    }
    if($this->answer->privateStatus){
      $arr['Private Status'] = $this->answer->PrivateStatus->name;
    }
    return $arr;
  }
  
  public function getAttachment(){
    return $this->answer->attachment;
  }
  
  public function getUpdatedAt(){
    return strtotime($this->answer->updatedAt);
  }

}
?>