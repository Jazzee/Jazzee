<?php
namespace Jazzee\Entity\Page;
/**
 * The Standard Application Page
 * 
 * Unless special functionaility is required all pages are of this type
 */
class Standard extends AbstractPage {
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
  
  /**
   * Skip an optional page
   * 
   */
  public function skip(){
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
  
  public function unskip(){
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
        foreach($element->getJazzeeElement()->getElementAnswers($input->get('el'.$element->getId())) as $elementAnswer){
          $answer->addElementAnswer($elementAnswer);
        }
      }
      $this->getForm()->applyDefaultValues();
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
        $value = $element->getJazzeeElement()->formValue($answer);
        if($value) $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
      }
      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }
  
  public function getAnswers(){
    return $this->_applicant->findAnswersByPage($this->_applicationPage->getPage());
  }
  
  public function getXmlAnswers(\DOMDocument $dom){
    $answers = array();
    foreach($this->_applicant->findAnswersByPage($this->_applicationPage->getPage()) as $answer){
      $answerXml = $dom->createElement('answer');
      $answerXml->setAttribute('answerId', $answer->getId());
      $answerXml->setAttribute('updatedAt', $answer->getUpdatedAt()->format('c'));
      foreach($this->_applicationPage->getPage()->getElements() as $element){
        $eXml = $dom->createElement('element');
        $eXml->setAttribute('elementId', $element->getId());
        $eXml->setAttribute('title', $element->getTitle());
        $eXml->setAttribute('type', $element->getType()->getClass());
        if($value = $element->getJazzeeElement()->rawValue($answer)) $eXml->appendChild($dom->createCDATASection($value));
        $answerXml->appendChild($eXml);
      }
      $answers[] = $answerXml;
    }
    return $answers;
  }
  
  public function getStatus(){
    $answers = $this->getAnswers();
    if(!$this->_applicationPage->isRequired() and count($answers) and $answers[0]->getPageStatus() == self::SKIPPED){
      return self::SKIPPED;
    }
    if(is_null($this->_applicationPage->getMin()) or count($this->getAnswers()) < $this->_applicationPage->getMin()){
      return self::INCOMPLETE;
    } else {
      return self::COMPLETE;
    }
  }
  
}