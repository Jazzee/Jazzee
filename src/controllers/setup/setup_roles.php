<?php
/**
 * Setup Program Roles
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage setup
 */
class SetupRolesController extends \Jazzee\AdminController {
  const MENU = 'Setup';
  const TITLE = 'Program Roles';
  const PATH = 'setup/roles';
  
  /**
   * List all the Roles
   */
  public function actionIndex(){
    $this->setVar('roles', Doctrine::getTable('Role')->findByProgramID($this->program->id));
  }
  
  /**
   * Edit a role
   * @param integer $roleID
   */
   public function actionEdit($roleID){ 
    if($role = Doctrine::getTable('Role')->findOneByIDAndProgramID($roleID, $this->program->id)){
      $form = new Form;
      $form->action = $this->path("setup/roles/edit/{$role->id}");
      $field = $form->newField(array('legend'=>"Edit {$role->name} role"));
      $element = $field->newElement('TextInput','name');
      $element->label = 'Role Name';
      $element->addValidator('NotEmpty');
      $element->value = $role->name;
      $auths = $this->getAuths();
      foreach($auths as $controllerName => $controller){
        if($this->checkIsAllowed($controllerName, 'index')){
          $element = $field->newElement('CheckboxList',$controllerName);
          $element->label = "{$controller->name} actions";
          foreach($controller->getActions() as $actionName => $action){
            if($this->checkIsAllowed($controllerName, $actionName))
              $element->addItem($actionName, $action->name);
          }
          $values = array();
          foreach($role->Actions as $action){
            if($action->controller == $controllerName)
              $values[] = $action->action;
          }
          $element->value = $values;
        }
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
        $this->redirect($this->path("setup/roles/"));
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
    $form->action = $this->path("setup/roles/new/");
    $field = $form->newField(array('legend'=>"New Program Role"));
    $element = $field->newElement('TextInput','name');
    $element->label = 'Role Name';
    $element->addValidator('NotEmpty');

    $form->newButton('submit', 'Add Role');
    $this->setVar('form', $form); 
    if($input = $form->processInput($this->post)){
      $role = new Role;
      $role->global = false;
      $role->programID = $this->program->id;
      $role->name = $input->name;
      $role->save();
      $this->messages->write('success', "Role Saved Successfully");
      $this->redirect($this->path("setup/roles/"));
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
    //all of the admin controllers, shoudl probably autodetect
    $controllers = array(
      'manage_users',
      'manage_configuration',
      'manage_roles',
      'manage_scores',
      'manage_programs',
      'manage_cycles',
      'manage_pagetypes',
      'manage_elementtypes',
      'applicants_view',
      'setup_application',
      'setup_pages',
      'setup_roles',
      'setup_users',
      'admin_profile',
      'admin_changecycle',
      'admin_changeprogram'
    );
    foreach($controllers as $controller){
      Lvc_FoundationConfig::includeController($controller);
      $auths[$controller] = call_user_func(array(Lvc_Config::getControllerClassName($controller), 'getControllerAuth')); 
    }
    return $auths;
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Program Roles';
    $auth->addAction('index', new ActionAuth('View Roles'));
    $auth->addAction('edit', new ActionAuth('Edit'));
    $auth->addAction('new', new ActionAuth('Create New'));
    return $auth;
  }
}
?>