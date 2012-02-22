<?php
namespace Jazzee\PaymentType;
/**
 * Abstract PaymeType Class
 *
 */
abstract class AbstractPaymentType implements \Jazzee\Interfaces\PaymentType{
  /**
   * The PaymentType Model
   * @var \Jazzee\Entity\PaymentType
   */
  protected $_paymentType;
  
  /**
   * The Page controller calling us
   * @var \Jazzee\Controller
   */
  protected $_controller;

  public function __construct(\Jazzee\Entity\PaymentType $paymentType,\Jazzee\Controller $controller){
    $this->_paymentType = $paymentType;
    $this->_controller = $controller;
  }

  public function setController(\Jazzee\Controller $controller){
    $this->_controller = $controller;
  }
}