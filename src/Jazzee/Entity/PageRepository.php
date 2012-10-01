<?php
namespace Jazzee\Entity;

/**
 * Page Repository
 * Special Repository methods for Pages
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PageRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * Check if a page has any answers associated with it
   * @param Page $page
   * @return boolean
   */
  public function hasAnswers(Page $page)
  {
    $query = $this->_em->createQuery('SELECT a.id FROM Jazzee\Entity\Answer a WHERE a.page = :pageId');
    $query->setParameter('pageId', $page->getId());
    $query->setMaxResults(1);
    $result = $query->getResult();

    return count($result);
  }

  /**
   * Check if a page has any answers associated with it in a specific cycle
   * @param Page $page
   * @param Cycle $cycle
   * @return boolean
   */
  public function hasCycleAnswers(Page $page, Cycle $cycle)
  {
    $query = $this->_em->createQuery('SELECT answer.id FROM Jazzee\Entity\Answer answer JOIN answer.applicant applicant JOIN applicant.application application WHERE answer.page = :pageId AND application.cycle = :cycleId');
    $query->setParameter('pageId', $page->getId());
    $query->setParameter('cycleId', $cycle->getId());
    $query->setMaxResults(1);
    $result = $query->getResult();

    return count($result);
  }

  /**
   * Check if a page has any answers associated with it for a specific application
   * @param Page $page
   * @param Application $application
   * @return boolean
   */
  public function hasApplicationAnswers(Page $page, Application $application)
  {
    $query = $this->_em->createQuery('SELECT COUNT(answer.id) as ancnt FROM Jazzee\Entity\Answer answer JOIN answer.applicant applicant WHERE answer.page = :pageId AND applicant.application = :applicationId');
    $query->setParameter('pageId', $page->getId());
    $query->setParameter('applicationId', $application->getId());
    $result = $query->getResult();
    return ($result[0]['ancnt'] > 0);
  }

}