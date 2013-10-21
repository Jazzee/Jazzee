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
  public function findIdByStatusAndTypeArray(
      $status,
      \Jazzee\Entity\PaymentType $paymentType,
      \DateTime $from = null,
      \DateTime $to = null
  ){
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
  
  /**
   * Find all of the payments in a status
   * 
   * @param string $status
   * @param array $paymentTypes leve empty for all types
   * @param \DateTime $from
   * @param \DateTime $to
   * @param \Jazzee\Entity\Program $program
   * @param \Jazzee\Entity\Cycle $cycle
   * @return array
   */
  public function findByStatusArray(
      $status,
      array $paymentTypes,
      \DateTime $from = null,
      \DateTime $to = null,
      \Jazzee\Entity\Program $program = null,
      \Jazzee\Entity\Cycle $cycle = null
  ){
    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->add(
        'select', 
        'payment, type, answer.updatedAt, variables, ' .
        'applicant.id AS applicantId, applicant.firstName AS applicantFirstName, applicant.lastName AS applicantLastName, ' .
        'program.name AS programName, program.id as programId, cycle.name AS cycleName');
    $queryBuilder->from('Jazzee\Entity\Payment', 'payment');
    
    $queryBuilder->leftJoin('payment.type', 'type');
    $queryBuilder->leftJoin('payment.variables', 'variables');
    $queryBuilder->leftJoin('payment.answer', 'answer');
    $queryBuilder->leftJoin('answer.applicant', 'applicant');
    $queryBuilder->leftJoin('applicant.application', 'application');
    $queryBuilder->leftJoin('application.cycle', 'cycle');
    $queryBuilder->leftJoin('application.program', 'program');

    $queryBuilder->where('payment.status = :status');
    $queryBuilder->setParameter('status', $status);
    
    if(!empty($paymentTypes)){
      $expression = $queryBuilder->expr()->orX();
      foreach($paymentTypes as $key => $paymentType){
        $paramKey = 'paymentType' . $key;
        $expression->add($queryBuilder->expr()->eq("payment.type", ":{$paramKey}"));
        $queryBuilder->setParameter($paramKey, $paymentType);
      }
      $queryBuilder->andWhere($expression);
    }
    if(!is_null($from)){
        $queryBuilder->andWhere('answer.updatedAt > :from');
        $queryBuilder->setParameter('from', $from);
    }
    if(!is_null($to)){
        if(!is_null($from) and $to < $from){
            throw new \Exception("From date {$from->format('c')} must be before To date {$to->format('c')}");
        }
        $queryBuilder->andWhere('answer.updatedAt < :to');
        $queryBuilder->setParameter('to', $to);
    }
    if(!is_null($program)){
        $queryBuilder->andWhere('application.program = :program');
        $queryBuilder->setParameter('program', $program);
    }
    if(!is_null($cycle)){
        $queryBuilder->andWhere('application.cycle = :cycle');
        $queryBuilder->setParameter('cycle', $cycle);
    }
    
    $queryBuilder->orderBy('answer.updatedAt', 'DESC');
    
    $query = $queryBuilder->getQuery();
    $query->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    
    $payments = array();
    foreach ($query->execute() as $arr) {
      $paymentArr = $arr[0];
      unset($arr[0]);
      $payments[] =  array_merge($paymentArr, $arr);
    }
    return $payments;
  }
}