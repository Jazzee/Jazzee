<?php
/**
 * A page with no form just text
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class TextPage extends StandardPage {
  /**
   * Text pages dont have forms
   */
  protected function makeForm(){
    return false;
  }
  
  /**
   * Nothign is entered by applicants so nothing is displayed
   */
  public function showPageData(){
    return false;
  }
  
  /**
   * Edit the title and the sinle Text block
   * @return Form
   */
  public function getEditPropertiesForm(){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Edit {$this->applicationPage->title} properties"));
    $element = $field->newElement('TextInput','title');
    $element->label = 'Title';
    $element->addValidator('NotEmpty');
    $element->value = $this->applicationPage->title;
    
    $element = $field->newElement('Textarea','leadingText');
    $element->label = 'Text';
    $element->addValidator('NotEmpty');
    $element->value = $this->applicationPage->leadingText;
    
    $form->newButton('submit', 'Save');
    return $form;
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
   * Only has one text block which is stored in leadingText
   */
  public function editProperties(FormInput $input){
    $this->applicationPage->Page->title = $input->title;
    $this->applicationPage->Page->leadingText = $input->leadingText;
    $this->applicationPage->save();
  }
  
  /**
   * TextPages are always complete
   */
  public function getStatus(){
    return self::COMPLETE;
  }
}
?>