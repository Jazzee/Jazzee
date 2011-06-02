<?php
namespace Jazzee;
/**
 * ApplyAnswer interface 
 * 
 * Most page types provide thier own answer types that must implement this interface
 */
interface Answer 
{

  /**
   * Get the ID of the answer 
   * @return integer
   */
  function getID();
  
  /**
   * Update the values in an answer
   * @param FormInput $input
   */
  function update(\Foundation\Form\Input $input);
  
  /**
   * The tools for apply_page view
   * @param string $basePath
   * @return array of links 
   */
  function applyTools();
  
  /**
   * Tools for applicant_view view
   * @return array of links
   */
  function applicantsTools();
  
  /**
   * The Status text for apply_page view
   * @return array of statuses
   */
  function applyStatus();
  
  /**
   * The Status text for applicants_view single
   * @return array of statuses
   */
  function applicantsStatus();
}