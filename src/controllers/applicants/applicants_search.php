<?php
/**
 * Search the applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsSearchController extends \Jazzee\AdminController {
  const MENU = 'Applicants';
  const TITLE = 'Search';
  const PATH = 'applicants/search';
  
  const ACTION_INDEX = 'Search Applicants';
  
  /**
   * Display a search form and links to views
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setAction($this->path("applicants/search"));
    $field = $form->newField();
    $field->setLegend('Search Applicants');
    $element = $field->newElement('TextInput','firstName');
    $element->setLabel('First Name');
    $element = $field->newElement('TextInput','lastName');
    $element->setLabel('Last Name');
    $element = $field->newElement('TextInput','applicantId');
    $element->setLabel('Applicant ID');
    $form->newButton('submit', 'Search');
    if($input = $form->processInput($this->post)){   
      $applicants = array();
      if($input->get('applicantId')){
        $applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->find($input->get('applicantId'));
        if($applicant)  $applicants[] = $applicant;
      } else {
        $applicants = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName($input->get('firstName') . '%', $input->get('lastName') . '%', $this->_application);        
      }
      $this->setVar('applicants', $applicants);
    }
    $this->setVar('form', $form);
  }
}