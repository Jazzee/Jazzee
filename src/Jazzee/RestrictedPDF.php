<?php

namespace Jazzee;

/**
 * Restricted PDFs are Safe for applicants to view, they don't include any extra information
 * 
 * 
 * @todo Probably should baseclass the PDF process and then have Admin/Applicant PDFs for a clearer seperation
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @author  Lawrence Roberts <Lawrence.Roberts@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class RestrictedPDF extends ApplicantPDF
{

  /**
   * PDF a full single applicant
   * @param \Jazzee\Entity\Applicant $applicant
   * @return string the PDF buffer suitable for display
   */
  public function pdf(\Jazzee\Entity\Applicant $applicant)
  {
    $this->setFont('p');
    $this->pdf->set_info("Title", $this->pdf->convert_to_unicode('utf8', $applicant->getFullName(), '') . ' Application');
    $this->addText($applicant->getFullName() . "\n", 'h1');
    $this->addText('Email Address: ' . $applicant->getEmail() . "\n", 'p');
    $this->write();
    foreach ($applicant->getApplication()->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $page) {
      if ($page->getJazzeePage() instanceof \Jazzee\Interfaces\PdfPage) {
        $page->getJazzeePage()->setApplicant($applicant);
        $page->getJazzeePage()->setController($this->_controller);
        $page->getJazzeePage()->renderPdfSection($this);
      }
    }
    $this->write();
    $this->pdf->end_page_ext("");
    $this->attachPdfs();
    $this->pdf->end_document("");

    return $this->pdf->get_buffer();
  }

}