<?php
namespace Jazzee;
/**
 * Interface for PaymentTypes
 */
interface PaymentType{

  /**
   * Get the form for new payments
   * @param \Jazzee\Entity\Applicant $applicant
   * @param float $amount
   * @param string $actionPath where we are posting the form to
   * @return Form
   */
  function paymentForm(\Jazzee\Entity\Applicant $applicant, $amount, $actionPath);
  
  /**
   * Applypage paymetn status text
   * 
   * Instructions to display in the status portion of a payment answer
   * @param \Jazzee\Entity\Payment $payment
   * @return string
   */
  function getStatusText(\Jazzee\Entity\Payment $payment);
  
  /**
   * Get the setup form
   * @param PaymentType $paymentType
   * @return Form
   */
  function getSetupForm();
  
  /**
   * Setup the payment type
   * @param Input $input the input from the form
   */
  function setup(\Foundation\Form\Input $input);
  
  /**
   * Record a payment as pending
   * Pending payments have not been settled they allow the applicant to move on
   * but decisions cannot be made until a payment is settled
   * @param \Jazzee\Entity\Payment $payment
   * @param \Foundation\Form\Input $input
   */
  function pendingPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input);
  
  /**
   * The form to display to adminstrators so they can settle the payment
   * @param \Jazzee\Entity\Payment $payment
   * @return Form
   */
  function getSettlePaymentForm(\Jazzee\Entity\Payment $payment);
  
  /**
   * Once funds have been recieved or transactions verified a payment is settled
   * @param \Jazzee\Entity\Payment $payment
   * @param \Foundation\Form\Input $input
   */
  function settlePayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input);
  
  /**
   * The form to display to adminstrators so they can reject the payment
   * @param \Jazzee\Entity\Payment $payment
   * @return Form
   */
  function getRejectPaymentForm(\Jazzee\Entity\Payment $payment);
  
  /**
   * Payments which are denied or never recieved get set to rejected
   * @param \Jazzee\Entity\Payment $payment
   * @param \Foundation\Form\Input $input
   */
  function rejectPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input);
  
  /**
   * The form to display to adminstrators so they can refund the payment
   * @param \Jazzee\Entity\Payment $payment
   * @return Form
   */
  function getRefundPaymentForm(\Jazzee\Entity\Payment $payment);
  
  /**
   * Refund a payment
   * @param \Jazzee\Entity\Payment $payment
   * @param \Foundation\Form\Input $input
   */
  function refundPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input);
  
  /**
   * Get the applicant admin tools
   * @param \Jazzee\Entity\Payment $payment
   */
  function applicantTools(\Jazzee\Entity\Payment $payment);
}