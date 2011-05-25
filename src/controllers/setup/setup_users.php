<?php
/**
 * Setup Program Users
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage setup
 */
class SetupUsersController extends SetupController {
  const MENU = 'Setup';
  const TITLE = 'Program Users';
  const PATH = 'setup/users';
  
  /**
   * Search for a user to modify
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("setup/users/index");
    $field = $form->newField(array('legend'=>'Search Users'));
    $element = $field->newElement('TextInput','firstName');
    $element->label = 'First Name';

    $element = $field->newElement('TextInput','lastName');
    $element->label = 'Last Name';
    
    $form->newButton('submit', 'Search');
    
    $results = array();  //array of all the users who match the search
    if($input = $form->processInput($this->post)){
      $q = Doctrine_Query::create()
            ->from('User u')
            ->where('u.firstName LIKE ?', "%{$input->firstName}%")
            ->andwhere('u.lastName LIKE ?', "%{$input->lastName}%")
            ->orderby('u.lastName, u.firstName');
      $results = $q->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
    }
    $this->setVar('results', $results);
    $this->setVar('form', $form);
  }
  
  /**
   * Edit a user
   * @param integer $userID
   */
   public function actionProgramRoles($userID){ 
    if($user = Doctrine::getTable('User')->find($userID)){
      $form = new Form;
      $form->action = $this->path("setup/users/programRoles/{$userID}");
      $field = $form->newField(array('legend'=>"Roles for {$user->firstName} {$user->lastName}"));

      $element = $field->newElement('CheckboxList','roles');
      $element->label = 'Roles';
      foreach(Doctrine::getTable('Role')->findByProgramID($this->program->id) as $role){
        $element->addItem($role->id, $role->name);
      }
      $values = array();
      foreach($user->Roles as $role){
        $values[] = $role->Role->id;
      }
      $element->value = $values;
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        foreach($user->Roles as $id => $role){
          if($role->Role->programID == $this->program->id)
            $user->Roles->remove($id);
        }
        if(!empty($input->roles)){
          foreach($input->roles as $roleID){
            $role = new UserRole;
            $role->roleID = $roleID;
            $user->Roles[] = $role;
          }
        }
        $user->save();
        $this->messages->write('success', "Changes Saved Successfully");
        $this->redirect($this->path("setup/users"));
        $this->afterAction();
        exit(); 
      }
    } else {
      $this->messages->write('error', "Error: User #{$userID} does not exist.");
    }
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Program Users';
    $auth->addAction('index', new ActionAuth('Find'));
    $auth->addAction('programRoles', new ActionAuth('Modify Program Roles'));
    return $auth;
  }
}
?>