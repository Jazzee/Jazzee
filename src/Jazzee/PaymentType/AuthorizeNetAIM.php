<?php
namespace Jazzee\PaymentType;

require_once __DIR__ . '/../../../lib/anet_sdk/AuthorizeNet.php';

/**
 * Pay via Authorize.net Advanced Integration Method
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AuthorizeNetAIM extends AbstractPaymentType
{

  const APPLY_PAGE_ELEMENT = 'AuthorizeNetPayment-apply_page';
  const APPLICANTS_SINGLE_ELEMENT = 'AuthorizeNetPayment-applicants_single';
  const MIN_CRON_INTERVAL = 21500; //6 hours minus a bit

  /**
   * Credit card payment form
   * Different form for administrators
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */

  public function paymentForm(\Jazzee\Entity\Applicant $applicant, $amount)
  {
    $form = new \Foundation\Form();
    //we pass the amount back as a hidden element so PaymentPage will have it again
    $form->newHiddenElement('amount', $amount);

    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_paymentType->getName());
    if (\is_a($this->_controller, 'ApplyPageController')) {
      $field->setInstructions("<p><strong>Application Fee:</strong> &#36;{$amount}</p>");

      $element = $field->newElement('TextInput', 'cardNumber');
      $element->setLabel('Credit Card Number');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addValidator(new \Foundation\Form\Validator\CreditCard($element, explode(',', $this->_paymentType->getVar('acceptedCards'))));

      $element = $field->newElement('ShortDateInput', 'expirationDate');
      $element->setLabel('Expiration Date');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addValidator(new \Foundation\Form\Validator\DateAfter($element, 'last month'));
      $element->addFilter(new \Foundation\Form\Filter\DateFormat($element, 'mY'));

      $element = $field->newElement('TextInput', 'cardCode');
      $element->setLabel('CCV');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

      $element = $field->newElement('TextInput', 'postalCode');
      $element->setLabel('Billing Postal Code');
      $element->setInstructions('US Credit Cards which do not provide a postal code will be rejected.');

      $form->newButton('submit', 'Pay with Credit Card');
    } else if (\is_a($this->_controller, 'ApplicantsSingleController')) {
      $field->setInstructions("<p>Credit card details should be enterd directly at the authorize.net website and then the transaction entered here.</p>");
      $element = $field->newElement('TextInput', 'transactionId');
      $element->setLabel('Transaction ID');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $form->newButton('submit', 'Lookup Transaction');
    }

    return $form;
  }

  /**
   * Setup the payment types and the AIM credentials
   * @see ApplyPayment::setupForm()
   */
  public function getSetupForm()
  {
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Setup Authorize.net Payments');
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Payment Name');
    $element->setValue($this->_paymentType->getName());
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'description');
    $element->setLabel('Description');
    $element->setFormat('Appears on credit card statement for applicant');
    $element->setValue($this->_paymentType->getVar('description'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'gatewayId');
    $element->setLabel('Payment Gateway ID');
    $element->setValue($this->_paymentType->getVar('gatewayId'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'gatewayKey');
    $element->setLabel('Payment Gateway Key');
    $element->setValue($this->_paymentType->getVar('gatewayKey'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'gatewayHash');
    $element->setLabel('Payment Gateway Hashphrase');
    $element->setValue($this->_paymentType->getVar('gatewayHash'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('RadioList', 'testAccount');
    $element->setLabel('Is this a test account?');
    $element->setFormat('Test accounts are handled differenty by Authorize.net and need to be sent to a different URL.');
    $element->setValue($this->_paymentType->getVar('testAccount'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');

    $element = $field->newElement('CheckboxList', 'acceptedCards');
    $element->setLabel('What credit card types do you accept?');
    $types = \Foundation\Form\Validator\CreditCard::listTypes();
    foreach ($types as $id => $value) {
      $element->newitem($id, $value);
    }
    $element->setValue(explode(',', $this->_paymentType->getVar('acceptedCards')));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save');

    return $form;
  }

  public function setup(\Foundation\Form\Input $input)
  {
    $this->_paymentType->setName($input->get('name'));
    $this->_paymentType->setClass('\\Jazzee\\PaymentType\AuthorizeNetAIM');
    $this->_paymentType->setVar('description', $input->get('description'));
    $this->_paymentType->setVar('gatewayId', $input->get('gatewayId'));
    $this->_paymentType->setVar('gatewayKey', $input->get('gatewayKey'));
    $this->_paymentType->setVar('gatewayHash', $input->get('gatewayHash'));
    $this->_paymentType->setVar('testAccount', $input->get('testAccount'));
    $this->_paymentType->setVar('acceptedCards', implode(',', $input->get('acceptedCards')));
  }

  /**
   * Record transaction information pending until it is settled with the bank
   */
  public function pendingPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    if (\is_a($this->_controller, 'ApplyPageController')) {
      $aim = new \AuthorizeNetAIM($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayKey'));
      $aim->setSandBox($this->_paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
      $aim->amount = $input->get('amount');
      $aim->cust_id = $payment->getAnswer()->getApplicant()->getId();
      $aim->customer_ip = $_SERVER['REMOTE_ADDR'];
      $aim->email = $payment->getAnswer()->getApplicant()->getEmail();
      $aim->email_customer = 0;
      $aim->card_num = $input->get('cardNumber');
      $aim->exp_date = $input->get('expirationDate');
      $aim->card_code = $input->get('cardCode');
      $aim->zip = $input->get('postalCode');
      $aim->description = $this->_paymentType->getVar('description');
      $aim->test_request = ($this->_controller->getConfig()->getStatus() == 'PRODUCTION') ? 0 : 1;
      $response = $aim->authorizeAndCapture();
      if ($response->approved) {
        $payment->setAmount($response->amount);
        $payment->setVar('transactionId', $response->transaction_id);
        $payment->setVar('authorizationCode', $response->authorization_code);
        $payment->pending();

        return true;
      } else {
        $payment->setAmount($response->amount);
        $payment->setVar('transactionId', $response->transaction_id);
        $payment->setVar('rejectedReasonCode', $response->response_reason_code);
        $payment->setVar('rejectedReason', $response->response_reason_text);
        $payment->rejected();

        return false;
      }
    } else if (\is_a($this->_controller, 'ApplicantsSingleController')) {
      $transactionDetails = new \AuthorizeNetTD($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayKey'));
      $transactionDetails->setSandBox($this->_paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
      // Get Transaction Details
      $transactionId = $input->get('transactionId');
      $response = $transactionDetails->getTransactionDetails($transactionId);
      if (!$response->response or $response->isError()) {
        throw new \Jazzee\Exception("Unable to get transaction details for transcation id {$transactionId}", E_ERROR, 'There was a problem getting payment information.');
      }
      if ((int) $response->xml->transaction->customer->id != $payment->getAnswer()->getApplicant()->getId()) {
        throw new \Jazzee\Exception("Transaction {$transactionId} does not belong to applicant #" . $payment->getAnswer()->getApplicant()->getId());
      }

      if ((int) $response->xml->transaction->responseCode == 1) {
        $payment->setAmount((string) $response->xml->transaction->authAmount);
        $payment->setVar('transactionId', $transactionId);
        $payment->setVar('authorizationCode', (string) $response->xml->transaction->authorization_code);
        $payment->pending();

        return true;
      }
      $payment->setAmount((string) $response->xml->transaction->authAmount);
      $payment->setVar('transactionId', (string) $response->xml->transaction->transid);
      $payment->setVar('rejectedReasonCode', (string) $response->xml->transaction->responseReasonCode);
      $payment->setVar('rejectedReason', (string) $response->xml->transaction->responseReasonDescription);
      $payment->rejected();
    }

    return false;
  }

  /**
   * Attempt to settle payment with anet's API
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function getSettlePaymentForm(\Jazzee\Entity\Payment $payment)
  {
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Settle Payment');

    $element = $field->newElement('Plaintext', 'info');
    $element->setValue("Transactions have to be settled by Authorize.net.  To check the status of this payment click 'Attempt Settlement'");

    $form->newButton('submit', 'Attempt Settlement');

    return $form;
  }

  /**
   * Attempt to settle the payment with authorize.net
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function settlePayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $transactionDetails = new \AuthorizeNetTD($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayKey'));
    $transactionDetails->setSandBox($this->_paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    // Get Transaction Details
    $transactionId = $payment->getVar('transactionId');
    $response = $transactionDetails->getTransactionDetails($transactionId);
    if (!$response->response or $response->isError()) {
      throw new \Jazzee\Exception('Unable to get transaction details for payment #' . $payment->getId() . " transcation id {$transactionId} authorize.net said " . $response->getMessageText(), E_ERROR, 'There was a problem getting payment information.');
    }
    //has this transaction has been settled already
    if ($response->xml->transaction->transactionStatus == 'settledSuccessfully') {
      $payment->settled();
      $payment->setVar('settlementTimeUTC', (string) $response->xml->transaction->batch->settlementTimeUTC);

      return true;
    } else if ($response->xml->transaction->transactionStatus == 'voided') {
      $payment->rejected();
      $payment->setVar('rejectedReason', 'This payment was voided.');

      return true;
    }

    return "Unable to settle transaction #{$payment->getVar('transactionId')} authorize.net said: " . $response->getMessageText();
  }

  /**
   * Record the reason the payment was rejected
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function getRejectPaymentForm(\Jazzee\Entity\Payment $payment)
  {
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Reject Payment');

    $element = $field->newElement('Textarea', 'rejectedReason');
    $element->setLabel('Reason displayed to Applicant');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save');

    return $form;
  }

  /**
   * Void a transaction before it is settled
   */
  public function rejectPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $aim = new \AuthorizeNetAIM($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayKey'));
    $aim->setSandBox($this->_paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    $aim->test_request = ($this->_controller->getConfig()->getStatus() == 'PRODUCTION') ? 0 : 1;
    $response = $aim->void($payment->getVar('transactionId'));
    if ($response->approved) {
      $payment->rejected();
      $payment->setVar('rejectedReason', $input->get('rejectedReason'));

      return true;
    }
    //if we cant void we are probably already settled so try and settle the payment in our system
    $settled = $this->settlePayment($payment, $input);
    if ($settled === true) {
      return 'Cannot void payment becuase it has already been settled.';
    }

    //otherwise return the original error
    return "Unable to submit void for transaction #{$payment->getVar('transactionId')} authorize.net said: " . $response->getMessageText();
  }

  /**
   * Record the reason the payment was refunded
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  public function getRefundPaymentForm(\Jazzee\Entity\Payment $payment)
  {
    $transactionDetails = new \AuthorizeNetTD($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayKey'));
    $transactionDetails->setSandBox($this->_paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    // Get Transaction Details
    $transactionId = $payment->getVar('transactionId');
    $response = $transactionDetails->getTransactionDetails($transactionId);
    if ($response->isError()) {
      throw new \Jazzee\Exception('Unable to get transaction details for payment #' . $payment->getId() . " transcation id {$transactionId}.  Authorize.net said " . $response->getMessageText(), E_ERROR, 'There was a problem getting payment information.');
    }

    $submitted = new \DateTime($response->xml->transaction->submitTimeLocal);
    if ($submitted->diff(new \DateTime())->days > 120) {
      throw new \Jazzee\Exception('Cannot refund payment, it is too old. Payment ID #' . $payment->getId() . " transcation id {$transactionId}.", E_ERROR, 'Payment is too old to refund.');
    }
    $form = new \Foundation\Form;
    $field = $form->newField();
    $field->setLegend('Refund Payment');
    $element = $field->newElement('Plaintext', 'details');
    $element->setLabel('Details');
    $element->setValue('Refund $' . $payment->getAmount() . ' to card ' . $response->xml->transaction->payment->creditCard->cardNumber);

    $element = $field->newElement('Textarea', 'refundedReason');
    $element->setLabel('Reason displayed to Applicant');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newHiddenElement('cardNumber', substr($response->xml->transaction->payment->creditCard->cardNumber, strlen($response->xml->transaction->payment->creditCard->cardNumber) - 4, 4));
    $form->newHiddenElement('zip', (string) $response->xml->transaction->billTo->zip);
    $form->newButton('submit', 'Save');

    return $form;
  }

  /**
   * Contact anet and attempt to refund the payment
   * @see ApplyPaymentInterface::refundPayment()
   */
  public function refundPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $aim = new \AuthorizeNetAIM($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayKey'));
    $aim->setSandBox($this->_paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    $aim->test_request = ($this->_controller->getConfig()->getStatus() == 'PRODUCTION') ? 0 : 1;
    $aim->zip = $input->get('zip');
    $response = $aim->credit($payment->getVar('transactionId'), $payment->getAmount(), $input->get('cardNumber'));
    if ($response->approved) {
      $payment->refunded();
      $payment->setVar('refundedReason', $input->get('refundedReason'));

      return true;
    } else {
      return "Unable to submit refund for transaction #{$payment->getVar('transactionId')} authorize.net said: " . $response->getMessageText();
    }

    return false;
  }

  /**
   * Attempt to settle payments
   * @param AdminCronController $cron
   */
  public static function runCron(\AdminCronController $cron)
  {
    $paymentType = $cron->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->findOneBy(array('class' => '\\' . get_called_class()));
    $cronIntervalVar = 'authorizeNetPaymentLastRun-id-' . $paymentType->getId();
    if (time() - (int) $cron->getVar($cronIntervalVar) > self::MIN_CRON_INTERVAL) {
      $cron->setVar($cronIntervalVar, time());
      $count = 0;
      $unsettledIds = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Payment')->findIdByStatusAndTypeArray(\Jazzee\Entity\Payment::PENDING, $paymentType);
      $fakeInput = new \Foundation\Form\Input(array());
      foreach ($unsettledIds as $id) {
        $payment = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Payment')->find($id);
        $result = $paymentType->getJazzeePaymentType($cron)->settlePayment($payment, $fakeInput);
        if ($result === true) {
          $count++;
          $cron->getEntityManager()->persist($payment);
          foreach ($payment->getVariables() as $var) {
            $cron->getEntityManager()->persist($var);
          }
        }
        unset($payment);
      }

      if ($count) {
        $cron->log("Settled {$count} {$paymentType->getClass()} Payments.");
      }
    }
  }

}