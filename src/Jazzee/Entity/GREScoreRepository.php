<?php
namespace Jazzee\Entity;

/**
 * GREScoreRepository
 * Special Repository methods for GREScore
 * 
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class GREScoreRepository extends \Doctrine\ORM\EntityRepository
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
    $query = $this->_em->createQuery('SELECT count(g) as Total FROM Jazzee\Entity\GREScore g');
    $result = $query->getResult();
    $return['total'] = $result[0]['Total'];

    $query = $this->_em->createQuery("SELECT DISTINCT(CONCAT(CONCAT(g.testYear,'-'),g.cycleNumber) as Cycle FROM Jazzee\Entity\GREScore g ORDER BY Cycle DESC");

    foreach ($query->getResult() as $arr) {
      $return['cycles'][] = $arr['Cycle'];
    }

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
    $query = $this->_em->createQuery('SELECT s FROM Jazzee\Entity\GREScore s WHERE s.firstName LIKE :firstName AND s.lastName LIKE :lastName order by s.lastName, s.firstName');
    //ETS strips apostraphes from names
    $search = array("'");
    $query->setParameter('firstName', str_ireplace($search, '', $firstName));
    $query->setParameter('lastName', str_ireplace($search, '', $lastName));

    return $query->getResult();
  }

}