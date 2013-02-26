<?php

/**
 * Manage Answer verification status options
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManageAnswerstatusController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Answer Status';
  const PATH = 'manage/answerstatus';
  const ACTION_INDEX = 'View Status Options';
  const ACTION_NEW = 'New Status';
  const ACTION_EDIT = 'Replace Status';
  const ACTION_DELETE = 'Delete Status';
  const REQUIRE_APPLICATION = false;

  /**
   * List cycles
   */
  public function actionIndex()
  {
    $this->setVar('statuses', $this->_em->getRepository('\Jazzee\Entity\AnswerStatusType')->findAll());
  }

  /**
   * Edit an answer status
   * @param integer $id
   */
  public function actionEdit($id)
  {
    if ($status = $this->_em->getRepository('\Jazzee\Entity\AnswerStatusType')->find($id)) {
      $form = new \Foundation\Form;
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("manage/answerstatus/edit/" . $status->getId()));
      $field = $form->newField();
      $field->setLegend('Edit ' . $status->getName());
      $element = $field->newElement('TextInput', 'name');
      $element->setLabel('Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));
      $element->setValue($status->getName());

      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        $status->setName($input->get('name'));
        $this->_em->persist($status);
        $this->addMessage('success', "Changes Saved Successfully");
        $this->redirectPath('manage/answerstatus');
      }
    } else {
      $this->addMessage('error', "Error: Answer Status #{$id} does not exist.");
    }
  }

  /**
   * Delete a file
   * @param integer $id
   */
  public function actionDelete($id)
  {
    if ($status = $this->_em->getRepository('\Jazzee\Entity\AnswerStatusType')->find($id)) {
      $this->_em->remove($status);
      $this->addMessage('success', "Answer Status Removed Successfully");
    } else {
      $this->addMessage('error', "Error: Answer Status #{$id} does not exist.");
    }
    $this->redirectPath('manage/answerstatus');
  }

  /**
   * Create a new virtual file
   */
  public function actionNew()
  {
    $form = new \Foundation\Form;

    $form->setAction($this->path("manage/answerstatus/new"));
    $form->setCSRFToken($this->getCSRFToken());
    $field = $form->newField();
    $field->setLegend('New Answer Status');
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $form->newButton('submit', 'Save');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      $status = new \Jazzee\Entity\AnswerStatusType();
      $status->setName($input->get('name'));
      $this->_em->persist($status);
      $this->addMessage('success', "New Answer Status Saved");
      $this->redirectPath('manage/answerstatus');
    }
  }

}