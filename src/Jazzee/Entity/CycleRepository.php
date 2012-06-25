<?php
namespace Jazzee\Entity;

/**
 * CycleRepository
 * Special Repository methods for Cycles
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class CycleRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * find best current cycle
   *
   * If a user doesn't have a cycle we need to search for an find the best current cycle for them
   * If there is a program then use that, otherwise just get the most recent cycle
   * @param Program $program
   * @return Cycle
   */
  public function findBestCycle(Program $program = null)
  {
    if ($program) {
      $query = $this->_em->createQuery('SELECT c FROM Jazzee\Entity\Cycle c JOIN c.applications a WHERE a.program = :program ORDER BY c.end DESC');
      $query->setParameter('program', $program);
    } else {
      $query = $this->_em->createQuery('SELECT c FROM Jazzee\Entity\Cycle c ORDER BY c.end DESC');
    }
    $result = $query->getResult();
    if (count($result)) {
      return $result[0];
    }

    return false;
  }

}