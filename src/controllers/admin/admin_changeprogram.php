<?php
/**
 * Change the a users current program and defautl program
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 */
class AdminChangeprogramController extends \Jazzee\AdminController {
  const MENU = 'My Account';
  const TITLE = 'Change Program';
  const PATH = 'changeprogram';
  
  const ACTION_INDEX = 'Change Program';
  
  /**
   * Display index
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setAction($this->path('changeprogram'));
    $field = $form->newField();
    $field->setLegend('Select Program');
    $element = $field->newElement('SelectList','program');
    $element->setLabel('Program');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $programList = $this->_em->getRepository('\Jazzee\Entity\Program')->findAll();
    $programs = array();
    foreach($programList as $program){
      $programs[$program->getId()] = $program->getName();
    }
    asort($programs);
    foreach($programs as $id => $name){
      $element->newItem($id, $name);
    }
    if($this->_program) $element->setValue($this->_program->getId());
    //only ask if the user already has a default cycle
    if($this->_user->getDefaultProgram()){
      $element = $field->newElement('RadioList','default');
      $element->setLabel('Set as your default');
      $element->newItem(0, 'No');
      $element->newItem(1, 'Yes');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    }
    $form->newButton('submit', 'Change Program');
    
    if($input = $form->processInput($this->post)){
      $this->_program = $this->_em->getRepository('\Jazzee\Entity\Program')->find($input->get('program'));

      //if they wish it, or if the user has no default cycle
      if(!$this->_user->getDefaultProgram() OR $input->get('default')){
        $this->_user->setDefaultProgram($this->_program);
        $this->_em->persist($this->_user);
        $this->addMessage('success', 'Default program changed to ' . $this->_program->getName());
      }
      $this->addMessage('success', 'Program changed to ' . $this->_program->getName());
      $this->redirectPath('welcome');
    }
    
    $this->setVar('form', $form);
  }
}
?>