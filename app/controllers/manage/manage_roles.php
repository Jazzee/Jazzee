<?php
/**
 * Manage Global Roles
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManageRolesController extends ManageController {
  const MENU = 'Manage';
  const TITLE = 'Roles';
  const PATH = 'manage/roles';
  
  /**
   * List all the Roles
   */
  public function actionIndex(){
    $this->setVar('roles', Doctrine::getTable('Role')->findByGlobal(true));
  }
  
  /**
   * Edit a role
   * @param integer $roleID
   */
   public function actionEdit($roleID){ 
    if($role = Doctrine::getTable('Role')->find($roleID)){
      $form = new Form;
      $form->action = $this->path("manage/roles/edit/{$role->id}");
      $field = $form->newField(array('legend'=>"Edit {$role->name} role"));
      $element = $field->newElement('TextInput','name');
      $element->label = 'Role Name';
      $element->addValidator('NotEmpty');
      $element->value = $role->name;
      $auths = $this->getAuths();
      foreach($auths as $controllerName => $controller){
        $element = $field->newElement('CheckboxList',$controllerName);
        $element->label = "{$controller->name} actions";
        foreach($controller->getActions() as $actionName => $action){
          $element->addItem($actionName, $action->name);
        }
        $values = array();
        foreach($role->Actions as $action){
          if($action->controller == $controllerName)
            $values[] = $action->action;
        }
        $element->value = $values;
      }
      $form->newButton('submit', 'Edit Role');
      $this->setVar('form', $form);
      if($input = $form->processInput($this->post)){
        $role->name = $input->name;
        $role->Actions->clear();
        foreach($auths as $controllerName => $controller){
          if(!empty($input->$controllerName)){
            foreach($input->$controllerName as $actionName){
              $action = new RoleAction;
              $action->controller = $controllerName;
              $action->action = $actionName;
              $role->Actions[] = $action;
            }
          }
        }
        $role->save();
        $this->messages->write('success', "Role Saved Successfully");
        $this->redirect($this->path("manage/roles/"));
        $this->afterAction();
        exit(); 
      }
    } else {
      $this->messages->write('error', "Error: Role #{$roleID} does not exist.");
    }
  }
   
  /**
   * Create a new pagetype
   */
   public function actionNew(){
    $form = new Form;
    $form->action = $this->path("manage/roles/new/");
    $field = $form->newField(array('legend'=>"New Global Role"));
    $element = $field->newElement('TextInput','name');
    $element->label = 'Role Name';
    $element->addValidator('NotEmpty');

    $form->newButton('submit', 'Add Role');
    $this->setVar('form', $form); 
    if($input = $form->processInput($this->post)){
      $role = new Role;
      $role->global = true;
      $role->name = $input->name;
      $role->save();
      $this->messages->write('success', "Role Saved Successfully");
      $this->redirect($this->path("manage/roles/"));
      $this->afterAction();
      exit(); 
    }
  }
  
  /**
   * Get All of the possible controllers and actions
   * @return array of ControllerAuths
   */
  protected function getAuths(){
    $auths = array();
    foreach($this->listControllers() as $controller){
      FoundationVC_Config::includeController($controller);
      $auth = call_user_func(array(Lvc_Config::getControllerClassName($controller), 'getControllerAuth'));
      if($auth instanceof ControllerAuth) $auths[$controller] = $auth;
    }
    return $auths;
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Roles';
    $auth->addAction('index', new ActionAuth('View Roles'));
    $auth->addAction('edit', new ActionAuth('Edit'));
    $auth->addAction('new', new ActionAuth('Create New'));
    return $auth;
  }
}
?>