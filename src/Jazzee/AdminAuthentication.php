<?php
namespace Jazzee;
/**
 * AdminAuthentication interface
 * Allows differnt authentication systems to be plugged in
 * Creates an authenticated \Jazzee\Entity\User object
 */
interface AdminAuthentication 
{
  
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
   * Logout the user with the identity provider
   * 
   */
  function logoutUser();
}