<?php
/**
 * Abstract class for the different types of payment
 */
abstract class ApplyPayment implements ApplyPaymentInterface{
  /**
   * The PaymentType Model
   * @var PaymentType
   */
  protected $paymentType;
  
  /**
   * Constructor
   * @param PaymentType $paymentType
   */
  public function __construct(PaymentType $paymentType){
    $this->paymentType = $paymentType;  
  }
}