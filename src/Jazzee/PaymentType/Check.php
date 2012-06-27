<?php
namespace Jazzee\PaymentType;

/**
 * Pay by check
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Check extends AbstractPaymentType
{

  const APPLY_PAGE_ELEMENT = 'CheckPayment-apply_page';
  const APPLICANTS_SINGLE_ELEMENT = 'CheckPayment-applicants_single';

  public function paymentForm(\Jazzee\Entity\Applicant $applicant, $amount)
  {
    $form = new \Foundation\Form();
    $form->setAction($this->_controller->getActionPath());
    $form->newHiddenElement('amount', $amount);
    $field = $form->newField();
    $field->setLegend($this->_paymentType->getName());
    $field = $form->newField();

    $instructions = "<p><strong>Application Fee:</strong> &#36;{$amount}</p>";
    $instructions .= '<p><strong>Make Checks Payable to:</strong> ' . $this->_paymentType->getVar('payable') . '</p>';
    if ($this->_paymentType->getVar('address')) {
      $instructions .= '<p><h4>Mail Check to:</h4>' . nl2br($this->_paymentType->getVar('address')) . '</p>';
    }
    if ($this->_paymentType->getVar('coupon')) {
      $instructions .= '<p><h4>Include the following information with your payment:</h4> ' . nl2br($this->_paymentType->getVar('coupon')) . '</p>';
    }
    $search = array(
      '_Applicant_Name_',
      '_Applicant_ID_',
      '_Program_Name_',
      '_Program_ID_'
    );
    $replace = array();
    $replace[] = $applicant->getFirstName() . ' ' . $applicant->getLastName();
    $replace[] = $applicant->getId();
    $replace[] = $applicant->getApplication()->getProgram()->getName();
    $replace[] = $applicant->getApplication()->getProgram()->getId();
    $instructions = str_ireplace($search, $replace, $instructions);
    $instructions .= '<p>Click the Pay By Check button to pay your fee by check.  Your account will be temporarily credited and you can complete your application.  Your application will not be reviewed until your check is recieved.</p>';
    $field->setInstructions($instructions);

    $form->newButton('submit', 'Pay By Check');

    return $form;
  }

  public function getSetupForm()
  {
    $filters = array(
      'Applicant Name' => '_Applicant_Name_',
      'Applicant ID' => '_Applicant_ID_',
      'Program Name' => '_Program_Name_',
      'Program ID' => '_Program_ID_'
    );
    $instructions = 'These wildcards will be replaced in the text of each element: ';
    foreach ($filters as $title => $wildcard) {
      $instructions .= "<br />{$title}:{$wildcard}";
    }
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Setup Payment');
    $field->setInstructions($instructions);
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Payment Name');
    $element->setValue($this->_paymentType->getName());
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'payable');
    $element->setLabel('Make the check payable to');
    $element->setValue($this->_paymentType->getVar('payable'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('Textarea', 'address');
    $element->setLabel('Address to send the check to');
    $element->setValue($this->_paymentType->getVar('address'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('Textarea', 'coupon');
    $element->setLabel('Text for Payment Coupon');
    $element->setValue($this->_paymentType->getVar('coupon'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save');

    return $form;
  }

  public function setup(\Foundation\Form\Input $input)
  {
    $this->_paymentType->setName($input->get('name'));
    $this->_paymentType->setClass('\\Jazzee\\PaymentType\\Check');
    $this->_paymentType->setVar('payable', $input->get('payable'));
    $this->_paymentType->setVar('address', $input->get('address'));
    $this->_paymentType->setVar('coupon', $input->get('coupon'));
  }

  /**
   * Check Payments are pending with no verification
   * @see ApplyPaymentInterface::pendingPayment()
   */
  public function pendingPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $payment->setAmount($input->get('amount'));
    $payment->pending();

    return true;
  }

  /**
   * Record the check number and the deposit date for a payment
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @see ApplyPaymentInterface::settlePaymentForm()
   */
  public function getSettlePaymentForm(\Jazzee\Entity\Payment $payment)
  {
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Settle Payment');

    $element = $field->newElement('TextInput', 'checkNumber');
    $element->setLabel('Check Number');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('DateInput', 'checkSettlementDate');
    $element->setLabel('The Date the check was settled');
    $element->setValue('today');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\DateBefore($element, 'tomorrow'));
    $element->addFilter(new \Foundation\Form\Filter\DateFormat($element, 'c'));

    $form->newButton('submit', 'Save');

    return $form;
  }

  /**
   * Once checks have been cashed we settle the payment
   * @see ApplyPaymentInterface::settlePayment()
   */
  public function settlePayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $payment->settled();
    $payment->setVar('checkNumber', $input->get('checkNumber'));
    $payment->setVar('checkSettlementDate', $input->get('checkSettlementDate'));

    return true;
  }

  /**
   * Record the reason the payment was refunded
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @see ApplyPaymentInterface::rejectPaymentForm()
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
   * Check payments are refunded outside Jazzee and then marked as refunded
   * @see ApplyPaymentInterface::refundPayment()
   */
  public function rejectPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $payment->rejected();
    $payment->setVar('rejectedReason', $input->get('rejectedReason'));

    return true;
  }

  /**
   * Record the reason the payment was refunded
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  public function getRefundPaymentForm(\Jazzee\Entity\Payment $payment)
  {
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Refund Payment');

    $element = $field->newElement('Textarea', 'refundedReason');
    $element->setLabel('Reason displayed to Applicant');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save');

    return $form;
  }

  /**
   * Check payments are refunded outside Jazzee and then marked as refunded
   * @see ApplyPaymentInterface::refundPayment()
   */
  public function refundPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $payment->refunded();
    $payment->setVar('refundedReason', $input->get('refundedReason'));

    return true;
  }

}