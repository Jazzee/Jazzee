<?php
/**
 * Manage Cycles
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManageCyclesController extends \Jazzee\AdminController {
  const MENU = 'Manage';
  const TITLE = 'Cycles';
  const PATH = 'manage/cycles';
  
  const ACTION_INDEX = 'View Cycles';
  const ACTION_EDIT = 'New Cycle';
  const ACTION_NEW = 'Edit Cycle';
  const REQUIRE_APPLICATION = false;
  
  /**
   * List cycles
   */
  public function actionIndex(){
    $this->setVar('cycles', $this->_em->getRepository('\Jazzee\Entity\Cycle')->findAll());
  }
  
  /**
   * Edit a cycle
   * @param integer $cycleID
   */
   public function actionEdit($cycleID){ 
    if($cycle = $this->_em->getRepository('\Jazzee\Entity\Cycle')->find($cycleID)){
      $form = new \Foundation\Form;
      
      $form->setAction($this->path("manage/cycles/edit/{$cycleID}"));
      $field = $form->newField();
      $field->setLegend('Edit ' . $cycle->getName() . ' cycle');
      $element = $field->newElement('TextInput','name');
      $element->setLabel('Cycle Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addFilter(new \Foundation\Form\Filter\UrlSafe($element));
      $element->setValue($cycle->getName());
      
      $element = $field->newElement('DateInput','start');
      $element->setLabel('Start Date');
      $element->addValidator(new \Foundation\Form\Validator\DateBeforeElement($element, 'end'));
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($cycle->getStart()->format('m/d/Y'));
      
      $element = $field->newElement('DateInput','end');
      $element->setLabel('End Date');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($cycle->getEnd()->format('m/d/Y'));
  
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        $cycle->setName($input->get('name'));
        $cycle->setStart($input->get('start'));
        $cycle->setEnd($input->get('end'));
        $this->_em->persist($cycle);
        $this->addMessage('success', "Changes Saved Successfully");
        $this->redirectPath('manage/cycles');
      }
    } else {
      $this->addMessage('error', "Error: Cycle #{$cycleID} does not exist.");
    }
  }
   
  /**
   * Create a new cycle
   */
  public function actionNew(){
    $form = new \Foundation\Form;
      
    $form->setAction($this->path("manage/cycles/new"));
    $field = $form->newField();
    $field->setLegend('New cycle');
    $element = $field->newElement('TextInput','name');
    $element->setLabel('Cycle Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\UrlSafe($element));
    
    $element = $field->newElement('DateInput','start');
    $element->setLabel('Start Date');
    $element->addValidator(new \Foundation\Form\Validator\DateBeforeElement($element, 'end'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('DateInput','end');
    $element->setLabel('End Date');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save Changes');
    $this->setVar('form', $form);  
    if($input = $form->processInput($this->post)){
      $cycle = new \Jazzee\Entity\Cycle;
      $cycle->setName($input->get('name'));
      $cycle->setStart($input->get('start'));
      $cycle->setEnd($input->get('end'));
      $this->_em->persist($cycle);
      $this->addMessage('success', "New Cycle Saved");
      $this->redirectPath('manage/cycles');
    }
  }
}
?>