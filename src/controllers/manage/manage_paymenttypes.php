<?php

/**
 * Manage Payment types
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManagePaymenttypesController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Payment Types';
  const PATH = 'manage/paymenttypes';
  const ACTION_INDEX = 'View Payment Types';
  const ACTION_EDIT = 'Edit Payment Types';
  const ACTION_NEW = 'New Payment Type';
  const ACTION_EXPIRE = 'Expire Payment Type';
  const ACTION_UNEXPIRE = 'Un-Expire Payment Type';
  const REQUIRE_APPLICATION = false;

  /**
   * List all the active PaymentTypes and find any new classes on the file system
   */
  public function actionIndex()
  {
    $this->setVar('paymentTypes', $this->_em->getRepository('\Jazzee\Entity\PaymentType')->findAll());
  }

  /**
   * Edit an PaymentType
   * @param integer $paymentTypeId
   */
  public function actionEdit($paymentTypeId)
  {
    if ($paymentType = $this->_em->getRepository('\Jazzee\Entity\PaymentType')->find($paymentTypeId)) {

      $form = $paymentType->getJazzeePaymentType($this)->getSetupForm();
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("manage/paymenttypes/edit/{$paymentTypeId}"));
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        $paymentType->getJazzeePaymentType($this)->setup($input);
        $this->_em->persist($paymentType);
        foreach ($paymentType->getVariables() as $var) {
          $this->_em->persist($var);
        }
        $this->addMessage('success', "Changes Saved");
        $this->redirectPath('manage/paymenttypes');
      }
    } else {
      $this->addMessage('error', "Error: Paymenttype #{$paymentTypeId} does not exist.");
    }
  }

  /**
   * Expire Type
   * @param integer $paymentTypeId
   */
  public function actionExpire($paymentTypeId)
  {
    if ($paymentType = $this->_em->getRepository('\Jazzee\Entity\PaymentType')->find($paymentTypeId)) {
      $paymentType->expire();
      $this->_em->persist($paymentType);
      $this->addMessage('success', "Payment Type Expired");
      $this->redirectPath('manage/paymenttypes');
    } else {
      $this->addMessage('error', "Error: Paymenttype #{$paymentTypeId} does not exist.");
    }
  }

  /**
   * Un-Expire Type
   * @param integer $paymentTypeId
   */
  public function actionUnExpire($paymentTypeId)
  {
    if ($paymentType = $this->_em->getRepository('\Jazzee\Entity\PaymentType')->find($paymentTypeId)) {
      $paymentType->unExpire();
      $this->_em->persist($paymentType);
      $this->addMessage('success', "Payment Type Un-Expired");
      $this->redirectPath('manage/paymenttypes');
    } else {
      $this->addMessage('error', "Error: Paymenttype #{$paymentTypeId} does not exist.");
    }
  }

  /**
   * Create a new pagetype
   */
  public function actionNew()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("manage/paymenttypes/new"));
    $field = $form->newField();
    $element = $field->newElement('TextInput', 'className');
    $element->setLabel('Class');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Next');
    if (isset($this->post['className'])) {
      $className = $this->post['className'];
      if (!isset($this->post['newtypeform'])) {
        $this->post = array(); //reset $_POST so we don't try and validate the empty form
      }

//class_exists causes doctrine to try and load the class which fails so we look first if doctrine can load it and then
      //if that fails we use class exists with no auto_load so it just looks in existing includes
      if (\Doctrine\Common\ClassLoader::classExists(ltrim($className, '\\')) or class_exists($className, false)) {
        $paymentType = new \Jazzee\Entity\PaymentType();
        $paymentClass = new $className($paymentType, $this);
        $form = $paymentClass->getSetupForm();
        $form->setAction($this->path("manage/paymenttypes/new"));
        $form->newHiddenElement('className', $className);
        $form->newHiddenElement('newtypeform', true);
      } else {
        $form->getElementByName('className')->addMessage('That is not a valid class name.  The class must eithier by loadable by a Doctrine::classLoader in the autoload stack or already be included.');
      }
    }
    $this->setVar('form', $form);

    if ($input = $form->processInput($this->post)) {
      if ($input->get('newtypeform')) {
        if($this->_em->getRepository('Jazzee\Entity\PaymentType')->findBy(array('name'=> $input->get('name')))){
          $form->getElementByName('name')->addMessage('That payment name has already been used.');
          return false;
        }
        $paymentClass->setup($input);
        $this->_em->persist($paymentType);
        foreach ($paymentType->getVariables() as $var) {
          $this->_em->persist($var);
        }
        $this->addMessage('success', $input->get('name') . ' saved.');
        $this->redirectPath('manage/paymenttypes');
      }
    }
  }

}