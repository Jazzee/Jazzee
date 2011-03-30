<?php
/**
 * Interface for ApplyPayment
 */
interface ApplyPaymentInterface{
  /**
   * Get the payment form
   * @param Applicant $applicant
   * @param Array $amounts
   * @param Form $form if a form is already in place we can use it
   * @return Form
   */
  function paymentForm(Applicant $applicant, $amounts, Form $form = null);

  /**
   * Get the leadingText for the payment page
   * @param Applicant $applicant
   * @return string
   */
  function leadingText(Applicant $applicant);
  
  /**
   * Get the trailingText for the payment page
   * @param Applicant $applicant
   * @return string
   */
  function trailingText(Applicant $applicant);
  
  /**
   * Get the setup form
   * @param PaymentType $paymentType if it already exists use it to populate the form
   * @return Form
   */
  static function setupForm(PaymentType $paymentType = null);
  
  /**
   * Setup the payment type
   * @param PaymentType $paymentType
   * @param Input $input the input from the form
   */
  static function setup(PaymentType $paymentType, Input $input);
  
  /**
   * Record a payment as pending
   * Pending payments have not been settled they allow the applicant to move on
   * but decisions cannot be made until a payment is settled
   * @param Payment $payment
   */
  function pendingPayment(Payment $payment);
  
  /**
   * Once funds have been recieved or transactions verified a payment is settled
   * @param Payment $payment
   */
  function settlePayment(Payment $payment);
  
  /**
   * Payments which are denied or never recieved get set to rejected
   * @param Payment $payment
   */
  function rejectPayment(Payment $payment);
  
  /**
   * Refund a payment
   * @param Payment $payment
   */
  function refundPayment(Payment $payment);
  
}