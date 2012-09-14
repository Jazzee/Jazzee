<?php
namespace Jazzee\Interfaces;

/**
 * LORPage interface
 * Allows us to define interface for pages which can be used to compelted
 * letters of recommendation
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface LorPage
{

  /**
   * Create a new LOR answer from user input
   * @param \Foundation\Form\Input $input
   * @param \Jazzee\Entity\Answer $answer
   * @return bool
   */
  function newLorAnswer(\Foundation\Form\Input $input, \Jazzee\Entity\Answer $answer);

  /**
   * Update a LOR Answer
   * @param \Foundation\Form\Input $input
   * @param \Jazzee\Entity\Answer $answer
   * @return bool
   */
  function updateLorAnswer(\Foundation\Form\Input $input, \Jazzee\Entity\Answer $answer);

  /**
   * Delete LOR answer
   * @param \Jazzee\Entity\Answer $answer
   * @return bool
   */
  function deleteLorAnswer(\Jazzee\Entity\Answer $answer);

  /**
   * Fill Lor form from answer
   * @param \Jazzee\Entity\Answer $answer
   */
  function fillLorForm(\Jazzee\Entity\Answer $answer);

  /**
   * Build the pdf section
   *
   * @param \Jazzee\ApplicantPDF
   * @param \Jazzee\Entity\Page $page
   * @param \Jazzee\Entity\Answer $answer
   */
  function renderLorPdfAnswer(\Jazzee\ApplicantPDF $pdf, \Jazzee\Entity\Page $page, \Jazzee\Entity\Answer $answer);

  /**
   * Get the element for the LOR page view
   * @return string
   */
  public static function lorPageElement();

  /**
   * Get the element for the LOR page view
   * @return string
   */
  public static function lorReviewElement();

  /**
   * Get the element for the LOR page view
   * @return string
   */
  public static function lorApplicantsSingleElement();
}