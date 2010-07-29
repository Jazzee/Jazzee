<?php
/**
 * Activate new account or finish password reset for an old account
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminActivateController extends AdminController {
  protected $sessionName = 'guest';
  protected $layout = 'default';
  
  /**
   * Check the token and show the password form
   * @param string $token
   */
  public function actionIndex($token){
    if($token AND $users = Doctrine::getTable('User')->findByActivateToken($token) AND $users->count() == 1){
      $form = new Form;
      $form->action = $this->path("admin/activate/index/{$token}");
      $field = $form->newField(array('legend'=>'Choose a password'));
       
      $element = $field->newElement('PasswordInput','password');
      $element->label = 'Password';
      $element->addValidator('NotEmpty');
      
      $element = $field->newElement('PasswordInput','passwordConfirm');
      $element->label = 'Confirm Password';
      $element->addValidator('NotEmpty');
      $element->addValidator('SameAs', 'password');
      
      //setup recaptcha element keys
      Form_CaptchaElement::setKeys($this->config->captcha_private_key, $this->config->captcha_public_key);
      $element = $field->newElement('Captcha','captcha');
      
      $form->newButton('submit', 'Save');
      if($input = $form->processInput($this->post)){
        $user = $users->getFirst();
        $user->password = $input->password;
        $user->activateToken = null;
        $user->save();
        $this->messages->write('success', "Your password has been saved successfully.");
        $this->messages->write('success', "Please login with your email address and new password.");
        $this->redirect($this->path("admin/login"));
        $this->afterAction();
        exit();
      }
      $this->setVar('form', $form);
    } else {
      sleep(5); //sleep to slow down guessing
      throw new Lvc_Exception("Invalid activation token: {$token}");
    }
  }
  
  public static function isAllowed($controller, $action, $user, $programID, $cycleID, $actionParams){
    return true; //everyone is allowed to activate
  }
  /**
   * Get Navigation
   */
  public function getNavigation(){
    $navigation = new Navigation();
    $menu = $navigation->newMenu();
    $menu->title = 'Navigation';
    $menu->newLink(array('text'=>'Login', 'href'=>$this->path("manage/auth/login")));
//    $menu->newLink(array('text'=>'Request Account', 'href'=>$this->path("manage/auth/request")));
//    $menu->newLink(array('text'=>'Reset Password', 'href'=>$this->path("manage/auth/reset")));
    return $navigation;
  }
}
?>