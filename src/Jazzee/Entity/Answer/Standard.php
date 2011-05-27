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
   * @var \Doctrine\ORM\EntityManager
   */
  protected $_em;
  
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
   * @see Jazzee.Answer::setEntityManager()
   */
  public function setEntityManager(\Doctrine\ORM\EntityManager $em){
    $this->_em = $em;
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
    foreach($this->_answer->getElementAnswers() as $ea){
      $this->_em->remove($ea);
      $this->_answer->getElementAnswers()->removeElement($ea);
    }
    foreach($this->_answer->getPage()->getElements() as $element){
      foreach($element->getJazzeeElement()->getElementAnswers($input->get('el'.$element->getId())) as $elementAnswer){
        $elementAnswer->setAnswer($this->_answer);
        $this->_em->persist($elementAnswer); 
      }
    }
  }
  
  public function getElements(){
    return array();
    $arr = array();
    foreach($this->elements as $id => $element){
      $arr[$id] = $element->title;
    }
    return $arr;
  }

  public function getFormValueForElement($elementID){
    return '';
    if(isset($this->elements[$elementID])){
      return $this->elements[$elementID]->formValue();
    }
    return false;
  }
  
  public function applyTools(){
    return array(
      'Edit' => '/edit/' . $this->_answer->getId(),
      'Delete' => '/delete/' . $this->_answer->getId(),
    );
  }
  
  public function applicantTools(){
    return array();
    $arr = array(
      array(
        'title' => 'Edit',
         'class' => 'editAnswer',
         'path' => "editAnswer/{$this->answer->id}"
       ),
       array(
        'title' => 'Delete',
         'class' => 'deleteAnswer',
         'path' => "deleteAnswer/{$this->answer->id}"
       ),
       array(
        'title' => 'Verify',
         'class' => 'verifyAnswer',
         'path' => "verifyAnswer/{$this->answer->id}"
       )
    );
    return $arr;
  }

  public function applyStatus(){
    return array();
    $arr = array(
      'Last updated' => date('M d Y g:i a',$this->getUpdatedAt())
    );
    if($this->answer->publicStatus){
      $arr['Status'] = $this->answer->PublicStatus->name;
    }
    return $arr;
  }
  
  public function applicantStatus(){
    return array();
    $arr = array(
      'Last updated' => date('M d Y g:i a',$this->getUpdatedAt())
    );
    if($this->answer->publicStatus){
      $arr['Public Status'] = $this->answer->PublicStatus->name;
    }
    if($this->answer->privateStatus){
      $arr['Private Status'] = $this->answer->PrivateStatus->name;
    }
    return $arr;
  }
  
  public function getAttachment(){
    return $this->_answer->getAttachment();
  }
  
  public function getUpdatedAt(){
    return $this->_answer->getUpdatedAt();
  }
}
?>