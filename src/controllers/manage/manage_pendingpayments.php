<?php

/**
 * Manage Pending Payments
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManagePendingPaymentsController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Pending Payments';
  const PATH = 'manage/pendingpayments';
  const ACTION_INDEX = 'View Pending Payments';
  const ACTION_SETTLE = 'Settle Pending Payment';
  const REQUIRE_APPLICATION = false;

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/scripts/classes/Status.class.js'));
    $this->addScript($this->path('resource/scripts/classes/ChangeProgram.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/manage_pendingpayments.controller.js'));
  }

  /**
   * List all the pending payments in the system
   */
  public function actionIndex()
  {
    $pendingPayments = $this->_em->getRepository('\Jazzee\Entity\Payment')->findBy(array('status' => \Jazzee\Entity\Payment::PENDING));
    $this->setVar('pendingPayments', $pendingPayments);
  }

}