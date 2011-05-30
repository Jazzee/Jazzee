<?php
/**
 * Manage Element types
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManageElementtypesController extends \Jazzee\AdminController {
  const MENU = 'Manage';
  const TITLE = 'Element Types';
  const PATH = 'manage/elementtypes';
  
  const ACTION_INDEX = 'View Element Types';
  const ACTION_EDIT = 'Edit Element Types';
  const ACTION_NEW = 'New Element Type';
  
  /**
   * List all the active ElementTypes and find any new classes on the file system
   */
  public function actionIndex(){
    $this->setVar('elementTypes', $this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll());
  }
  
  /**
   * Edit an ElementType
   * @param integer $elementTypeID
   */
   public function actionEdit($elementTypeID){ 
    if($elementType =$this->_em->getRepository('\Jazzee\Entity\ElementType')->find($elementTypeID)){
      $form = new \Foundation\Form();
      $form->setAction($this->path("manage/elementtypes/edit/{$elementTypeID}"));
      $field = $form->newField();
      $field->setLegend('Edit ' . $elementType->getName() . ' element type');
      $element = $field->newElement('TextInput','name');
      $element->setLabel('Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($elementType->getName());
  
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        $elementType->setName($input->get('name'));
        $this->addMessage('success', "Changes Saved");
        $this->_em->persist($elementType);
        $this->redirectPath('manage/elementtypes');
      }
    } else {
      $this->addMessage('error', "Error: ElementType #{$elementTypeID} does not exist.");
    }
  }
   
  /**
   * Create a new pagetype
   */
  public function actionNew(){
    $form = new \Foundation\Form();
    $form->setAction($this->path("manage/elementtypes/new"));
    $field = $form->newField();
    $field->setLegend('New element type');
    $element = $field->newElement('TextInput','name');
    $element->setLabel('Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput','class');
    $element->setLabel('Class');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Add Element');
    $this->setVar('form', $form); 
    if($input = $form->processInput($this->post)){
      if(!class_exists($input->get('class'))){
        $this->addMessage('error', "That is not a valid class name");
      } else {
        $elementType = new \Jazzee\Entity\ElementType;
        $elementType->setName($input->get('name'));
        $elementType->setClass($input->get('class'));
        $this->_em->persist($elementType);
      }
    }
  }
}
?>