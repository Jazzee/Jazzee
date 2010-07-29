<?php
/**
 * Manage Users
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
class ManageUsersController extends ManageController {
  /**
   * Search for a user to modify
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("manage/users/index");
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
   public function actionEdit($userID){ 
    if($user = Doctrine::getTable('User')->find($userID)){
      $form = new Form;
      
      $form->action = $this->path("manage/users/edit/{$userID}");
      $field = $form->newField(array('legend'=>"Edit User {$user->firstName} {$user->lastName}"));
      $element = $field->newElement('TextInput','firstName');
      $element->label = 'First Name';
      $element->addValidator('NotEmpty');
      $element->value = $user->firstName;
  
      $element = $field->newElement('TextInput','lastName');
      $element->label = 'Last Name';
      $element->addValidator('NotEmpty');
      $element->value = $user->lastName;
      
      $element = $field->newElement('TextInput','email');
      $element->label = 'Email Address';
      $element->addValidator('NotEmpty');
      $element->addFilter('Lowercase');
      $element->addValidator('EmailAddress');
      $element->value = $user->email;
      
      $element = $field->newElement('CheckboxList','roles');
      $element->label = 'Global Roles';
      foreach(Doctrine::getTable('Role')->findByGlobal(true) as $role){
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
        $user->firstName = $input->firstName;
        $user->lastName = $input->lastName;
        $user->email = $input->email;
        foreach($user->Roles as $id => $role){
          if($role->Role->global)
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
        $this->redirect($this->path("manage/users"));
        $this->afterAction();
        exit(); 
      }
    } else {
      $this->messages->write('error', "Error: User #{$userID} does not exist.");
    }
  }
   
  /**
   * Create a new user
   */
   public function actionNew(){
    $form = new Form;
    
    $form->action = $this->path("manage/users/new");
    $field = $form->newField(array('legend'=>"New User"));
    $element = $field->newElement('TextInput','firstName');
    $element->label = 'First Name';
    $element->addValidator('NotEmpty');

    $element = $field->newElement('TextInput','lastName');
    $element->label = 'Last Name';
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('TextInput','email');
    $element->label = 'Email Address';
    $element->addValidator('NotEmpty');
    $element->addValidator('EmailAddress');
    $element->addFilter('Lowercase');
 
    $form->newButton('submit', 'Add User');
    $this->setVar('form', $form);  
    if($input = $form->processInput($this->post)){
      $user = new User;
      $user->firstName = $input->firstName;
      $user->lastName = $input->lastName;
      $user->email = $input->email;
      try {
        $user->save();
        $this->messages->write('success', "User Added Successfully");
        $text = "An account has been created for you on the Application Management Sytem.  Please click the following link to access the online system and setup your account.  You may need to copy and paste this link into your browser. \n";
        $this->emailUser($user, 'Account Created', $text);
        $this->redirect($this->path("manage/users"));
        $this->afterAction();
        exit(); 
      }
      catch (Doctrine_Validator_Exception $e){
        $records = $e->getInvalidRecords();
        $errors = $records[0]->getErrorStack();
        if($errors->contains('email')){
          if(in_array('unique', $errors->get('email'))){
            $this->messages->write('error', "User with email address {$input->email} already exists.");
            return;
          }
        }
        throw new Jazzee_Exception($e->getMessage(),E_USER_ERROR,'There was a problem saving the user.');
      }
    }
  }
  
  /**
   * Reset User Password
   * @param integer $userID
   */
  public function actionReset($userID){ 
    if($user = Doctrine::getTable('User')->find($userID)){
      $user->password = null;
      $text = "Your password has been reset on the Application Management Sytem.  Please click the following link to access the online system to select a new password.  You may need to copy and paste this link into your browser. \n";
      $this->emailUser($user, 'Password Reset', $text);
      $this->redirect($this->path("manage/users"));
      $this->afterAction();
      exit(); 
    }
  }
  
  /**
   * Send Activation email to a user
   * @param User $user
   * @param string $subject Email message subject
   * @param string $text Email message text
   */
  protected function emailUser(User $user, $subject, $text){
    /* Create a random unique token
     * 1) Random number(for security) + uniqid(so it is unique for this time)
     * 2) Hash them to get a fixed length string
     * 3) Shorten the activation URL to a reasonable length between 8-15 charecters so it fits in a single line of an email message
     * 4) Prepend the userID to be sure this is still unique (sincle the hash plus shortening MIGHT result in a collision)
     */
    $user->activateToken = $user->id . substr(sha1(mt_rand() . uniqid()), 0, rand(8,15));
    $user->save();
    $text = 'Dear ' . $user->firstName . ' ' . $user->lastName . ",\n" . $text;
    $text .= SERVER_URL . $this->path('admin/activate/index/' . $user->activateToken);
    $message = new EmailMessage;
    $message->to($user->email, "{$user->firstName} {$user->lastName}");
    $message->from($this->user->email, "{$this->user->firstName} {$this->user->lastName}");
    $message->subject = $subject;
    $message->body = $text;
    if(!JazzeeMail::getInstance()->send($message)){
      $this->messages->write('error', 'Email message could not be sent');
      return false;
    }
    $this->messages->write('success', 'Email sent to ' . $user->email);
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Users';
    $auth->addAction('index', new ActionAuth('Find'));
    $auth->addAction('edit', new ActionAuth('Edit'));
    $auth->addAction('new', new ActionAuth('Create New'));
    $auth->addAction('reset', new ActionAuth('Reset Password'));
    return $auth;
  }
}
?>