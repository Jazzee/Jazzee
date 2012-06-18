<?php
namespace Jazzee\Interfaces;
/**
 * PDF Page interface
 * Pages which implement this can generate PDF sections
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
interface PdfPage 
{
  /**
   * Build the pdf section for this page type
   * 
   * @param \Jazzee\ApplicantPDF
   */
  function renderPdfSection(\Jazzee\ApplicantPDF $pdf);
}