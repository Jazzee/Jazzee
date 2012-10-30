<?php
namespace Jazzee\Interfaces;

/**
 * Review Page interface
 * Pages which implement this can be viewed on the admin review screen
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface ReviewPage extends DataPage
{

  /**
   * Get the element for the applicants single view
   * @return string
   */
  public static function applicantsSingleElement();
}