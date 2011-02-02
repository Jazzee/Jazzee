<?php
/**
 * The suport portal allows applicants to ask, review, and respond to questions
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage apply
 */
class ApplySupportController extends ApplyController {  
  public function beforeAction(){
    parent::beforeAction();
    $this->setLayoutVar('layoutTitle', $this->application->Cycle->name . ' ' . $this->application->Program->name . ' Support');
    $this->setVar('applicant', $this->applicant);
    $this->form = new Form;
    $this->form->action = $this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/support/new");
    $field = $this->form->newField();
    $field->legend = 'Ask a question';
    $element = $field->newElement('Textarea', 'text');
    $element->label = 'Your Question';
    $element->addValidator('NotEmpty');
    $this->form->newButton('submit', 'Submit');
    $this->setVar('form', $this->form);
  }
  
  /**
   * Display the page
   */
  public function actionIndex() {
    
  }
  
  /**
   * Ask a new question
   */
  public function actionNew() {
    if($input = $this->form->processInput($this->post)){
      $communication = new Communication;
      $communication->sentBy = 'applicant';
      $communication->applicantID = $this->applicant->id;
      $communication->text = $input->text;
      $communication->save();
      $this->messages->write('success', 'Your message has been sent.');
    }
    $this->loadView($this->controllerName . '/index');
  }
  
  /**
   * Navigation
   * @return Navigation
   */
  public function getNavigation(){
    $navigation = new Navigation;
    $menu = $navigation->newMenu();
    $menu->title = 'Navigation';
    $menu->newLink(array('text'=>'Back to Application', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/page/{$this->application['Pages']->getFirst()->id}")));
    $menu->newLink(array('text'=>'Your Questions', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/support/")));
    $menu->newLink(array('text'=>'Logout', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/applicant/logout")));
    return $navigation;
  }
}
?>
