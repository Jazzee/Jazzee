<?php

/**
 * Provide services for javascript classes
 * such as checkign authorization or getting a good path
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminServicesController extends \Jazzee\AdminController
{

  const REQUIRE_AUTHORIZATION = true;
  const REQUIRE_APPLICATION = false;

  protected function setUp()
  {
    $this->layout = 'json';
    $this->setLayoutVar('status', 'error');
  }

  /**
   * Get some information
   */
  public function actionIndex()
  {
    $result = false;
    if (!empty($this->post['service'])) {
      switch ($this->post['service']) {
        case 'checkIsAllowed':
          $this->setLayoutVar('status', 'success');
          $result = $this->checkIsAllowed($this->post['controller'], $this->post['action']);
            break;
        case 'pathToController':
          $this->setLayoutVar('status', 'success');
          \Foundation\VC\Config::includeController($this->post['controller']);
          if ($class = \Foundation\VC\Config::getControllerClassName($this->post['controller'])) {
            $result = $this->path($class::PATH);
          }
            break;
        case 'currentApplicationId':
          $result = $this->_application->getId();
            break;
        case 'listDisplays':
          $this->setLayoutVar('status', 'success');
          $result = $this->listDisplays();
          break;
        case 'maximumDisplay':
          $this->setLayoutVar('status', 'success');
          $userMaximumDisplay = $this->_user->getMaximumDisplayForApplication($this->_application);
          $result = array(
            'type' => 'maximum',
            'id'  => $userMaximumDisplay->getId(),
            'name' => $userMaximumDisplay->getName(),
            'pageIds' => $userMaximumDisplay->getPageIds(),
            'elementIds' => $userMaximumDisplay->getElementIds(),
            'elements' => $userMaximumDisplay->listElements()
          );
          break;
        case 'currentApplication':
          if($this->_application){
            $this->setLayoutVar('status', 'success');
            $result = $this->_em->getRepository('Jazzee\Entity\Application')->findArray($this->_application->getId());
          } else {
            $this->setLayoutVar('status', 'error');
            $this->addMessage('error', 'You do not have an application in this program and cycle.  Or you have not selected a program or cycle.');
          }
          break;
        default:
          $this->addMessage('error', 'Invalid service requested');
      }
    } else {
      $this->addMessage('error', 'No service requested');
    }
    $this->setVar('result', $result);
  }

  /**
   * Save a users preferences
   */
  public function actionSavePreferences()
  {
    $this->setLayoutVar('status', 'success');
    $applicationId = $this->_application?$this->_application->getId():0;
    $preferences = json_decode($this->post['preferences']);
    $this->_user->setPreferences($applicationId, $preferences);
    $this->_em->persist($this->_user);
    $this->setVar('result', false);
    $this->loadView('admin_services/index');
  }

  /**
   * Save a users preferences
   */
  public function actionGetPreferences()
  {
    $this->setLayoutVar('status', 'success');
    $applicationId = $this->_application?$this->_application->getId():0;
    $this->setVar('result', $this->_user->getPreferences($applicationId));
    $this->loadView('admin_services/index');
  }

  /**
   * Any user can access
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if (in_array($action, array('index', 'savePreferences', 'getPreferences')) AND $user) {
      return true;
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}