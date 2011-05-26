<?php
/**
 * Authentication and Creation of applicants
 */
class ApplyApplicantController extends \Jazzee\Controller {
  /**
   * The application
   * @var \Jazzee\Entity\Application
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
    $this->application = $this->_em->getRepository('Jazzee\Entity\Application')->findEasy($this->actionParams['programShortName'],$this->actionParams['cycleName']);
    if(!$this->application) throw new \Jazzee\Exception("Unable to load {$this->actionParams['programShortName']} {$this->actionParams['cycleName']} application", E_USER_NOTICE, 'That is not a valid application');
    if(!$this->application->isPublished()){
      $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/');
    }
    $this->setLayoutVar('layoutTitle', $this->application->getCycle()->getName() . ' ' . $this->application->getProgram()->getName() . ' Application');
    $this->setLayoutVar('navigation', $this->getNavigation());
  }
  
  /**
   * Authenticate applicants
   * @param string $programShortName
   * @param string $cycleName
   * @return null
   */
  public function actionLogin() {
    $form = new \Foundation\Form();
    $form->setAction($this->path('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/login'));
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Login');
    $form->addField($field);
    
    $element = $field->newElement('TextInput','email');
    $element->setLabel('Email Address');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Lowercase($element));
     
    $element = $field->newElement('PasswordInput','password');
    $element->setLabel('Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Login');
    
    if($input = $form->processInput($this->post)){
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneByEmailAndApplication($input->get('email'), $this->application);
      if($applicant){
        if($applicant->checkPassword($input->get('password'))){
          $applicant->login();
          
          $store = $this->_session->getStore('apply', $this->_config->getApplicantSessionLifetime());
          $store->applicantID = $applicant->getId();
          $this->addMessage('success', 'Welcome to the ' . $this->application->getProgram()->getName() . ' application.');
          $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/page/' . $this->application->getPages()->first()->getId());
        }
        $applicant->loginFail();
      }
      $this->addMessage('error', 'Incorrect username or password.');
      sleep(3); //wait 5 seconds before announcing failure to slow down guessing.
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
    $form = new \Foundation\Form;
    $form->setAction($this->path('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/new'));
    
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Create New Application');
    $form->addField($field);
    
    $element = $field->newElement('TextInput','first');
    $element->setLabel('First Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput','middle');
    $element->setLabel('Middle Name');
    
    $element = $field->newElement('TextInput','last');
    $element->setLabel('Last Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput','suffix');
    $element->setLabel('Suffix');
    $element->setFormat('Example: Jr., III');
    
    $element = $field->newElement('TextInput','email');
    $element->setLabel('Email Address');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));
    $element->addFilter(new \Foundation\Form\Filter\Lowercase($element));
    
    $element = $field->newElement('PasswordInput','password');
    $element->setLabel('Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('PasswordInput','confirm-password');
    $element->setLabel('Confirm Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\SameAs($element, 'password'));
    
    //setup recaptcha element keys
    \Foundation\Form\Element\Captcha::setKeys($this->_config->getRecaptchaPrivateKey(), $this->_config->getRecaptchaPublicKey());
    $element = $field->newElement('Captcha','captcha');
    
    $form->newButton('submit', 'Create Account');
    if($input = $form->processInput($this->post)){
      $duplicate = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneByEmailAndApplication($input->get('email'), $this->application);
      if($duplicate){
        $this->addMessage('error', 'You have already started a ' . $this->application->getProgram()->getName() . ' application.  Please login to retrieve it.');
        $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/login');
      }
      $applicant = new \Jazzee\Entity\Applicant;
      $applicant->setApplication($this->application);
      $applicant->setEmail($input->get('email'));
      $applicant->setPassword($input->get('password'));
      $applicant->setFirstName($input->get('first'));
      $applicant->setMiddleName($input->get('middle'));
      $applicant->setLastName($input->get('last'));
      $applicant->setSuffix($input->get('suffix'));
      
      $applicant->login();
      $this->_em->persist($applicant);
      $this->_em->flush();
      $store = $this->_session->getStore('apply', $this->_config->getApplicantSessionLifetime());
      $store->applicantID = $applicant->getId();
      $this->addMessage('success', 'Welcome to the ' . $this->application->getProgram()->getName() . ' application.');
      $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/page/' . $this->application->getPages()->first()->getId());
    }
    $this->setVar('form', $form);
  }
  
  public function actionLogout($programShortName,$cycleName){
    $this->_session->getStore('apply')->expire();
  }
  
  public function getNavigation(){
    $navigation = new \Foundation\Navigation\Container();
    $menu = new \Foundation\Navigation\Menu();
    
    $menu->setTitle('Navigation');

    $path = 'apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName();
    $link = new \Foundation\Navigation\Link('Welcome');
    $link->setHref($this->path($path));
    $menu->addLink($link); 
    
    $link = new \Foundation\Navigation\Link('Other Cycles');
    $link->setHref($this->path('apply/' . $this->application->getProgram()->getShortName() . '/'));
    $menu->addLink($link);
    
    $link = new \Foundation\Navigation\Link('Returning Applicants');
    $link->setHref($this->path($path . '/applicant/login/'));
    $menu->addLink($link);
    
    $link = new \Foundation\Navigation\Link('Start a New Application');
    $link->setHref($this->path($path . '/applicant/new/'));
    $menu->addLink($link);
    
    $navigation->addMenu($menu);
    return $navigation;
  } 
}