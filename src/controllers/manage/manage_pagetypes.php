<?php

/**
 * Manage Page types
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManagePagetypesController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Page Types';
  const PATH = 'manage/pagetypes';
  const ACTION_INDEX = 'View Page Types';
  const ACTION_EDIT = 'Edit Page Types';
  const ACTION_NEW = 'New Page Type';
  const REQUIRE_APPLICATION = false;

  /**
   * List all the active ElementTypes and find any new classes on the file system
   */
  public function actionIndex()
  {
    $this->setVar('pageTypes', $this->_em->getRepository('\Jazzee\Entity\PageType')->findAll());
  }

  /**
   * Edit an Payment Type
   * @param integer $pageTypeId
   */
  public function actionEdit($pageTypeId)
  {
    if ($pageType = $this->_em->getRepository('\Jazzee\Entity\PageType')->find($pageTypeId)) {
      $form = new \Foundation\Form();
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("manage/pagetypes/edit/{$pageTypeId}"));
      $field = $form->newField();
      $field->setLegend('Edit ' . $pageType->getName() . ' page type');
      $element = $field->newElement('TextInput', 'name');
      $element->setLabel('Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));
      $element->setValue($pageType->getName());

      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        $pageType->setName($input->get('name'));
        $this->addMessage('success', "Changes Saved");
        $this->_em->persist($pageType);
        $this->redirectPath('manage/pagetypes');
      }
    } else {
      $this->addMessage('error', "Error: PageType #{$pageTypeId} does not exist.");
    }
  }

  /**
   * Create a new pagetype
   */
  public function actionNew()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("manage/pagetypes/new"));
    $field = $form->newField();
    $field->setLegend('New page type');
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Name');
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'class');
    $element->setLabel('Class');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Add Page');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      if (!class_exists($input->get('class'))) {
        $this->addMessage('error', "That is not a valid class name");
      } else {
        $pageType = new \Jazzee\Entity\PageType();
        $pageType->setName($input->get('name'));
        $pageType->setClass($input->get('class'));
        $this->addMessage('success', $input->get('name') . "  added.");
        $this->_em->persist($pageType);
        $this->redirectPath('manage/pagetypes');
      }
    }
  }

}