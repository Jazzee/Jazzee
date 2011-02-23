<?php
/**
 * Change the a users current cycle
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 */
class AdminChangecycleController extends AdminController {
  const MENU = 'My Account';
  const TITLE = 'Change Cycle';
  const PATH = 'admin/changecycle';
  
  /**
   * Display index
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("admin/changecycle");
    $field = $form->newField(array('legend'=>'Change Cycle'));
    $element = $field->newElement('SelectList','cycle');
    $element->label = 'Cycle';
    $element->addValidator('NotEmpty');
    $table = Doctrine_Core::getTable('Cycle');
    $table->setAttribute(Doctrine_Core::ATTR_COLL_KEY, 'id');
    $cycles = $table->findAll();
    foreach($cycles as $cycle){
      if(strtotime($cycle['start']) < time() AND strtotime($cycle['end']) > time())
        $element->addItem($cycle['id'], $cycle['name']);
    }
    $element->value = $this->session->cycleID;
    //only ask if the user already has a default cycle
    if($this->user->defaultCycle){
      $element = $field->newElement('RadioList','default');
      $element->label = 'Set as your default';
      $element->addItem(0, 'No');
      $element->addItem(1, 'Yes');
      $element->addValidator('NotEmpty');
    }
    $form->newButton('submit', 'Change Cycle');
    
    if($input = $form->processInput($this->post)){
      if($cycles->contains($input->cycle)){
        $this->session->cycleID = $input->cycle;
        $cycle = $cycles->get($input->cycle);
        $this->messages->write('success', "Cycle changed to {$cycle->name}");
        //if they wish it, or if the user has no default cycle
        if(!$this->user->defaultCycle OR $input->default){
          $this->user->defaultCycle = $input->cycle;
          $this->user->save();
          $this->messages->write('success', "Default cycle changed to {$cycle->name}");
        }
        $this->redirect($this->path("admin/welcome"));
        $this->afterAction();
        exit(0);
      }
    }
    
    $this->setVar('form', $form);
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Change Cycle';
    $auth->addAction('index', new ActionAuth('Change Own Cycle'));
    return $auth;
  }
}
?>