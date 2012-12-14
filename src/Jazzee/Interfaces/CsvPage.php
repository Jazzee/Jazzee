<?php
namespace Jazzee\Interfaces;

/**
 * CSV Page interface
 * Pages which implement this can generate CSV for their answers
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface CsvPage extends DataPage
{

  /**
   * Get the CSV Headers
   *
   * Some elements like branching need special attention for the header row
   * of a csv file
   * @return array
   */
  function getCsvHeaders();

  /**
   * Get the CSV Answers
   *
   * CSV Answers have to be linear so we use a counter when asking for the answers
   * and return only the answer for that counter.  If there is no answer return a
   * blank array with the right number of blank elements
   * @param array $pageArr
   * @param int $position
   * @return array
   */
  function getCsvAnswer(array $pageArr, $position);
}