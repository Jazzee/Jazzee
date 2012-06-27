<?php
namespace Jazzee\Interfaces;

/**
 * AdminController interface
 *
 * Defines the interface for \Jazzee\AdminController primarily so Authentication
 * and Directory can be called from the Console with a stub
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface AdminController
{

  /**
   * Get the entity manager
   *
   * @return \Doctrine\ORM\EntityManager
   */
  function getEntityManager();

  /**
   * Get the current configuration
   *
   * @return \Jazzee\Configuration
   */
  function getConfig();

  /**
   * Get the current session store
   *
   * @return \Foundation\Session\Store
   */
  function getStore();

  /**
   * Check the credentials of a user
   *
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null);

  /**
   * Add a path to the AdminController::controllersPaths
   *
   * @param string $path
   * @throws Jazzee_Exception
   */
  static function addControllerPath($path);
}