<?php
namespace Jazzee\Entity;

/**
 * Answer Event Listener
 * In order to update the answer updateAt we have to listen to answer as well as
 * elemetn answers and payments
 * 
 * The Doctrien built in LIfeCycle Callbacks were not able to handle this correctly, 
 * or else I was never able to write them correctly
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 * */
class AnswerEventListener
{
  /**
   * Reverse Cascade answer entities up the chain so we can mark
   * updatedAt or call special methods consitently
   * 
   * We have to do this operation usign onFlush so we can catch inserts, updated
   * and deletes for all associations
   * 
   * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
   */
  public function onFlush(\Doctrine\ORM\Event\OnFlushEventArgs $eventArgs)
  {
    $entityManager = $eventArgs->getEntityManager();
    $uow = $entityManager->getUnitOfWork();
    $entities = array_merge(
      $uow->getScheduledEntityInsertions(),
      $uow->getScheduledEntityUpdates(),
      $uow->getScheduledEntityDeletions()
    );
    $answerMetadata = $entityManager->getClassMetadata('Jazzee\Entity\Answer');
    foreach($entities as $entity){
      $answer = null;
      switch(get_class($entity)){
        case 'Jazzee\Entity\Answer':
          $answer = $entity;
          break;
        case 'Jazzee\Entity\ElementAnswer':
        case 'Jazzee\Entity\Payment':
          $answer = $entity->getAnswer();
          break;
        case 'Jazzee\Entity\PaymentVariable':
          $answer = $entity->getPayment()->getAnswer();
          break;
      }
      while(!is_null($answer)) {
        if(!$uow->isScheduledForDelete($answer)){
          $answer->markLastUpdate();
          if($uow->isScheduledForUpdate($answer) or $uow->isScheduledForInsert($answer)){
            $uow->recomputeSingleEntityChangeSet($answerMetadata, $answer);
          } else {
            $entityManager->persist($answer);
            $uow->computeChangeSet($answerMetadata, $answer);
          }
          $answer = $answer->getParent();
        } else {
          $answer = null;
        }
      }
    }
  }
  
  /**
   * When element answers are deleted they need to notify thier jazzee elements
   * 
   * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
   */
  public function preRemove(\Doctrine\ORM\Event\LifecycleEventArgs $eventArgs)
  {
    switch(get_class($eventArgs->getEntity())){
      case 'Jazzee\Entity\Answer':
        $answer = $eventArgs->getEntity();
        foreach($answer->getElementAnswers() as $elementAnswer){
          $elementAnswer->preRemove();
        }
        if($attachment = $answer->getAttachment()){
          $attachment->preRemove();
        }
        break;
    }
  }
  
}