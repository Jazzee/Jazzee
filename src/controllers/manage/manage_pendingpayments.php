<?php
/**
 * Manage Pending Payments
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManagePendingPaymentsController extends \Jazzee\AdminController {
  const MENU = 'Manage';
  const TITLE = 'Pending Payments';
  const PATH = 'manage/pendingpayments';
  
  const ACTION_INDEX = 'View Pending Payments';
  const ACTION_SETTLE = 'Settle Pending Payment';
  const REQUIRE_APPLICATION = false;
  
  /**
   * Add the required JS
   */
  protected function setUp(){
    parent::setUp();
    $this->addScript($this->path('resource/scripts/classes/Status.class.js'));
    $this->addScript($this->path('resource/scripts/classes/ChangeProgram.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/manage_pendingpayments.controller.js'));
  }
  
  /**
   * List all the pending payments in the system
   */
  public function actionIndex(){
    $pendingPayments = $this->_em->getRepository('\Jazzee\Entity\Payment')->findBy(array('status'=>\Jazzee\Entity\Payment::PENDING));
    $this->setVar('pendingPayments', $pendingPayments);
  }
  
  /**
   * Edit an PaymentType
   * @param integer $paymentTypeId
   */
   public function actionEdit($paymentTypeId){ 
    if($paymentType = $this->_em->getRepository('\Jazzee\Entity\PaymentType')->find($paymentTypeId)){
      
      $form = $paymentType->getJazzeePaymentType()->getSetupForm();
      $form->setAction($this->path("manage/paymenttypes/edit/{$paymentTypeId}"));
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        $paymentType->getJazzeePaymentType()->setup($input);
        $this->_em->persist($paymentType);
        foreach($paymentType->getVariables() as $var) $this->_em->persist($var);
        $this->addMessage('success', "Changes Saved");
        $this->redirectPath('manage/paymenttypes');
      }
    } else {
      $this->addMessage('error', "Error: Paymenttype #{$paymentTypeId} does not exist.");
    }
  }
  
}
?>