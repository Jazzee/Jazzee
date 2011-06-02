<?php
namespace Jazzee\Entity\Answer;
/**
 * A single StandardPage Applicant Answer
 */
class Standard implements \Jazzee\Answer 
{
 /**
  * The Answer entity
  * @var \Jazzee\Entity\Answer
  */
  protected $_answer;

 /**
  * Contructor
  * 
  * Store the answer and create the elements array
  * @param \Doctrine\ORM\EntityManager $em
  * @param \Jazzee\Entity\Answer $answer
  */
  public function __construct(\Jazzee\Entity\Answer $answer){
    $this->_answer = $answer;
  }

  /**
   * 
   * @see Jazzee.Answer::getID()
   */
  public function getID(){
    return $this->_answer->getId();
  }

  /**
   * 
   * @see Jazzee.Answer::update()
   */
  public function update(\Foundation\Form\Input $input){
    foreach($this->_answer->getPage()->getElements() as $element){
      foreach($element->getJazzeeElement()->getElementAnswers($input->get('el'.$element->getId())) as $elementAnswer){
        $this->_answer->addElementAnswer($elementAnswer);
      }
    }
  }
  
  public function applyTools(){
    return array(
      'Edit' => '/edit/' . $this->_answer->getId(),
      'Delete' => '/delete/' . $this->_answer->getId(),
    );
  }
  
  public function applicantsTools(){
    $arr = array(
      array(
        'title' => 'Edit',
         'class' => 'editAnswer',
         'path' => '/editAnswer/' . $this->_answer->getId()
       ),
       array(
        'title' => 'Delete',
         'class' => 'deleteAnswer',
         'path' => '/deleteAnswer/' . $this->_answer->getId()
       ),
       array(
        'title' => 'Verify',
         'class' => 'verifyAnswer',
         'path' => '/verifyAnswer/' . $this->_answer->getId()
       )
    );
    return $arr;
  }

  public function applyStatus(){
    $arr = array(
      'Last updated' => $this->_answer->getUpdatedAt()->format('M d Y g:i a')
    );
    if($this->_answer->getPublicStatus()){
      $arr['Status'] = $this->_answer->getPublicStatus()->getName();
    }
    return $arr;
  }
  
  public function applicantsStatus(){
    $arr = array(
      'Last updated' => $this->_answer->getUpdatedAt()->format('M d Y g:i a')
    );
    if($this->_answer->getPublicStatus()){
      $arr['Public Status'] = $this->_answer->getPublicStatus()->getName();
    }
    if($this->_answer->getPrivateStatus()){
      $arr['Private Status'] = $this->_answer->getPrivateStatus()->getName();
    }
    return $arr;
  }
}
?>