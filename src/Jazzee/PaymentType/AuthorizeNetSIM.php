<?php
namespace Jazzee\PaymentType;

require_once __DIR__ . '/../../../lib/anet_sdk/AuthorizeNet.php';

/**
 * Pay via Authorize.net Simple Integrtion Method
 * Payment form is hosted by authorize.net and applicant is directed there
 * anet the posts results to the transaction controller
 * transaction controller statically calls AuthorizeNetSIM::transaction which records the payment and then
 * sends a redirect back to authorize.net who sends the applicant back to the original page
 *
 * Use this class if you don't want to meet anythign but the most basic PCI requirements
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AuthorizeNetSIM extends AuthorizeNetAIM
{

  /**
   * Display a form which posts to authorize.net's server
   */
  public function paymentForm(\Jazzee\Entity\Applicant $applicant, $amount)
  {
    if (\is_a($this->_controller, 'ApplyPageController')) {
      $time = time();
      $fpSequence = $applicant->getId() . $time;
      $form = new \Foundation\Form();

      $form->newHiddenElement('x_show_form', 'PAYMENT_FORM');
      $form->newHiddenElement('x_amount', $amount);
      $form->newHiddenElement('x_test_request', ($this->_controller->getConfig()->getStatus() == 'PRODUCTION') ? 0 : 1);
      $form->newHiddenElement('x_fp_sequence', $fpSequence);
      $form->newHiddenElement('x_fp_hash', \AuthorizeNetSIM_Form::getFingerprint($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayKey'), $amount, $fpSequence, $time));
      $form->newHiddenElement('x_fp_timestamp', $time);
      $form->newHiddenElement('x_relay_response', "TRUE");
      $form->newHiddenElement('x_relay_url', $this->_controller->getServerPath() . $this->_controller->getActionPath() . '/../../../../../transaction/' . \urlencode(get_class($this)));
      $form->newHiddenElement('x_cancel_url', $this->_controller->getServerPath() . $this->_controller->getActionPath());
      $form->newHiddenElement('redirect_url', $this->_controller->getServerPath() . $this->_controller->getActionPath());
      $form->newHiddenElement('x_login', $this->_paymentType->getVar('gatewayId'));

      $form->newHiddenElement('x_cust_id', $applicant->getId());
      $form->newHiddenElement('x_customer_ip', $_SERVER['REMOTE_ADDR']);
      $form->newHiddenElement('x_email', $applicant->getEmail());
      $form->newHiddenElement('x_email_customer', 0);
      $form->newHiddenElement('x_description', $this->_paymentType->getVar('description'));


      $form->setAction($this->_paymentType->getVar('testAccount') ? \AuthorizeNetDPM::SANDBOX_URL : \AuthorizeNetDPM::LIVE_URL);
      $field = $form->newField();
      $field->setLegend($this->_paymentType->getName());
      $field->setInstructions("<p>Clicking the 'Pay with Credit Card' button will redirect you to a secure payment page.  Once you have completed your transaction you will be returned to the application.<strong>Application Fee:</strong> &#36;{$amount}</p>");

      $form->newButton('submit', 'Pay with Credit Card');
    } else {
      $form = parent::paymentForm($applicant, $amount);
    }

    return $form;
  }

  /**
   * Record transaction information pending
   * $input isn't used here becuase the DPM method uses the post data directly off the global $_POST
   */
  public function pendingPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $input = false;
    $response = new \AuthorizeNetSIM($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayHash'));
    if ($response->isAuthorizeNet()) {
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

        return true;
      }
    }

    return false;
  }

  public function setup(\Foundation\Form\Input $input)
  {
    parent::setup($input);
    $this->_paymentType->setClass('\\Jazzee\\PaymentType\AuthorizeNetSIM');
  }

  /**
   * Parse the transaction results sent from Authorize.net Direct Post
   * @param \TransactionController $controller
   */
  public static function transaction($controller)
  {
    $matches = array();
    preg_match('#page/(\d{1,})/?#', $_POST['redirect_url'], $matches);
    if (!isset($matches[1])) {
      throw new \Jazzee\Exception("No page id match found in redirect_url: '{$_POST['redirect_url']}");
    }
    $applicationPage = $controller->getEntityManager()->getRepository('\Jazzee\Entity\ApplicationPage')->find($matches[1]);
    if (!$applicationPage) {
      throw new \Jazzee\Exception("{$matches[1]} is not a valid applicationPage id");
    }
    if(!empty($_POST['x_cust_id'])){
      $applicant = $controller->getEntityManager()->getRepository('\Jazzee\Entity\Applicant')->find($_POST['x_cust_id']);
      if (!$applicant) {
        throw new \Jazzee\Exception("{$_POST['x_cust_id']} is not a valid applicant id.  Anet post: " . var_export($_POST, true));
      }
      $answer = new \Jazzee\Entity\Answer();
      $answer->setPage($applicationPage->getPage());
      $applicant->addAnswer($answer);

      $payment = new \Jazzee\Entity\Payment();
      $payment->setType($controller->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->find($_POST['paymentType']));
      $answer->setPayment($payment);
      $fakeInput = new \Foundation\Form\Input(array());
      if ($payment->getType()->getJazzeePaymentType($controller)->pendingPayment($payment, $fakeInput)) {
        $controller->getEntityManager()->persist($applicant);
        $controller->getEntityManager()->persist($answer);
        $controller->getEntityManager()->persist($payment);
        foreach ($payment->getVariables() as $var) {
          $controller->getEntityManager()->persist($var);
        }
        $controller->getEntityManager()->flush();
        print \AuthorizeNetDPM::getRelayResponseSnippet($_POST['redirect_url']);
      }
    }
  }

}