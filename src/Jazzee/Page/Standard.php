<?php
namespace Jazzee\Page;
/**
 * The Standard Application Page
 * 
 * Unless special functionaility is required all pages are of this type
 */
class Standard extends AbstractPage {
  const APPLY_PAGE_ELEMENT = 'Standard-apply_page';
  const APPLICANTS_SINGLE_ELEMENT = 'Standard-applicants_single';
  const APPLY_STATUS_ELEMENT = 'Standard-apply_status';
  
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
      $element->getJazzeeElement()->setController($this->_controller);
      $element->getJazzeeElement()->addToField($field);
    }
    $form->setCSRFToken($this->_controller->getCSRFToken());
    $form->newButton('submit', 'Save');
    $form->newButton('reset', 'Clear Form');
    return $form;
  }
  
  /**
   * Skip an optional page
   * 
   */
  public function do_skip(){
    if(count($this->getAnswers())){
      $this->_controller->addMessage('error', 'You must delete your existing answers before you can skip this page.');
      return false;
    }
    if(!$this->_applicationPage->isRequired()){
      $answer = new \Jazzee\Entity\Answer();
      $answer->setPage($this->_applicationPage->getPage());
      $this->_applicant->addAnswer($answer);
      $answer->setPageStatus(self::SKIPPED);
      $this->_controller->getEntityManager()->persist($answer);
    }
  }
  
  public function do_unskip(){
    $answers = $this->getAnswers();
    if(count($answers) and $answers[0]->getPageStatus() == self::SKIPPED){
      $this->_applicant->getAnswers()->removeElement($answers[0]);
      $this->_controller->getEntityManager()->remove($answers[0]);
    }
  }
  
  public function newAnswer($input){
    if(is_null($this->_applicationPage->getMax()) or count($this->getAnswers()) < $this->_applicationPage->getMax()){
      $answer = new \Jazzee\Entity\Answer();
      $answer->setPage($this->_applicationPage->getPage());
      $this->_applicant->addAnswer($answer);
      foreach($this->_applicationPage->getPage()->getElements() as $element){
        $element->getJazzeeElement()->setController($this->_controller);
        foreach($element->getJazzeeElement()->getElementAnswers($input->get('el'.$element->getId())) as $elementAnswer){
          $answer->addElementAnswer($elementAnswer);
        }
      }
      $this->_form->applyDefaultValues();
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->addMessage('success', 'Answered Saved Successfully');
      //flush here so the answerId will be correct when we view
      $this->_controller->getEntityManager()->flush();
    }
  }
  
  public function updateAnswer($input, $answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      foreach($answer->getElementAnswers() as $ea){
        $answer->getElementAnswers()->removeElement($ea);
        $this->_controller->getEntityManager()->remove($ea);
      }
      foreach($this->_applicationPage->getPage()->getElements() as $element){
        $element->getJazzeeElement()->setController($this->_controller);
        foreach($element->getJazzeeElement()->getElementAnswers($input->get('el'.$element->getId())) as $elementAnswer){
          $answer->addElementAnswer($elementAnswer);
        }
      }
      $this->getForm()->applyDefaultValues();
      $this->getForm()->setAction($this->_controller->getActionPath());
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->addMessage('success', 'Answer Updated Successfully');
    }
  }

  public function deleteAnswer($answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      $this->_controller->getEntityManager()->remove($answer);
      $this->_applicant->getAnswers()->removeElement($answer);
      $this->_applicant->markLastUpdate();
      $this->_controller->getEntityManager()->persist($this->_applicant);
      $this->_controller->addMessage('success', 'Answered Deleted Successfully');
    }
  }
  
  public function fill($answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      foreach($this->_applicationPage->getPage()->getElements() as $element){
        $element->getJazzeeElement()->setController($this->_controller);
        $value = $element->getJazzeeElement()->formValue($answer);
        if($value) $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
      }
      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }
  
  /**
   * Get all the answers for this page
   * @return \Jazzee\Entity\Answer
   */
  public function getAnswers(){
    return $this->_applicant->findAnswersByPage($this->_applicationPage->getPage());
  }
  
  public function getXmlAnswers(\DOMDocument $dom){
    $answers = array();
    foreach($this->_applicant->findAnswersByPage($this->_applicationPage->getPage()) as $answer){
      $answers[] = $this->xmlAnswer($dom, $answer);
    }
    return $answers;
  }
  
  public function getStatus(){
    $answers = $this->getAnswers();
    if(!$this->_applicationPage->isRequired() and count($answers) and $answers[0]->getPageStatus() == self::SKIPPED){
      return self::SKIPPED;
    }
    if(is_null($this->_applicationPage->getMin()) and count($answers)) return self::COMPLETE;
    if(!is_null($this->_applicationPage->getMin()) and count($answers) >= $this->_applicationPage->getMin()) return self::COMPLETE;
    
    return self::INCOMPLETE;
  }
}