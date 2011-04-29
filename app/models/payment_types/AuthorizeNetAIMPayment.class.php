<?php
require_once SRC_ROOT . '/lib/anet_sdk/AuthorizeNet.php'; 
/**
 * Pay via Authorize.net Advanced Integration Method
 */
class AuthorizeNetAIMPayment extends ApplyPayment{
  const PENDING_TEXT = 'Approved';
  const SETTLED_TEXT = 'Approved';
  const REJECTED_TEXT = 'Card was rejected';
  const REFUNDED_TEXT = 'This transaction was refunded';
  
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
    $field = $form->newField(array('legend'=>"Setup Authorize.net SIM Payments"));        
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
  function pendingPayment(Payment $payment, FormInput $input){
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
   * Once funds have cleared the payment is settled
   * @see ApplyPaymentInterface::settlePayment()
   */
  function settlePayment(Payment $payment, FormInput $input){
    
  }
  
  /**
   * Credit card transactions which were rejected
   * Record the reason for the rejection and details for troubleshooting
   * @see ApplyPaymentInterface::rejectPayment()
   */
  function rejectPayment(Payment $payment, FormInput $input){
    
  }
  
  /**
   * Authorize.net doens't have a refund api for SIM transactions 
   * User the AIM method to attempt to process a refund since no CC details are exposed
   * @see ApplyPaymentInterface::refundPayment()
   */
  function refundPayment(Payment $payment, FormInput $input){
    
  }
}