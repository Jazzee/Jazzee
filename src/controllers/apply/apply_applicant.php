<?php
/**
 * Authentication and Creation of applicants
 */
class ApplyApplicantController extends \Jazzee\Controller {
  /**
   * Maximum number of times an applicant is allowed to fail
   * @var integer
   */
  const MAX_FAILED_LOGIN_ATTEMPTS = 5;
  
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
  }
  
  /**
   * Authenticate applicants
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
    
    $element = $field->newElement('Plaintext','forgotlink');
    $element->setValue('<a href="' . $this->path('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/forgotpassword') . '">Forgot your password?</a>');
    
    
    $form->newButton('submit', 'Login');
    
    if($input = $form->processInput($this->post)){
      $message = '';
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneByEmailAndApplication($input->get('email'), $this->application);
      if($applicant){
        if($applicant->getFailedLoginAttempts() >= self::MAX_FAILED_LOGIN_ATTEMPTS){
          $this->addMessage('error', 'Your account has been locked because an incorect password was entered too many times.  Please reset your password to unlock your account.');
          $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/forgotpassword');
        } else {
          if($applicant->checkPassword($input->get('password'))){
            $applicant->login();
            
            $store = $this->_session->getStore('apply', $this->_config->getApplicantSessionLifetime());
            $store->applicantID = $applicant->getId();
            $this->addMessage('success', 'Welcome to the ' . $this->application->getProgram()->getName() . ' application.');
            $pages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$this->application->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc'));
            $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/page/' . $pages[0]->getId());
          }
          $applicant->loginFail();
          $message = ' After ' . self::MAX_FAILED_LOGIN_ATTEMPTS . ' failed login attempts your account will be locked and you will need to reset your password to gain access.  You have ' . (string)(self::MAX_FAILED_LOGIN_ATTEMPTS - $applicant->getFailedLoginAttempts()) . ' more attempts.';
        }
      }
      $this->addMessage('error', 'Incorrect username or password.' . $message);
      sleep(3); //wait 5 seconds before announcing failure to slow down guessing.
    }
    $this->setVar('form', $form);
  }
  
  /**
   * Send Forgot applicant password email request
   */
  public function actionForgotpassword() {
    $form = new \Foundation\Form();
    $form->setAction($this->path('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/forgotpassword'));
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Forgot Password');
    $form->addField($field);
    
    $element = $field->newElement('TextInput','email');
    $element->setLabel('Email Address');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Lowercase($element));
    
    $form->newButton('submit', 'Submit');
    
    if($input = $form->processInput($this->post)){
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneByEmailAndApplication($input->get('email'), $this->application);
      if($applicant){
        $applicant->generateUniqueId();
        $body = "We have received a request to reset your password.  In order to reset your password you will need to click on the link at the bottom of this email.  This will take you back to the secure website were you will be ale to enter a new password. \n \n"
      . "If you cannot click on the link you should copy and paste it into your browser. \n"
      . "For your protection this link will only be valid for a limited time. \n \n"
      . $this->path('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/resetpassword/' . $applicant->getUniqueId());
        $message = $this->newMessage();
        $message->AddAddress($applicant->getEmail(), $applicant->getFullName());
        $message->Subject = 'Password Reset Request';
        $message->Body = $body;
        $message->Send();
        $this->_em->persist($applicant);
        $this->addMessage('success', 'Instructions for reseting your password have been sent to your email address.');
        $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/login');
      }
      $this->addMessage('error', 'Invalid email address.');
      sleep(3); //wait 5 seconds before announcing failure to slow down guessing.
    }
    $this->setVar('form', $form);
  }
  

  
  /**
   * Reset applicant password
   */
  public function actionResetpassword() {
    $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneBy(array('uniqueId'=>$this->actionParams['uniqueId']));
    if(!$applicant){
      sleep(3);
      throw new \Jazzee\Exception(
      'Bad uniqueId in applicant password reset request', E_STRICT, 
      'We were not able to find your password reset request.  It may have expired. ' .
      'You can <a href="' . $this->path('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/forgotpassword/') . '">Try your request again.</a>');
    }
    $form = new \Foundation\Form();
    $form->setAction($this->path('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/resetpassword/' . $this->actionParams['uniqueId']));
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Reset Password');
    $form->addField($field);
    
    $element = $field->newElement('PasswordInput','password');
    $element->setLabel('New Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('PasswordInput','confirm-password');
    $element->setLabel('Confirm Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\SameAs($element, 'password'));
    
    if($this->_config->getRecaptchaPrivateKey()){
      //setup recaptcha element keys
      \Foundation\Form\Element\Captcha::setKeys($this->_config->getRecaptchaPrivateKey(), $this->_config->getRecaptchaPublicKey());
      $element = $field->newElement('Captcha','captcha');
    }
    $form->newButton('submit', 'Reset Password');
    
    if($input = $form->processInput($this->post)){
      $applicant->setPassword($input->get('password'));
      $applicant->setUniqueId(null);
      $this->_em->persist($applicant);
      $this->addMessage('success', 'Your password was reset successfully.');
      $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/applicant/login');
    }
    $this->setVar('form', $form);
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
    
    if($this->_config->getRecaptchaPrivateKey()){
      //setup recaptcha element keys
      \Foundation\Form\Element\Captcha::setKeys($this->_config->getRecaptchaPrivateKey(), $this->_config->getRecaptchaPublicKey());
      $element = $field->newElement('Captcha','captcha');
    }
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
      $pages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$this->application->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc'));
      $this->redirectPath('apply/' . $this->application->getProgram()->getShortName() . '/' . $this->application->getCycle()->getName() . '/page/' . $pages[0]->getId());
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
    $link->setHref($this->path('apply/' . $this->application->getProgram()->getShortName()));
    $menu->addLink($link);
    
    $link = new \Foundation\Navigation\Link('Returning Applicants');
    $link->setHref($this->path($path . '/applicant/login'));
    $menu->addLink($link);
    
    $link = new \Foundation\Navigation\Link('Start a New Application');
    $link->setHref($this->path($path . '/applicant/new'));
    $menu->addLink($link);
    
    $navigation->addMenu($menu);
    return $navigation;
  } 
}