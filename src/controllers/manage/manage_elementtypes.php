<?php

/**
 * Manage Element types
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManageElementtypesController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Element Types';
  const PATH = 'manage/elementtypes';
  const ACTION_INDEX = 'View Element Types';
  const ACTION_EDIT = 'Edit Element Types';
  const ACTION_NEW = 'New Element Type';
  const REQUIRE_APPLICATION = false;

  /**
   * List all the active ElementTypes and find any new classes on the file system
   */
  public function actionIndex()
  {
    $this->setVar('elementTypes', $this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll());
  }

  /**
   * Edit an ElementType
   * @param integer $elementTypeID
   */
  public function actionEdit($elementTypeID)
  {
    if ($elementType = $this->_em->getRepository('\Jazzee\Entity\ElementType')->find($elementTypeID)) {
      $form = new \Foundation\Form();
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("manage/elementtypes/edit/{$elementTypeID}"));
      $field = $form->newField();
      $field->setLegend('Edit ' . $elementType->getName() . ' element type');
      $element = $field->newElement('TextInput', 'name');
      $element->setLabel('Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));
      $element->setValue($elementType->getName());

      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        $elementType->setName($input->get('name'));
        $this->addMessage('success', "Changes Saved");
        $this->_em->persist($elementType);
        $this->redirectPath('manage/elementtypes');
      }
    } else {
      $this->addMessage('error', "Error: ElementType #{$elementTypeID} does not exist.");
    }
  }

  /**
   * Create a new pagetype
   */
  public function actionNew()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("manage/elementtypes/new"));
    $field = $form->newField();
    $field->setLegend('New element type');
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $element = $field->newElement('TextInput', 'class');
    $element->setLabel('Class');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Add Element');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      //class_exists causes doctrine to try and load the class which fails so we look first if doctrine can load it and then
      //if that fails we use class exists with no auto_load so it just looks in existing includes
      if (\Doctrine\Common\ClassLoader::classExists(ltrim($input->get('class'), '\\')) or class_exists($input->get('class'), false)) {
        $elementType = new \Jazzee\Entity\ElementType;
        $elementType->setName($input->get('name'));
        $elementType->setClass($input->get('class'));
        $this->addMessage('success', $input->get('name') . " saved.");
        $this->_em->persist($elementType);
        $this->redirectPath('manage/elementtypes');
      } else {
        $this->addMessage('error', "That is not a valid class name.  The class must eithier by loadable by a Doctrine::classLoader registered in the autoload stack or already be included.");
      }
    }
  }

}