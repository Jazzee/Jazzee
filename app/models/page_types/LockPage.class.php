<?php
/**
 * Test the application for completeness and lock it
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class LockPage extends StandardPage {
  const SHOW_PAGE = false;
  
  protected function makeForm(){
    $form = new Form;
    $form->action = $this->actionPath;
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
    $this->applicant->lock();
    $this->applicant->save();
    return true;
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
  
}
?>