<?php
namespace Jazzee\Entity;

/**
 * Application Event Listener
 * Whenever anything in an application changes catch it here so the cache can be invalidated
 * 
 * The Doctrien built in LIfeCycle Callbacks were not able to handle this correctly, 
 * or else I was never able to write them correctly
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 * */
class ApplicationEventListener
{
  /**
   * Reverse Cascade application and answer entities up the chain so we can mark
   * call special methods consitently
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
    $applications = array();
    foreach($entities as $entity){
      switch(get_class($entity)){
        case 'Jazzee\Entity\Application':
          $applications[$entity->getId()] = $entity;
          break;
        case 'Jazzee\Entity\ApplicationPage':
        case 'Jazzee\Entity\PDFTemplate':
          $applications[$entity->getApplication()->getId()] = $entity->getApplication();
          break;
        case 'Jazzee\Entity\Element':
          foreach($entity->getPage()->getApplicationPages() as $applicationPage){
            $applications[$applicationPage->getApplication()->getId()] = $applicationPage->getApplication();
          }
          break;
        case 'Jazzee\Entity\ElementListItem':
          foreach($entity->getElement()->getPage()->getApplicationPages() as $applicationPage){
            $applications[$applicationPage->getApplication()->getId()] = $applicationPage->getApplication();
          }
          break;
        case 'Jazzee\Entity\ElementListItemVariable':
          foreach($entity->getItem()->getElement()->getPage()->getApplicationPages() as $applicationPage){
            $applications[$applicationPage->getApplication()->getId()] = $applicationPage->getApplication();
          }
          break;
      }
    }
    
    $collections = array_merge(
      $uow->getScheduledCollectionUpdates(),
      $uow->getScheduledCollectionDeletions()
    );
    foreach($collections as $collection){
      switch($collection->getTypeClass()->name){
        case 'Jazzee\Entity\Tag':
          foreach($collection as $tag){
            foreach($entityManager->getRepository('Jazzee\Entity\Application')->findByTag($tag) as $application){
              $applications[$application->getId()] = $application;
            }
          }
          break;
      }
    }
    
    foreach($applications as $application){
        if(!$uow->isScheduledForDelete($application)){
          $application->clearCache();
        }
    }
  }
}