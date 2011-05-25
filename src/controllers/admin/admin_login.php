<?php
/**
 * Authentication for Administration
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminLoginController extends AdminController {
  protected $sessionName = 'guest';
  protected $layout = 'default';
  /**
   * Login for administrators
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("admin/login");
    $field = $form->newField(array('legend'=>'Login'));
    $element = $field->newElement('TextInput','email');
    $element->label = 'Email Address';
    $element->addValidator('NotEmpty');
    $element->addFilter('Lowercase');
     
    $element = $field->newElement('PasswordInput','password');
    $element->label = 'Password';
    $element->addValidator('NotEmpty');

    $form->newButton('submit', 'Login');
    if($input = $form->processInput($this->post)){
      $user = Doctrine_Core::getTable('User')->findOneByEmail($input->email);
      if($user){
        if($user->checkPassword($input->password)){
          $s = Session::getInstance();
          $session = $s->getStore('admin');
          $session->userID = $user->id;
          $session->programID = $user->defaultProgram;
          $session->cycleID = $user->defaultCycle;
          $session->lastLogin = $user->lastLogin;
          $session->lastLogin_ip = $user->lastLogin_ip;
          $session->lastFailedLogin_ip = $user->failedLoginAttempts;
          $session->failedLoginAttempts = $user->lastFailedLogin_ip;
          
          $user->lastLogin = date('Y-m-d H:i:s');
          $user->lastLogin_ip = $_SERVER['REMOTE_ADDR'];
          $user->lastFailedLogin_ip = null;
          $user->failedLoginAttempts = 0;
          $user->save();
          
          $this->redirect($this->path("admin/welcome"));
          $this->afterAction();
          exit(0);
        } else {
          $user->failedLoginAttempts++;
          $user->lastFailedLogin_ip = $_SERVER['REMOTE_ADDR'];
          $user->save();
        }
      }
      $this->messages->write('error', 'Incorrect username or password.');
      sleep(5); //wait 5 seconds before announcing failure to slow down guessing.
    }
    $this->setVar('form', $form);
    $this->setLayoutVar('layoutTitle', 'Login');
  }
  
  
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    return true; //everyone is allowed to login
  }
  
  /**
   * Get Navigation
   */
  public function getNavigation(){
    $navigation = new Navigation();
    $menu = $navigation->newMenu();
    $menu->title = 'Navigation';
    $menu->newLink(array('text'=>'Login', 'href'=>$this->path("admin/login")));
//    $menu->newLink(array('text'=>'Request Account', 'href'=>$this->path("manage/auth/request")));
//    $menu->newLink(array('text'=>'Reset Password', 'href'=>$this->path("manage/auth/reset")));
    return $navigation;
  }
  
}
?>
