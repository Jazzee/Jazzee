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
        default:
          $this->addMessage('error', 'Invalid service requested');
      }
    } else {
      $this->addMessage('error', 'No service requested');
    }
    $this->setVar('result', $result);
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
    if (in_array($action, array('index')) AND $user) {
      return true;
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}