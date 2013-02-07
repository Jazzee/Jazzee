<?php

/**
 * Applicant Grid
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsGridController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'Grid View';
  const PATH = 'applicants/grid';
  const ACTION_INDEX = 'All Applicants';

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->layout = 'json';
    $this->addCss('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css');
    $this->addCss('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables_themeroller.css');
    $this->addScript('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js');
    
    $this->addScript($this->path('resource/scripts/classes/Display.class.js'));
    $this->addScript($this->path('resource/scripts/classes/Application.class.js'));
    $this->addScript($this->path('resource/scripts/classes/ApplicantData.class.js'));
    $this->addScript($this->path('resource/scripts/classes/DisplayChooser.class.js'));
    $this->addScript($this->path('resource/scripts/classes/Grid.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/applicants_grid.controller.js'));
    $this->addCss($this->path('resource/styles/grid.css'));
  }

  /**
   * List all applicants
   */
  public function actionIndex()
  {
    $this->layout = 'wide';
  }
  
  /**
   * Get applicant JSON
   */
  public function actionListApplicants(){
    $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findIdsByApplication($this->_application, true);
    $this->setVar('result', $applicants);
    $this->loadView('applicants_single/result');
  }
  
  /**
   * Get applicant JSON
   */
  public function actionGetApplicants(){
    $results = array();
    $display = $this->getDisplay($this->post['display']);
    
    foreach ($this->post['applicantIds'] as $id) {
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->findArray($id, $display);
      $arr = $this->_application->formatApplicantDisplayArray($applicant, $display);
      $arr['link'] = $this->path("applicants/single/{$arr['id']}");
      $results[] = $arr;
    }
    $this->setVar('result', array('applicants' => $results));
    $this->loadView('applicants_single/result');
  }

  /**
   * Controll actions with the index action
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if (in_array($action, array('getApplicants', 'listApplicants', 'describeDisplay'))) {
      $action = 'index';
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}