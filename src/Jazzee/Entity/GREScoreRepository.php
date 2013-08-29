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

    $return['cycles'] = array();
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

  /**
   * Find all the ets scores in an array indexed by the reg-testdate
   *
   * @param array $registrationNumbers an option array of registration numbers to limit the results to
   * @return array
   */
  public function findAllArray()
  {
    $dql = "SELECT CONCAT(CONCAT(s.registrationNumber, s.testMonth),s.testYear) as uniqueIdentifier, s.id FROM Jazzee\Entity\GREScore s";
    $query = $this->_em->createQuery($dql);
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
    $query = $this->_em->createQuery('UPDATE Jazzee\Entity\Answer a SET a.greScore = :scoreId WHERE a.id = :answerId');
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
    $query = $this->_em->createQuery('SELECT DISTINCT score.id FROM Jazzee\Entity\Answer a JOIN a.greScore score WHERE score IS NOT NULL');
    $existingIds = array();
    foreach($query->getScalarResult() as $arr){
      $existingIds[] = $arr['id'];
    }
    $existingIds = implode(',', $existingIds);
    $query = $this->_em->createQuery("DELETE FROM \Jazzee\Entity\GREScore s WHERE s.id NOT IN ({$existingIds}) AND s.testDate < :olderThan");
    return $query->execute(array('olderThan' => $olderThan));
  }

}