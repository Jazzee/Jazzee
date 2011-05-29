<?php
namespace Jazzee\Entity\Page;
/**
 * Payment Page
 * 
 * Branching form to select payment type
 */
class Payment extends AbstractPage {
  /**
   * The answer class for this page type
   * @const string
   */
  const ANSWER_CLASS = '\Jazzee\Entity\Answer\Payment';
  
  
  /**
   * The payment type and the amount are selected first
   * then we display the form for the payment type
   * @see StandardPage::makeForm()
   */
  protected function makeForm(){
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    
    $element = $field->newElement('SelectList', 'paymentType');
    $element->setLabel('Payment Method');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $paymentTypes = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->findAll();
    foreach($paymentTypes as $type){
      $element->newItem($type->getId(), $type->getName());
    }
    $element = $field->newElement('RadioList', 'amount');
    $element->setLabel('Type of payment');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    for($i = 1; $i<=$this->_applicationPage->getPage()->getVar('amounts'); $i++){
      $element->newItem($this->_applicationPage->getPage()->getVar('amount'.$i), $this->_applicationPage->getPage()->getVar('description'.$i));
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
      $result = $this->getForm()->processInput($input);
      //if there is an problem with the input then do what we would normally do
      if(!$result) return false;
    }
    
    //we are eithier processing a good choice of payment and amount or the input from an \Jazzee\Payment form
    //eithier way we need to create the apply payment form
    $this->_form = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->find($input['paymentType'])->getJazzeePaymentType()->paymentForm($this->_applicant, $input['amount'], $this->_controller->getActionPath());
    $this->_form->newHiddenElement('level', 2);
    $this->_form->newHiddenElement('paymentType',$input['paymentType']);
    
    //if we were processing a good choice of payment and amount we now return false so the newly created form can be displayed to the applicant
    if($input['level'] == 1) return false;
    //otherwise we process the input from the ApplyPayment form
    return $this->_form->processInput($input);
  }
  
  public function newAnswer($input){
    $payment = new \Jazzee\Entity\Payment();
    $payment->setApplicant($this->_applicant);
    $payment->setType($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->find($input->get('paymentType')));
    $answer = new \Jazzee\Entity\Answer\Payment($payment);
    $this->_controller->getEntityManager()->persist($payment);
    $result = $answer->update($input);
    
    if($result) $this->_form = null;
    $this->_controller->getEntityManager()->flush();
    return $result;
  }
  
  public function updateAnswer($input, $answerID){
    return false;
  }
  
  public function getAnswers(){
    $answers = array();
    foreach($this->_applicant->getPayments() as $p){
      $answers[] = new \Jazzee\Entity\Answer\Payment($p);
    }
    return $answers;
  }
  
  public function deleteAnswer($answerId){
    //can't delete payment answers
    return;
  }
  
  public function fill($answerId){
    //no edit so not fill
  }
  
  public function getStatus(){
    //need to check if we have at least one pending or settled payment not rejected or refunded
    return self::INCOMPLETE;
  }
}