<?php
namespace Jazzee\Interfaces;

/**
 * Display interface
 * There are many kinds of displays from those created by users to built in ones
 *  like Full and ListOnly
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface Display
{
  /**
   * Get the name of the display
   * 
   * @return string
   */
  function getName();
  
  /**
   * Get the unique id of the display
   * 
   * @return string
   */
  function getId();
  
  /**
   * Get an array of page ids that are shown by the display
   * 
   * @return array
   */
  function getPageIds();
  
  /**
   * Get an array of elemnet IDs that are returned by the display
   * 
   * @return array
   */
  function getElementIds();
  
  /**
   * Should a page be displayed
   * 
   * @param \Jazzee\Entity\Page $page
   * 
   * @return boolean
   */
  function displayPage(\Jazzee\Entity\Page $page);
  
  /**
   * Should an Element be displayed
   * @param \Jazzee\Entity\Element $element
   * 
   * @return boolean
   */
  function displayElement(\Jazzee\Entity\Element $element);
  
  /**
   * Is the first name displayed
   * 
   * @return boolean
   */
  function isFirstNameDisplayed();
  
  /**
   * Is the last name displayed
   * 
   * @return boolean
   */
  function isLastNameDisplayed();
  
  /**
   * Is the email address displayed
   * 
   * @return boolean
   */
  function isEmailDisplayed();
  
  /**
   * Is the lastupdate time shown
   * 
   * @return boolean
   */
  function isUpdatedAtDisplayed();
  
  /**
   * Is the createdAt time shown
   * 
   * @return boolean
   */
  function isCreatedAtDisplayed();
  
  /**
   * Is the lastLogin time shown
   * 
   * @return boolean
   */
  function isLastLoginDisplayed();
  
  /**
   * Is the percent complete displayed
   * 
   * @return boolean
   */
  function isPercentCompleteDisplayed();
  
  /**
   * Is the percent complete displayed
   * 
   * @return boolean
   */
  function isHasPaidDisplayed();
  
  /**
   * Is the lock status displayed
   * 
   * @return boolean
   */
  function isIsLockedDisplayed();
}