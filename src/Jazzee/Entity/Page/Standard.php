<?php
namespace Jazzee\Entity\Page;
/**
 * The Standard Application Page
 * 
 * Unless special functionaility is required all pages are of this type
 */
class Standard extends AbstractPage {
  /**
   * The answer class for this page type
   * @const string
   */
  const ANSWER_CLASS = '\Jazzee\Entity\Answer\Standard';
  
  /**
   * 
   * @see Jazzee\Page.AbstractPage::makeForm()
   */
  protected function makeForm(){
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    foreach($this->_applicationPage->getPage()->getElements() as $element){
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newButton('submit', 'Save');
    $form->newButton('reset', 'Clear Form');
    return $form;
  }
  
  
  public function newAnswer($input){
    $answer = new \Jazzee\Entity\Answer();
    $answer->setPage($this->_applicationPage->getPage());
    $answer->setApplicant($this->_applicant);
    $answer->getJazzeeAnswer()->setEntityManager($this->_controller->getEntityManager());
    $answer->getJazzeeAnswer()->update($input);
    $this->_form->applyDefaultValues();
    $this->_controller->getEntityManager()->persist($answer);
    $this->_controller->addMessage('success', 'Answered Saved Successfully');
    //flush here so the answerId will be correct when we view
    $this->_controller->getEntityManager()->flush();
  }
  
  public function updateAnswer($input, $answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      $answer->getJazzeeAnswer()->setEntityManager($this->_controller->getEntityManager());
      $answer->getJazzeeAnswer()->update($input);
      $this->getForm()->applyDefaultValues();
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->addMessage('success', 'Answer Updated Successfully');
    }
  }

  public function deleteAnswer($answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      $this->_controller->getEntityManager()->remove($answer);
      $this->_applicant->getAnswers()->removeElement($answer);
      $this->_controller->addMessage('success', 'Answered Deleted Successfully');
    }
  }
  
  
  public function fill($answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      foreach($this->_applicationPage->getPage()->getElements() as $element){
        $value = $element->getJazzeeElement()->formValue($answer);
        if($value) $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
      }
      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }
  
  public function getAnswers(){
    return $this->_applicant->findAnswersByPage($this->_applicationPage->getPage());
  }
  
  public function getStatus(){
    return self::INCOMPLETE;
    if(count($this->getAnswers()) < $this->applicationPage->min){
      return self::INCOMPLETE;
    } else {
      return self::COMPLETE;
    }
  }
  
}