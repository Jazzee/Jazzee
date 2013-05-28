<?php
namespace Jazzee\Entity;

/**
 * ElementListItemRepository
 * Special Repository methods for ElementListItems
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ElementListItemRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * Find list item by search
   * @param \Jazzee\Entity\Element $element
   * @param array $searchTerms
   * @param array $variables
   * @return array
   */
  public function search(Element $element, $searchTerms, $variables = array())
  {
    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->add('select', 'item')
      ->from('Jazzee\Entity\ElementListItem', 'item')
      ->leftJoin('item.variables', 'variable');
    $queryBuilder->where('item.element = :elementId');
    $queryBuilder->setParameter('elementId', $element->getId());
    $expression = $queryBuilder->expr()->orX();
    foreach($searchTerms as $term){
      $expression->add($queryBuilder->expr()->like("item.value", ":term"));
      $expression2 = $queryBuilder->expr()->andX();
      foreach($variables as $key => $name){
        $expression2->add($queryBuilder->expr()->eq("variable.name", ":v{$key}"));
        $expression2->add($queryBuilder->expr()->like("variable.value", ":term"));
        $queryBuilder->setParameter('v'.$key, $name);
      }
      $expression->add($expression2);
      $queryBuilder->setParameter('term', '%'.$term.'%');
    }
    $queryBuilder->andWhere($expression);
    return $queryBuilder->getQuery()->getResult();
  }

}