<?php
/**
 * Test the application for completeness and lock it
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class LockPage extends StandardPage {
  protected function makeForm(){
    $form = new Form;
    $field = $form->newField();
    $field->legend = $this->applicationPage->title;
    $field->instructions = $this->applicationPage->instructions;
    $element = $field->newElement('RadioList', 'lock');
    $element->label = 'Do you wish to lock your application?';
    $element->addItem(0,'No');
    $element->addItem(1,'Yes');
    
    $form->newButton('submit','Submit Application');
    $form->newButton('reset', 'Clear Form');
    return $form;
  }
  
  /**
   * Test each page to see if it is complete
   * @param FormInput $input
   * @return bool
   */
  public function validateInput($input){
    if(!$input = $this->form->processInput($input)) return false;
    if(!$input->lock){
      $this->form->elements['lock']->addMessage('You must answer yes to submit your application.');
      return false;
    }
    $error = false;
    foreach($this->applicant->Application->Pages as $page){
      if($page->id != $this->applicationPage->Page->id){ //dont check the lock page (this page), it will never be complete
        if(class_exists($page->Page->PageType->class) AND is_subclass_of($page->Page->PageType->class, 'ApplyPage')){
          $class = new $page->Page->PageType->class($page, $this->applicant);
          if($class->getStatus() == self::INCOMPLETE){
            $error = true;
            Message::getInstance()->write('error', "You have not completed the {$page->title} page");
          }
        }
      }
    }
    if($error) return false;
    return true;
  }
  
  public function newAnswer($input){
    $this->applicant->locked = date('Y-m-d H:i:s');
    $this->applicant->save();
  }
  
  /**
   * Lock page is always incomplete
   */
  public function getStatus(){
    return self::INCOMPLETE;
  }
  
  /**
   * No data is entered so none is displayed
   */
  public function showPageData(){
    return false;
  }
  
  /**
   * Remove the elemnts tab
   * @return array
   */
  public function getTabs(){
    $tabs = parent::getTabs();
    unset($tabs['elements']);
    return $tabs;
  }
  
  /**
   * Get the edit properties form
   * @return Form
   */
  public function getEditPropertiesForm(){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Edit {$this->applicationPage->title} properties"));
    $element = $field->newElement('TextInput','title');
    $element->label = 'Title';
    $element->addValidator('NotEmpty');
    $element->value = $this->applicationPage->title;
    
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
    $this->applicationPage->instructions = $input->instructions;
    $this->applicationPage->leadingText = $input->leadingText;
    $this->applicationPage->trailingText = $input->trailingText;
    $this->applicationPage->save();
  }
}
?>