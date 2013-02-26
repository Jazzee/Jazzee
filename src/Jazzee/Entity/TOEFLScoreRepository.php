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
      $queryBuilder->andWhere('s.firstName LIKE :firstName');
      $queryBuilder->setParameter('firstName', $firstName);
    }

    if (!empty($lastName)) {
      $queryBuilder->andWhere('s.lastName LIKE :lastName');
      $queryBuilder->setParameter('lastName', $lastName);
    }

    $queryBuilder->orderBy('s.lastName, s.firstName');
    return $queryBuilder->getQuery()->getResult();
  }

  /**
   * Find all the ets scores in an array indexed by the reg-testdate
   *
   * @return array
   */
  public function findAllArray()
  {
    $query = $this->_em->createQuery("SELECT CONCAT(CONCAT(s.registrationNumber, s.testMonth),s.testYear) as uniqueIdentifier, s.id FROM Jazzee\Entity\TOEFLScore s");
    $results = array();
    foreach($query->getArrayResult() as $arr){
      $results[$arr['uniqueIdentifier']] = $arr['id'];
    }

    return $results;
  }
  
  /**
   * Match a scores to an answer by their IDs
   * 
   * @param integer $answerId
   * @param integer $scoreId
   */
  public function matchScore($answerId, $scoreId){ 
    $query = $this->_em->createQuery('UPDATE Jazzee\Entity\Answer a SET a.toeflScore = :scoreId WHERE a.id = :answerId');
    return $query->execute(array('answerId' => $answerId, 'scoreId' => $scoreId));
  }
  
  /**
   * Prune unmatched scores older than a date
   * 
   * @param \DateTime $olderThan
   */
  public function pruneUnmatchedScores(\DateTime $olderThan)
  {
    //since mysql subselect performance is so bad we do this as two queries
    $query = $this->_em->createQuery('SELECT DISTINCT score.id FROM Jazzee\Entity\Answer a JOIN a.toeflScore score WHERE score IS NOT NULL');
    $existingIds = array();
    foreach($query->getScalarResult() as $arr){
      $existingIds[] = $arr['id'];
    }
    $existingIds = implode(',', $existingIds);
    $query = $this->_em->createQuery("DELETE FROM \Jazzee\Entity\TOEFLScore s WHERE s.id NOT IN ({$existingIds}) AND s.testDate < :olderThan");
    return $query->execute(array('olderThan' => $olderThan));
  }

}