<?php
/**
 * Pay by check
 */
class CheckPayment extends ApplyPayment{

  /**
   * Display information about mailing a check and allow the applicant to record a preliminary check payment
   * @see ApplyPayment::paymentForm()
   */
  public function paymentForm(Applicant $applicant, $amounts, Form $form = null){
    if(is_null($form)) $form = new Form;
    $field = $form->newField(array('legend'=>"Pay by check")); 
    $field->instructions = 'Click the Pay By Check button to pay your fee by check.  Your account will be temporarily credited and you can complete your application.  Your application will not be reviewed until your check is recieved.';       
    $element = $field->newElement('RadioList', 'amount');
    $element->label = 'Type of payment';
    $element->addValidator('NotEmpty');
    foreach($amounts as $amount){
      $element->addItem($amount['Amount'], $amount['Description']);
    }
    $form->newButton('submit', 'Pay By Check');
    return $form;
  }
  
  public function leadingText(Applicant $applicant){
    $leadingText = '<p><strong>Make Checks Payable to:</strong> ' . $this->paymentType->getVar('payable') . '</p>';
    if($this->paymentType->getVar('address')) $leadingText .= '<p><h4>Mail Check to:</h4>' . nl2br($this->paymentType->getVar('address')) . '</p>';
    if($this->paymentType->getVar('coupon')) $leadingText .= '<p><h4>Include the following information with your payment:</h4> ' . nl2br($this->paymentType->getVar('coupon')) . '</p>';
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
    return str_ireplace($search, $replace, $leadingText);
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
    
    $element = $field->newElement('TextArea','address');
    $element->label = 'Address to send the check to';
    if($paymentType) $element->value = $paymentType->getVar('address');
    $element->format = $format;
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('TextArea','coupon');
    $element->label = 'Text for Payment Coupon';
    if($paymentType) $element->value = $paymentType->getVar('coupon');
    $element->format = $format;
    
    return $form;
  }
  
  public static function setup(PaymentType $paymentType, Input $input){
    $paymentType->name = $input->name;
    $paymentType->class = 'CheckPayment';
    $paymentType->setVar('payable', $input->payable);
    $paymentType->setVar('address', $input->address);
    $paymentType->setVar('coupon', $input->coupon);
  }

  /**
   * Pending paymentsa are for checks which have been sent but not recieved and cashed
   * @see ApplyPayment::pendingPayment()
   */
  public function pendingPayment(Payment $payment){
    $payment->pending();
  }

  public function settlePayment(Payment $payment){}

  public function rejectPayment(Payment $payment){}

  public function refundPayment(Payment $payment){}
}