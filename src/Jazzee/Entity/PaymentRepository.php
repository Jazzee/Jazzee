<?php
namespace Jazzee\Entity;

/**
 * PaymentRepository
 * Special Repository methods for Payments
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PaymentRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * Find all of the pending payments for a given payment type
   * @param string $status
   * @param \Jazzee\Entity\PaymentType $paymentType
   * 
   * @return array
   */
  public function findIdByStatusAndTypeArray($status, \Jazzee\Entity\PaymentType $paymentType)
  {
    $dql = 'SELECT p.id FROM \Jazzee\Entity\Payment p WHERE p.type = :paymentType AND p.status=:status';
    $query = $this->_em->createQuery($dql);
    $query->setParameter('paymentType', $paymentType->getId());
    $query->setParameter('status', $status);
    
    $ids = array();
    foreach($query->getArrayResult() as $arr){
      $ids[] = $arr['id'];
    }
    
    return $ids;
  }
}