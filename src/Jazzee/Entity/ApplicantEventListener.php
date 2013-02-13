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
   * Reverse Cascade applicant and answer entities up the chain so we can mark
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
    $applicantMetadata = $entityManager->getClassMetadata('Jazzee\Entity\Applicant');
    foreach($entities as $entity){
      $applicant = false;
      switch(get_class($entity)){
        case 'Jazzee\Entity\Applicant':
          $applicant = $entity;
          break;
        case 'Jazzee\Entity\Answer':
        case 'Jazzee\Entity\Attachment':
        case 'Jazzee\Entity\Decision':
        case 'Jazzee\Entity\Thread':
        case 'Jazzee\Entity\Decision':
          $applicant = $entity->getApplicant();
          break;
        case 'Jazzee\Entity\Message':
          $applicant = $entity->getThread()->getApplicant();
          break;
        case 'Jazzee\Entity\ElementAnswer':
          $applicant = $entity->getAnswer()->getApplicant();
          break;
        case 'Jazzee\Entity\Payment':
          $applicant = $entity->getAnswer()->getApplicant();
          break;
        case 'Jazzee\Entity\Message':
          $entity = new \Jazzee\Entity\PaymentVariable();
          $applicant = $entity->getPayment()->getAnswer()->getApplicant();
          break;
      }
      if($applicant and !$uow->isScheduledForDelete($applicant)){
        $applicant->markLastUpdate();
        if($uow->isScheduledForUpdate($applicant) or $uow->isScheduledForInsert($applicant)){
          $uow->recomputeSingleEntityChangeSet($applicantMetadata, $applicant);
        } else {
          $entityManager->persist($applicant);
          $uow->computeChangeSet($applicantMetadata, $applicant);
        }
      }
    }
  }
}