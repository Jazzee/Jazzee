<?php
namespace Jazzee\Entity\Answer;
/**
 * A single StandardPage Applicant Answer
 */
class Branching extends Standard
{
  public function update(\Foundation\Form\Input $input){
    $answer = $this->_answer->getChildByPage($this->_answer->getPage()->getChildById($input->get('branching')));
    foreach($answer->getPage()->getElements() as $element){
      foreach($element->getJazzeeElement()->getElementAnswers($input->get('el'.$element->getId())) as $elementAnswer){
        $answer->addElementAnswer($elementAnswer);
      }
    }
  }
  
  public function getActiveChild(){
    foreach($this->_answer->getChildren() as $child){
      if($child->getElementAnswers()->count()) return $child;
    }
    return false;
  }
}