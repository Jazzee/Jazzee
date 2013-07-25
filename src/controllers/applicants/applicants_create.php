<?php

/**
 * Create applicants
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsCreateController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'Create';
  const PATH = 'applicants/create';
  const ACTION_INDEX = 'Create applicants';
  
  protected $_defaultEmail;
  /**
   * Add the required JS and create a default email to be used in single and bulk
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/scripts/controllers/applicants_create.controller.js'));
    $this->_defaultEmail = "Dear _Applicant_Name_,\n\nWe would like to invite you to submit an application to our {$this->_program->getName()} program. " .
      "An account has been created for you on our online application system. To apply, visit our online application system at:\n_Link_\n\n" .
      "Please login using the email and password listed below.\n" .
      "Email: _Email_\nPassword: _Password_\n\n" .
      "We recommend you change your Password after initially logging on." .
      "You may change your password by clicking 'My Account' in the top right hand corner and clicking 'Change Password.'\n\n" .
      "You will have until _Deadline_ to complete your application.\n\n" .
      "If this application is not submitted by this deadline, your application for admission " .
      "will be denied and you will be ineligible to be considered for {$this->_cycle->getName()} admission.\n\n" .
      "If you have decided that you no longer wish to apply to our program, please inform us immediately so that we may cancel your application.\n\n" .
      "If you have trouble with your account, please contact {$this->_application->getContactName()} at {$this->_application->getContactEmail()}\n" . 
      "Thank you.\n{$this->_application->getContactName()}";
  }

  /**
   * Create a new spplicant
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/create'));
    $field = $form->newField();
    $field->setLegend('Create Applicant');
    
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

    $element = $field->newElement('TextInput', 'notificationSubject');
    $element->setLabel('Subject for Notification Email');
    $element->setValue('New Application Account');

    $element = $field->newElement('Textarea', 'notificationMessage');
    $element->setLabel('Notification Email Message');
    $element->setFormat('Leave this blank if you do not want to notify the applicant of their new account');
    
    $notificationMessagereplacements = array(
      '_Applicant_Name_',
      '_Deadline_',
      '_Link_',
      '_Email_',
      '_Password_'
    );
    $element->setInstructions('You can use these tokens in the text, they will be replaced automatically: <br />' . implode('</br />', $notificationMessagereplacements));
    $element->setValue($this->_defaultEmail);
    $element = $field->newElement('TextInput', 'password');
    $element->setLabel('Password');
    $element->setFormat('If you leave the password blank a random password will be generated.');

    $element = $field->newElement('DateInput', 'deadlineExtension');
    $element->setLabel('Deadline');
    $element->setFormat('If you wish to extend this applicants deadline past the application deadline enter it here.');
    if($this->_application->getClose()){
      $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, $this->_application->getClose()->format('c')));
    }
    $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, date('c')));
    $element = $field->newElement('TextInput', 'externalId');
    $element->addValidator(new \Foundation\Form\Validator\SpecialObject($element, array(
      'object' => $this->_application,
      'method' => 'validateExternalId',
      'errorMessage' => 'This is not a valid External ID for this program.'
    )));
    $element->setLabel('External ID');

    $form->newButton('submit', 'Create Applicant');
    if ($input = $form->processInput($this->post)) {
      $duplicate = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneByEmailAndApplication($input->get('email'), $this->_application);
      if ($duplicate) {
        $form->getElementByName('email')->addMessage('An applicant with that email address already exists.');
      } else {
        $applicant = new \Jazzee\Entity\Applicant;
        $applicant->setApplication($this->_application);
        $applicant->setEmail($input->get('email'));
        if($input->get('password')){
          $applicant->setPassword($input->get('password'));
          $plainTextPassword = $input->get('password');
        } else {
          $plainTextPassword = $applicant->generatePassword();
        }
        $applicant->setFirstName($input->get('first'));
        $applicant->setMiddleName($input->get('middle'));
        $applicant->setLastName($input->get('last'));
        $applicant->setSuffix($input->get('suffix'));
        $applicant->setExternalId($input->get('externalId'));
        if($input->get('deadlineExtension')){
          $applicant->setDeadlineExtension($input->get('deadlineExtension'));
        }
        $this->_em->persist($applicant);
        $this->_em->flush();
        $this->setVar('applicant', $applicant);
        $this->setVar('plainTextPassword', $plainTextPassword);
        $this->addMessage('success', 'Applicant Created Successfully');
        if($input->get('notificationMessage')){
          $replace = array(
            $applicant->getFullName(),
            $applicant->getDeadline()?$applicant->getDeadline()->format('l F jS Y g:ia'):'',
            $this->absoluteApplyPath("apply/{$this->_application->getProgram()->getShortName()}/{$this->_application->getCycle()->getName()}/applicant/login"),
            $applicant->getEmail(),
            $plainTextPassword
          );
          $body = str_ireplace($notificationMessagereplacements, $replace, $input->get('notificationMessage'));
          $subject = $input->get('notificationSubject')?$input->get('notificationSubject'):'New Application Account';
          $email = $this->newMailMessage();
          $email->AddCustomHeader('X-Jazzee-Applicant-ID:' . $applicant->getId());
          $email->AddAddress(
              $applicant->getEmail(),
              $applicant->getFullName()
          );
          
          $email->setFrom($this->_application->getContactEmail(), $this->_application->getContactName());
          $email->Subject = $subject;
          $email->Body = $body;
          $email->Send();
          
          $thread = new \Jazzee\Entity\Thread();
          $thread->setSubject($subject);
          $thread->setApplicant($applicant);

          $message = new \Jazzee\Entity\Message();
          $message->setSender(\Jazzee\Entity\Message::PROGRAM);
          $message->setText(nl2br($body));
          $message->read();
          $thread->addMessage($message);
          $this->_em->persist($thread);
          $this->_em->persist($message);
          
         $this->addMessage('success', 'New account email sent to ' . $applicant->getEmail());
        }
      }
    }
    $this->setVar('form', $form);
  }

  /**
   * Bulk create applicants from a csv file
   */
  public function actionBulk()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/create/bulk'));
    $field = $form->newField();
    $field->setLegend('Create Applicants From File');
    
    $element = $field->newElement('FileInput', 'file');
    $element->setLabel('File');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\CSVArray($element));
    
    $element = $field->newElement('TextInput', 'notificationSubject');
    $element->setLabel('Subject for Notification Email');
    $element->setValue('New Application Account');

    $element = $field->newElement('Textarea', 'notificationMessage');
    $element->setLabel('Notification Email Message');
    $element->setFormat('Leave this blank if you do not want to notify the applicant of their new account');
    
    $notificationMessagereplacements = array(
      '_Applicant_Name_',
      '_Deadline_',
      '_Link_',
      '_Email_',
      '_Password_'
    );
    $element->setInstructions('You can use these tokens in the text, they will be replaced automatically: <br />' . implode('</br />', $notificationMessagereplacements));
    $element->setValue($this->_defaultEmail);

    $element = $field->newElement('DateInput', 'deadlineExtension');
    $element->setLabel('Deadline');
    $element->setFormat('If you wish to extend this applicants deadline past the application deadline enter it here.');
    if($this->_application->getClose()){
      $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, $this->_application->getClose()->format('c')));
    }

    $form->newButton('submit', 'Upload File and create applicants');
    if ($input = $form->processInput($this->post)) {
      $newApplicants = $input->get('file');
      $first = $newApplicants[0];
      $requiredHeaders = array(
        'External ID',
        'First Name',
        'Middle Name',
        'Last Name',
        'Suffix',
        'Email Address',
        'Password'
      );
      $error = false;
      foreach($requiredHeaders as $value){
        if(!in_array($value, $first)){
          $form->getElementByName('file')->addMessage("The uploaded file must contain a column '{$value}'");
          $error = true;
        }
      }
      if(!$error){
        $headers = array_shift($newApplicants);
        $byKey = array();
        foreach($newApplicants as $newApplicant){
          $arr = array();
          foreach($headers as $key => $value){
            $arr[$value] = $newApplicant[$key];
          }
          $byKey[] = $arr;
        }
        $newApplicants = $byKey;
        $results = array();
        foreach($newApplicants as $newApplicant){
          $result = array(
            'messages' => array(),
            'applicant' => null,
            'plainTextPassword' => ''
          );
          $duplicate = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneByEmailAndApplication($newApplicant['Email Address'], $this->_application);
          if ($duplicate) {
            $result['status'] = 'duplicate';
            $result['messages'][] = 'An applicant with that email address already exists.';
            $result['applicant'] = $duplicate;
          } else if (!empty($newApplicant['External ID']) AND !$this->_application->validateExternalId($newApplicant['External ID'])) {
            $result['status'] = 'badExternalId';
            $result['messages'][] = $newApplicant['External ID'] . ' is not a valid external ID for this program';
            $result['applicantName'] = "{$newApplicant['First Name']} {$newApplicant['Last Name']}";
            $result['applicantEmail'] = $newApplicant['Email Address'];
          } else {
            $result['status'] = 'success';
            $applicant = new \Jazzee\Entity\Applicant;
            $applicant->setApplication($this->_application);
            $applicant->setEmail($newApplicant['Email Address']);
            if($newApplicant['Password']){
              $applicant->setPassword($newApplicant['Password']);
              $plainTextPassword = $newApplicant['Password'];
            } else {
              $plainTextPassword = $applicant->generatePassword();
            }
            $applicant->setFirstName($newApplicant['First Name']);
            $applicant->setMiddleName($newApplicant['Middle Name']);
            $applicant->setLastName($newApplicant['Last Name']);
            $applicant->setSuffix($newApplicant['Suffix']);
            $applicant->setExternalId($newApplicant['External ID']);
            if($input->get('deadlineExtension')){
              $applicant->setDeadlineExtension($input->get('deadlineExtension'));
            }
            $this->_em->persist($applicant);

            $result['applicant'] = $applicant;
            $result['plainTextPassword'] = $plainTextPassword;
            $result['messages'][] = 'Applicant Created Successfully';
            if($input->get('notificationMessage')){
              $replace = array(
                $applicant->getFullName(),
                $applicant->getDeadline()?$applicant->getDeadline()->format('l F jS Y g:ia'):'',
                $this->absoluteApplyPath("apply/{$this->_application->getProgram()->getShortName()}/{$this->_application->getCycle()->getName()}/applicant/login"),
                $applicant->getEmail(),
                $plainTextPassword
              );
              $body = str_ireplace($notificationMessagereplacements, $replace, $input->get('notificationMessage'));
              $subject = $input->get('notificationSubject')?$input->get('notificationSubject'):'New Application Account';
              $email = $this->newMailMessage();
              $email->AddCustomHeader('X-Jazzee-Applicant-ID:' . $applicant->getId());
              $email->AddAddress(
                  $applicant->getEmail(),
                  $applicant->getFullName()
              );

              $email->setFrom($this->_application->getContactEmail(), $this->_application->getContactName());
              $email->Subject = $subject;
              $email->Body = $body;
              $email->Send();

              $thread = new \Jazzee\Entity\Thread();
              $thread->setSubject($subject);
              $thread->setApplicant($applicant);

              $message = new \Jazzee\Entity\Message();
              $message->setSender(\Jazzee\Entity\Message::PROGRAM);
              $message->setText(nl2br($body));
              $message->read();
              $thread->addMessage($message);
              $this->_em->persist($thread);
              $this->_em->persist($message);
              $result['messages'][] = 'New account email sent';
            }
          }
          $results[] = $result;
        }
        $this->setVar('results', $results);
        $this->_em->flush();
      }
    }
    $this->setVar('form', $form);
  }

  /**
   * Download a sample file for upload
   */
  public function actionSampleFile()
  {
    $headers = array(
      'External ID',
      'First Name',
      'Middle Name',
      'Last Name',
      'Suffix',
      'Email Address',
      'Password'
    );
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=bulk_upload_sample.csv");
    ob_end_clean();
    $handle = fopen('php://output', 'w');
    fputcsv($handle, $headers);
    fclose($handle);
    exit(0);
  }

  /**
   * Use the index action to controll acccess
   * require a published application
   * @param type $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @param \Jazzee\Entity\Application $application
   * @return type
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if(!$application || !$application->isPublished()){
      return false;
    }
    //several views are controller by the index action
    if (in_array($action, array('bulk', 'sampleFile'))) {
      $action = 'index';
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}