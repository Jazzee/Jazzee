<?php
namespace Jazzee\Interfaces;

/**
 * AdminAuthentication interface
 * Allows differnt authentication systems to be plugged in
 * Creates an authenticated \Jazzee\Entity\User object
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface AdminAuthentication
{

  /**
   * Constructor
   * Pass the controller so we can access configuration and entity manager
   * @param \Jazzee\Interfaces\AdminController
   */
  public function __construct(\Jazzee\Interfaces\AdminController $controller);

  /**
   * Successfull authentication does not always give us a valid user
   * Check to see if the user is valid
   *
   * @return boolean
   */
  function isValidUser();

  /**
   * Get the authenticated user
   *
   * @return \Jazzee\Entity\User
   */
  function getUser();

  /**
   * Login the user with the identity provider
   *
   */
  function loginUser();

  /**
   * Logout the user with the identity provider
   *
   */
  function logoutUser();
}