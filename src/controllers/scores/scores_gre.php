<?php

/**
 * Search GRE Scores
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ScoresGreController extends \Jazzee\AdminController
{

  const MENU = 'Scores';
  const TITLE = 'Search GRE';
  const PATH = 'scores/gre';
  const ACTION_INDEX = 'Search';
  const REQUIRE_APPLICATION = false;

  /**
   * Search GRE Scores
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('scores/gre'));
    $field = $form->newField();
    $field->setLegend('Search GRE Scores');

    $element = $field->newElement('TextInput', 'firstName');
    $element->setLabel('First Name');
    $element = $field->newElement('TextInput', 'lastName');
    $element->setLabel('Last Name');

    $form->newButton('submit', 'Search');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      $results = $this->_em->getRepository('\Jazzee\Entity\GREScore')->findByName($input->get('firstName') . '%', $input->get('lastName') . '%');
      $this->setVar('results', $results);
    }
  }

}