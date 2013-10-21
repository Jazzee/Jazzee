<?php

/**
 * Payment report on all transactions
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PaymentsReportController extends \Jazzee\AdminController
{

  const MENU = 'Payments';
  const TITLE = 'Settled Report';
  const PATH = 'payments/report';
  const ACTION_INDEX = 'Report on Payments';
  const REQUIRE_APPLICATION = false;

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addCss('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css');
    $this->addCss('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables_themeroller.css');
    $this->addScript('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js');
    
    $this->addCss('//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.4/css/TableTools.min.css');
    $this->addCss('//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.4/css/TableTools_JUI.min.css');
    $this->addScript('//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.4/js/TableTools.min.js');
    
    $this->addScript($this->path('resource/scripts/classes/Status.class.js'));
    $this->addScript($this->path('resource/scripts/classes/ChangeProgram.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/payments_report.controller.js'));
  }

  /**
   * List all the pending payments in the system
   */
  public function actionIndex()
  {
    $this->setLayoutVar('pageTitle', 'Payment Report');
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('payments/report'));
    $field = $form->newField();
    $field->setLegend('Search');
    
    $element = $field->newElement('CheckboxList', 'types');
    $element->setLabel('Payment Type');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $paymentTypes = array();
    foreach($this->_em->getRepository('Jazzee\Entity\PaymentType')->findWithPayment() as $type){
        $paymentTypes[$type->getId()] = $type;
    }
    foreach($paymentTypes as $type){
        $element->newItem($type->getId(), $type->getName());
    }
    $element = $field->newElement('DateInput', 'from');
    $element->setLabel('From');
    $element = $field->newElement('DateInput', 'to');
    $element->setLabel('To');
    $element->addValidator(new \Foundation\Form\Validator\DateAfterElement($element, 'from'));
    
    $element = $field->newElement('SelectList', 'program');
    $element->setLabel('Program');
    $element->newItem(null, '');
    $programs = array();
    foreach ($this->_em->getRepository('\Jazzee\Entity\Program')->findBy(array('isExpired' => false), array('name' => 'ASC')) as $program) {
      $programs[$program->getId()] = $program;
    }
    foreach($programs as $program){
        $element->newItem($program->getId(), $program->getName());
    }
    
    $element = $field->newElement('SelectList', 'cycle');
    $element->setLabel('Cycle');
    $element->newItem(null, '');
    $cycles = array();
    foreach ($this->_em->getRepository('\Jazzee\Entity\Cycle')->findAll() as $cycle) {
      $cycles[$cycle->getId()] = $cycle;
    }
    foreach($cycles as $cycle){
        $element->newItem($cycle->getId(), $cycle->getName());
    }
    
    $payments = array();
    $form->newButton('submit', 'Search');
    if(empty($this->post)){
        $from = new \DateTime('midnight yesterday');
        $to = new \DateTime('now');
        $payments = $this->_em->getRepository('\Jazzee\Entity\Payment')
            ->findByStatusArray(\Jazzee\Entity\Payment::SETTLED, array(), $from, $to);
        $this->setVar('resultsDescription', '');
        $description = sprintf('%s payments since midnight yesterday', count($payments));
      $this->setVar('searchResultsDescription', $description);
      $form->getElementByName('types')->setValue(array_keys($paymentTypes));
    } else if ($input = $form->processInput($this->post)) {
      set_time_limit(120);
      $types = array();
      foreach($input->get('types') as $id){
          $types[] = $paymentTypes[$id];
      }
      $program = $input->get('program')?$programs[$input->get('program')]:null;
      $cycle = $input->get('cycle')?$cycles[$input->get('cycle')]:null;
      $from = $input->get('from')?new \DateTime($input->get('from')):null;
      $to = $input->get('to')?new \DateTime($input->get('to')):null;
      $payments = $this->_em->getRepository('\Jazzee\Entity\Payment')->
        findByStatusArray(\Jazzee\Entity\Payment::SETTLED, $types, $from, $to, $program, $cycle);
//      $description = sprintf('%s results from search', count($payments));
      $this->setVar('searchResultsDescription', '');
    } else {
        $this->setVar('searchResultsDescription', '');
        
    }
    foreach($payments as &$payment){
        $paymentType = $paymentTypes[$payment['type']['id']]->getJazzeePaymentType($this);
        $payment['notes'] = $paymentType->getPaymentNotesFromArray($payment);
    }
    $this->setVar('payments', $payments);
    $this->setVar('form', $form);
  }

}