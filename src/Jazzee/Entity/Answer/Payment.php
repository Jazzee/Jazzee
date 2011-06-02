<?php
namespace Jazzee\Entity\Answer;
/**
 * A single StandardPage Applicant Answer
 */
class Payment implements \Jazzee\Answer 
{
  /**
  * The Payment entity
  * @var \Jazzee\Entity\Payment
  */
  protected $_payment;

 /**
  * Contructor
  * 
  * Store the payment
  * @param \Jazzee\Entity\Payment $payment
  */
  public function __construct(\Jazzee\Entity\Payment $payment){
    $this->_payment = $payment;
  }
  
  /**
   * Get the Payment
   * 
   * @return \Jazzee\Entity\Payment
   */
  public function getPayment(){
    return $this->_payment;
  }
  /**
   * 
   * @see Jazzee.Answer::getID()
   */
  public function getID(){
    return $this->_payment->getId();
  }
  
  public function update(\Foundation\Form\Input $input){
    return $this->_payment->getType()->getJazzeePaymentType()->pendingPayment($this->_payment, $input);
  }
  
  public function applyTools(){
    return array();
  }
  
  public function applicantsTools(){
    return $this->_payment->getType()->getJazzeePaymentType()->applicantTools();
  }
  public function applyStatus(){
    $arr = array(
      'Status' => $this->getStatusText()
    );
    //add the reson to refunded payments
    if($this->_payment->getStatus() == \Jazzee\Entity\Payment::REFUNDED) $arr['Reason'] = $this->_payment->getVar('reasonText');
    
    //add the reson to rejected payments
    if($this->_payment->getStatus() == \Jazzee\Entity\Payment::REJECTED) $arr['Reason'] = $this->_payment->getVar('reasonText');
    return $arr;
  }
  
  public function applicantsStatus(){
    $arr = array(
      'Status' => $this->_payment->getStatus(),
      'Applicant Status Message' => $this->getStatusText()
    );
    //add the reson to rejected payments
    if($this->_payment->getStatus() == \Jazzee\Entity\Payment::REJECTED) $arr['Reason'] = $this->_payment->getVar('reasonText');
    return $arr;
  }
  
  /**
   * Get Status Text
   * Get the ApplyPayment status text for the specific payment type
   * @return string
   */
  protected function getStatusText(){
    $class = $this->_payment->getType()->getClass();
    switch($this->_payment->getStatus()){
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