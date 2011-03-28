<?php
/**
 * Search the applicants
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsSearchController extends ApplicantsController {
  const MENU = 'Applicants';
  const TITLE = 'Search';
  const PATH = 'applicants/search';
  
  /**
   * Add the required JS
   */
  public function setUp(){
    parent::setUp();
    $this->addScript('common/scripts/controllers/applicants_search.controller.js');
  }
  /**
   * Display a search form and links to views
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("applicants/search");
    $field = $form->newField(array('legend'=>'Search Applicants'));
    $element = $field->newElement('TextInput','firstName');
    $element->label = 'First Name';
    $element = $field->newElement('TextInput','lastName');
    $element->label = 'Last Name';
    $element = $field->newElement('TextInput','applicantID');
    $element->label = 'Applicant ID';
    $form->newButton('submit', 'Search');
    if($input = $form->processInput($this->post)){   
      $applicants = array();
      if($input->applicantID){
        $applicant = $this->application->getApplicantByID($input->applicantID);
        if($applicant)  $applicants[] = $applicant;
      } else {
        $applicants = $this->application->findApplicantsByName($input->lastName, $input->firstName);        
      }
      $this->setVar('applicants', $applicants);
    }
    $this->setVar('form', $form);
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Search Applicants';
    $auth->addAction('index', new ActionAuth('Search Applicants'));
    return $auth;
  }
}