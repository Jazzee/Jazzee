<?php
/**
 * Pay by check
 */
class CheckPayment extends ApplyPayment{  
  const PENDING_TEXT = 'We have not recieved your check';
  const SETTLED_TEXT = 'Your check has been received';
  const REJECTED_TEXT = 'Your check did not clear or was rejected';
  const REFUNDED_TEXT = 'We have sent you a refund for this payment';
  
  /**
   * Display information about mailing a check and allow the applicant to record a preliminary check payment
   * @see ApplyPayment::paymentForm()
   */
  public function paymentForm(Applicant $applicant, $amount, $actionPath){
    $form = new Form;
    $form->action = $actionPath;
    $form->newHiddenElement('amount', $amount);
    $field = $form->newField(array('legend'=>$this->paymentType->name)); 
    $instructions = "<p><strong>Application Fee:</strong> &#36;{$amount}</p>";
    $instructions .= '<p><strong>Make Checks Payable to:</strong> ' . $this->paymentType->getVar('payable') . '</p>';
    if($this->paymentType->getVar('address')) $instructions .= '<p><h4>Mail Check to:</h4>' . nl2br($this->paymentType->getVar('address')) . '</p>';
    if($this->paymentType->getVar('coupon')) $instructions .= '<p><h4>Include the following information with your payment:</h4> ' . nl2br($this->paymentType->getVar('coupon')) . '</p>';
    $search = array(
     '%Applicant_Name%',
     '%Applicant_ID%',
     '%Program_Name%',
     '%Program_ID%'
    );
    $replace = array();
    $replace[] = "{$applicant->firstName} {$applicant->lastName}";
    $replace[] = $applicant->id;
    $replace[] = $applicant->Application->Program->name;
    $replace[] = $applicant->Application->Program->id;
    $instructions = str_ireplace($search, $replace, $instructions);
    $field->instructions = $instructions . '<p>Click the Pay By Check button to pay your fee by check.  Your account will be temporarily credited and you can complete your application.  Your application will not be reviewed until your check is recieved.</p>';       
    
    $form->newButton('submit', 'Pay By Check');
    return $form;
  }
  
  /**
   * Setup the instructions for mailing the check including the address and any special markings (like appicant ID)
   * @see ApplyPayment::setupForm()
   */
  public static function setupForm(PaymentType $paymentType = null){
    $filters = array(
      'Applicant Name' => '%Applicant_Name%',
      'Applicant ID' => '%Applicant_ID%',
      'Program Name' => '%Program_Name%',
      'Program ID' => '%Program_ID%'
    );
    $format = 'These wildcards will be replaced in the text: ';
    foreach($filters as $title => $wildcard){
      $format .= "<br />{$title}:{$wildcard}";
    }
    $form = new Form;
    $field = $form->newField(array('legend'=>"Setup Check Payments"));        
    $element = $field->newElement('TextInput','name');
    $element->label = 'Payment Name';
    if($paymentType) $element->value = $paymentType->name;
    $element->addValidator('NotEmpty');
    $element = $field->newElement('TextInput','payable');
    $element->label = 'Make the check payable to';
    if($paymentType) $element->value = $paymentType->getVar('payable');
    $element->format = $format;
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('Textarea','address');
    $element->label = 'Address to send the check to';
    if($paymentType) $element->value = $paymentType->getVar('address');
    $element->format = $format;
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('Textarea','coupon');
    $element->label = 'Text for Payment Coupon';
    if($paymentType) $element->value = $paymentType->getVar('coupon');
    $element->format = $format;
    
    return $form;
  }
  
  public static function setup(PaymentType $paymentType, FormInput $input){
    $paymentType->name = $input->name;
    $paymentType->class = 'CheckPayment';
    $paymentType->setVar('payable', $input->payable);
    $paymentType->setVar('address', $input->address);
    $paymentType->setVar('coupon', $input->coupon);
  }
  
  /**
   * Check Payments are pending with no verification
   * @see ApplyPaymentInterface::pendingPayment()
   */
  function pendingPayment(Payment $payment, FormInput $input){
    $payment->amount = $input->amount;
    $payment->pending();
  }
  
  /**
   * Record the check number and the deposit date for a payment
   * @see ApplyPaymentInterface::settlePaymentForm()
   */
  function getSettlePaymentForm(Payment $payment){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Settle {$this->paymentType->name} Payment"));        
    $element = $field->newElement('TextInput','checkNumber');
    $element->label = 'Check Number';
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('DateInput','checkSettlementDate');
    $element->label = 'The Date the check was settled';
    $element->value = 'today';
      
    $element->addValidator('DateBefore', 'tomorrow');
    $element->addFilter('DateFormat', 'Y-m-d H:i:s');
    $element->addValidator('NotEmpty');
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  /**
   * Once checks have been cashed we settle the payment
   * @see ApplyPaymentInterface::settlePayment()
   */
  function settlePayment(Payment $payment, FormInput $input){
    $payment->settled();
    $payment->setVar('checkNumber', $input->checkNumber);
    $payment->setVar('checkSettlementDate', $input->checkSettlementDate);
    $payment->save();
    return true;
  }
  
  /**
   * Record the reason the payment was rejected
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  function getRejectPaymentForm(Payment $payment){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Reject {$this->paymentType->name} Payment"));        
    $element = $field->newElement('Textarea','reason');
    $element->label = 'Reason displayed to Applicant';
    $element->addValidator('NotEmpty');
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  /**
   * Bounced checks get rejected
   * @see ApplyPaymentInterface::rejectPayment()
   */
  function rejectPayment(Payment $payment, FormInput $input){
    $payment->rejected();
    $payment->setVar('rejectedReason', $input->reason);
    $payment->save();
    return true;
  }
  
  /**
   * Record the reason the payment was refunded
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  function getRefundPaymentForm(Payment $payment){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Refund {$this->paymentType->name} Payment"));        
    $element = $field->newElement('Textarea','reason');
    $element->label = 'Reason displayed to Applicant';
    $element->addValidator('NotEmpty');
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  /**
   * Check payments are refunded outside Jazzee and then marked as refunded
   * @see ApplyPaymentInterface::refundPayment()
   */
  function refundPayment(Payment $payment, FormInput $input){
    $payment->refunded();
    $payment->setVar('refundedReason', $input->reason);
    $payment->save();
    return true;
  }
  
  /**
   * Check tools
   * @see ApplyPaymentInterface::applicantTools()
   */
  public function applicantTools(Payment $payment){
    $arr = array();
    switch($payment->status){
      case Payment::PENDING:
        $arr[] = array(
          'title' => 'Clear Check',
          'class' => 'settlePayment',
          'path' => "settlePayment/{$payment->id}"
        );
        $arr[] = array(
          'title' => 'Reject Check',
          'class' => 'rejectPayment',
          'path' => "rejectPayment/{$payment->id}"
        );
        break;
      case Payment::SETTLED:
        $arr[] = array(
          'title' => 'Apply Refund',
          'class' => 'refundPayment',
          'path' => "refundPayment/{$payment->id}"
        );
    }
    return $arr;
  }
}