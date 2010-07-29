<?php
/**
 * Manage Cycles
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
class ManageCyclesController extends ManageController {
  /**
   * List cycles
   */
  public function actionIndex(){
    $this->setVar('cycles', Doctrine::getTable('Cycle')->findAll(Doctrine::HYDRATE_ARRAY));
  }
  
  /**
   * Edit a cycle
   * @param integer $cycleID
   */
   public function actionEdit($cycleID){ 
    if($cycle = Doctrine::getTable('Cycle')->find($cycleID)){
      $form = new Form;
      
      $form->action = $this->path("manage/cycles/edit/{$cycleID}");
      $field = $form->newField(array('legend'=>"Edit Cycle {$cycle->name}"));
      $element = $field->newElement('TextInput','name');
      $element->label = 'Cycle Name';
      $element->addValidator('NotEmpty');
      $element->addFilter('UrlSafe');
      $element->value = $cycle->name;
      
      $element = $field->newElement('TextInput','start');
      $element->label = 'Start Date';
      $element->addValidator('Date');
      $element->addValidator('DateBeforeElement','end');
      $element->addValidator('NotEmpty');
      $element->addFilter('DateFormat','Y-m-d');
      $element->value = date('m/d/Y',strtotime($cycle->start));
      
      $element = $field->newElement('TextInput','end');
      $element->label = 'End Date';
      $element->addValidator('Date');
      $element->addValidator('NotEmpty');
      $element->addFilter('DateFormat','Y-m-d');
      $element->value = date('m/d/Y',strtotime($cycle->end));
  
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        $cycle->name = $input->name;
        $cycle->start = $input->start;
        $cycle->end = $input->end;
        
        $cycle->save();
        $this->messages->write('success', "Changes Saved Successfully");
        $this->redirect($this->path("manage/cycles"));
        $this->afterAction();
        exit(); 
      }
    } else {
      $this->messages->write('error', "Error: Cycle #{$cycleID} does not exist.");
    }
  }
   
  /**
   * Create a new cycle
   */
   public function actionNew(){
    $form = new Form;
    $form->action = $this->path("manage/cycles/new/");
    $field = $form->newField(array('legend'=>"New Application Cycle"));
    $element = $field->newElement('TextInput','name');
    $element->label = 'Cycle Name';
    $element->addValidator('NotEmpty');
    $element->addFilter('UrlSafe');
    
    $element = $field->newElement('TextInput','start');
    $element->label = 'Start Date';
    $element->addValidator('Date');
    $element->addValidator('DateBeforeElement','end');
    $element->addValidator('NotEmpty');
    $element->addFilter('DateFormat','Y-m-d');
    
    $element = $field->newElement('TextInput','end');
    $element->label = 'End Date';
    $element->addValidator('Date');
    $element->addValidator('NotEmpty');
    $element->addFilter('DateFormat','Y-m-d');

    $form->newButton('submit', 'Save Changes');
    $this->setVar('form', $form); 
    if($input = $form->processInput($this->post)){
      $cycle = new Cycle;
      $cycle->name = $input->name;
      $cycle->start = $input->start;
      $cycle->end = $input->end;
      try {
        $cycle->save();
        $this->messages->write('success', "Changes Saved Successfully");
        $this->redirect($this->path("manage/cycles"));
        $this->afterAction();
        exit(); 
      }
      catch (Doctrine_Validator_Exception $e){
        $records = $e->getInvalidRecords();
        $errors = $records[0]->getErrorStack();
        if($errors->contains('name')){
          if(in_array('unique', $errors->get('name'))){
            $this->messages->write('error', "Cycle with name {$input->name} already exists.");
            return;
          }
        }
        throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem creating a new cycle.');
      }
    }
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Cycles';
    $auth->addAction('index', new ActionAuth('View Cycles'));
    $auth->addAction('edit', new ActionAuth('Edit Cycle'));
    $auth->addAction('new', new ActionAuth('Create New Cycle'));
    return $auth;
  }
}
?>