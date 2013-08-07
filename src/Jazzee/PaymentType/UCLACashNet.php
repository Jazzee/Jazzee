<?php
namespace Jazzee\PaymentType;

/**
 * Pay via UCLA Cashnet / Bruinbuy System
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class UCLACashNet extends AbstractPaymentType
{

  const APPLY_PAGE_ELEMENT = 'UCLACashNet-apply_page';
  const APPLICANTS_SINGLE_ELEMENT = 'UCLACashNet-applicants_single';
  const CASHNET_URL = 'https://commerce.cashnet.com/404Handler/pageredirpost.aspx?virtual=';
  const CASHNET_WSDL = 'http://commerce.cashnet.com/ws/CASHNetWebService.asmx?WSDL';
  const CASHNET_TEST_VIRTUALDIRECTORY = 'UCLAINQTEST';
  const CASHNET_VIRTUALDIRECTORY = 'UCLAINQUIRY';
  const MIN_CRON_INTERVAL = 21500; //6 hours minus a bit

  /**
   * Credit card payment form
   * Different form for administrators
   */
  public function paymentForm(\Jazzee\Entity\Applicant $applicant, $amount)
  {
    $form = new \Foundation\Form();
    if (\is_a($this->_controller, 'ApplyPageController')) {
      $form->newHiddenElement('ucla_ref_no', uniqid(rand()));
      $form->newHiddenElement('amount1', $amount);
      $form->newHiddenElement('itemcode1', $this->_paymentType->getVar('itemCode'));
      $form->newHiddenElement('desc1', $this->_paymentType->getVar('itemDescription'));
      $form->newHiddenElement('ref1type1', $this->_paymentType->getVar('reftypeApplicantId'));
      $form->newHiddenElement('ref1val1', $applicant->getId());
      $form->newHiddenElement('ref2type1', $this->_paymentType->getVar('reftypeForwardUrl'));
      $form->newHiddenElement('ref2val1', $this->_controller->getServerPath() . $this->_controller->getActionPath());
      $form->newHiddenElement('signouturl',  $this->_controller->absolutePath('transaction/' . \urlencode(get_class($this))));
      if($this->_controller->getConfig()->getStatus() == 'PRODUCTION'){
        $form->setAction(self::CASHNET_URL . $this->_paymentType->getVar('liveSiteName'));
      } else {
        $form->setAction(self::CASHNET_URL . $this->_paymentType->getVar('devSiteName'));
      }
      $field = $form->newField();
      $field->setLegend($this->_paymentType->getName());
      $field->setInstructions("<p>Clicking the 'Pay with Credit Card' button will redirect you to a secure payment page.  Once you have completed your transaction you will be returned to the application.<strong>Application Fee:</strong> &#36;{$amount}</p>");
      $form->newButton('submit', 'Pay with Credit Card');
    } else {
      
      $field = $form->newField();
      $field->setLegend($this->_paymentType->getName());
      $field->setInstructions("<p>Credit card details should be enterd directly at the cashnet website and then the transaction ID entered here.</p>");
      $element = $field->newElement('TextInput', 'transactionId');
      $element->setLabel('Transaction ID');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      //we pass the amount back as a hidden element so PaymentPage will have it again
      $form->newHiddenElement('amount', $amount);
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
    $field->setLegend('Setup UCLA Cashnet Payments');
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Payment Name');
    $element->setValue($this->_paymentType->getName());
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput', 'storeName');
    $element->setLabel('Store Name');
    $element->setValue($this->_paymentType->getVar('storeName'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput', 'devSiteName');
    $element->setLabel('Development Site Name');
    $element->setValue($this->_paymentType->getVar('devSiteName'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput', 'liveSiteName');
    $element->setLabel('Live Site Name');
    $element->setValue($this->_paymentType->getVar('liveSiteName'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput', 'itemCode');
    $element->setLabel('Item Code');
    $element->setValue($this->_paymentType->getVar('itemCode'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput', 'itemDescription');
    $element->setLabel('Item Description');
    $element->setValue($this->_paymentType->getVar('itemDescription'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput', 'reftypeApplicantId');
    $element->setLabel('Applicant ID Reference Varialbe Name');
    $element->setInstructions('You will need to setup a Reference Type varialbe with cashnet (ref1typeY) for storing the applicant ID.  If possible use the default "Applicant ID"');
    $element->setValue($this->_paymentType->getVar('reftypeApplicantId')?$this->_paymentType->getVar('reftypeApplicantId'):'Applicant ID');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput', 'reftypeForwardUrl');
    $element->setLabel('Forward URL Reference Varialbe Name');
    $element->setInstructions('You will need to setup a Reference Type varaible with cashnet (ref2typeY) for storing the URL applicants will be forwarded to.  If possible use the default "Forward URL"');
    $element->setValue($this->_paymentType->getVar('reftypeForwardUrl')?$this->_paymentType->getVar('reftypeForwardUrl'):'Forward URL');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput', 'operatorId');
    $element->setLabel('Operator Id');
    $element->setInstructions('This operator must have access to the cashnet Transactio INquiry API');
    $element->setValue($this->_paymentType->getVar('operatorId'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput', 'operatorPassword');
    $element->setLabel('Operator Password');
    $element->setValue($this->_paymentType->getVar('operatorPassword'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Save');

    return $form;
  }

  public function setup(\Foundation\Form\Input $input)
  {
    $this->_paymentType->setName($input->get('name'));
    $this->_paymentType->setClass(get_class($this));
    $this->_paymentType->setVar('storeName', $input->get('storeName'));
    $this->_paymentType->setVar('devSiteName', $input->get('devSiteName'));
    $this->_paymentType->setVar('liveSiteName', $input->get('liveSiteName'));
    $this->_paymentType->setVar('itemCode', $input->get('itemCode'));
    $this->_paymentType->setVar('itemDescription', $input->get('itemDescription'));
    $this->_paymentType->setVar('reftypeApplicantId', $input->get('reftypeApplicantId'));
    $this->_paymentType->setVar('reftypeForwardUrl', $input->get('reftypeForwardUrl'));
    $this->_paymentType->setVar('operatorId', $input->get('operatorId'));
    $this->_paymentType->setVar('operatorPassword', $input->get('operatorPassword'));
  }

  /**
   * Record transaction information pending
   */
  public function pendingPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    if (\is_a($this->_controller, 'TransactionController')) {
      foreach($payment->getAnswer()->getApplicant()->getAnswers() as $answer){
        if($existingPayment = $answer->getPayment()){
          if($existingPayment->getType() == $payment->getType() and $existingPayment->getVar('UCLA_REF_NO') == $input->get('UCLA_REF_NO')){
            //UCLA double posts transactions so if we have already stored this answer then we shouldn't store it again.
            return false;
          }
        }
      }

      $payment->setAmount($input->get('amount1')?$input->get('amount1'):0);
      if ($input->get('result') == '0' ) {
        $payment->setVar('tx', $input->get('tx'));
        $payment->pending();
        $client = new \SoapClient(self::CASHNET_WSDL);
        $parameters = array(
          'OperatorID' => $this->_paymentType->getVar('operatorId'),
          'Password' => $this->_paymentType->getVar('operatorPassword'),
          'VirtualDirectory'  => ($this->_controller->getConfig()->getStatus() == 'PRODUCTION')?self::CASHNET_VIRTUALDIRECTORY:self::CASHNET_TEST_VIRTUALDIRECTORY,
          'TransactionNo' =>  $payment->getVar('tx')
        );

        $results = $client->CASHNetSOAPRequestInquiry(array('inquiryParams'=>$parameters));
        $xml = new \SimpleXMLElement($results->CASHNetSOAPRequestInquiryResult);
        if($xml->result != 0){
          $this->_controller->log("Unable to get transaction details from cashnet for transaction: {$payment->getVar('tx')} for applicant {$payment->getAnswer()->getApplicant()->getId()}.  Cashnet said: {$xml->respmessage}",  \Monolog\Logger::ERROR);
        } else {
          if($xml->transactions[0]->transaction->txno == $payment->getVar('tx') and $xml->transactions[0]->transaction->totalamount == $payment->getAmount()){
            $payment->settled();
          } else {
            throw new \Jazzee\Exception("Transaction details differ between cashnet and payment for applicant {$payment->getAnswer()->getApplicant()->getId()}.  Payment (TX: {$payment->getVar('tx')}, Ammount: {$payment->getAmount()}) Cashnet Payment (TX: {$xml->transactions[0]->transaction->txno}, Amount: {$xml->transactions[0]->transaction->totalamount}) ");
          }
        }
      } else {
        $payment->setVar('tx', $input->get('failedtx'));
        $payment->setVar('rejectedReason', $input->get('respmessage'));
        $payment->rejected();
      }
      
      $payment->setVar('custcode', $input->get('custcode'));
      $payment->setVar('pmtcode', $input->get('pmtcode'));
      $payment->setVar('itemcode', $input->get('itemcode1'));
      $payment->setVar('UCLA_REF_NO', $input->get('UCLA_REF_NO'));
    } else if (\is_a($this->_controller, 'ApplicantsSingleController')) {
      $client = new \SoapClient(self::CASHNET_WSDL);
      $parameters = array(
        'OperatorID' => $this->_paymentType->getVar('operatorId'),
        'Password' => $this->_paymentType->getVar('operatorPassword'),
        'VirtualDirectory'  => ($this->_controller->getConfig()->getStatus() == 'PRODUCTION')?self::CASHNET_VIRTUALDIRECTORY:self::CASHNET_TEST_VIRTUALDIRECTORY,
        'TransactionNo' =>  $input->get('transactionId')
      );

      $results = $client->CASHNetSOAPRequestInquiry(array('inquiryParams'=>$parameters));
      $xml = new \SimpleXMLElement($results->CASHNetSOAPRequestInquiryResult);
      if($xml->result != 0){
        throw new \Jazzee\Exception("Unable to get transaction details from cashnet for transaction: {$input->get('transactionId')} for applicant {$payment->getAnswer()->getApplicant()->getId()}.  Cashnet said: {$xml->respmessage}");
      }
      
      $payment->setVar('tx', $xml->transactions[0]->transaction->txno);
      $payment->setAmount($xml->transactions[0]->transaction->totalamount);
      $payment->setVar('custcode', $xml->transactions[0]->transaction->custcode);
      $payment->setVar('pmtcode', $xml->transactions[0]->transaction->pmtcode);
      $payment->setVar('itemcode', $xml->transactions[0]->transaction->itemcode);
      foreach ($xml->transactions[0]->transaction->trefs->tref as $tref) {
        $payment->setVar($tref->reftype, $tref->refvalue);
      }
      $payment->settled();
    } else {
      throw new \Jazzee\Exception("UCLACashNET::pendingPayment called form invalid controller: " . get_class($this->_controller));
    }

    return true;
  }

  /**
   * Attempt to settle payment with cashnets API
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function getSettlePaymentForm(\Jazzee\Entity\Payment $payment)
  {
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Settle Payment');

    $element = $field->newElement('Plaintext', 'info');
    $element->setValue("Transactions have to be settled by UCLA.  To check the status of this payment click 'Attempt Settlement'");

    $form->newButton('submit', 'Attempt Settlement');

    return $form;
  }

  /**
   * Attempt to settle the payment with authorize.net
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function settlePayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $client = new \SoapClient(self::CASHNET_WSDL);
    $parameters = array(
      'OperatorID' => $this->_paymentType->getVar('operatorId'),
      'Password' => $this->_paymentType->getVar('operatorPassword'),
      'VirtualDirectory'  => ($this->_controller->getConfig()->getStatus() == 'PRODUCTION')?self::CASHNET_VIRTUALDIRECTORY:self::CASHNET_TEST_VIRTUALDIRECTORY,
      'TransactionNo' => $payment->getVar('tx')
    );
    $results = $client->CASHNetSOAPRequestInquiry(array('inquiryParams'=>$parameters));
    $xml = new \SimpleXMLElement($results->CASHNetSOAPRequestInquiryResult);
    if($xml->result != 0){
      return "Unable to get transaction details from cashnet for payment: {$payment->getId()}, transaction: {$payment->getVar('tx')} for applicant {$payment->getAnswer()->getApplicant()->getId()}.  Cashnet said: {$xml->respmessage}";
    }
    if($xml->transactions[0]->transaction->txno == $payment->getVar('tx') and $xml->transactions[0]->transaction->totalamount == $payment->getAmount()){
      $payment->settled();
      return true;
    } else {
      throw new \Jazzee\Exception("Transaction details differ between cashnet and payment: {$payment->getId()} for applicant {$payment->getAnswer()->getApplicant()->getId()}.");
    }
  }

  /**
   * Record the reason the payment was rejected
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function getRejectPaymentForm(\Jazzee\Entity\Payment $payment)
  {
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Void Payment');

    $element = $field->newElement('Plaintext', 'info');
    $element->setValue("Transactions have to be voided by UCLA.  Enter details into the system for record keeping only.");

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
    $payment->rejected();
    $payment->setVar('rejectedReason', $input->get('rejectedReason'));

    return true;
  }

  /**
   * Record the reason the payment was refunded
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  public function getRefundPaymentForm(\Jazzee\Entity\Payment $payment)
  {
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Refund Payment');
    $field->setInstructions('Transactions have to be refunded by UCLA.  After refunding this transaction enter details into this system for record keeping.');

    $element = $field->newElement('TextInput', 'transactionId');
    $element->setLabel('Refund Transaction ID');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('Textarea', 'refundedReason');
    $element->setLabel('Reason displayed to Applicant');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save');

    return $form;
  }

  /**
   * Contact anet and attempt to refund the payment
   * @see ApplyPaymentInterface::refundPayment()
   */
  public function refundPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input)
  {
    $client = new \SoapClient(self::CASHNET_WSDL);
    $parameters = array(
      'OperatorID' => $this->_paymentType->getVar('operatorId'),
      'Password' => $this->_paymentType->getVar('operatorPassword'),
      'VirtualDirectory'  => ($this->_controller->getConfig()->getStatus() == 'PRODUCTION')?self::CASHNET_VIRTUALDIRECTORY:self::CASHNET_TEST_VIRTUALDIRECTORY,
      'TransactionNo' => $input->get('transactionId')
    );
    $results = $client->CASHNetSOAPRequestInquiry(array('inquiryParams'=>$parameters));
    $xml = new \SimpleXMLElement($results->CASHNetSOAPRequestInquiryResult);
    if($xml->result != 0){
      return "Unable to get refund transaction details from cashnet for payment: {$payment->getId()}, transaction: {$input->get('transactionId')} for applicant {$payment->getAnswer()->getApplicant()->getId()}.  Cashnet said: {$xml->respmessage}";
    }
    if($xml->transactions[0]->transaction->txno == $input->get('transactionId')){
      $payment->refunded();
      $payment->setVar('refundAmount', $xml->transactions[0]->transaction->totalamount);
      $payment->setVar('refundTransactionId', $xml->transactions[0]->transaction->txno);
      $payment->setVar('refundedReason', $input->get('refundedReason'));
      return true;
    } else {
      throw new \Jazzee\Exception("Transaction details differ between cashnet and payment: {$payment->getId()} for applicant {$payment->getAnswer()->getApplicant()->getId()}.");
    }

    return true;
  }

  /**
   * Parse the transaction results sent from cashnet
   * @param \TransactionController $controller
   */
  public static function transaction($controller)
  {
    if(empty($_POST['ref1val1']) or empty($_POST['ref2val1'])){
      throw new \Jazzee\Exception("refval1 or refval2 not set by cashnet.  Cashnet post: " . var_export($_POST, true));
    }
    $matches = array();
    preg_match('#page/(\d{1,})/?#', $_POST['ref2val1'], $matches);
    if (!isset($matches[1])) {
      throw new \Jazzee\Exception("No page id match found in ref2val1: '{$_POST['ref2val1']}'");
    }
    $applicationPage = $controller->getEntityManager()->getRepository('\Jazzee\Entity\ApplicationPage')->find($matches[1]);
    if (!$applicationPage) {
      throw new \Jazzee\Exception("{$matches[1]} is not a valid applicationPage id");
    }
    $applicant = $controller->getEntityManager()->getRepository('\Jazzee\Entity\Applicant')->find($_POST['ref1val1']);
    if (!$applicant) {
      throw new \Jazzee\Exception("{$_POST['ref1val1']} is not a valid applicant id.  Cashnet post: " . var_export($_POST, true));
    }
    $answer = new \Jazzee\Entity\Answer();
    $answer->setPage($applicationPage->getPage());
    $applicant->addAnswer($answer);

    $payment = new \Jazzee\Entity\Payment();
    $payment->setType($controller->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->findOneBy(array('class' => get_called_class())));
    $answer->setPayment($payment);
    $input = new \Foundation\Form\Input($_POST);
    if ($payment->getType()->getJazzeePaymentType($controller)->pendingPayment($payment, $input)) {
      $controller->getEntityManager()->persist($applicant);
      $controller->getEntityManager()->persist($answer);
      $controller->getEntityManager()->persist($payment);
      foreach ($payment->getVariables() as $var) {
        $controller->getEntityManager()->persist($var);
      }
      $controller->getEntityManager()->flush();
      header('Location: ' . $_POST['ref2val1']);
      die();
    }
    throw new \Jazzee\Exception("We were unable to record this payment.  Cashnet post: " . var_export($_POST, true));
  }

  /**
   * Attempt to settle payments
   * @param AdminCronController $cron
   */
  public static function runCron(\AdminCronController $cron)
  {
    $paymentType = $cron->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->findOneBy(array('class' => get_called_class()));
    $cronIntervalVar = 'uclacashnetPaymentLastRun-id-' . $paymentType->getId();
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
        } else {
          $cron->log($result);
        }
        unset($payment);
      }

      if ($count) {
        $cron->log("Settled {$count} {$paymentType->getClass()} Payments.");
      }
    }
  }

}