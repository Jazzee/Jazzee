<?php
namespace Jazzee\Entity\PaymentType;
/**
 * Abstract PaymeType Class
 *
 */
abstract class AbstractPaymentType implements \Jazzee\PaymentType{
  /**
   * Status text constants
   */
  const PENDING_TEXT = 'pending';
  const SETTLED_TEXT = 'settled';
  const REJECTED_TEXT = 'rejected';
  const REFUNDED_TEXT = 'refunded';
  
  /**
   * The PaymentType Model
   * @var PaymentType
   */
  protected $_paymentType;
  
  /**
   * Constructor
   * @param \Jazzee\Entity\PaymentType $paymentType
   */
  public function __construct(\Jazzee\Entity\PaymentType $paymentType){
    $this->_paymentType = $paymentType;
  }
  
}

