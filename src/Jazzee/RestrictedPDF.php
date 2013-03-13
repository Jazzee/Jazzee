<?php

namespace Jazzee;

/**
 * Create a PDF from an Applicant object
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
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
    $fullName = $this->pdf->utf8_to_utf16($applicant->getFullName(), '');
    $this->pdf->set_info("Title", $fullName . ' Application');
    $this->setFont('p');
    $this->addText($fullName . "\n", 'h1');
    $this->addText('Email Address: ' . $this->pdf->utf8_to_utf16($applicant->getEmail(), '') . "\n", 'p');


    if ($applicant->isLocked()) {
      switch ($applicant->getDecision()->status()) {
        case 'finalDeny':
          $status = 'Denied';
            break;
        case 'finalAdmit':
          $status = 'Admited';
            break;
        case 'acceptOffer':
          $status = 'Accepted';
            break;
        case 'declineOffer':
          $status = 'Declined';
            break;
        default: $status = 'No Decision';
      }
    } else {
      $status = 'Not Locked';
    }

    $this->addText("Admission Status: **********\n", 'p');
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
    foreach ($applicant->getAttachments() as $attachment) {
      $this->addPdf($attachment->getAttachment());
    }
    $this->attachPdfs();
    $this->pdf->end_document("");

    return $this->pdf->get_buffer();
  }

}