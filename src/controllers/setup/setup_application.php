<?php

/**
 * Setup the application
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupApplicationController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Application';
  const PATH = 'setup/application';
  const ACTION_INDEX = 'View Setup';
  const ACTION_EDITCONTACT = 'Edit Contact Information';
  const ACTION_EDITWELCOME = 'Edit Welcome Page';
  const ACTION_EDITSTATUSINCOMPLETE = 'Edit message for applicants who missed the deadline';
  const ACTION_EDITSTATUSDEACTIVATED = 'Edit message for applicants who have been deactivated';
  const ACTION_EDITSTATUSNODECISION = 'Edit message for locked applicants with no decision';
  const ACTION_EDITSTATUSADMIT = 'Edit message for admitted applicants';
  const ACTION_EDITSTATUSDENY = 'Edit message for denied applicants';
  const ACTION_EDITSTATUSACCEPT = 'Edit message for applicants who accept their offer';
  const ACTION_EDITSTATUSDECLINE = 'Edit message for applicants who decline their offer';
  const ACTION_EDITSTATUS = 'Edit status information (open, close, published, visible)';
  const ACTION_EDITEXTERNALIDVALIDATION = 'Edit the validation for external IDs';
  const REQUIRE_APPLICATION = false;

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/foundation/scripts/jquery.wysiwyg.js'));
    $this->addCss($this->path('resource/foundation/styles/jquery.wysiwyg.css'));
    $this->addScript($this->path('resource/scripts/controllers/setup_application.controller.js'));
  }

  /**
   * View the current Setup or setup a new app
   */
  public function actionIndex()
  {
    if ($this->_application) {
      $this->setVar('application', $this->_application);
    } else {
      $form = new \Foundation\Form();
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("setup/application"));
      $field = $form->newField();
      $field->setLegend('Create Application');

      $element = $field->newElement('TextInput', 'contactName');
      $element->setLabel('Contact Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($this->_user->getFirstName() . ' ' . $this->_user->getLastName());
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));

      $element = $field->newElement('TextInput', 'contactEmail');
      $element->setLabel('Contact Email');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));
      $element->setValue($this->_user->getEmail());
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));

      $form->newButton('submit', 'Create Application');
      $this->setVar('form', $form);

      if ($input = $form->processInput($this->post)) {
        $application = new \Jazzee\Entity\Application();
        $application->setProgram($this->_program);
        $application->setCycle($this->_cycle);
        $application->setContactName($input->get('contactName'));
        $application->setContactEmail($input->get('contactEmail'));

        $this->_em->persist($application);
        $this->addMessage('success', 'Application Created.');
        unset($this->_store->AdminControllerGetNavigation);
        $this->redirectPath('setup/application');
      }
      $this->loadView($this->controllerName . '/form');
    }
  }

  /**
   * Edit the contact information
   */
  public function actionEditContact()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/application/editContact"));
    $field = $form->newField();
    $field->setLegend('Edit Contact Information');

    $element = $field->newElement('TextInput', 'contactName');
    $element->setLabel('Contact Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->setValue($this->_application->getContactName());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $element = $field->newElement('TextInput', 'contactEmail');
    $element->setLabel('Contact Email');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));
    $element->setValue($this->_application->getContactEmail());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $form->newButton('submit', 'Save');

    if ($input = $form->processInput($this->post)) {
      $this->_application->setContactName($input->get('contactName'));
      $this->_application->setContactEmail($input->get('contactEmail'));
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Contact Information saved.');
      $this->redirectPath('setup/application');
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit welcome page
   */
  public function actionEditWelcome()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/application/editWelcome"));
    $field = $form->newField();
    $field->setLegend('Edit Welcome Page');

    $element = $field->newElement('Textarea', 'welcome');
    $element->setLabel('Welcome Message');
    $element->setValue($this->_application->getWelcome());
    $element->addFilter(new \Foundation\Form\Filter\SafeHTML($element));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save');

    if ($input = $form->processInput($this->post)) {
      $this->_application->setWelcome($input->get('welcome'));
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Welcome Page Saved.');
      $this->redirectPath('setup/application');
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit Message for Incomplete Applicants
   */
  public function actionEditStatusIncomplete()
  {
    $form = $this->getStatusForm('Incomplete');
    $this->processStatusForm('Incomplete', $form);
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit Message for Deactivated Applicants
   */
  public function actionEditStatusDeactivated()
  {
    $form = $this->getStatusForm('Deactivated');
    $this->processStatusForm('Deactivated', $form);
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit Message for No Decision Applicants
   */
  public function actionEditStatusNoDecision()
  {
    $form = $this->getStatusForm('NoDecision');
    $this->processStatusForm('NoDecision', $form);
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit Message for Admitted Applicants
   */
  public function actionEditStatusAdmit()
  {
    $form = $this->getStatusForm('Admit');
    $this->processStatusForm('Admit', $form);
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit Message for Denied Applicants
   */
  public function actionEditStatusDeny()
  {
    $form = $this->getStatusForm('Deny');
    $this->processStatusForm('Deny', $form);
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit Message for Accepted Applicants
   */
  public function actionEditStatusAccept()
  {
    $form = $this->getStatusForm('Accept');
    $this->processStatusForm('Accept', $form);
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit Message for Declined Applicants
   */
  public function actionEditStatusDecline()
  {
    $form = $this->getStatusForm('Decline');
    $this->processStatusForm('Decline', $form);
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit the status (open, close, begin, published, visible)
   */
  public function actionEditStatus()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/application/editStatus"));
    $field = $form->newField();
    $field->setLegend('Edit Application Status');

    $element = $field->newElement('DateInput', 'open');
    $element->setLabel('Application Open');
    if ($this->_application->getOpen()) {
      $element->setValue($this->_application->getOpen()->format('c'));
    }

    $element = $field->newElement('DateInput', 'close');
    $element->setLabel('Application Close');
    if ($this->_application->getClose()) {
      $element->setValue($this->_application->getClose()->format('c'));
    }
    $element->addValidator(new \Foundation\Form\Validator\DateAfterElement($element, 'open'));

    $element = $field->newElement('DateInput', 'begin');
    $element->setLabel('Program Start Date');
    if ($this->_application->getBegin()) {
      $element->setValue($this->_application->getBegin()->format('c'));
    }

    $element = $field->newElement('RadioList', 'visible');
    $element->setLabel('Is this application visible in the list of cycles?');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->setValue($this->_application->isVisible());

    $element = $field->newElement('RadioList', 'byInvitationOnly');
    $element->setLabel('Is this application by invitation only?');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->setValue((int)$this->_application->isByInvitationOnly());

    $form->newButton('submit', 'Save');

    if ($input = $form->processInput($this->post)) {
      $this->_application->setOpen($input->get('open'));
      $this->_application->setClose($input->get('close'));
      $this->_application->setBegin($input->get('begin'));
      if ($input->get('visible')) {
        $this->_application->visible();
      } else {
        $this->_application->inVisible();
      }
      if ($input->get('byInvitationOnly')) {
        $this->_application->byInvitationOnly();
      } else {
        $this->_application->notByInvitationOnly();
      }
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Application Status Saved.');
      $this->redirectPath('setup/application');
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Edit the external ID validation
   */
  public function actionEditExternalIdValidation()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/application/editExternalIdValidation"));
    $field = $form->newField();
    $field->setLegend('Edit External iD Validation');

    $element = $field->newElement('TextInput', 'externalRegex');
    $element->setLabel('Regular Expression');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\IsRegex($element));
    $element->setValue($this->_application->getExternalIdValidationExpression());

    $form->newButton('submit', 'Save');

    if ($input = $form->processInput($this->post)) {
      $this->_application->setExternalIdValidationExpression($input->get('externalRegex'));
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'External ID validation Saved.');
      $this->redirectPath('setup/application');
    }
    $this->setVar('form', $form);
    $this->loadView($this->controllerName . '/form');
  }

  /**
   * Get a form for a specific status text var
   * @param string $status
   * @return \Foundation\Form
   */
  protected function getStatusForm($status)
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/application/editStatus{$status}"));
    $field = $form->newField();
    $field->setLegend("Edit {$status} Page");
    $search = array(
      '_Applicant_Name_',
      '_Application_Deadline_',
      '_Offer_Response_Deadline_',
      '_SIR_Link_',
      '_Admit_Letter_',
      '_Deny_Letter_',
      '_Admit_Date_',
      '_Deny_Date_',
      '_Accept_Date_',
      '_Decline_Date_'
    );
    $field->setInstructions('You can use these tokens in the text, they will be replaced automatically: <br />' . implode('</br />', $search));
    $element = $field->newElement('Textarea', 'message');
    $element->setLabel('Message');
    $func = "getStatus{$status}Text";
    $element->setValue($this->_application->$func());
    $element->addFilter(new \Foundation\Form\Filter\SafeHTML($element));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save');

    return $form;
  }

  /**
   * Process post data for all status text forms
   * @param string $status
   * @param \Foundation\Form $form
   */
  protected function processStatusForm($status, \Foundation\Form $form)
  {
    if ($input = $form->processInput($this->post)) {
      $func = "setStatus{$status}Text";
      $this->_application->$func($input->get('message'));
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Message Saved.');
      $this->redirectPath('setup/application');
    }
  }

  /**
   * Don't allow users who don't have a program and a cycle
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @param \Jazzee\Entity\Application $application
   * @return boolean
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if (!$program) {
      return false;
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}