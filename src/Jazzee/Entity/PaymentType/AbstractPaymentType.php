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
  
  /**
   * Get Status Text
   * Get the ApplyPayment status text for the specific payment type
   * 
   * @param \Jazzee\Entity\Payment $payment
   * @return string
   */
  public function getStatusText($payment){
    $class = $payment->getType()->getClass();
    switch($payment->getStatus()){
      case \Jazzee\Entity\Payment::PENDING:
        $status = $class::PENDING_TEXT;
        break;
      case \Jazzee\Entity\Payment::SETTLED:
        $status = $class::SETTLED_TEXT;
        break;
      case \Jazzee\Entity\Payment::REJECTED:
        $status = $class::REJECTED_TEXT;
        break;
      case \Jazzee\Entity\Payment::REFUNDED:
        $status = $class::REFUNDED_TEXT;
        break;
    } 
    return $status;
  }
  
}

