<?php
/**
 * ApplyAnswer interface 
 * Most page types provide thier own answer types that must implement this interface
 */
interface ApplyAnswer {
  /**
   * Get the ID of the answer 
   * @return integer
   */
  function getID();
  
  /**
   * Update the values in an answer 
   * @param FormInput $input
   */
  function update(FormInput $input);
  
  /**
   * Get a list of the elements
   * @return array
   */
  function getElements();
  
  /**
   * Get the display value for an element by ID
   * @param mixed $id
   * @return string
   */
  function getDisplayValueForElement($elementID);
  
  /**
   * Get the form value for an element by ID
   * @param mixed $id
   * @return string
   */
  function getFormValueForElement($elementID);
  
  /**
   * The tools for apply_page view
   * @param string $basePath
   * @return array of links 
   */
  function applyTools($basePath);
  
  /**
   * Tools for applicant_view view
   * @return array of links
   */
  function applicantTools();
  
  /**
   * The Status text for apply_page view
   * @return array of statuses
   */
  function applyStatus();
  
    /**
   * The Status text for applicants_view single
   * @return array of statuses
   */
  function applicantStatus();
  
  /**
   * Get the last update time
   * @return integer
   */
  function getUpdatedAt();
}