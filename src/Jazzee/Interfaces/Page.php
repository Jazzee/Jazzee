<?php
namespace Jazzee\Interfaces;

/**
 * Page interface
 * Allows us to impelement a strategy pattern to load page types with thier Entities
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface Page
{
  /**
   * Page status constants
   */

  const INCOMPLETE = 0;
  const COMPLETE = 1;
  const SKIPPED = 2;

  /**
   * Set the controller
   *
   * @param \Jazzee\Controller $controller
   */
  function setController(\Jazzee\Controller $controller);

  /**
   * Set the Applicant
   *
   * @param \Jazzee\Entity\Applicant $applicant
   */
  function setApplicant(\Jazzee\Entity\Applicant $applicant);

  /**
   * Contructor
   *
   * @param \Jazzee\Entity\ApplicationPage $applicationPage
   */
  function __construct(\Jazzee\Entity\ApplicationPage $applicationPage);

  /**
   * Get the element for the apply page view
   * @return string
   */
  public static function applyPageElement();

  /**
   * Get the pagebuilder script page to include for JS
   * @return string
   */
  public static function pageBuilderScriptPath();

  /**
   * Get the current status
   * @return integer self::INCOMPLETE | self::COMPLETE | self:SKIPPED
   */
  function getStatus();

  /**
   * Setup a new page
   *
   * Eg - create a form with fixed ids
   */
  function setupNewPage();

  /**
   * Set a page variable
   * Do this here so we can check that the value is good
   * @param string $name
   * @param string $value
   * @throws \Jazzee\Exception
   */
  public function setVar($name, $value);

  /**
   * Compare a page to another page
   *
   *
   * @return array
   */
  public function compareWith(\Jazzee\Entity\ApplicationPage $applicationPage);
}