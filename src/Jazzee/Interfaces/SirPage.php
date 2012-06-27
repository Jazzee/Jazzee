<?php
namespace Jazzee\Interfaces;

/**
 * SIRPage interface
 * Allows us to define interface for pages which can be used to ask post sir questions
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface SirPage
{

  /**
   * Get the element for the SIR page view
   * @return string
   */
  public static function sirPageElement();

  /**
   * Get the element for the SIR page view
   * @return string
   */
  public static function sirApplicantsSingleElement();
}