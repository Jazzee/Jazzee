<?php
namespace Jazzee\Answer;
/**
 * A single StandardPage Applicant Answer
 */
class Standard implements \Jazzee\Answer 
{
 /**
  * The Answer entity
  * @var \Jazzee\Entity\Answer
  */
  protected $_answer;
  
  /**
   * The Elements for an answer
   * @var array of \Jazzee\Element
   */
  protected $_elements = array();
  
 /**
  * Contructor
  * 
  * Store the answer and create the elements array
  */
  public function __construct(\Jazzee\Entity\Answer $answer){
    $this->_answer = $answer;
    
    /*$allAnswersByElementId = array();
    foreach($this->answer->Elements as $answerElement){
      $allAnswersByElementId[$answerElement->elementID][] = $answerElement;
    }
    foreach($this->answer->Page->findElementsByWeight() as $e){
      $this->elements[$e->id] = new $e->ElementType->class($e);
      if(!empty($allAnswersByElementId[$e->id])) $this->elements[$e->id]->setValueFromAnswer($allAnswersByElementId[$e->id]);
    }
    */
  }

  public function getID(){
    return $this->_answer->getId();
  }

  public function update(FormInput $input){
    /*
    $this->answer->Elements->delete();
    foreach($this->elements as $id => $element){
      $element->setValueFromInput($input->{'el'.$id});
      foreach($element->getAnswers() as $elementAnswer){
        $this->answer->Elements[] = $elementAnswer;
      }
    }
    */
  }
  
  public function getElements(){
    return array();
    $arr = array();
    foreach($this->elements as $id => $element){
      $arr[$id] = $element->title;
    }
    return $arr;
  }

  public function getDisplayValueForElement($elementID){
    return '';
    if(isset($this->elements[$elementID])){
      return $this->elements[$elementID]->displayValue();
    }
    return false;
  }

  public function getFormValueForElement($elementID){
    return '';
    if(isset($this->elements[$elementID])){
      return $this->elements[$elementID]->formValue();
    }
    return false;
  }
  
  public function applyTools($basePath){
    return array();
    return array(
      'Edit' => "{$basePath}/edit/{$this->answer->id}",
      'Delete' => "{$basePath}/delete/{$this->answer->id}",
    );
  }
  
  public function applicantTools(){
    return array();
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
    return array();
    $arr = array(
      'Last updated' => date('M d Y g:i a',$this->getUpdatedAt())
    );
    if($this->answer->publicStatus){
      $arr['Status'] = $this->answer->PublicStatus->name;
    }
    return $arr;
  }
  
  public function applicantStatus(){
    return array();
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
    return $this->_answer->getAttachment();
  }
  
  public function getUpdatedAt(){
    return $this->_answer->getUpdatedAt();
  }
}
?>