<?php
namespace Jazzee\Interfaces;

/**
 * PDF Element interface
 * Elements which implement this can generate PDF sections
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface PdfElement
{

  /**
   * Get the pdf value of the element
   *
   * @param \Jazzee\Entity\Answer $answer
   * @param \Jazzee\ApplicantPDF $pdf
   * @return mixed
   */
  function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf);

  /**
   * Get the pdf value of the element from an array
   *
   * @param array $answer
   * @param \Jazzee\ApplicantPDF $pdf
   * @return mixed
   */
  function pdfValueFromArray(array $answerData, \Jazzee\ApplicantPDF $pdf);

  /**
   * Get the template pdf values of the element
   * Takes all the answers and returns a single string that sumerizes the data
   *
   * @param array $answers
   * @return string
   */
  function pdfTemplateValue(array $answers);

  /**
   * Get the template pdf values of the element from array data
   * Takes all the answers and returns a single string that sumerizes the data
   *
   * @param array $answers
   * @return string
   */
  function pdfTemplateValueFromArray(array $answers);

}