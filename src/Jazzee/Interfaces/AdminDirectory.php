<?php
namespace Jazzee\Interfaces;

/**
 * AdminDirectory interface
 * Allows differnt user directory systems to be plugged in
 * Find new users to add and get attributes for existing users
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface AdminDirectory
{

  /**
   * Constructor
   * Pass the controller so we can access configuration and entity manager
   * @param \Jazzee\Interfaces\AdminController
   */
  public function __construct(\Jazzee\Interfaces\AdminController $controller);

  /**
   * Search for a user
   *
   * @param string $firstName
   * @param string $lastName
   * @return array
   */
  function search($firstName, $lastName);

  /**
   * Search by uniqueName
   *
   * When we already know the uniquename we dont need a search
   * @param string uniqueName
   * @return array
   */
  function findByUniqueName($name);
}