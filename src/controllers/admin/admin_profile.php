<?php
/**
 * Current user can edit their profile
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 */
class AdminProfileController extends AdminController {
  const MENU = 'My Account';
  const TITLE = 'My Profile';
  const PATH = 'admin/profile';
  
  /**
   * Display index
   */
  public function actionIndex(){
    $this->setVar('user', $this->user);
    $defaultProgram = Doctrine_Core::getTable('Program')->find($this->user->defaultProgram);
    $this->setVar('defaultProgram', $defaultProgram->name);
    $defaultCycle = Doctrine_Core::getTable('Cycle')->find($this->user->defaultCycle);
    $this->setVar('defaultCycle', $defaultCycle->name);
  }
  
  /**
   * Edit profile information
   */
  public function actionEditProfile(){
    $form = new Form;
    $form->action = $this->path("admin/profile/editProfile");
    $field = $form->newField(array('legend'=>'Edit Profile'));
    $element = $field->newElement('TextInput','firstName');
    $element->label = 'First Name';
    $element->addValidator('NotEmpty');
    $element->value = $this->user->firstName;
    
    $element = $field->newElement('TextInput','lastName');
    $element->label = 'Last Name';
    $element->addValidator('NotEmpty');
    $element->value = $this->user->lastName;
    
    $element = $field->newElement('SelectList','program');
    $element->label = 'Default Program';
    $element->addValidator('NotEmpty');
    foreach(Doctrine_Core::getTable('Program')->findAll() as $program){
      if(!$program->expires OR strtotime($program->expires) > time())
        $element->addItem($program->id, $program->name);
    }
    $element->value = $this->user->defaultProgram;
    
    $element = $field->newElement('SelectList','cycle');
    $element->label = 'Default Cycle';
    $element->addValidator('NotEmpty');
    foreach(Doctrine_Core::getTable('Cycle')->findAll() as $cycle){
      $element->addItem($cycle->id, $cycle->name);
    }
    $element->value = $this->user->defaultCycle;
    
    $form->newButton('submit', 'Save Changes');
    
    if($input = $form->processInput($this->post)){
      $this->user->firstName = $input->firstName;
      $this->user->lastName = $input->lastName;
      $this->user->defaultProgram = $input->program;
      $this->user->defaultCycle = $input->cycle;
      
      $this->user->save();
      $this->messages->write('success', 'Profile Updated');
      $this->redirect($this->path("admin/profile"));
      $this->afterAction();
      exit(0);
    }
    
    $this->setVar('form', $form);
  }
  
  /**
   * Change Password
   */
  public function actionChangePassword(){
    $form = new Form;
    $form->action = $this->path("admin/profile/changePassword");
    $field = $form->newField(array('legend'=>'Change Password'));
    $element = $field->newElement('PasswordInput','oldpass');
    $element->label = 'Old Password';
    
    $element = $field->newElement('PasswordInput','newpass');
    $element->label = 'New Password';
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('PasswordInput','confirmnew');
    $element->label = 'Confirm New Password';
    $element->addValidator('NotEmpty');
    $element->addValidator('SameAs', 'newpass');
    
    $form->newButton('submit', 'Change Password');
    $this->setVar('form', $form);
    if($input = $form->processInput($this->post)){
      if(!$this->user->checkPassword($input->oldpass)){
        $form->elements['oldpass']->addMessage('Password Incorect');
        return false;
      }
      $this->user->password = $input->newpass;
      $this->user->save();
      $this->messages->write('success', 'Password Changed');
      $this->redirect($this->path("admin/profile"));
      $this->afterAction();
      exit(0);
    }
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'User Profile';
    $auth->addAction('index', new ActionAuth('View Profile'));
    $auth->addAction('editProfile', new ActionAuth('Edit Own Profile'));
    $auth->addAction('changePassword', new ActionAuth('Change Own Password'));
    return $auth;
  }
}
?>