<?php
/**
 * Setup the application
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class SetupApplicationController extends \Jazzee\AdminController {
  const MENU = 'Setup';
  const TITLE = 'Application';
  const PATH = 'setup/application';
  
  const ACTION_INDEX = 'Make Changes';
  const REQUIRE_APPLICATION = false;
  
  /**
   * If there is no application then create a new one to work with
   */
  protected function setUp(){
    parent::setUp();
    if(!$this->_application){
      $this->_application = new \Jazzee\Entity\Application();
      $this->_application->setProgram($this->_program);
      $this->_application->setCycle($this->_cycle);
    }
  }
  
  /**
   * Setup the current application and cycle
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/application"));
    $field = $form->newField();
    $field->setLegend('Setup Applicant');
    
    $element = $field->newElement('TextInput','contactName');
    $element->setLabel('Contact Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->setValue($this->_application->getContactName());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('TextInput','contactEmail');
    $element->setLabel('Contact Email');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));
    $element->setValue($this->_application->getContactEmail());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('Textarea','welcome');
    $element->setLabel('Welcome Message');
    $element->setValue($this->_application->getWelcome());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
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
    
    $instructions = 'You can use these tokens in the text: <br />' . implode('</br />', $search);
    
    $element = $field->newElement('Textarea','statusIncompleteText');
    $element->setLabel('Message for applicants who missed the deadline.');
    $element->setInstructions($instructions);
    $element->setValue($this->_application->getStatusIncompleteText());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('Textarea','statusNoDecisionText');
    $element->setLabel('Message for locked applicants with no decision');
    $element->setInstructions($instructions);
    $element->setValue($this->_application->getStatusNoDecisionText());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('Textarea','statusAdmitText');
    $element->setLabel('Message for admitted applicants');
    $element->setInstructions($instructions);
    $element->setValue($this->_application->getStatusAdmitText());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('Textarea','statusDenyText');
    $element->setLabel('Message for denied applicants');
    $element->setInstructions($instructions);
    $element->setValue($this->_application->getStatusDenyText());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('Textarea','statusAcceptText');
    $element->setLabel('Message for applicants who accept their offer.');
    $element->setInstructions($instructions);
    $element->setValue($this->_application->getStatusAcceptText());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('Textarea','statusDeclineText');
    $element->setLabel('Message for applicants who decline their offer');
    $element->setInstructions($instructions);
    $element->setValue($this->_application->getStatusDeclineText());
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('DateInput','open');
    $element->setLabel('Application Open');
    $element->addValidator(new \Foundation\Form\Validator\DateBeforeElement($element, 'close'));
    if($this->_application->getOpen()) $element->setValue($this->_application->getOpen()->format('c'));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('DateInput','close');
    $element->setLabel('Application close');
    if($this->_application->getClose()) $element->setValue($this->_application->getClose()->format('c'));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $element = $field->newElement('DateInput','begin');
    $element->setLabel('Program Start Date');
    if($this->_application->getOpen()) $element->setValue($this->_application->getBegin()->format('c'));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    
    $element = $field->newElement('RadioList','visible');
    $element->setLabel('Visible');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    $element->setValue($this->_application->isVisible());
    
    $element = $field->newElement('RadioList','published');
    $element->setLabel('Published');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    $element->setValue($this->_application->isPublished());

    $form->newButton('submit', 'Save');
    
    if($input = $form->processInput($this->post)){
      $this->_application->setContactName($input->get('contactName'));
      $this->_application->setContactEmail($input->get('contactEmail'));
      $this->_application->setWelcome($input->get('welcome'));
      $this->_application->setStatusIncompleteText($input->get('statusIncompleteText'));
      $this->_application->setStatusNoDecisionText($input->get('statusNoDecisionText'));
      $this->_application->setStatusAdmitText($input->get('statusAdmitText'));
      $this->_application->setStatusDenyText($input->get('statusDenyText'));
      $this->_application->setStatusAcceptText($input->get('statusAcceptText'));
      $this->_application->setStatusDeclineText($input->get('statusDeclineText'));
      $this->_application->setOpen($input->get('open'));
      $this->_application->setClose($input->get('close'));
      $this->_application->setBegin($input->get('begin'));
      if($input->get('visible')) $this->_application->visible(); else $this->_application->inVisible();
      if($input->get('published')) $this->_application->publish(); else $this->_application->unPublish();
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Application saved.');
      unset($this->_store->AdminControllerGetNavigation);
      $this->redirectPath('setup/application');
    }
    
    $this->setVar('form', $form);
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
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null){
    if(!$program) return false;
    return parent::isAllowed($controller, $action, $user, $program, $application);
  }
}