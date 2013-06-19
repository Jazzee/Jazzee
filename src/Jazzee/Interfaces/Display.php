<?php
namespace Jazzee\Interfaces;

/**
 * Display interface
 * There are many kinds of displays from those created by users to built in ones
 * like FullApplication and Minimal
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
   * Get an ordered list of elements to display
   * 
   * @return array
   */
  function listElements();
  
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
   * Does the display have this element in it
   * @param \Jazzee\Display\Element $displayElement
   * 
   * @return boolean
   */
  function hasDisplayElement(\Jazzee\Display\Element $displayElement);
}