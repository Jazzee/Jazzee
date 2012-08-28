<?php

/**
 * Manage Cycles
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManageCyclesController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Cycles';
  const PATH = 'manage/cycles';
  const ACTION_INDEX = 'View Cycles';
  const ACTION_EDIT = 'New Cycle';
  const ACTION_NEW = 'Edit Cycle';
  const REQUIRE_APPLICATION = false;

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/scripts/controllers/manage_cycles.controller.js'));
  }

  /**
   * List cycles
   */
  public function actionIndex()
  {
    $this->setVar('cycles', $this->_em->getRepository('\Jazzee\Entity\Cycle')->findAll());
  }

  /**
   * Edit a cycle
   * @param integer $cycleID
   */
  public function actionEdit($cycleID)
  {
    if ($cycle = $this->_em->getRepository('\Jazzee\Entity\Cycle')->find($cycleID)) {
      $form = new \Foundation\Form;
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("manage/cycles/edit/{$cycleID}"));
      $field = $form->newField();
      $field->setLegend('Edit ' . $cycle->getName() . ' cycle');
      $element = $field->newElement('TextInput', 'name');
      $element->setLabel('Cycle Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addFilter(new \Foundation\Form\Filter\UrlSafe($element));
      $element->addFilter(new \Foundation\Form\Filter\Safe($element));
      $element->setValue($cycle->getName());

      $element = $field->newElement('DateInput', 'start');
      $element->setLabel('Start Date');
      $element->addValidator(new \Foundation\Form\Validator\DateBeforeElement($element, 'end'));
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($cycle->getStart()->format('m/d/Y'));

      $element = $field->newElement('DateInput', 'end');
      $element->setLabel('End Date');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($cycle->getEnd()->format('m/d/Y'));

      $element = $field->newElement('CheckboxList', 'requiredPages');
      $element->setLabel('Required Pages');
      $globalPages = array();
      $values = array();
      foreach ($this->_em->getRepository('\Jazzee\Entity\Page')->findBy(array('isGlobal' => true), array('title' => 'ASC')) as $page) {
        $globalPages[$page->getId()] = $page;
        $element->newItem($page->getId(), $page->getTitle());
        if ($cycle->hasRequiredPage($page)) {
          $values[] = $page->getId();
        }
      }
      $element->setValue($values);
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        if ($input->get('name') != $cycle->getName() and count($this->_em->getRepository('\Jazzee\Entity\Cycle')->findBy(array('name' => $input->get('name'))))) {
          $this->addMessage('error', "A cycle with that name already exists");
        } else {
          $cycle->setName($input->get('name'));
          $cycle->clearDates();
          $cycle->setStart($input->get('start'));
          $cycle->setEnd($input->get('end'));
          foreach ($cycle->getRequiredPages() as $page) {
            $cycle->getRequiredPages()->removeElement($page);
          }
          if ($input->get('requiredPages')) {
            foreach ($input->get('requiredPages') as $id) {
              $cycle->addRequiredPage($globalPages[$id]);
            }
          }
          $this->_em->persist($cycle);
          $this->addMessage('success', "Changes Saved Successfully");
          $this->redirectPath('manage/cycles');
        }
      }
    } else {
      $this->addMessage('error', "Error: Cycle #{$cycleID} does not exist.");
    }
  }

  /**
   * Create a new cycle
   */
  public function actionNew()
  {
    $form = new \Foundation\Form;
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("manage/cycles/new"));
    $field = $form->newField();
    $field->setLegend('New cycle');
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Cycle Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\UrlSafe($element));
    $element->addFilter(new \Foundation\Form\Filter\Safe($element));

    $element = $field->newElement('DateInput', 'start');
    $element->setLabel('Start Date');
    $element->addValidator(new \Foundation\Form\Validator\DateBeforeElement($element, 'end'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('DateInput', 'end');
    $element->setLabel('End Date');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save Changes');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      if (count($this->_em->getRepository('\Jazzee\Entity\Cycle')->findBy(array('name' => $input->get('name'))))) {
        $this->addMessage('error', "A cycle with that name already exists");
      } else {
        $cycle = new \Jazzee\Entity\Cycle;
        $cycle->setName($input->get('name'));
        $cycle->setStart($input->get('start'));
        $cycle->setEnd($input->get('end'));
        $this->_em->persist($cycle);
        $this->_em->flush();
        $this->addMessage('success', "New Cycle Saved");
        $this->redirectPath("manage/cycles/edit/{$cycle->getId()}");
      }
    }
  }

}