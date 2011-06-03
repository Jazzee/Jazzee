<?php
namespace Jazzee\Entity\Answer;
/**
 * A single StandardPage Applicant Answer
 */
class Payment extends Standard
{
  
  public function update(\Foundation\Form\Input $input){
    return $this->_answer->getPayment()->getType()->getJazzeePaymentType()->pendingPayment($this->_answer->getPayment(), $input);
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