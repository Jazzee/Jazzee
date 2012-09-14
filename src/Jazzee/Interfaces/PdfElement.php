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
}