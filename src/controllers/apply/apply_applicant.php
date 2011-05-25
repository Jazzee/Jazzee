<?php
/**
 * Authentication and Creation of applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class ApplyApplicantController extends JazzeeController {
  /**
   * The application
   * @var Application
   */
  protected $application;
  
  /**
   * Before any action do some setup
   * If we know the program and cycle load the applicant var
   * If we only know the program fill that in
   * @return null
   */
  protected function beforeAction(){
    parent::beforeAction();
    $program = Doctrine::getTable('Program')->findOneByShortName($this->actionParams['programShortName']);
    $cycle = Doctrine::getTable('Cycle')->findOneByName($this->actionParams['cycleName']);
    $this->application = Doctrine::getTable('Application')->findOneByProgramIDAndCycleID($program->id, $cycle->id);
    if(!$this->application->published){
      $this->redirectPath("apply/{$this->application->Program->shortName}/");
    }
    $this->setLayoutVar('layoutTitle', $this->application->Cycle->name . ' ' . $this->application->Program->name . ' Application');
  }
  
  /**
   * Authenticate applicants
   * @param string $programShortName
   * @param string $cycleName
   * @return null
   */
  public function actionLogin() {
    $form = new Form;
    $form->action = $this->path("apply/{$this->application->Program->shortName}/{$this->application->Cycle->name}/applicant/login");
    $field = $form->newField(array('legend'=>'Login'));
    $element = $field->newElement('TextInput','email');
    $element->label = 'Email Address';
    $element->addValidator('NotEmpty');
    $element->addFilter('Lowercase');
     
    $element = $field->newElement('PasswordInput','password');
    $element->label = 'Password';

    $form->newButton('submit', 'Login');
    
    if($input = $form->processInput($this->post)){
      $applicant = Doctrine::getTable('Applicant')->findOneByEmailAndApplicationID($input->email,$this->application->id);
      if($applicant){
        if($applicant->checkPassword($input->password)){
          $applicant->lastLogin = date('Y-m-d H:i:s', time());
          $applicant->lastLogin_ip = $_SERVER['REMOTE_ADDR'];
          $applicant->lastFailedLogin_ip = null;
          $applicant->failedLoginAttempts = 0;
          $applicant->save();
          $s = Session::getInstance();
          $session = $s->getStore('apply', $this->config->session_lifetime);
          $session->applicantID = $applicant->id;
          $this->messages->write('success', "Welcome to the {$this->application->Program->name} application.");
          $this->redirect($this->path("apply/{$this->application->Program->shortName}/{$this->application->Cycle->name}/page/{$this->application->findPagesByWeight()->getFirst()->id}"));
          return;
        }
        $applicant->failedLoginAttempts++;
        $applicant->lastFailedLogin_ip = $_SERVER['REMOTE_ADDR'];
        $applicant->save();
      }
      $this->messages->write('error', 'Incorrect username or password.');
      sleep(5); //wait 5 seconds before announcing failure to slow down guessing.
    }
    $this->setVar('form', $form);
    
  }
  
  /**
   * Reset applicant password
   * @param string $programShortName
   * @param string $cycleName
   * @return null
   */
  public function actionReset($programShortName, $cycleName) {
//    $form = new Form;
//    $form->attr('action', $this->url('manage_user', 'reset'));
//    $field = $form->newField();
//    $field->attr('legend', 'Reset Password');
//    $email = $field->newElement('TextInput');
//    $email->attr('name', 'email');
//    $email->attr('label', 'Email Address');
//    $email->addValidator('NotEmpty');
//    $email->addFilter('Lowercase');
//    if($input = $this->getFormInput($form)){
//      
//    }
//    $this->setVar('form', $form->render('html'));
  }
  
  /**
   * Create a new application
   * @param string $programShortName
   * @param string $cycleName
   * @return null
   */
  public function actionNew($programShortName, $cycleName) {
    $form = new Form;
    $form->action = $this->path("apply/{$programShortName}/{$cycleName}/applicant/new");
    $field = $form->newField(array('legend'=>'Create New Application'));
    $el = $field->newElement('TextInput', 'first');
    $el->label = 'First Name';
    $el->addValidator('NotEmpty');
    
    $el = $field->newElement('TextInput', 'middle');
    $el->label = 'Middle Name';
    
    $el = $field->newElement('TextInput','last');
    $el->label = 'Last Name';
    $el->addValidator('NotEmpty');
        
    $el = $field->newElement('TextInput', 'suffix');
    $el->label = 'Suffix';
    $el->format = 'Example: Jr., III';

    $el = $field->newElement('TextInput', 'email');
    $el->label = 'Email Address';
    $el->addValidator('NotEmpty');
    $el->addValidator('EmailAddress');
    $el->addFilter('Lowercase');
    
    $el = $field->newElement('PasswordInput', 'password');
    $el->label = 'Password';
    $el->addValidator('NotEmpty');
    
    $el = $field->newElement('PasswordInput','confirm-password');
    $el->label = 'Confirm Password';
    $el->addValidator('NotEmpty');
    $el->addValidator('SameAs', 'password');
    
    //setup recaptcha element keys
    Form_CaptchaElement::setKeys($this->config->captcha_private_key, $this->config->captcha_public_key);
    $el = $field->newElement('Captcha','captcha');
    
    $form->newButton('submit', 'Create Account');
    if($input = $form->processInput($this->post)){
      $applicant = new Applicant;
      $applicant->applicationID = $this->application->id;
      $applicant->email = $input->email;
      $applicant->password = $input->password;
      $applicant->firstName = $input->first;
      $applicant->middleName = $input->middle;
      $applicant->lastName = $input->last;
      $applicant->suffix = $input->suffix;
      
      try {
        $applicant->save();
        $this->messages->write('success', "Welcome to the {$this->application['Program']->name} application.");
        $s = Session::getInstance();
        $session = $s->getStore('apply');
        $session->applicantID = $applicant->id;
        $this->redirect($this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/page/{$this->application['Pages']->getFirst()->id}"));
        exit();
      }
      catch (Doctrine_Connection_Exception $e){
        if($e->getPortableCode() == Doctrine::ERR_ALREADY_EXISTS){
          $this->messages->write('error', "You have already started a {$this->application['Program']->name} application.  Please login to retrieve it.");
          $this->redirect($this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/applicant/login"));
          exit();
        }
        throw new Jazzee_Exception($e->getPortableMessage(),E_USER_ERROR,'There was a problem saving your application.');
      }
    }
    $this->setVar('form', $form);
  }
  
  public function actionLogout($programShortName,$cycleName){
    $s = Session::getInstance()->getStore('apply')->expire();
  }
  
  public function getNavigation(){
    $path = "apply/{$this->application->Program->shortName}/{$this->application->Cycle->name}";
    $navigation = new Navigation();
    $menu = $navigation->newMenu();
    $menu->title = 'Navigation';
    $menu->newLink(array('text'=>'Welcome', 'href'=>$this->path("{$path}/")));
    $menu->newLink(array('text'=>'Other Cycles', 'href'=>$this->path("apply/{$this->application->Program->shortName}/")));
    $menu->newLink(array('text'=>'Returning Applicants', 'href'=>$this->path("{$path}/applicant/login/")));
    $menu->newLink(array('text'=>'Start a New Application', 'href'=>$this->path("{$path}/applicant/new/")));
    return $navigation;
  } 
}