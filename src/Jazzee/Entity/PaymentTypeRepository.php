<?php
namespace Jazzee\Entity;

/**
 * PaymentTypeRepository
 * Special Repository methods for PaymentType
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PaymentTypeRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * All types with at least one payment
   * @return array
   */
  public function findWithPayment()
  {
    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->add('select', 'type')
      ->from('Jazzee\Entity\PaymentType', 'type')
      ->innerJoin('type.payments', 'payment')
      ->orderBy('type.name');
    return $queryBuilder->getQuery()->getResult();
  }

}