<?php
/**
 * Change the a users current program and defautl program
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @todo When authentication is working only allow a user to change to a program in which they have priviliges
 */
class AdminChangeprogramController extends AdminController {
  /**
   * Display index
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("admin/changeprogram");
    $field = $form->newField(array('legend'=>'Change Program'));
    $element = $field->newElement('SelectList','program');
    $element->label = 'Program';
    $element->addValidator('NotEmpty');
    $table = Doctrine_Core::getTable('Program');
    $table->setAttribute(Doctrine_Core::ATTR_COLL_KEY, 'id');
    
    $programs = $table->findAll();
    foreach($programs as $program){
      if(!$program->expires OR strtotime($program->expires) > time())
        $element->addItem($program->id, $program->name);
    }
    $element->value = $this->session->programID;
    //only ask if the user already has a default cycle
    if($this->user->defaultProgram){
      $element = $field->newElement('RadioList','default');
      $element->label = 'Set as your default';
      $element->addItem(0, 'No');
      $element->addItem(1, 'Yes');
      $element->addValidator('NotEmpty');
    }
    $form->newButton('submit', 'Change Program');
    
    if($input = $form->processInput($this->post)){
      if($programs->contains($input->program)){
        $this->session->programID = $input->program;
        $program = $programs->get($input->program);
        $this->messages->write('success', "Program changed to {$program->name}");
        //if they wish it, or if the user has no default cycle
        if(!$this->user->defaultProgram OR $input->default){
          $this->user->defaultProgram = $input->program;
          $this->user->save();
          $this->messages->write('success', "Default program changed to {$program->name}");
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
    $auth->name = 'Change Program';
    $auth->addAction('index', new ActionAuth('Change own program'));
    return $auth;
  }
}
?>