<?php
/**
 * Manage Programs
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
class ManageProgramsController extends ManageController {
  /**
   * List programs
   */
  public function actionIndex(){
    $this->setVar('programs', Doctrine::getTable('Program')->findAll(Doctrine::HYDRATE_ARRAY));
  }
  
  /**
   * Edit a program
   * @param integer $programID
   */
   public function actionEdit($programID){ 
    if($program = Doctrine::getTable('Program')->find($programID)){
      $form = new Form;
      
      $form->action = $this->path("manage/programs/edit/{$programID}");
      $field = $form->newField(array('legend'=>"Edit Program {$program->name}"));
      $element = $field->newElement('TextInput','name');
      $element->label = 'Program Name';
      $element->addValidator('NotEmpty');
      $element->value = $program->name;
      
      $element = $field->newElement('TextInput','shortName');
      $element->label = 'Short Name';
      $element->instructions = 'Forms the URL for accessing this program, must be unique';
      $element->addValidator('NotEmpty');
      $element->addFilter('UrlSafe');
      $element->value = $program->shortName;
  
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        $program->name = $input->name;
        $program->shortName = $input->shortName;
        try {
          $program->save();
          $this->messages->write('success', "Changes Saved Successfully");
          $this->redirect($this->path("manage/programs"));
          $this->afterAction();
          exit(); 
        } catch (Doctrine_Validator_Exception $e){
          $records = $e->getInvalidRecords();
          $errors = $records[0]->getErrorStack();
          if($errors->contains('shortName')){
            if(in_array('unique', $errors->get('shortName'))){
              $this->messages->write('error', "Program with short name {$input->shortName} already exists.");
              return;
            }
          }
          throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem creating a new program.');
        }
      }
    } else {
      $this->messages->write('error', "Error: Program #{$programID} does not exist.");
    }
  }
   
  /**
   * Create a new program
   */
   public function actionNew(){
    $form = new Form;
    $form->action = $this->path("manage/programs/new/");
    $field = $form->newField(array('legend'=>"New Application Program"));
    $element = $field->newElement('TextInput','name');
    $element->label = 'Program Name';
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('TextInput','shortName');
    $element->label = 'Short Name';
    $element->instructions = 'Forms the URL for accessing this program, must be unique';
    $element->addValidator('NotEmpty');
    $element->addFilter('UrlSafe');

    $form->newButton('submit', 'Save Changes');
    $this->setVar('form', $form); 
    if($input = $form->processInput($this->post)){
      $program = new Program;
      $program->name = $input->name;
      $program->shortName = $input->shortName;
      try {
        $program->save();
        $this->messages->write('success', "Program Created Successfully");
        $this->redirect($this->path("manage/programs"));
        $this->afterAction();
        exit(); 
      }
      catch (Doctrine_Validator_Exception $e){
        $records = $e->getInvalidRecords();
        $errors = $records[0]->getErrorStack();
        if($errors->contains('shortName')){
          if(in_array('unique', $errors->get('shortName'))){
            $this->messages->write('error', "Program with short name {$input->shortName} already exists.");
            return;
          }
        }
        throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem creating a new program.');
      }
    }
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Programs';
    $auth->addAction('index', new ActionAuth('List Programs'));
    $auth->addAction('edit', new ActionAuth('Edit Program'));
    $auth->addAction('new', new ActionAuth('Create New Program'));
    return $auth;
  }
}
?>