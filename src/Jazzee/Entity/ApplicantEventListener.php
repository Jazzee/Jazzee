<?php
namespace Jazzee\Entity;

/**
 * Applicant Event Listener
 * In order to updat the applicant meta data we listen for events to answers, payments, 
 * elementAnswers, etc
 * 
 * The Doctrien built in LIfeCycle Callbacks were not able to handle this correctly, 
 * or else I was never able to write them correctly
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 * */
class ApplicantEventListener
{
  /**
   * Before persisting anything check if we should mark last update on the applicant
   * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs
   */
  public function prePersist(\Doctrine\ORM\Event\LifecycleEventArgs $eventArgs)
  {
    $events = array(
      'Jazzee\Entity\Answer' => 'handleAnswer',
      'Jazzee\Entity\Applicant'=> 'handleApplicant',
      'Jazzee\Entity\Attachment' => 'handleAttachment',
      'Jazzee\Entity\Decision' => 'handleDecision',
      'Jazzee\Entity\Thread' => 'handleThread',
      'Jazzee\Entity\Message' => 'handleMessage',
      'Jazzee\Entity\Payment' => 'handlePayment',
      'Jazzee\Entity\PaymentVariable' => 'handlePaymentVariable'
    );
    
    foreach($events as $entityType => $methodName){
      if($eventArgs->getEntity() instanceof $entityType){
        $this->$methodName($eventArgs->getEntity());
      }
    }
  }
  
  /**
   * Whenever any of these are deleted update the applicant
   * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs
   */
  public function preRemove(\Doctrine\ORM\Event\LifecycleEventArgs $eventArgs)
  {
    $events = array(
      'Jazzee\Entity\Answer' => 'handleAnswer',
      'Jazzee\Entity\Attachment' => 'handleAttachment',
      'Jazzee\Entity\Decision' => 'handleDecision',
      'Jazzee\Entity\Thread' => 'handleThread',
      'Jazzee\Entity\Message' => 'handleMessage',
      'Jazzee\Entity\Payment' => 'handlePayment',
      'Jazzee\Entity\PaymentVariable' => 'handlePaymentVariable'
    );
    
    foreach($events as $entityType => $methodName){
      if($eventArgs->getEntity() instanceof $entityType){
        $this->$methodName($eventArgs->getEntity());
      }
    }
  }
  
  /**
   * Mark updates for an answer
   * @param \Jazzee\Entity\Answer $answer
   */
  protected function handleAnswer(Answer $answer){
    if ($parent = $answer->getParent()) {
      //child pages should update their parents
      $parent->markLastUpdate();
    }
    if($applicant = $answer->getApplicant()){
      $this->handleApplicant($applicant);
    }
    $answer->markLastUpdate();
  }
  
  /**
   * Mark updates for an attachment
   * @param \Jazzee\Entity\Attachment $attachment
   */
  protected function handleAttachment(Attachment $attachment){
    if($applicant = $attachment->getApplicant()){
      $this->handleApplicant($applicant);
    }
  }
  
  /**
   * Mark updates for an decision
   * @param \Jazzee\Entity\Decision $decision
   */
  protected function handleDecision(Decision $decision){
    if($applicant = $decision->getApplicant()){
      $this->handleApplicant($applicant);
    }
  }
  
  /**
   * Mark updates for an thread
   * @param \Jazzee\Entity\Thread $threa
   */
  protected function handleThread(Thread $thread){
    if($applicant = $thread->getApplicant()){
      $this->handleApplicant($applicant);
    }
  }
  
  /**
   * Mark updates for an message
   * @param \Jazzee\Entity\Message $message
   */
  protected function handleMessage(Message $message){
    if($applicant = $message->getThread()->getApplicant()){
      $this->handleApplicant($applicant);
    }
  }
  
  /**
   * Mark updates for an applicant
   * @param \Jazzee\Entity\Applicant $applicant
   */
  protected function handleApplicant(Applicant $applicant){
    $applicant->markLastUpdate();
  }
  
  /**
   * Mark updates for an elementAnswer
   * @param \Jazzee\Entity\ElementAnswer $elementAnswer
   */
  protected function handleElementAnswer(ElementAnswer $elementAnswer){
    $elementAnswer->getAnswer()->markLastUpdate();
    $this->handleApplicant($elementAnswer->getAnswer()->getApplicant());
  }
  
  /**
   * Mark updates for an payment
   * @param \Jazzee\Entity\Payment $payment
   */
  protected function handlePayment(Payment $payment){
    $payment->getAnswer()->markLastUpdate();
    $this->handleApplicant($payment->getAnswer()->getApplicant());
  }
  
  /**
   * Mark updates for an payment variable
   * @param \Jazzee\Entity\PaymentVariable $var
   */
  protected function handlePaymentVariable(PaymentVariable $var){
    $var->getPayment()->getAnswer()->markLastUpdate();
    $this->handleApplicant($var->getPayment()->getAnswer()->getApplicant());
  }
}