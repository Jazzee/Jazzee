<?php

/**
 * Manage Global Schools List
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManageSchoolsController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Global Schools';
  const PATH = 'manage/schools';
  const ACTION_INDEX = 'View Global Schools';
  const ACTION_NEW = 'New School';
  const ACTION_EDIT = 'Edit Schools';
  const ACTION_DELETE = 'Delete School';
  const REQUIRE_APPLICATION = false;

  /**
   * List cycles
   */
  public function actionIndex()
  {
    $schoolCount = $this->_em->getRepository('\Jazzee\Entity\School')->getCount();
    if($schoolCount < 25){
      $this->setVar('schools', $this->_em->getRepository('\Jazzee\Entity\School')->findBy(array(), array('name'=> 'ASC')));
    } else {
      $form = new \Foundation\Form;
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("manage/schools"));
      $field = $form->newField();
      $field->setLegend('Find School');
      $element = $field->newElement('TextInput', 'find');
      $element->setLabel('Find School');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

      $form->newButton('submit', 'Search');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        if($this->_em->getRepository('\Jazzee\Entity\School')->getSearchCount($input->get('find')) == 0){
          $element->addMessage('No schools found.');
        } else if($this->_em->getRepository('\Jazzee\Entity\School')->getSearchCount($input->get('find')) > 25){
          $element->addMessage('Too many results, please add more terms to your search.');
        } else {
          $this->setVar('schools', $this->_em->getRepository('\Jazzee\Entity\School')->search($input->get('find')));
        }
      }
    }
  }

  /**
   * Edit an answer status
   * @param integer $id
   */
  public function actionEdit($id)
  {
    if ($school = $this->_em->getRepository('\Jazzee\Entity\School')->find($id)) {
      $form = new \Foundation\Form;
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("manage/schools/edit/" . $school->getId()));
      $field = $form->newField();
      $field->setLegend('Edit ' . $school->getName());
      $element = $field->newElement('TextInput', 'name');
      $element->setLabel('Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($school->getName());
      
      $element = $field->newElement('TextInput', 'code');
      $element->setLabel('Code');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($school->getCode());
      
      $element = $field->newElement('TextInput', 'city');
      $element->setLabel('City');
      $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 64));
      $element->setValue($school->getCity());
      
      $element = $field->newElement('TextInput', 'state');
      $element->setLabel('State');
      $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 64));
      $element->setValue($school->getState());
      
      $element = $field->newElement('TextInput', 'country');
      $element->setLabel('Country');
      $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 64));
      $element->setValue($school->getCountry());
      
      $element = $field->newElement('TextInput', 'postalCode');
      $element->setLabel('Postal Code');
      $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 10));
      $element->setValue($school->getPostalCode());
      
      $element = $field->newElement('Textarea', 'searchTerms');
      $element->setLabel('Search Terms');
      $element->setValue($school->getSearchTerms());

      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);
      if ($input = $form->processInput($this->post)) {
        $school->setName($input->get('name'));
        $school->setCode($input->get('code'));
        $school->setCity($input->get('city'));
        $school->setState($input->get('state'));
        $school->setCountry($input->get('country'));
        $school->setPostalCode($input->get('postalCode'));
        $school->setSearchTerms($input->get('searchTerms'));
        $this->_em->persist($school);
        $this->addMessage('success', "Changes Saved Successfully");
        $this->redirectPath('manage/schools');
      }
    } else {
      $this->addMessage('error', "Error: School #{$id} does not exist.");
    }
  }

  /**
   * Delete a school
   * @param integer $id
   */
  public function actionDelete($id)
  {
    if ($school = $this->_em->getRepository('\Jazzee\Entity\School')->find($id)) {
      $this->_em->remove($school);
      $this->addMessage('success', "School Removed Successfully");
    } else {
      $this->addMessage('error', "Error: School #{$id} does not exist.");
    }
    $this->redirectPath('manage/schools');
  }

  /**
   * Create a new virtual file
   */
  public function actionNew()
  {
    $form = new \Foundation\Form;
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("manage/schools/new"));
    $field = $form->newField();
    $field->setLegend('New School');
    
    $element = $field->newElement('TextInput', 'name');
    $element->setLabel('Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'code');
    $element->setLabel('Code');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'city');
    $element->setLabel('City');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 64));

    $element = $field->newElement('TextInput', 'state');
    $element->setLabel('State');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 64));

    $element = $field->newElement('TextInput', 'country');
    $element->setLabel('Country');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 64));

    $element = $field->newElement('TextInput', 'postalCode');
    $element->setLabel('Postal Code');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 10));

    $element = $field->newElement('Textarea', 'searchTerms');

    $form->newButton('submit', 'Save');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      $school = new \Jazzee\Entity\School();
      $school->setName($input->get('name'));
      $school->setCode($input->get('code'));
      $school->setCity($input->get('city'));
      $school->setState($input->get('state'));
      $school->setCountry($input->get('country'));
      $school->setPostalCode($input->get('postalCode'));
      $school->setSearchTerms($input->get('searchTerms'));
      $this->_em->persist($school);
      $this->addMessage('success', "New School Saved");
      $this->redirectPath('manage/schools');
    }
  }

}