<?php
namespace Jazzee\Entity;

/**
 * ElementAnswerRepository
 * Special Repository methods for ElementAnswers
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ElementAnswerRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * Find Elements answers for an ElementType
   * @param ElementType $type
   * @param array $paramters list of any other ElementAnswer parameters to include
   * @param mixed $limit limit results to this many
   * @return array
   */
  public function findByType(ElementType $type, array $parameters = array(), $limit = false)
  {
    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->add('select', 'ea')
            ->from('Jazzee\Entity\ElementAnswer', 'ea');
    $queryBuilder->where('ea.element IN (SELECT e.id FROM Jazzee\Entity\Element as e WHERE e.type = :typeId)');
    $queryBuilder->setParameter('typeId', $type->getId());
    $possibleParameters = array(
      'position',
      'eShortString',
      'eText',
      'eDate',
      'eInteger',
      'eDecimal',
      'eBlob'
    );
    foreach($possibleParameters as $name){
      if (array_key_exists($name, $parameters)) {
        if($parameters[$name] === null ){
          $queryBuilder->andWhere("ea.{$name} IS NULL");
        } else {
          $queryBuilder->andWhere("ea.{$name} = :{$name}");
          $queryBuilder->setParameter($name, $parameters[$name]);
        }
      }
    }
    if ($limit) {
      $queryBuilder->setMaxResults($limit);
    }
    return $queryBuilder->getQuery()->getResult();
  }

}