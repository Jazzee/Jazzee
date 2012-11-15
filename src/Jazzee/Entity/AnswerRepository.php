<?php
namespace Jazzee\Entity;

/**
 * AnswerRepository
 * Special Repository methods for answers
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AnswerRepository extends \Doctrine\ORM\EntityRepository
{
  
  /**
   * Find any unmatched score answers for a page
   * @param \Jazzee\Entity\Page $page
   * @return array
   */
  public function findUnMatchedScores(Page $page){
    $query = 'SELECT a, e FROM Jazzee\Entity\Answer a ';
    $query .= 'LEFT JOIN a.elements e ';
    $query .= 'WHERE a.page = :pageId AND a.pageStatus IS NULL AND a.greScore IS NULL AND a.toeflScore IS NULL';
    
    $query = $this->_em->createQuery($query);
    $query->setParameter('pageId', $page->getId());
    $query->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true);

    return $query->getArrayResult();
    
  }

}
