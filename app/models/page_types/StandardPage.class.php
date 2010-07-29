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
   * Create the form from the $page
   * @return Form
   */
  protected function makeForm(){
    $form = new Form;
    $field = $form->newField();
    $field->legend = $this->applicationPage->title;
    $field->instructions = $this->applicationPage->instructions;
    foreach($this->applicationPage->Page->Elements as $e){
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
  
  public function showPageData(){
    return true;
  }
  
  public function getTabs(){
    $tabs = array();
    $tab = new PageCreatorTab('Properties', 'form');
    $tab->method = 'editProperties';
    $tab->setForm($this->getEditPropertiesForm());
    $tabs['properties'] = $tab;
    
    $tab = new PageCreatorTab('Elements', 'elements');
    $tab->elementPageID = $this->applicationPage->Page->id;
    $elements = array();
    foreach($this->applicationPage->Page->Elements AS $element){
      $tab->addElement($element);
    }
    $tabs['elements'] = $tab;
    
    $tab = new PageCreatorTab('Preview', 'preview');
    
    $propeties = array('form' => $this->getForm());
    $html = "<div id='leadingText'>{$this->applicationPage->leadingText}</div>";
    if($propeties['form']){
      $view = Lvc_FoundationConfig::getElementView('form', $propeties);
      $html .= $view->getOutput();
    }
    $html .=  "<div id='trailingText'>{$this->applicationPage->trailingText}</div>";
    
    $tab->setHTML($html);
    
    $tabs['preview'] = $tab;
    
    return $tabs;
  }
  
  /**
   * Get the standard edit properties form
   * @return Form
   */
  public function getEditPropertiesForm(){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Edit {$this->applicationPage->title} properties"));
    $element = $field->newElement('TextInput','title');
    $element->label = 'Title';
    $element->addValidator('NotEmpty');
    $element->value = $this->applicationPage->title;
    
    $element = $field->newElement('TextInput','min');
    $element->label = 'Minimum Entries';
    $element->addValidator('NotEmpty');
    $element->addValidator('Integer');
    $element->value = $this->applicationPage->min;
    
    $element = $field->newElement('TextInput','max');
    $element->label = 'Maximum Entries';
    $element->addValidator('NotEmpty');
    $element->addValidator('Integer');
    $element->value = $this->applicationPage->max;
    
    $element = $field->newElement('RadioList','optional');
    $element->label = 'Is this page optional?';
    $element->addValidator('NotEmpty');
    $element->addItem(1,'Yes');
    $element->addItem(0, 'No');
    $element->value = (int)$this->applicationPage->optional;
    
    $element = $field->newElement('Textarea','instructions');
    $element->label = 'Instructions';
    $element->value = $this->applicationPage->instructions;
    
    $element = $field->newElement('Textarea','leadingText');
    $element->label = 'Leading Text';
    $element->value = $this->applicationPage->leadingText;
    
    $element = $field->newElement('Textarea','trailingText');
    $element->label = 'Trailing Text';
    $element->value = $this->applicationPage->trailingText;
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  public function editProperties(FormInput $input){
    $this->applicationPage->title = $input->title;
    $this->applicationPage->min = $input->min;
    $this->applicationPage->max = $input->max;
    $this->applicationPage->optional = (bool)$input->optional;
    $this->applicationPage->instructions = $input->instructions;
    $this->applicationPage->leadingText = $input->leadingText;
    $this->applicationPage->trailingText = $input->trailingText;
    $this->applicationPage->save();
  }
  
}

/**
 * A single StandardPage Applicant Answer
 */
class StandardAnswer extends ApplyAnswer {
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
    foreach($this->answer->Page->Elements as $e){
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