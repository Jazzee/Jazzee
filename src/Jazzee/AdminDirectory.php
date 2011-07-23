<?php
namespace Jazzee;
/**
 * AdminDirectory interface
 * Allows differnt user directory systems to be plugged in
 * Find new users to add and get attributes for existing users
 */
interface AdminDirectory 
{
  
  /**
   * Search for a user
   * 
   * @param array $attributes
   * @return array
   */
  function search(array $attributes);
  
}