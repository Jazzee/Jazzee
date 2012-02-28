<?php
namespace Jazzee\Interfaces;
/**
 * AdminDirectory interface
 * Allows differnt user directory systems to be plugged in
 * Find new users to add and get attributes for existing users
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
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
   * @param array $attributes
   * @return array
   */
  function search(array $attributes);
  
}