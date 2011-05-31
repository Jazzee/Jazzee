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
    $form->setAction($this->path("setup/application"));
    $field = $form->newField();
    $field->setLegend('Setup Applicant');
    
    $element = $field->newElement('TextInput','contactName');
    $element->setLabel('Contact Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->setValue($this->_application->getContactName());
    
    $element = $field->newElement('TextInput','contactEmail');
    $element->setLabel('Contact Email');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));
    $element->setValue($this->_application->getContactEmail());
    
    $element = $field->newElement('Textarea','welcome');
    $element->setLabel('Welcome Message');
    $element->setValue($this->_application->getWelcome());
    
    $element = $field->newElement('DateInput','open');
    $element->setLabel('Application Open');
    $element->addValidator(new \Foundation\Form\Validator\DateBeforeElement($element, 'close'));
    if($this->_application->getOpen()) $element->setValue($this->_application->getOpen()->format('c'));
    
    $element = $field->newElement('DateInput','close');
    $element->setLabel('Application close');
    if($this->_application->getClose()) $element->setValue($this->_application->getClose()->format('c'));

    $element = $field->newElement('DateInput','begin');
    $element->setLabel('Program Start Date');
    if($this->_application->getOpen()) $element->setValue($this->_application->getBegin()->format('c'));
    
    $element = $field->newElement('RadioList','visible');
    $element->setLabel('Visible');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->setValue($this->_application->isVisible());
    
    $element = $field->newElement('RadioList','published');
    $element->setLabel('Published');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->setValue($this->_application->isPublished());

    $form->newButton('submit', 'Save');
    
    if($input = $form->processInput($this->post)){
      $this->_application->setContactName($input->get('contactName'));
      $this->_application->setContactEmail($input->get('contactEmail'));
      $this->_application->setWelcome($input->get('welcome'));
      $this->_application->setOpen($input->get('open'));
      $this->_application->setClose($input->get('close'));
      $this->_application->setBegin($input->get('begin'));
      if($input->get('visible')) $this->_application->visible(); else $this->_application->inVisible();
      if($input->get('published')) $this->_application->publish(); else $this->_application->unPublish();
      $this->_em->persist($this->_application);
      $this->addMessage('success', 'Application saved.');
      $this->redirectPath('setup/application');
    }
    
    $this->setVar('form', $form);
  }
  
}