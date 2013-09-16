<?php
namespace Jazzee\Interfaces;

/**
 * DisplayElement interface
 * Stadardized access for different types of display elements, applicant, page, element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface DisplayElement
{
  /**
   * Get the name of the element
   * 
   * @return string
   */
  function getName();
  
  /**
   * Get the type of the element
   * 
   * @return string
   */
  function getType();
  
  /**
   * Get the title of the element
   * 
   * @return string
   */
  function getTitle();
  
  /**
   * Get the weight of the element
   * 
   * @return intiger
   */
  function getWeight();
  
  /**
   * Get the pageId of the element
   * 
   * @return intiger
   */
  function getPageId();
  
  /**
   * Check if an element is the same as anotehr element
   * @param \Jazzee\Interfaces\DisplayElement $element
   */
  function sameAs(\Jazzee\Interfaces\DisplayElement $element);
}