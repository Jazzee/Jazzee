<?php
namespace Jazzee\Entity;

/**
 * Tag Repository
 * Special Repository methods for Tags
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class TagRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * Find all the tags for an application
   *
   * @param Application $application
   * @return array
   */
  public function findByApplication(Application $application)
  {
    $query = $this->_em->createQuery('SELECT tag FROM Jazzee\Entity\Tag tag INDEX BY tag WHERE tag IN (SELECT DISTINCT t.id FROM Jazzee\Entity\Applicant a JOIN a.tags t WHERE a.application = :applicationId) order by tag.title ASC');

    $query->setParameter('applicationId', $application->getId());

    return $query->getResult();
  }

}