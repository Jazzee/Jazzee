<?php
namespace Jazzee\Entity;

/**
 * SchoolRepository
 *
 */
class SchoolRepository extends \Doctrine\ORM\EntityRepository
{
  /**
   * Search for schools
   * @param string $string
   * 
   * @return array \Jazzee\Entity\School
   */
  public function search($string)
  {
    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->add('select', 'school')->from('Jazzee\Entity\School', 'school');
    $searchTerms = explode(' ',$string);
    $queryBuilder->andWhere($this->getSearchExpression($searchTerms));
    foreach($searchTerms as $key => $value){
      $queryBuilder->setParameter("term{$key}", '%'.$value.'%');
    }
    $queryBuilder->orderBy('school.name');
    return $queryBuilder->getQuery()->getResult();   
  }

  /**
   * Get the count of schools which will be retured by a search
   * @param string $string
   * 
   * @return integer
   */
  public function getSearchCount($string)
  {
    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->add('select', 'count(school.id)')->from('Jazzee\Entity\School', 'school');
    $searchTerms = explode(' ',$string);
    $queryBuilder->andWhere($this->getSearchExpression($searchTerms));
    foreach($searchTerms as $key => $value){
      $queryBuilder->setParameter("term{$key}", '%'.$value.'%');
    }
    return $queryBuilder->getQuery()->getSingleScalarResult();   
  }

  /**
   * Get the search expression for finding schools by string
   * @param array $searchTerms
   * 
   * @return \Doctrine\ORM\Query\Expr\Andx
   */
  protected function getSearchExpression(array $searchTerms)
  {
    $expression = new \Doctrine\ORM\Query\Expr\Andx;
    $variables = array('name', 'searchTerms', 'city', 'state', 'country', 'postalCode', 'code');
    foreach($searchTerms as $key => $term){
      $expression2 = new \Doctrine\ORM\Query\Expr\Orx;
      foreach($variables as $name){
        $expression2->add(new \Doctrine\ORM\Query\Expr\Comparison("school.{$name}", 'LIKE', ":term{$key}"));
      }
      $expression->add($expression2);
    }

    return $expression;
  }
  
  /**
   * Count the schools
   * @return integer
   */
  public function getCount(){
    $query = $this->_em->createQuery('SELECT COUNT(s.id) FROM Jazzee\Entity\School s');

    return $query->getSingleScalarResult();    
  }

}