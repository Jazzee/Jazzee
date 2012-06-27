<?php

/**
 * Manage Mail
 *
 * Test email communication
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManageMailController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Test Email';
  const PATH = 'manage/mail';
  const ACTION_INDEX = 'View Settings';
  const ACTION_TEST = 'Test Settings';
  const REQUIRE_APPLICATION = false;

  /**
   * List cycles
   */
  public function actionIndex()
  {
    $this->setVar('config', $this->_config);
  }

  /**
   * Test Email Communication
   */
  public function actionTest()
  {
    $form = new \Foundation\Form;
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('manage/mail/test'));
    $field = $form->newField();
    $field->setLegend('Send Test Email');
    $element = $field->newElement('TextInput', 'address');
    $element->setLabel('To');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));

    $form->newButton('submit', 'Send Test');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      $message = $this->newMailMessage();
      $message->AddAddress($input->get('address'));
      $message->Subject = 'Test Email';
      $message->Body = 'This is a test email from the application system.';
      $message->Send();
      $this->addMessage('success', "Test Email Sent");
      $this->redirectPath('manage/mail');
    }
  }

}