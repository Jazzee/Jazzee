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
    $expression2 = $queryBuilder->expr()->andX();
    foreach($searchTerms as $key => $term){
      $expression2->add($queryBuilder->expr()->like("item.value", ":term{$key}"));
      $queryBuilder->setParameter("term{$key}", '%'.$term.'%');
    }
    $expression->add($expression2);
    $expression2 = $queryBuilder->expr()->andX();
    foreach($searchTerms as $key => $term){
      $expression3 = $queryBuilder->expr()->andX();
      foreach($variables as $key2 => $name){
        $expression3->add($queryBuilder->expr()->eq("variable.name", ":v{$key2}"));
        $expression3->add($queryBuilder->expr()->like("variable.value", ":term{$key}"));
        $queryBuilder->setParameter('v'.$key2, $name);
      }
      $expression2->add($expression3);
      $queryBuilder->setParameter("term{$key}", '%'.$term.'%');
    }
    $expression->add($expression2);
    $queryBuilder->andWhere($expression);
    return $queryBuilder->getQuery()->getResult();
  }

}