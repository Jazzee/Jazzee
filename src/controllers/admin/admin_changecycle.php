<?php

/**
 * Change the a users current cycle
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminChangecycleController extends \Jazzee\AdminController
{

  const MENU = 'My Account';
  const TITLE = 'Change Cycle';
  const PATH = 'changecycle';
  const REQUIRE_AUTHORIZATION = true;
  const REQUIRE_APPLICATION = false;
  const ACTION_INDEX = 'Change Cycle';

  /**
   * Display index
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setAction($this->path('changecycle'));
    $form->setCSRFToken($this->getCSRFToken());
    $field = $form->newField();
    $field->setLegend('Select Cycle');
    $element = $field->newElement('SelectList', 'cycle');
    $element->setLabel('Cycle');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $cycles = $this->_em->getRepository('\Jazzee\Entity\Cycle')->findAll();
    foreach ($cycles as $cycle) {
      $element->newItem($cycle->getId(), $cycle->getName());
    }
    if ($this->_cycle) {
      $element->setValue($this->_cycle->getId());
    }
    //only ask if the user already has a default cycle
    if ($this->_user->getDefaultCycle()) {
      $element = $field->newElement('RadioList', 'default');
      $element->setLabel('Set as your default');
      $element->newItem(0, 'No');
      $element->newItem(1, 'Yes');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    }
    $form->newButton('submit', 'Change Cycle');

    if ($input = $form->processInput($this->post)) {
      $this->_cycle = $this->_em->getRepository('\Jazzee\Entity\Cycle')->find($input->get('cycle'));

      //if they wish it, or if the user has no default cycle
      if (!$this->_user->getDefaultCycle() OR $input->get('default')) {
        $this->_user->setDefaultCycle($this->_cycle);
        $this->_em->persist($this->_user);
        $this->addMessage('success', 'Default cycle changed to ' . $this->_cycle->getName());
      }
      unset($this->_store->AdminControllerGetNavigation);
      $this->addMessage('success', 'Cycle changed to ' . $this->_cycle->getName());
      $this->redirectPath('welcome');
    }

    $this->setVar('form', $form);
  }

}