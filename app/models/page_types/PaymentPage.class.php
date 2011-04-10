<?php
/**
 * Payment Page
 * Displays branching form to select payment type 
 * Then displays payment form from that model and passes input
 * @package jazzee
 * @subpackage apply
 */
class PaymentPage extends StandardPage {
  const SHOW_PAGE = false;

  /**
   * Get all the PaymentType forms
   * return Array of Form
   */
  public function makeForm(){
    $forms = array();   
    $paymentTypes = Doctrine::getTable('PaymentType')->findAll();
    foreach($paymentTypes as $paymentType){
      $paymentClass = new $paymentType->class($paymentType);
      for($i = 1; $i<=$this->applicationPage->Page->getVar('amounts'); $i++){
        $amounts[] = array(
          'Amount' => $this->applicationPage->Page->getVar('amount'.$i),
          'Description' => $this->applicationPage->Page->getVar('description'.$i)
        );
      }
      $form = $paymentClass->paymentForm($this->applicant, $amounts);
      $form->newHiddenElement('paymentType', $paymentType->id);
      $forms[$paymentType->id] = $form;
    }
    return $forms;
  }
  
  public function validateInput($input){
    return $this->form[$input['paymentType']]->processInput($input);
  }
  
  public function newAnswer($input){
    $payment = $this->applicant->Payments->get(null);
    $answer = new PaymentAnswer($payment);
    $answer->update($input);
    $this->applicant->save();
    $this->form = null;
    return true;
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
    $paymentClass->pendingPayment($this->payment, $input);
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
    $arr = array();
    $arr[] = array(
        'title' => 'Details',
         'class' => 'paymentDetails',
         'path' => "paymentDetails/{$this->payment->id}"
       );
//    
//    $arr[] = array(
//        'title' => 'Settle',
//         'class' => 'settlePayment',
//         'path' => "settlePayment/{$this->payment->id}"
//       );
    return $arr;
  }

  public function applyStatus(){
    $arr = array(
      'Status' => $this->payment->status
    );
    return $arr;
  }
  
  public function applicantStatus(){
    $arr = array(
      'Status' => $this->payment->status
    );
    return $arr;
  }
  
  public function getAttachment(){
    return null;
  }
  
  public function getUpdatedAt(){
    return null;
  }

}
?>