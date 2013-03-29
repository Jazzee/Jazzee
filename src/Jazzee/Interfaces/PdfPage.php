<?php
namespace Jazzee\Interfaces;

/**
 * PDF Page interface
 * Pages which implement this can generate PDF sections
 * 
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
interface PdfPage extends DataPage
{

  /**
   * Build the pdf section for this page type
   *
   * @param \Jazzee\ApplicantPDF
   */
  function renderPdfSection(\Jazzee\ApplicantPDF $pdf);

  /**
   * Render the pdf section of this page from an applicant display array
   *
   * @param array $pageData
   * @param \Jazzee\ApplicantPDF $pdf
   * @return mixed
   */
  function renderPdfSectionFromArray(array $pageData, \Jazzee\ApplicantPDF $pdf);
}