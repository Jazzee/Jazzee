<?php
namespace Jazzee\Entity;

/**
 * ApplicationRepository
 * Special Repository methods for Application to make searchign for special conditions easier
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicationRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * findOneByProgramAndCycle
   * Search for an Application using its Program and Cycle
   * @param Program $program
   * @param Cycle $cycle
   * @return Application
   */
  public function findOneByProgramAndCycle(Program $program, Cycle $cycle)
  {
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Application a WHERE a.program = :programId AND  a.cycle = :cycleId');
    $query->setParameter('programId', $program->getId());
    $query->setParameter('cycleId', $cycle->getId());
    $result = $query->getResult();
    if (count($result)) {
      return $result[0];
    }

    return false;
  }

  /**
   * Search for all the Applications belonging to a program
   * @param Program $program
   * @param boolean $onlyPublished only find publisehd applications
   * @param boolean $onlyVisible only find visible applications
   * @param array   $excludedIds any application ids to exclude
   * @return Doctrine\Common\Collections\Collection $applications
   */
  public function findByProgram(Program $program, $onlyPublished = false, $onlyVisible = false, array $excludedIds = array())
  {
//    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Application a JOIN a.cycle c WHERE a.program = :programId ORDER BY c.start DESC');
//    $query->setParameter('programId', $program->getId());
//
//    return $query->getResult();

    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->add('select', 'a')
            ->from('Jazzee\Entity\Application', 'a');

    $queryBuilder->where('a.program = :programId');
    $queryBuilder->setParameter('programId', $program->getId());

    if (($onlyPublished)) {
      $queryBuilder->andWhere('a.published = true');
    }
    if (($onlyVisible)) {
      $queryBuilder->andWhere('a.visible = true');
    }
    if(!empty($excludedIds)){
      //force integers from the values
      $safeids = array_map('intval',$excludedIds);
      $ids = implode(',', $safeids);
      $queryBuilder->andWhere("a.id NOT IN ({$ids})");
    }

    return $queryBuilder->getQuery()->getResult();
  }

  /**
   * Get the answer counts for each page.
   * @param Application $application
   * @return array
   */
  public function getPageAnswerCounts(Application $application)
  {
    $query = $this->_em->createQuery('SELECT page.id as pageId, count(answer.id) as answers FROM Jazzee\Entity\Answer answer JOIN answer.page page JOIN answer.applicant applicant WHERE answer.applicant IN (SELECT app1.id FROM Jazzee\Entity\Applicant app1 WHERE app1.application = :applicationId) GROUP BY answer.applicant, answer.page');
    $query->setParameter('applicationId', $application->getId());
    $pages = array();
    foreach ($query->getResult() as $arr) {
      if (!array_key_exists($arr['pageId'], $pages) or $pages[$arr['pageId']] < $arr['answers']) {
        $pages[$arr['pageId']] = $arr['answers'];
      }
    }
    return $pages;
  }

  /**
   * Find an application be the program short name and cycle name
   *
   * @param string $programShortName
   * @param string $cycleNamme
   * @return Application
   */
  public function findEasy($programShortName, $cycleName)
  {
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Application a WHERE a.program = (SELECT p FROM Jazzee\Entity\Program p WHERE p.shortName = :programShortName) AND  a.cycle = (SELECT c FROM \Jazzee\Entity\Cycle c WHERE c.name= :cycleName)');
    $query->setParameter('programShortName', $programShortName);
    $query->setParameter('cycleName', $cycleName);
    $result = $query->getResult();
    if (count($result)) {
      return $result[0];
    }

    return false;
  }

}