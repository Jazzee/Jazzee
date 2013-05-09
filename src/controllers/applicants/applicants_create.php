<?php

/**
 * Create applicants
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsCreateController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'Create';
  const PATH = 'applicants/create';
  const ACTION_INDEX = 'Create applicants';

  /**
   * List all applicants
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/create'));
    $field = $form->newField();
    $field->setLegend('Create Applicant');
    
    $element = $field->newElement('TextInput', 'first');
    $element->setLabel('First Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'middle');
    $element->setLabel('Middle Name');

    $element = $field->newElement('TextInput', 'last');
    $element->setLabel('Last Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'suffix');
    $element->setLabel('Suffix');
    $element->setFormat('Example: Jr., III');

    $element = $field->newElement('TextInput', 'email');
    $element->setLabel('Email Address');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\EmailAddress($element));
    $element->addFilter(new \Foundation\Form\Filter\Lowercase($element));

    $element = $field->newElement('TextInput', 'password');
    $element->setLabel('Password');
    $element->setFormat('If you leave the password blank a random password will be generated.');

    $element = $field->newElement('TextInput', 'externalId');
    $element->setLabel('External ID');

    $form->newButton('submit', 'Create Applicant');
    if ($input = $form->processInput($this->post)) {
      $duplicate = $this->_em->getRepository('Jazzee\Entity\Applicant')->findOneByEmailAndApplication($input->get('email'), $this->_application);
      if ($duplicate) {
        $form->getElementByName('email')->addMessage('An applicant with that email address already exists.');
      } else {
        $applicant = new \Jazzee\Entity\Applicant;
        $applicant->setApplication($this->_application);
        $applicant->setEmail($input->get('email'));
        if($input->get('password')){
          $applicant->setPassword($input->get('password'));
          $plainTextPassword = $input->get('password');
        } else {
          $plainTextPassword = $applicant->generatePassword();
        }
        $applicant->setFirstName($input->get('first'));
        $applicant->setMiddleName($input->get('middle'));
        $applicant->setLastName($input->get('last'));
        $applicant->setSuffix($input->get('suffix'));
        $applicant->setExternalId($input->get('externalId'));
        $this->_em->persist($applicant);
        $this->_em->flush();
        $this->setVar('applicant', $applicant);
        $this->setVar('plainTextPassword', $plainTextPassword);
        $this->addMessage('success', 'Applicant Created Sucessfully');
        $form->applyDefaultValues();
      }
    }
    $this->setVar('form', $form);
  }

}