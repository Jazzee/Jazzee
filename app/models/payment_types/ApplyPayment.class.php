<?php
/**
 * Abstract class for the different types of payment
 */
abstract class ApplyPayment{
  /**
   * The PaymentType Model
   * @var PaymentType
   */
  protected $paymentType;
  
  /**
   * Constructor
   * @param PaymentType $paymentType
   */
  public function __construct(PaymentType $paymentType){
    $this->paymentType = $paymentType;  
  }
  
  /**
   * Get the payment form
   * @param Applicant $applicant
   * @param Array $amounts
   * @param Form $form if a form is already in place we can use it
   * @return Form
   */
  abstract public function paymentForm(Applicant $applicant, $amounts, Form $form = null);

  /**
   * Get the leadingText for the payment page
   * @param Applicant $applicant
   * @return string
   */
  public function leadingText(Applicant $applicant){return '';}
  
  /**
   * Get the trailingText for the payment page
   * @param Applicant $applicant
   * @return string
   */
  public function trailingText(Applicant $applicant){return '';}
  
  /**
   * Get the setup form
   * @param PaymentType $paymentType if it already exists use it to populate the form
   * @return Form
   */
  abstract public static function setupForm(PaymentType $paymentType = null);
  
  /**
   * Setup the payment type
   * @param PaymentType $paymentType
   * @param Input $input the input from the form
   */
  abstract public static function setup(PaymentType $paymentType, Input $input);
  
  /**
   * Record a payment as pending
   * Pending payments have not been settled they allow the applicant to move on
   * but decisions cannot be made until a payment is settled
   * @param Payment $payment
   */
  abstract public function pendingPayment(Payment $payment);
  
  /**
   * Once funds have been recieved or transactions verified a payment is settled
   * @param Payment $payment
   */
  abstract public function settlePayment(Payment $payment);
  
  /**
   * Payments which are denied or never recieved get set to rejected
   * @param Payment $payment
   */
  abstract public function rejectPayment(Payment $payment);
  
  /**
   * Refund a payment
   * @param Payment $payment
   */
  abstract public function refundPayment(Payment $payment);
  
}