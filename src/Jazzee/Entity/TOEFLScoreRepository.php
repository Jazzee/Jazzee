<?php
namespace Jazzee\Entity;

/**
 * TOEFLScoreRepository
 * Special Repository methods for TOEFLScore
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class TOEFLScoreRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * Score stats
   *
   * Get statistics on scores in the system
   * @return array
   */
  public function getStatistics()
  {
    $return = array();
    $query = $this->_em->createQuery('SELECT count(t) as Total FROM Jazzee\Entity\TOEFLScore t');
    $result = $query->getResult();
    $return['total'] = $result[0]['Total'];

    return $return;
  }

  /**
   * Find scores by name
   *
   * @param string $firstName
   * @param string $lastName
   * @return \Doctrine\ORM\Collection
   */
  public function findByName($firstName, $lastName)
  {
    ////ETS strips apostraphes from names
    $search = array("'");
    $firstName = str_ireplace($search, '', $firstName);
    $lastName = str_ireplace($search, '', $lastName);

    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->add('select', 's')->from('Jazzee\Entity\TOEFLScore', 's');

    if (!empty($firstName)) {
      $queryBuilder->where('s.firstName LIKE :firstName');
      $queryBuilder->setParameter('firstName', $firstName);
    }

    if (!empty($lastName)) {
      $queryBuilder->where('s.lastName LIKE :lastName');
      $queryBuilder->setParameter('lastName', $lastName);
    }

    $queryBuilder->orderBy('s.lastName, s.firstName');
    return $queryBuilder->getQuery()->getResult();
  }

}