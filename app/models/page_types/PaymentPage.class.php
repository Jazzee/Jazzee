<?php
/**
 * Payment Page
 * Displays branching form to select payment type 
 * Then displays payment form from that model and passes input
 * @package jazzee
 * @subpackage apply
 */
class PaymentPage extends StandardPage {
  
  /**
   * The payment type and the amount are selected first
   * then we display the form for the payment type
   * @see StandardPage::makeForm()
   */
  protected function makeForm(){
    $form = new Form;
    $form->action = $this->actionPath;
    $field = $form->newField();
    $field->legend = $this->applicationPage->title;
    $element = $field->newElement('SelectList', 'paymentType');
    $element->label = 'Payment Method';
    $element->addValidator('NotEmpty');
    $paymentTypes = Doctrine::getTable('PaymentType')->findAll();
    foreach($paymentTypes as $type){
      $element->addItem($type->id, $type->name);
    }
    $element = $field->newElement('RadioList', 'amount');
    $element->label = 'Type of payment';
    $element->addValidator('NotEmpty');
    for($i = 1; $i<=$this->applicationPage->Page->getVar('amounts'); $i++){
      $element->addItem($this->applicationPage->Page->getVar('amount'.$i), $this->applicationPage->Page->getVar('description'.$i));
    }
      
    $form->newHiddenElement('level', 1);
    $form->newButton('submit', 'Select');
    return $form;
  }
  
  /**
   * Validate form input from either the intial payment selection form
   * or the ApplyPayment form
   * @see StandardPage::validateInput()
   */
  public function validateInput($input){
    if($input['level'] == 1){
      $result = $this->form->processInput($input);
      //if there is an problem with the input then do what we would normally do
      if(!$result) return false;
    }
    //we are eithier processing a good choice of payment and amount or the input from an ApplyPayment form
    //eithier way we need to create the apply payment form
    $this->applicationPage->leadingText .= "<a href='{$this->actionPath}'>Select a different payment option</a>";
    $paymentType = Doctrine::getTable('PaymentType')->find($input['paymentType']);
    $paymentClass = new $paymentType->class($paymentType);
    $this->form = $paymentClass->paymentForm($this->applicant, $input['amount'], $this->actionPath);
    $this->form->newHiddenElement('level', 2);
    $this->form->newHiddenElement('paymentType', $input['paymentType']);
    
    //if we were processing a good choice of payment and amount we now return false so the newly created form can be displayed to the applicant
    if($input['level'] == 1) return false;
    //otherwise we process the input from the ApplyPayment form
    return $this->form->processInput($input);
  }
  
  public function newAnswer($input){
    $payment = $this->applicant->Payments->get(null);
    $answer = new PaymentAnswer($payment);
    if($answer->update($input)){
      $this->applicant->save();
      $this->form = null;
      return true;
    }
    $this->applicant->save(); //save the applicant either way so we can record rejected payments
    return false;
  }
  
  public function updateAnswer($input, $answerID){
    return false;
  }
  
  public function getAnswers(){
    $answers = array();
    foreach($this->applicant->Payments as $p){
      $answers[] = new PaymentAnswer($p);
    }
    return $answers;
  }
}


/**
 * A single PaymentAnswer Applicant Answer
 */
class PaymentAnswer implements ApplyAnswer {
 /**
  * The Payment model
  * @var Payment
  */
  protected $payment;
  
 /**
  * Contructor
  * Store the answer and create the elements array
  */
  public function __construct(Payment $payment){
    $this->payment = $payment;
  }

  public function getID(){
    return $this->payment->id;
  }
  
  public function update(FormInput $input){
    $paymentType = Doctrine::getTable('PaymentType')->find($input->paymentType);
    $this->payment->amount = $input->amount;
    $this->payment->paymentTypeID = $paymentType->id;
    $paymentClass = new $paymentType->class($paymentType);
    return $paymentClass->pendingPayment($this->payment, $input);
  }
  
  public function getElements(){
    return array('type'=>'Payment Type','amount'=>'Amount');
  }

  public function getDisplayValueForElement($elementID){
    switch($elementID){
      case 'type':
        return $this->payment->PaymentType->name;
        break;
      case 'amount':
        return $this->payment->amount;
        break;
    }
  }
  
  public function getFormValueForElement($elementID){
    return false;
  }
  
  public function applyTools($basePath){
    return array();
  }
  
  public function applicantTools(){
    $paymentType = new $this->payment->PaymentType->class($this->payment->PaymentType);
    return $paymentType->applicantTools($this->payment);
  }

  public function applyStatus(){
     
    $arr = array(
      'Status' => $this->getStatusText()
    );
    //add the reson to refunded payments
    if($this->payment->status == Payment::REFUNDED) $arr['Reason'] = $this->payment->getVar('refundedReason');
    
    //add the reson to rejected payments
    if($this->payment->status == Payment::REJECTED) $arr['Reason'] = $this->payment->getVar('rejectedReason');
    return $arr;
  }
  
  public function applicantStatus(){
    $arr = array(
      'Status' => $this->payment->status,
      'Applicant Status Message' => $this->getStatusText()
    );
    //add the reson to rejected payments
    if($this->payment->status == Payment::REJECTED) $arr['Reason'] = $this->payment->getVar('rejectedReason');
    return $arr;
  }
  
  /**
   * Get Status Text
   * Get the ApplyPayment status text for the specific payment type
   * @return string
   */
  protected function getStatusText(){
    $class = $this->payment->PaymentType->class;
    switch($this->payment->status){
      case Payment::PENDING:
        $status = $class::PENDING_TEXT;
        break;
      case Payment::SETTLED:
        $status = $class::SETTLED_TEXT;
        break;
      case Payment::REJECTED:
        $status = $class::REJECTED_TEXT;
        break;
      case Payment::REFUNDED:
        $status = $class::REFUNDED_TEXT;
        break;
    } 
    return $status;
  }
  
  public function getAttachment(){
    return null;
  }
  
  public function getUpdatedAt(){
    return null;
  }

}
?>