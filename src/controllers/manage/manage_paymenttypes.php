<?php
/**
 * Manage Payment types
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManagePaymenttypesController extends \Jazzee\AdminController {
  const MENU = 'Manage';
  const TITLE = 'Payment Types';
  const PATH = 'manage/paymenttypes';
  
  const ACTION_INDEX = 'View Payment Types';
  const ACTION_EDIT = 'Edit Payment Types';
  const ACTION_NEW = 'New Payment Type';
  
  /**
   * List all the active PaymentTypes and find any new classes on the file system
   */
  public function actionIndex(){
    $this->setVar('paymentTypes', $this->_em->getRepository('\Jazzee\Entity\PaymentType')->findAll());
  }
  
  /**
   * Edit an PaymentType
   * @param integer $paymentTypeId
   */
   public function actionEdit($paymentTypeId){ 
    if($paymentType =$this->_em->getRepository('\Jazzee\Entity\PaymentType')->find($paymentTypeId)){
      $form = new \Foundation\Form();
      $form->setAction($this->path("manage/paymenttypes/edit/{$paymentTypeId}"));
      $field = $form->newField();
      $field->setLegend('Edit ' . $paymentType->getName() . ' page type');
      $element = $field->newElement('TextInput','name');
      $element->setLabel('Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($paymentType->getName());
  
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        $paymentType->setName($input->get('name'));
        $this->addMessage('success', "Changes Saved");
        $this->_em->persist($paymentType);
        $this->redirectPath('manage/paymenttypes');
      }
    } else {
      $this->addMessage('error', "Error: Paymenttype #{$paymentTypeId} does not exist.");
    }
  }
   
  /**
   * Create a new pagetype
   */
  public function actionNew(){
    $form = new \Foundation\Form();
    $form->setAction($this->path("manage/paymenttypes/new"));
    $field = $form->newField();
    $field->setLegend('New payment type');
    $element = $field->newElement('TextInput','name');
    $element->setLabel('Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput','class');
    $element->setLabel('Class');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Add Payment Type');
    $this->setVar('form', $form); 
    if($input = $form->processInput($this->post)){
      if(!class_exists($input->get('class'))){
        $this->addMessage('error', "That is not a valid class name");
      } else {
        $paymentType = new \Jazzee\Entity\PaymentType();
        $paymentType->setName($input->get('name'));
        $paymentType->setClass($input->get('class'));
        $this->_em->persist($paymentType);
      }
    }
  }
}
?>