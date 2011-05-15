<?php
/**
 * Abstract ApplyPayment Class
 * Specific Payment Types must extend this class
 *
 */
abstract class ApplyPayment implements ApplyPaymentInterface{
  /**
   * Status text constants
   */
  const PENDING_TEXT = 'pending';
  const SETTLED_TEXT = 'settled';
  const REJECTED_TEXT = 'rejected';
  const REFUNDED_TEXT = 'refunded';
  
  /**
   * The PaymentType Model
   * @var PaymentType
   */
  protected $paymentType;
  
  /**
   * The config file
   * @var ConfigManager
   */
  protected $config;
  
  /**
   * @var Message
   */
  protected $messages;
  
  /**
   * Constructor
   * @param Payment $payment
   * @param PaymentType $paymentType
   */
  public function __construct(PaymentType $paymentType){
    $this->paymentType = $paymentType;
    $this->config = new ConfigManager;
    $this->config->addContainer(new IniConfigType(SRC_ROOT . '/etc/config.ini.php'));
    $this->messages = Message::getInstance();
  }
  
}

/**
 * Interface for ApplyPayment
 */
interface ApplyPaymentInterface{
  
  /**
   * Constructor
   * Payment $payment 
   * PaymentType $paymentType
   */
  function __construct(PaymentType $paymentType);
  
  /**
   * Get the form for new payments
   * @param Applicant $applicant
   * @param float $amount
   * @param string $actionPath where we are posting the form to
   * @return Form
   */
  function paymentForm(Applicant $applicant, $amount, $actionPath);

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
  static function setup(PaymentType $paymentType, FormInput $input);
  
  /**
   * Record a payment as pending
   * Pending payments have not been settled they allow the applicant to move on
   * but decisions cannot be made until a payment is settled
   * @param Payment $payment
   * @param FormInput $input
   */
  function pendingPayment(Payment $payment, FormInput $input);
  
  /**
   * The form to display to adminstrators so they can settle the payment
   * @param Payment $payment
   * @return Form
   */
  function getSettlePaymentForm(Payment $payment);
  
  /**
   * Once funds have been recieved or transactions verified a payment is settled
   * @param Payment $payment
   * @param FormInput $input
   */
  function settlePayment(Payment $payment, FormInput $input);
  
  /**
   * The form to display to adminstrators so they can reject the payment
   * @param Payment $payment
   * @return Form
   */
  function getRejectPaymentForm(Payment $payment);
  
  /**
   * Payments which are denied or never recieved get set to rejected
   * @param Payment $payment
   * @param FormInput $input
   */
  function rejectPayment(Payment $payment, FormInput $input);
  
  /**
   * The form to display to adminstrators so they can refund the payment
   * @param Payment $payment
   * @return Form
   */
  function getRefundPaymentForm(Payment $payment);
  
  /**
   * Refund a payment
   * @param Payment $payment
   * @param FormInput $input
   */
  function refundPayment(Payment $payment, FormInput $input);
  
  /**
   * Get the applicant admin tools
   * @param Payment $payment
   */
  function applicantTools(Payment $payment);
}