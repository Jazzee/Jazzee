<?php

/**
 * Manage Programs
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManageProgramsController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Programs';
  const PATH = 'manage/programs';
  const ACTION_INDEX = 'View Programs';
  const ACTION_EDIT = 'Edit Program';
  const ACTION_NEW = 'New Program';
  const ACTION_ACTIVATE = 'Activate Expired Program';
  const ACTION_EXPIRE = 'Expire Program';
  const REQUIRE_APPLICATION = false;

  /**
   * List programs
   */
  public function actionIndex()
  {
    $this->setVar('activePrograms', $this->_em->getRepository('\Jazzee\Entity\Program')->findBy(array('isExpired'=>false), array('name' => 'ASC')));
    $this->setVar('expiredPrograms', $this->_em->getRepository('\Jazzee\Entity\Program')->findBy(array('isExpired'=>true), array('name' => 'ASC')));
  }

  /**
   * Edit a program
   * @param integer $programID
   */
  public function actionEdit($programID)
  {
    if ($program = $this->_em->getRepository('\Jazzee\Entity\Program')->find($programID)) {
      $form = new \Foundation\Form();

      $form->setAction($this->path("manage/programs/edit/{$programID}"));
      $form->setCSRFToken($this->getCSRFToken());
      $field = $form->newField();
      $field->setLegend('Edit ' . $program->getName() . ' program');
      $element = $field->newElement('TextInput', 'name');
      $element->setLabel('Program Name');
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($program->getName());

      $element = $field->newElement('TextInput', 'shortName');
      $element->setLabel('Short Name');
      $element->setInstructions('Forms the URL for accessing this program, must be unique');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addFilter(new \Foundation\Form\Filter\UrlSafe($element));
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));
      $element->setValue($program->getShortName());

      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        $program->setName($input->get('name'));
        $program->setShortName($input->get('shortName'));
        $this->addMessage('success', "Changes Saved");
        $this->_em->persist($program);
        $this->redirectPath('manage/programs');
      }
    } else {
      $this->addMessage('error', "Error: Program #{$programID} does not exist.");
    }
  }

  /**
   * Create a new program
   */
  public function actionNew()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("manage/programs/new"));
    $field = $form->newField();
    $field->setLegend('New Program');
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Program Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $element = $field->newElement('TextInput', 'shortName');
    $element->setLabel('Short Name');
    $element->setInstructions('Forms the URL for accessing this program, must be unique');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\UrlSafe($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $form->newButton('submit', 'Save Changes');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      $program = new \Jazzee\Entity\Program();
      $program->setName($input->get('name'));
      $program->setShortName($input->get('shortName'));
      $this->addMessage('success', "New Program Saved");
      $this->_em->persist($program);
      //if the user isn't in a program then make this one their default
      if (!$this->_program) {
        $this->_user->setDefaultProgram($program);
        $this->_em->persist($this->_user);
      }
      $this->redirectPath('manage/programs');
    }
  }

  /**
   * Activate a program
   * @param integer $programID
   */
  public function actionActivate($programID)
  {
    if ($program = $this->_em->getRepository('\Jazzee\Entity\Program')->find($programID)) {
      $program->unExpire();
      $this->addMessage('success', "{$program->getName()} activated.");
      $this->_em->persist($program);
      $this->redirectPath('manage/programs');

    } else {
      $this->addMessage('error', "Error: Program #{$programID} does not exist.");
    }
  }

  /**
   * Expire a program
   * @param integer $programID
   */
  public function actionExpire($programID)
  {
    if ($program = $this->_em->getRepository('\Jazzee\Entity\Program')->find($programID)) {
      $program->expire();
      $this->addMessage('success', "{$program->getName()} expired.");
      $this->_em->persist($program);
      $this->redirectPath('manage/programs');

    } else {
      $this->addMessage('error', "Error: Program #{$programID} does not exist.");
    }
  }

}