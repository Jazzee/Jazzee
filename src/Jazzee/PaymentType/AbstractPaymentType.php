<?php
namespace Jazzee\PaymentType;
/**
 * Abstract PaymeType Class
 *
 */
abstract class AbstractPaymentType implements \Jazzee\Interfaces\PaymentType{
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