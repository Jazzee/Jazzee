<?php

/**
 * Authentication and Creation of applicants
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplyApplicantController extends \Jazzee\ApplyController
{
  /**
   * Maximum number of times an applicant is allowed to fail
   * @var integer
   */

  const MAX_FAILED_LOGIN_ATTEMPTS = 5;

  /**
   * Minimum interval between clearning bad login applicants in seconds
   * @const ingeger
   */
  const MIN_INTERVAL_APPLICANTS = 7200;

  /**
   * Index redirects to login
   */
  public function actionIndex()
  {
    $this->redirectApplyPath('applicant/login');
  }

  /**
   * Authenticate applicants
   */
  public function actionLogin()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->applyPath('applicant/login'));
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Login');
    $form->addField($field);

    $element = $field->newElement('TextInput', 'email');
    $element->setLabel('Email Address');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Lowercase($element));

    $element = $field->newElement('PasswordInput', 'password');
    $element->setLabel('Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('Plaintext', 'forgotlink');
    $element->setValue('<a href="' . $this->applyPath('applicant/forgotpassword') . '">Forgot your password?</a>');


    $form->newButton('submit', 'Login');

    if ($input = $form->processInput($this->post)) {
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneByEmailAndApplication($input->get('email'), $this->_application);
      if ($applicant) {
        if ($applicant->getFailedLoginAttempts() + 1 >= self::MAX_FAILED_LOGIN_ATTEMPTS) {
          $applicant->loginFail();
          $this->_authLog->info('Too many attempts for applicant ' . $applicant->getId() . ' from ' . $_SERVER['REMOTE_ADDR'] . '. ' . $applicant->getFailedLoginAttempts() . ' attempts.');
          $this->addMessage('error', 'Your account has been locked because an incorect password was entered too many times.  You must reset your password to continue.');
          $this->redirectApplyPath('applicant/forgotpassword');
        } else {
          if ($applicant->checkPassword($input->get('password'))) {
            $applicant->login();
            $this->_authLog->info('Successfull login for applicant ' . $applicant->getId() . ' from ' . $_SERVER['REMOTE_ADDR']);
            $this->_store->expire();
            $this->_store->touchAuthentication();
            $this->_store->applicantID = $applicant->getId();
            if ($count = $applicant->unreadMessageCount()) {
              $this->addMessage('success', 'You have ' . $count . ' unread message(s).');
              $this->redirectApplyPath('support');
            } else {
              $this->addMessage('success', 'Welcome to the ' . $this->_application->getProgram()->getName() . ' application.');
              $this->redirectApplyFirstPage();
            }
          }
          $applicant->loginFail();
          $this->_authLog->info('Incorrect Password for applicant ' . $applicant->getId() . ' from ' . $_SERVER['REMOTE_ADDR'] . '. ' . $applicant->getFailedLoginAttempts() . ' attempts.');
        }
      }

      $this->addMessage('error', 'Incorrect username or password.  After ' . self::MAX_FAILED_LOGIN_ATTEMPTS . ' failed login attempts your account will be locked and you will need to reset your password to gain access.');
      sleep(3); //wait 5 seconds before announcing failure to slow down guessing.
    }
    $this->setVar('form', $form);
  }

  /**
   * Send Forgot applicant password email request
   */
  public function actionForgotpassword()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->applyPath('applicant/forgotpassword'));
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Forgot Password');
    $form->addField($field);

    $element = $field->newElement('TextInput', 'email');
    $element->setLabel('Email Address');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Lowercase($element));

    $form->newButton('submit', 'Submit');

    if ($input = $form->processInput($this->post)) {
      $emailInput = $input->get('email');
      $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findByEmail($emailInput);
      $foundApplications = array();
      if ($applicants) {
        $found = false;
        foreach($applicants as $applicant){
          if($applicant->getApplication()->getId() == $this->_application->getId()){
            $applicant->generateUniqueId();
            $messageBody = "We have received a request to reset your password.  In order to reset your password you will need to click on the link at the bottom of this email.  This will take you back to the secure website where you will be able to enter a new password. \n \n"
                    . "If you cannot click on the link you should copy and paste it into your browser. \n"
                    . "For your protection this link will only be valid for a limited time. \n \n"
                    . $this->absoluteApplyPath('applicant/resetpassword/' . $applicant->getUniqueId());
            $this->_em->persist($applicant);
            $found = true;
            break;
          } else {
            $foundApplications[$applicant->getApplication()->getId()] = $applicant->getApplication();
          }
        }
        if(!$found){
          $messageBody = "We have received a request to reset the applicant password for the "
             . "{$this->_application->getCycle()->getName()} {$this->_application->getProgram()->getName()} "
             . "application started using {$emailInput}, however we do "
             . "not have such an application. \nYou can create a new account at: \n" . $this->absoluteApplyPath('applicant/new')
             . "\n\nIt appears you have started applications in these programs: \n";
          foreach($foundApplications as $application){
            $messageBody .= "{$application->getCycle()->getName()} {$application->getProgram()->getName()} " 
            . $this->absolutePath("apply/{$application->getProgram()->getShortName()}/{$application->getCycle()->getName()}/applicant/login") . "\n";
          }
        }
      } else {
        $messageBody = "We have received a request to reset the applicant password for the "
             . "{$this->_application->getCycle()->getName()} {$this->_application->getProgram()->getName()} "
             . "application started using {$emailInput}, however we do "
             . "not have such an application. \nYou can create a new account at: \n" . $this->absoluteApplyPath('applicant/new');
      }
      $message = $this->newMailMessage();
      $message->AddAddress($emailInput);
      $message->Subject = 'Password Reset Request';
      $message->Body = $messageBody;
      $message->Send();
      
      $this->addMessage('success', "We have sent a message to {$emailInput} with further instructions");
      sleep(3); //wait 5 seconds before announcing failure to slow down guessing.
      $this->redirectApplyPath('applicant/login');
    }
    $this->setVar('form', $form);
  }

  /**
   * Reset applicant password
   */
  public function actionResetpassword()
  {
    $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneBy(array('uniqueId' => $this->actionParams['uniqueId']));
    if (!$applicant) {
      sleep(3);
      $this->addMessage('error', 'We were not able to find your password reset request.  It may have expired.  You can try your request again.');
      $this->redirectApplyPath('applicant/forgotpassword');
    }
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->applyPath('applicant/resetpassword/' . $applicant->getUniqueId()));
    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Reset Password');
    $form->addField($field);

    $element = $field->newElement('PasswordInput', 'password');
    $element->setLabel('New Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('PasswordInput', 'confirm-password');
    $element->setLabel('Confirm Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\SameAs($element, 'password'));

    if ($this->_config->getRecaptchaPrivateKey()) {
      //setup recaptcha element keys
      \Foundation\Form\Element\Captcha::setKeys($this->_config->getRecaptchaPrivateKey(), $this->_config->getRecaptchaPublicKey());
      $element = $field->newElement('Captcha', 'captcha');
    }
    $form->newButton('submit', 'Reset Password');

    if ($input = $form->processInput($this->post)) {
      $applicant->setPassword($input->get('password'));
      $applicant->setUniqueId(null);
      $this->_em->persist($applicant);
      $this->addMessage('success', 'Your password was reset successfully.');
      $this->redirectApplyPath('applicant/login');
    }
    $this->setVar('form', $form);
  }

  /**
   * Create a new application
   * @param string $programShortName
   * @param string $cycleName
   * @return null
   */
  public function actionNew()
  {
    if($this->_application->isByInvitationOnly()){
      $this->addMessage('error', 'This application is by invitation only.  You cannot create an account.');
      $this->redirectApplyPath('');
    }
    $form = new \Foundation\Form;
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->applyPath('applicant/new'));

    $field = new \Foundation\Form\Field($form);
    $field->setLegend('Create New Application');
    $form->addField($field);

    $element = $field->newElement('TextInput', 'first');
    $element->setLabel('First Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'middle');
    $element->setLabel('Middle Name');

    $element = $field->newElement('TextInput', 'last');
    $element->setLabel('Last Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'suffix');
    $element->setLabel('Suffix');
    $element->setFormat('Example: Jr., III');

    $element = $field->newElement('TextInput', 'email');
    $element->setLabel('Email Address');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));
    $element->addFilter(new \Foundation\Form\Filter\Lowercase($element));

    $element = $field->newElement('PasswordInput', 'password');
    $element->setLabel('Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('PasswordInput', 'confirm-password');
    $element->setLabel('Confirm Password');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\SameAs($element, 'password'));

    if ($this->_config->getRecaptchaPrivateKey()) {
      //setup recaptcha element keys
      \Foundation\Form\Element\Captcha::setKeys($this->_config->getRecaptchaPrivateKey(), $this->_config->getRecaptchaPublicKey());
      $element = $field->newElement('Captcha', 'captcha');
    }
    $form->newButton('submit', 'Create Account');
    if ($input = $form->processInput($this->post)) {
      $duplicate = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneByEmailAndApplication($input->get('email'), $this->_application);
      if ($duplicate) {
        $this->addMessage('error', 'You have already started a ' . $this->_application->getProgram()->getName() . ' application.  Please login to retrieve it.');
        $this->redirectApplyPath('applicant/login');
      }
      $applicant = new \Jazzee\Entity\Applicant;
      $applicant->setApplication($this->_application);
      $applicant->setEmail($input->get('email'));
      $applicant->setPassword($input->get('password'));
      $applicant->setFirstName($input->get('first'));
      $applicant->setMiddleName($input->get('middle'));
      $applicant->setLastName($input->get('last'));
      $applicant->setSuffix($input->get('suffix'));

      $applicant->login();
      $this->_em->persist($applicant);
      //flush here to get the ID
      $this->_em->flush();
      $this->_store->expire();
      $this->_store->touchAuthentication();
      $this->_store->applicantID = $applicant->getId();
      $this->_authLog->info('New account login for applicant ' . $applicant->getId() . ' from ' . $_SERVER['REMOTE_ADDR']);
      $this->addMessage('success', 'Welcome to the ' . $this->_application->getProgram()->getName() . ' application.');
      $this->redirectApplyFirstPage();
    }
    $this->setVar('form', $form);
  }

  public function actionLogout()
  {
    $this->_store->expire();
  }

  public function getNavigation()
  {
    $navigation = new \Foundation\Navigation\Container();
    $menu = new \Foundation\Navigation\Menu();

    $menu->setTitle('Navigation');

    $link = new \Foundation\Navigation\Link('Welcome');
    $link->setHref($this->applyPath(''));
    $menu->addLink($link);

    $link = new \Foundation\Navigation\Link('Other Cycles');
    $link->setHref($this->path('apply/' . $this->_application->getProgram()->getShortName()));
    $menu->addLink($link);

    $link = new \Foundation\Navigation\Link('Returning Applicants');
    $link->setHref($this->applyPath('applicant/login'));
    $menu->addLink($link);

    if(!$this->_application->isByInvitationOnly()){
      $link = new \Foundation\Navigation\Link('Start a New Application');
      $link->setHref($this->applyPath('applicant/new'));
      $link->addClass('highlight');
      $menu->addLink($link);
    }
    $navigation->addMenu($menu);

    if($this->isPreviewMode()){
      $menu = new \Foundation\Navigation\Menu();
      $navigation->addMenu($menu);

      $menu->setTitle('Preview Functions');
      $link = new \Foundation\Navigation\Link('Become Administrator');
      $link->setHref($this->path('admin/login'));
      $menu->addLink($link);
    }

    return $navigation;
  }

  /**
   * Reset lockout and kill password reset keys
   *
   * @param AdminCronController $cron
   */
  public static function runCron(AdminCronController $cron)
  {
    if (time() - (int) $cron->getVar('applyApplicantLastRun') > self::MIN_INTERVAL_APPLICANTS) {
      $cron->setVar('applyApplicantLastRun', time());
      $cron->getEntityManager()->getRepository('\Jazzee\Entity\Applicant')->resetFailedLoginCounters();
      $cron->getEntityManager()->getRepository('\Jazzee\Entity\Applicant')->resetUniqueIds();
    }
  }

}