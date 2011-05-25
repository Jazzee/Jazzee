<?php
/**
 * Setup the application
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class SetupApplicationController extends SetupController {
  const MENU = 'Setup';
  const TITLE = 'Application';
  const PATH = 'setup/application';
  
  /**
   * If there is no application then create a new one to work with
   */
  protected function setUp(){
    parent::setUp();
    if($this->application === false){
      $this->application = new Application;
      $this->application->programID = $this->program->id;
      $this->application->cycleID = $this->cycle->id;
    }
  }
  
  /**
   * Setup the current application and cycle
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("setup/application");
    $field = $form->newField(array('legend'=>'Setup Application'));
    
    $element = $field->newElement('TextInput','contactName');
    $element->label = 'Contact Name';
    $element->addValidator('NotEmpty');
    $element->value = $this->application->contactName;
    
    $element = $field->newElement('TextInput','contactEmail');
    $element->label = 'Contact Email';
    $element->addValidator('NotEmpty');
    $element->addValidator('EmailAddress');
    $element->value = $this->application->contactEmail;
    
    $element = $field->newElement('TextInput','contactPhone');
    $element->label = 'Contact Phone';
    $element->value = $this->application->contactPhone;
    
    $element = $field->newElement('Textarea','welcome');
    $element->label = 'Welcome Message';
    $element->value = $this->application->welcome;
    
    $element = $field->newElement('TextInput','open');
    $element->label = 'Application Open';
    $element->addValidator('Date');
    $element->addValidator('DateBeforeElement', 'close');
    $element->addFilter('DateFormat', 'Y-m-d H:i:s');
      $element->value = $this->application->open;
    
    $element = $field->newElement('TextInput','close');
    $element->label = 'Application Deadline';
    $element->addValidator('Date');
    $element->addFilter('DateFormat', 'Y-m-d H:i:s');
    $element->value = $this->application->close;
    
    $element = $field->newElement('TextInput','begin');
    $element->label = 'Program Start Date';
    $element->addValidator('Date');
    $element->addFilter('DateFormat', 'Y-m-d H:i:s');
    $element->addValidator('NotEmpty');
    $element->value = $this->application->begin;
    
    $element = $field->newElement('RadioList','visible');
    $element->label = 'Visible';
    $element->addItem(0, 'No');
    $element->addItem(1, 'Yes');
    $element->addValidator('NotEmpty');
    $element->value = $this->application->visible;
    
    $element = $field->newElement('RadioList','published');
    $element->label = 'Published';
    $element->addItem(0, 'No');
    $element->addItem(1, 'Yes');
    $element->addValidator('NotEmpty');
    $element->value = $this->application->published;

    $form->newButton('submit', 'Save');
    
    if($input = $form->processInput($this->post)){
      $this->application->contactName = $input->contactName;
      $this->application->contactEmail = $input->contactEmail;
      $this->application->contactPhone = $input->contactPhone;
      $this->application->welcome = $input->welcome;
      $this->application->open = $input->open;
      $this->application->close = $input->close;
      $this->application->begin = $input->begin;
      $this->application->feeDomestic = $input->feeDomestic;
      $this->application->feeForeign = $input->feeForeign;
      $this->application->visible = (bool)$input->visible;
      $this->application->published = (bool)$input->published;
      $this->application->save();
      $this->messages->write('success', 'Application saved.');
      $this->redirect($this->path("setup/application"));
      $this->afterAction();
      exit();
    }
    
    $this->setVar('form', $form);
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Setup Application';
    $auth->addAction('index', new ActionAuth('Make Changes'));
    return $auth;
  }
  
}