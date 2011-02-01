<?php
/**
 * Abstract Payment Page
 * Provides a guide for vendor specific payment types
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class PaymentPage extends StandardPage {
  const SHOW_PAGE = false;

  protected function makeForm(){
    //dont display the form if we have a settled or pending payment
    foreach($this->applicant->Payment as $payment){
      if($payment->status == 'settled' OR $payment->status == 'pending'){
        return null;
      }
    }
    //display a form for selecting payment type
    $form = new Form;
    $field = $form->newField();
    $field->legend = $this->applicationPage->title;
    $element = $field->newElement('RadioList', 'paymentTypeID');
    $element->label = 'Payment Method';
    $element->addValidator('NotEmpty');
    $paymentTypes = Doctrine::getTable('PaymentType')->findAll();
    foreach($paymentTypes as $type){
      $element->addItem($type->id, $type->name);
    }
    $form->newHiddenElement('level', 1);
    $form->newButton('submit', 'Select');
    return $form;
  }
  
  protected function paymentForm($paymentTypeID){
    $this->applicationPage->leadingText .= "<a href='{$this->applicationPage->id}'>Select a different payment option</a>";
    $paymentType = Doctrine::getTable('PaymentType')->find($paymentTypeID);
    $paymentClass = new $paymentType->class($paymentType);
    $this->form->reset();
    $amounts = array();
    for($i = 1; $i<=$this->applicationPage->Page->getVar('amounts'); $i++){
      $amounts[] = array(
        'Amount' => $this->applicationPage->Page->getVar('amount'.$i),
        'Description' => $this->applicationPage->Page->getVar('description'.$i)
      );
    }
    $paymentClass->paymentForm($this->applicant, $amounts, $this->form);
    $this->applicationPage->leadingText = $paymentClass->leadingText($this->applicant);
    $this->applicationPage->trailingText = $paymentClass->trailingText($this->applicant);
    $this->form->newHiddenElement('level', 2);
    $this->form->newHiddenElement('paymentTypeID', $paymentTypeID);
  }
  
  public function validateInput($input){
    $this->paymentForm($input['paymentTypeID']);
    if($input['level'] == 1) return false;
    return $this->form->processInput($input);
  }
  
  public function newAnswer($input){
    $paymentType = Doctrine::getTable('PaymentType')->find($input->paymentTypeID);
    $paymentClass = new $paymentType->class($paymentType);
    $payment = $this->applicant->Payment->get(null);
    $payment->amount = $input->amount;
    $payment->paymentTypeID = $paymentType->id;
    $paymentClass->pendingPayment($payment);
    $this->applicant->save();
    $this->form = null;
    return true;
  }
  
  public function updateAnswer($input, $answerID){
    return false;
  }
  
  public function getAnswers(){
    $answers = array();
    foreach($this->applicant->Payment as $p){
      $answers[] = new PaymentAnswer($p);
    }
    return $answers;
  }
}


/**
 * A single PaymentAnswer Applicant Answer
 */
class PaymentAnswer extends ApplyAnswer {
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