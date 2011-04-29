<?php
/**
 * Apply for a fee waiver
 */
class FeeWaiverPayment extends ApplyPayment{  
  const PENDING_TEXT = 'Your application has been received';
  const SETTLED_TEXT = 'Your application was approved';
  const REJECTED_TEXT = 'Your application was denied';
  const REFUNDED_TEXT = 'Your application was withdrawn';
  
  /**
   * Display fee waiver application
   * @see ApplyPayment::paymentForm()
   */
  public function paymentForm(Applicant $applicant, $amount, $actionPath){
    $form = new Form;
    $form->action = $actionPath;
    $form->newHiddenElement('amount', $amount);
    $field = $form->newField(array('legend'=>$this->paymentType->name));
    $form->newButton('submit', 'Apply for Fee Waiver');
    return $form;
  }
  
  /**
   * Setup the fee waiver form
   * @see ApplyPayment::setupForm()
   */
  public static function setupForm(PaymentType $paymentType = null){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Setup Fee Waiver Application"));        
    $element = $field->newElement('TextInput','name');
    $element->label = 'Payment Name';
    if($paymentType) $element->value = $paymentType->name;
    $element->addValidator('NotEmpty');
    return $form;
  }
  
  public static function setup(PaymentType $paymentType, FormInput $input){
    $paymentType->name = $input->name;
    $paymentType->class = 'FeeWaiverPayment';
  }
  
  /**
   * Fee waivers are pending until the application has been approved
   * @see ApplyPaymentInterface::pendingPayment()
   */
  function pendingPayment(Payment $payment, FormInput $input){
    $payment->amount = $input->amount;
    $payment->pending();
  }
  
  /**
   * We settled fee waivers when the application has been approved
   * @see ApplyPaymentInterface::settlePayment()
   */
  function settlePayment(Payment $payment, FormInput $input){
    
  }
  
  /**
   * Denied applications get rejected
   * @see ApplyPaymentInterface::rejectPayment()
   */
  function rejectPayment(Payment $payment, FormInput $input){
    
  }
  
  /**
   * If an application is withdrawn it is marked as refunded
   * @see ApplyPaymentInterface::refundPayment()
   */
  function refundPayment(Payment $payment, FormInput $input){
    
  }
}