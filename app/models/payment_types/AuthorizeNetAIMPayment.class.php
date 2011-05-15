<?php
require_once SRC_ROOT . '/lib/anet_sdk/AuthorizeNet.php'; 
/**
 * Pay via Authorize.net Advanced Integration Method
 */
class AuthorizeNetAIMPayment extends ApplyPayment{
  const PENDING_TEXT = 'Approved';
  const SETTLED_TEXT = 'Approved';
  const REJECTED_TEXT = 'Rejected or Voided';
  const REFUNDED_TEXT = 'Refunded';
  
  /**
   * Display the button to pass applicant to Authorize.net's hosted payment page
   * @see ApplyPayment::paymentForm()
   */
  public function paymentForm(Applicant $applicant, $amount, $actionPath){
    $form = new Form;
    //we pass the amount back as a hidden element so PaymentPage will have it again
    $form->newHiddenElement('amount', $amount);

    $form->action = $actionPath;
    $field = $form->newField();
    $field->legend = $this->paymentType->name;
    $field->instructions = "<p><strong>Application Fee:</strong> &#36;{$amount}</p>";
    
    $e = $field->newElement('TextInput', 'cardNumber');
    $e->label = 'Credit Card Number';
    $e->addValidator('NotEmpty');
    $e->addValidator('CreditCard', explode(',',$this->paymentType->getVar('acceptedCards')));
    
    $e = $field->newElement('ShortDateInput', 'expirationDate');
    $e->label = 'Expiration Date';
    $e->addValidator('NotEmpty');
    $e->addValidator('Date');
    $e->addValidator('DateAfter', date('m/d/Y', strtotime('Last Month')));
    $e->addFilter('DateFormat', 'my');
    
    $e = $field->newElement('TextInput', 'cardCode');
    $e->label = 'CCV';
    $e->addValidator('NotEmpty');
    
    $e = $field->newElement('TextInput', 'postalCode');
    $e->label = 'Billing Postal Code';
    $e->instructions = 'US Credit Cards which do not provide a postal code will be rejected.';

    $form->newButton('submit', 'Pay with Credit Card');
    return $form;
  }
  
  /**
   * Setup the instructions for mailing the check including the address and any special markings (like appicant ID)
   * @see ApplyPayment::setupForm()
   */
  public static function setupForm(PaymentType $paymentType = null){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Setup Authorize.net AIM Payments"));        
    $element = $field->newElement('TextInput','name');
    $element->label = 'Payment Name';
    if($paymentType) $element->value = $paymentType->name;
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('TextInput','description');
    $element->label = 'Description';
    if($paymentType) $element->value = $paymentType->getVar('description');
    $element->label = 'Appears on credit card statement for applicant';
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('TextInput','gatewayId');
    $element->label = 'Payment Gateway ID';
    if($paymentType) $element->value = $paymentType->getVar('gatewayId');
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('TextInput','gatewayKey');
    $element->label = 'Payment Gateway Key';
    if($paymentType) $element->value = $paymentType->getVar('gatewayKey');
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('TextInput','gatewayHash');
    $element->label = 'Payment Gateway Hashphrase';
    if($paymentType) $element->value = $paymentType->getVar('gatewayHash');
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('RadioList','testAccount');
    $element->label = 'Is this a test account?';
    $element->addItem(0, 'No');
    $element->addItem(1, 'Yes');
    if($paymentType) $element->value = $paymentType->getVar('testAccount');
    $element->format = 'Test accounts are handled differenty by Authorize.net and need to be sent to a different URL';
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('CheckboxList','acceptedCards');
    $element->label = 'What credit card types do you accept?';
    $types = Form_CreditCardValidator::listTypes();
    foreach($types as $id => $value){
      $element->addItem($id, $value);
    }
    if($paymentType) $element->value = explode(',',$paymentType->getVar('acceptedCards'));
    $element->addValidator('NotEmpty');
    
    return $form;
  }
  
  public static function setup(PaymentType $paymentType, FormInput $input){
    $paymentType->name = $input->name;
    $paymentType->class = 'AuthorizeNetAIMPayment';
    $paymentType->setVar('description', $input->description);
    $paymentType->setVar('gatewayId', $input->gatewayId);
    $paymentType->setVar('gatewayKey', $input->gatewayKey);
    $paymentType->setVar('gatewayHash', $input->gatewayHash);
    $paymentType->setVar('testAccount', $input->testAccount);
    $paymentType->setVar('acceptedCards', implode(',',$input->acceptedCards));
  }
  
  /**
   * Record transaction information pending until it is settled with the bank
   * @see ApplyPaymentInterface::pendingPayment()
   */
  public function pendingPayment(Payment $payment, FormInput $input){
    $aim = new AuthorizeNetAIM($this->paymentType->getVar('gatewayId'), $this->paymentType->getVar('gatewayKey'));
    $aim->setSandBox($this->paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    $aim->amount = $input->amount;
    $aim->cust_id = $payment->Applicant->id;
    $aim->customer_ip = $_SERVER['REMOTE_ADDR'];
    $aim->email = $payment->Applicant->email;
    $aim->email_customer = 0;
    $aim->card_num = $input->cardNumber;
    $aim->exp_date = $input->expirationDate;
    $aim->card_code = $input->cardCode;
    $aim->zip = $input->postalCode;
    $aim->description = $this->paymentType->getVar('description');
    $aim->test_request = $this->config->status == 'PRODUCTION'?0:1;
    $response = $aim->authorizeAndCapture();
    if($response->approved) {
      $payment->amount = $response->amount;
      $payment->setVar('transactionId', $response->transaction_id);
      $payment->setVar('authorizationCode', $response->authorization_code);
      $payment->pending();
    } else {
      $payment->amount = $response->amount;
      $payment->setVar('transactionId', $response->transaction_id);
      $payment->setVar('reasonCode', $response->response_reason_code);
      $payment->setVar('reasonText', $response->response_reason_text);
      $payment->rejected();
      $this->messages->write('error', 'Your credit card was rejected by our payment processor.');
      return false;
    }
    return true;
  }
  
  /**
   * Attempt to settle payment with anet's API
   * @see ApplyPaymentInterface::settlePaymentForm()
   */
  public function getSettlePaymentForm(Payment $payment){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Settle {$this->paymentType->name} Payment"));
    $element = $field->newElement('Plaintext','info');
    $element->value = "{$this->paymentType->name} transactions have to be settled by Authorize.net.  To check the status of this payment click 'Attempt Settlement'";
    $form->newButton('submit', 'Attempt Settlement');
    return $form;
  }
  
  /**
   * Once checks have been cashed we settle the payment
   * @see ApplyPaymentInterface::settlePayment()
   */
  public function settlePayment(Payment $payment, FormInput $input){
    $td = new AuthorizeNetTD($this->paymentType->getVar('gatewayId'), $this->paymentType->getVar('gatewayKey'));
    $td->setSandBox($this->paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    // Get Transaction Details
    $transactionId = $payment->getVar('transactionId');
    $response = $td->getTransactionDetails($transactionId);
    if($response->isError())
      throw new Jazzee_Exception("Unable to get transaction details for {$payment->id} transcation id {$transactionId}", E_ERROR, 'There was a problem getting payment information.');
    //has this transaction has been settled already
    if($response->xml->transaction->transactionStatus == 'settledSuccessfully'){
      $payment->settled();
      $payment->setVar('settlementTimeUTC', (string)$response->xml->transaction->batch->settlementTimeUTC);
      $payment->save();
      return true;
    } else if($response->xml->transaction->transactionStatus == 'voided'){
      $payment->rejected();
      if(isset($input->reason))
        $payment->setVar('rejectedReason', $input->reason);
      else
        $payment->setVar('rejectedReason', 'This payment was voided.');
      $payment->save();
      return true;
    }
    return false;
  }
  
  /**
   * Record the reason the payment was rejected
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  public function getRejectPaymentForm(Payment $payment){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Reject {$this->paymentType->name} Payment"));        
    $element = $field->newElement('Textarea','reason');
    $element->label = 'Reason displayed to Applicant';
    $element->addValidator('NotEmpty');
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  /**
   * Void a transaction before it is settled
   * @see ApplyPaymentInterface::rejectPayment()
   */
  public function rejectPayment(Payment $payment, FormInput $input){
    $aim = new AuthorizeNetAIM($this->paymentType->getVar('gatewayId'), $this->paymentType->getVar('gatewayKey'));
    $aim->setSandBox($this->paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    $aim->test_request = $this->config->status == 'PRODUCTION'?0:1;
    $response = $aim->void($payment->getVar('transactionId'));
    if($response->approved) {
      $payment->rejected();
      $payment->setVar('rejectedReason', $input->reason);
      $payment->save();
      return true;
    }
    //if we cant void we are probably already settled so try and settle the payment in our system
    return $this->settlePayment($payment, $input);
  }
  
  /**
   * Record the reason the payment was refunded
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  public function getRefundPaymentForm(Payment $payment){
    $td = new AuthorizeNetTD($this->paymentType->getVar('gatewayId'), $this->paymentType->getVar('gatewayKey'));
    $td->setSandBox($this->paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    // Get Transaction Details
    $transactionId = $payment->getVar('transactionId');
    $response = $td->getTransactionDetails($transactionId);
    if($response->isError())
      throw new Jazzee_Exception("Unable to get transaction details for {$payment->id} transcation id {$transactionId}", E_ERROR, 'There was a problem getting payment information.');
      
    $form = new Form;
    $field = $form->newField(array('legend'=>"Refund {$this->paymentType->name} Payment"));      
    $element = $field->newElement('Plaintext', 'details');
    $element->label = 'Details';
    $element->value = "Refund \${$payment->amount} to card " . $response->xml->transaction->payment->creditCard->cardNumber;  
    $element = $field->newElement('Textarea','reason');
    $element->label = 'Reason displayed to Applicant';
    $element->addValidator('NotEmpty');
    
    $form->newHiddenElement('cardNumber', substr($response->xml->transaction->payment->creditCard->cardNumber, strlen($response->xml->transaction->payment->creditCard->cardNumber)-4, 4));
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  /**
   * Check payments are refunded outside Jazzee and then marked as refunded
   * @see ApplyPaymentInterface::refundPayment()
   */
  public function refundPayment(Payment $payment, FormInput $input){
    $aim = new AuthorizeNetAIM($this->paymentType->getVar('gatewayId'), $this->paymentType->getVar('gatewayKey'));
    $aim->setSandBox($this->paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    $aim->test_request = $this->config->status == 'PRODUCTION'?0:1;
    $response = $aim->credit($payment->getVar('transactionId'), $payment->amount, $input->cardNumber);
    if($response->approved) {
      $payment->refunded();
      $payment->setVar('refundedReason', $input->reason);
      $payment->save();
      return true;
    }
    return false;
  }
  
  public function applicantTools(Payment $payment){
    $arr = array();
    switch($payment->status){
      case Payment::PENDING:
        $arr[] = array(
          'title' => 'Settle Payment',
          'class' => 'settlePayment',
          'path' => "settlePayment/{$payment->id}"
        );
        $arr[] = array(
          'title' => 'Reject Payment',
          'class' => 'rejectPayment',
          'path' => "rejectPayment/{$payment->id}"
        );
        break;
      case Payment::SETTLED:
        $arr[] = array(
          'title' => 'Refund Payment',
          'class' => 'refundPayment',
          'path' => "refundPayment/{$payment->id}"
        );
    }
    return $arr;
  }
}