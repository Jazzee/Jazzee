<?php
namespace Jazzee\Entity;

/**
 * SchoolRepository
 *
 */
class SchoolRepository extends \Doctrine\ORM\EntityRepository
{
  
  /**
   */
  public function findByAnyKeyword( $keywords){
    $query = 'SELECT a FROM Jazzee\Entity\School a ';
    $query .= 'WHERE ';
    $i = 0;
    $params = array();
    foreach($keywords as $keyword){
       if($i > 0) $query .= " and ";
       $query .= '(a.fullName like :keyword'.$i.' ';
       $query .= 'or a.shortName like :keyword'.$i.' ';
       $query .= 'or a.city like :keyword'.$i.' ';
       $query .= 'or a.state like :keyword'.$i.' ';
       $query .= 'or a.zip like :keyword'.$i.' ';
       $query .= 'or a.country like :keyword'.$i.') ';

       $params['keyword'.$i] = '%'.$keyword.'%';
       $i++;
    }   

    $query .= " order by a.fullName";
    $query = $this->_em->createQuery($query);
    $query->setParameters($params);
    $query->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true);

    return $query->getArrayResult();    
  }


  public function findByFullName( $fullName){
    $query = 'SELECT a FROM Jazzee\Entity\School a ';
    $query .= 'WHERE a.fullName = :keyword ';

    $query = $this->_em->createQuery($query);
    $query->setParameter('keyword', $fullName);
    $query->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true);

    return $query->getArrayResult();
    
  }

  public function findById( $id){
    $query = 'SELECT a FROM Jazzee\Entity\School a ';
    $query .= 'WHERE a.id = :keyword ';

    $query = $this->_em->createQuery($query);
    $query->setParameter('keyword', $id);
    $query->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true);

    $schools = $query->getArrayResult();
    if(count($schools) == 0)
    return null;

    return $schools[0];
  }

}
