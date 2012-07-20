<?php

/**
 * Remove the application
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupRemoveapplicationController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Remove Application';
  const PATH = 'setup/removeapplication';
  const ACTION_INDEX = 'Remove Application';
  const REQUIRE_APPLICATION = true;

  /**
   * View the current Setup or setup a new app
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/removeapplication"));
    $field = $form->newField();
    $field->setLegend('Remove Application');

    $element = $field->newElement('TextInput', 'confirm');
    $element->setLabel('Type CONFIRM in the box to remove application');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\SpecificString($element, 'CONFIRM'));

    if($this->_application->getApplicants()->count() > 0){
      $applicants = $this->_application->getApplicants();
      $element = $field->newElement('CheckboxList', 'deleteApps');
      $element->setLabel('The following applicants will be deleted as well.  Confirm each one by checking the box.');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->addValidator(new \Foundation\Form\Validator\AllChecked($element));
      foreach($applicants as $applicant){
        $element->newItem($applicant->getId(), $applicant->getFullName());
      }
    }

    $form->newButton('submit', 'Remove Application');
    $this->setVar('form', $form);

    if ($input = $form->processInput($this->post)) {
      $this->_em->remove($this->_application);
      unset($this->_store->AdminControllerGetNavigation);
      $this->addMessage('success', 'Application Removed.');
      $this->redirectPath('setup/application');
    }
    $this->loadView($this->controllerName . '/form');

  }

}