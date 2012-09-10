<?php

/**
 * Search the applicants
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsSearchController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'Search';
  const PATH = 'applicants/search';
  const ACTION_INDEX = 'Basic Search';
  const ACTION_ADVANCED = 'Advanced Search';

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/scripts/classes/ChangeProgram.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/applicants_search.controller.js'));
  }

  /**
   * Display a search form and links to views
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/search'));
    $field = $form->newField();
    $field->setLegend('Search Applicants');
    $element = $field->newElement('TextInput', 'firstName');
    $element->setLabel('First Name');
    $element = $field->newElement('TextInput', 'lastName');
    $element->setLabel('Last Name');
    $element = $field->newElement('TextInput', 'applicantId');
    $element->setLabel('Applicant ID');

    $element = $field->newElement('RadioList', 'limitSearch');
    $element->setLabel('Limit Search to this application?');
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');
    $element->setDefaultValue(0);

    $form->newButton('submit', 'Search');
    if ($input = $form->processInput($this->post)) {
      $applicants = array();
      if ($input->get('applicantId')) {
        $applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->find($input->get('applicantId'));
        if ($applicant and $applicant->getApplication()->getCycle() == $this->_cycle) {
          $applicants[] = $applicant;
        }
      } else {
        if ($input->get('limitSearch')) {
          $applicants = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName($input->get('firstName') . '%', $input->get('lastName') . '%', $this->_application);
        } else {
          $all = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName($input->get('firstName') . '%', $input->get('lastName') . '%');
          $searchablePrograms = array();

          foreach ($this->_em->getRepository('\Jazzee\Entity\Program')->findAll() as $program) {
            if ($this->_user->isAllowed($this->controllerName, 'index', $program)) {
              $searchablePrograms[] = $program->getId();
            }
          }
          foreach ($all as $applicant) {
            if (in_array($applicant->getApplication()->getProgram()->getId(), $searchablePrograms) AND $applicant->getApplication()->getCycle() == $this->_cycle) {
              $applicants[] = $applicant;
            }
          }
        }
      }
      $this->setVar('applicants', $applicants);
    }
    $this->setVar('form', $form);
  }

  /**
   * Advanced Search Form
   */
  public function actionAdvanced()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/search/advanced'));
    $field = $form->newField();
    $field->setLegend('Advanced Search');
    $element = $field->newElement('Textarea', 'query');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\Json($element));
    $element->setLabel('Query');

    $form->newButton('submit', 'Search');
    if ($input = $form->processInput($this->post)) {
      $obj = json_decode($input->get('query'));
      $applicants = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByQuery($obj, $this, $this->_application);
      $this->setVar('applicants', $applicants);
    }
    $this->setVar('form', $form);

    $this->loadView('applicants_search/index');
  }

}