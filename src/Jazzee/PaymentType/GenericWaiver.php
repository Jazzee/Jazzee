<?php
namespace Jazzee\PaymentType;

/**
 * Allows the applicant to request a fee waiver on a generic form, or for the administratoris
 * to grant waivers if there is an external process
 */
class GenericWaiver extends \Jazzee\PaymentType\AbstractPaymentType{  
  const APPLY_PAGE_ELEMENT = 'GenericWaiverPayment-apply_page';
  const APPLICANTS_SINGLE_ELEMENT = 'GenericWaiverPayment-applicants_single';
  
  public function paymentForm(\Jazzee\Entity\Applicant $applicant, $amount){
    $form = new \Foundation\Form();
    $form->setAction($this->_controller->getActionPath());
    $form->newHiddenElement('amount', $amount);
    $field = $form->newField();
    $field->setLegend($this->_paymentType->getName());
    
    $element = $field->newElement('Textarea','justification');
    $element->setLabel('What is your reason for applying for a fee waiver?');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 5000));

    $form->newButton('submit', 'Apply for Fee Waiver');
    return $form;
  }
  
  public function getSetupForm(){
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Setup Payment');  
    $element = $field->newElement('TextInput','name');
    $element->setLabel('Payment Name');
    $element->setValue($this->_paymentType->getName());
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  public function setup(\Foundation\Form\Input $input){
    $this->_paymentType->setName($input->get('name'));
    $this->_paymentType->setClass(get_class($this));
  }

  /**
   * Payments are pending with no verification
   * @see ApplyPaymentInterface::pendingPayment()
   */
  function pendingPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input){
    $payment->setAmount($input->get('amount'));
    $payment->setVar('justification', $input->get('justification'));
    $payment->pending();
    return true;
  }
  
  /**
   * 
   * @see ApplyPaymentInterface::settlePaymentForm()
   */
  function getSettlePaymentForm(\Jazzee\Entity\Payment $payment){
    $form = new \Foundation\Form(); 
    $field = $form->newField();
    $field->setLegend('Approve Fee Waiver');
    
    $element = $field->newElement('Plaintext','justification');
    $element->setLabel('Justification');
    $element->setValue(htmlentities($payment->getVar('justification'),ENT_COMPAT,'utf-8'));
    
    $form->newButton('submit', 'Approve Fee Waiver');

    return $form;
  }
  
  /**
   * @see ApplyPaymentInterface::settlePayment()
   */
  function settlePayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input){
    $payment->settled();
    return true;
  }
  
  /**
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  function getRejectPaymentForm(\Jazzee\Entity\Payment $payment){
    $form = new \Foundation\Form(); 
    $field = $form->newField();
    $field->setLegend('Deny Application');

    $element = $field->newElement('Plaintext','justification');
    $element->setLabel('Justification');
    $element->setValue(htmlentities($payment->getVar('justification'),ENT_COMPAT,'utf-8'));

    $element = $field->newElement('Textarea','rejectedReason');
    $element->setLabel('Reason displayed to Applicant');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Deny Fee Waiver');
    return $form;
  }
  
  /**
   * Check payments are refunded outside Jazzee and then marked as refunded
   * @see ApplyPaymentInterface::refundPayment()
   */
  function rejectPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input){
    $payment->rejected();
    $payment->setVar('rejectedReason', $input->get('rejectedReason'));
    return true;
  }
  
  /**
   * Record the reason the payment was refunded
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  function getRefundPaymentForm(\Jazzee\Entity\Payment $payment){
    $form = new \Foundation\Form(); 
    $field = $form->newField();
    $field->setLegend('Withdraw Application');

    $element = $field->newElement('Plaintext','justification');
    $element->setLabel('Justification');
    $element->setValue(htmlentities($payment->getVar('justification'),ENT_COMPAT,'utf-8'));

    $element = $field->newElement('Textarea','refundedReason');
    $element->setLabel('Reason displayed to Applicant');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Withdraw Fee Waiver');
    return $form;
  }
  
  /**
   * Check payments are refunded outside Jazzee and then marked as refunded
   * @see ApplyPaymentInterface::refundPayment()
   */
  function refundPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input){
    $payment->refunded();
    $payment->setVar('refundedReason', $input->get('refundedReason'));
    return true;
  }
}