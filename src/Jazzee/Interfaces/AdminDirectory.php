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
   * @param \Foundation\Form\Input $input
   * @return array
   */
  function search(\Foundation\Form\Input $input);
  
  /**
   * Form to find a user
   * 
   * @return \Foundation\Form
   */
  function getSearchForm();
  
  /**
   * Search by uniqueName
   * 
   * When we already know the uniquename we dont need a search
   * @param string uniqueName
   * @return array
   */
  function findByUniqueName($name);
}