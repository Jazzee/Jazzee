<?php
namespace Jazzee\Entity;

/**
 * DisplayRepository
 * Special Repository methods for Display
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class DisplayRepository extends \Doctrine\ORM\EntityRepository
{
  /**
   * Find users displays for an applicaiton as an array
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Application $application
   * 
   * @return array
   */
  public function findByUserApplicationArray(User $user, Application $application)
  {
    $queryBuilder = $this->makeQuery();
    $queryBuilder->andWhere('display.user = :userId');
    $queryBuilder->setParameter('userId', $user->getId());
    $queryBuilder->andWhere('display.application = :applicationId');
    $queryBuilder->setParameter('applicationId', $application->getId());
    
    $query = $queryBuilder->getQuery();
    return $query->getArrayResult();
  }
  
  /**
   * Find users displays for an applicaiton as an array
   * @param interger $id
   * 
   * @return array
   */
  public function findArray($id)
  {
    $queryBuilder = $this->makeQuery();
    $queryBuilder->andWhere('display.id = :id');
    $queryBuilder->setParameter('id', $id);
    
    $query = $queryBuilder->getQuery();
    if($result = $query->getArrayResult()){
      return $result[0];
    } 

    return false;
  }
  
  /**
   * Create a QueryBuilder to use elsewhere
   * @return \Doctrine\ORM\QueryBuilder
   */
  protected function makeQuery(){
    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->from('Jazzee\Entity\Display', 'display');
    $queryBuilder->add('select', 'display, pages, elements, applicationPage, element');
    $queryBuilder->leftJoin('display.pages', 'pages');
    $queryBuilder->leftJoin('pages.applicationPage', 'applicationPage');
    $queryBuilder->leftJoin('pages.elements', 'elements');
    $queryBuilder->leftJoin('elements.element', 'element');
    $queryBuilder->orderBy('display.name');
    
    return $queryBuilder;
  }

}
