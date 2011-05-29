<?php
namespace Jazzee\Entity\Page;
/**
 * Branch a child page depending on an applicant input
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage apply
 */
class Branching extends Standard 
{
  /**
   * The answer class for this page type
   * @const string
   */
  const ANSWER_CLASS = '\Jazzee\Entity\Answer\Branching';
  
  /**
   * 
   * Enter description here ...
   */
  protected function makeForm(){
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    
    $element = $field->newElement('SelectList', 'branching');
    $element->setLabel($this->_applicationPage->getPage()->getVar('branchingElementLabel'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    foreach($this->_applicationPage->getPage()->getChildren() as $child){
      $element->newItem($child->getId(), $child->getTitle());
    }
    $form->newHiddenElement('level', 1);
    $form->newButton('submit', 'Next');
    return $form;
  }
  
  protected function branchingForm($branchingPageID){
    $page = $this->_applicationPage->getPage()->getChildById($branchingPageID);
    
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    
    foreach($page->getElements() as $element){
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newHiddenElement('level', 2);
    $form->newHiddenElement('branching', $branchingPageID);
    $form->newButton('submit', 'Save');
    $form->newButton('reset', 'Clear Form');
    
    $this->_form = $form;
  }
  
  public function validateInput($input){
    $this->branchingForm($input['branching']);
    if($input['level'] == 1) return false;
    return parent::validateInput($input);
  }
  
  public function newAnswer($input){
    $answer = new \Jazzee\Entity\Answer();
    $answer->setPage($this->_applicationPage->getPage());
    $answer->setApplicant($this->_applicant);
    $childAnswer = new \Jazzee\Entity\Answer;
    $childAnswer->setPage($answer->getPage()->getChildById($input->get('branching')));
    $answer->addChild($childAnswer);
    $this->_controller->getEntityManager()->persist($childAnswer);
    $answer->getJazzeeAnswer()->update($input);
    $this->_form = $this->makeForm();
    $this->_form->applyDefaultValues();
    $this->_controller->getEntityManager()->persist($answer);
    $this->_controller->addMessage('success', 'Answered Saved Successfully');
    //flush here so the answerId will be correct when we view
    $this->_controller->getEntityManager()->flush();
  }
  
  public function updateAnswer($input, $answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      foreach($answer->getElementAnswers() as $ea){
        $this->_controller->getEntityManager()->remove($ea);
        $answer->getElementAnswers()->removeElement($ea);
      }
      foreach($answer->getChildren() as $childAnswer){
        $this->_controller->getEntityManager()->remove($childAnswer);
        $answer->getChildren()->removeElement($childAnswer);
      }
      $childAnswer = new \Jazzee\Entity\Answer;
      $childAnswer->setPage($answer->getPage()->getChildById($input->get('branching')));
      $answer->addChild($childAnswer);
      $answer->getJazzeeAnswer()->update($input);
      $this->_form = $this->makeForm();
      $this->getForm()->applyDefaultValues();
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->addMessage('success', 'Answer Updated Successfully');
    }
  }
  
  public function fill($answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      $child = $answer->getJazzeeAnswer()->getActiveChild();
      $this->branchingForm($child->getPage()->getId());
      foreach($child->getPage()->getElements() as $element){
        $value = $element->getJazzeeElement()->formValue($child);
        if($value) $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
      }
      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }
}