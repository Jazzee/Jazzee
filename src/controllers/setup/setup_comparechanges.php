<?php

/**
 * Compare applications from different cycles
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupComparechangesController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Compare Changes';
  const PATH = 'setup/comparechanges';
  const ACTION_INDEX = 'View Comparison';

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->addScript($this->path('resource/jsdiff.js'));
    $this->addScript($this->path('resource/scripts/controllers/setup_comparechanges.controller.js'));
  }

  /**
   * Choose a cycle to compare with and view the changes
   */
  public function actionIndex()
  {
    $cycles = array();
    $applications = $this->_em->getRepository('\Jazzee\Entity\Application')->findByProgram($this->_program, false, false, array($this->_application->getId()));
    foreach ($applications as $application) {
      $cycles[] = array(
        'id' => $application->getId(),
        'name' => $application->getCycle()->getName()
      );
    }
    $this->setVar('cycles', $cycles);
  }

  /**
   * Compare two cycles
   * @param integer $secondApplicationid
   */
  public function actionCompare($secondApplicationId)
  {
    $this->layout = 'json';
    $result = array();
    if ($previousApplication = $this->_em->getRepository('\Jazzee\Entity\Application')->findOneBy(array('id' => $secondApplicationId, 'program' => $this->_program->getId()))) {
      $result['thisCycle'] = $this->_application->getCycle()->getName();
      $result['otherCycle'] = $previousApplication->getCycle()->getName();
      $result['differences'] = $this->_application->compareWith($previousApplication);
    } else {
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'You do not have access to that application.');
    }
    $this->setVar('result', $result);
    $this->loadView($this->controllerName . '/result');
  }

  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    //all action authorizations are controlled by the index action
    $action = 'index';
    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}