<?php

namespace Jazzee;

/**
 * Create a PDF from an Applicant object usign a pre defined template
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class TemplatePDF
{

  /**
   * The License Key
   * @var string
   */
  protected $_licenseKey;

  /**
   * The Template
   * @var \Jazzee\Entity\PDFTemplate
   */
  protected $_template;

  /**
   * The callign controller
   * @var \Jazzee\Controller
   */
  protected $_controller;

  /**
   * Constructor
   * @param string $licenseKey the PDFLib license key we are using
   * @param int $pageType the type and size of the output
   */
  public function __construct($licenseKey, \Jazzee\Entity\PDFTemplate $template, \Jazzee\Controller $controller)
  {
    $this->_licenseKey = $licenseKey;
    $this->_controller = $controller;
    $this->_template = $template;
  }

  /**
   * Do we have the capability to created pdfs
   *
   * @return boolean
   */
  public static function isAvailable()
  {
    if (class_exists('\PDFlib')) {
      return true;
    }

    return false;
  }
  
  /**
   * Get a configured instance of PDFLib
   * @return \PDFlib
   * @throws Exception
   */
  protected function getPdf(){
    $pdf = new \PDFlib();
    if ($this->_licenseKey) {
      try {
        $pdf->set_parameter("license", $this->_licenseKey);
      } catch (PDFlibException $e) {
        throw new Exception("Unable to validate PDFLib license key, check that your PDFLib version is compatible with your key: " . $e->getMessage());
      }
    }
    //This means we must check return values of load_font() etc.
    $pdf->set_option("errorpolicy=exception");

    //  open new PDF file in memory
    $pdf->begin_document("", "");

    $pdf->set_info("Creator", "Jazzee");
    $pdf->set_info("Author", "Jazzee Open Application Platform");
    
    return $pdf;
  }
  
  protected function generatePDF(array $elements){
    $pdf = $this->getPdf();
    $pdf->set_info("Title", $pdf->convert_to_unicode('auto',$elements['applicant']['fullName'], '') . ' Application');
    $document = $pdf->open_pdi_document($this->_template->getTmpFilePath(), '');
    $pagecount = $pdf->pcos_get_number($document, "length:pages");
    for($pageNum = 0; $pageNum < $pagecount; $pageNum++){
      $page = $pdf->open_pdi_page($document, $pageNum+1, "");
      $width = $pdf->pcos_get_number($document, "pages[{$pageNum}]/width");
      $height = $pdf->pcos_get_number($document, "pages[{$pageNum}]/height");
      $pdf->begin_page_ext($width, $height, "");
      $pdf->fit_pdi_page($page, 0, 0, "");
      $blockcount = $pdf->pcos_get_number($document,"length:pages[{$pageNum}]/blocks");
      for($blockNum = 0; $blockNum < $blockcount; $blockNum++){
        $blockName = $pdf->pcos_get_string($document,"pages[{$pageNum}]/blocks[{$blockNum}]/Name");
        if($this->_template->hasBlock($blockName)){
          $blockData = $this->_template->getBlock($blockName);
          switch($blockData['type']){
            case 'applicant':
              $string = $pdf->convert_to_unicode('auto',$elements['applicant'][$blockData['element']], '');
              break;
            case 'page':
              $string = $pdf->convert_to_unicode('auto',$elements['pages'][$blockData['pageId']][$blockData['elementId']], '');
              break;
          }
          $length = strlen($string);
          $pdf->fill_textblock($page, $blockName, $string, "encoding=unicode textlen={$length}");
        }
      }
      $pdf->end_page_ext("");
      $pdf->close_pdi_page($page);
    }
    $pdf->end_document("");
    $pdf->close_pdi_document($document);
    
    return $pdf->get_buffer();
  }

  /**
   * PDF a full single applicant
   * @param \Jazzee\Entity\Applicant $applicant
   * @return string the PDF buffer suitable for display
   */
  public function pdf(\Jazzee\Entity\Applicant $applicant)
  {
    $elements = array('applicant' => array(), 'pages'=>array());
    $elements['applicant']['firstName'] = $applicant->getFirstName();
    $elements['applicant']['lastName'] = $applicant->getLastName();
    $elements['applicant']['middleName'] = $applicant->getMiddleName();
    $elements['applicant']['fullName'] = $applicant->getFullName();
    $elements['applicant']['suffix'] = $applicant->getSuffix();
    $elements['applicant']['email'] = $applicant->getEmail();
    $elements['applicant']['id'] = $applicant->getId();
    $elements['applicant']['externalid'] = $applicant->getExternalId();
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
    $elements['applicant']['status'] = $status;
    foreach($applicant->getApplication()->getApplicationPages() as $applicationPage){
      if($applicationPage->getJazzeePage() instanceof \Jazzee\Interfaces\PdfPage){
        $applicationPage->getJazzeePage()->setApplicant($applicant);
        $applicationPage->getJazzeePage()->setController($this->_controller);
        $elements['pages'][$applicationPage->getPage()->getId()] = $applicationPage->getJazzeePage()->getPdfTemplateValues();
      }
    }

    return $this->generatePDF($elements);
  }

  /**
   * PDF a full single applicant using the display array
   * @param \Jazzee\Entity\Application $application
   * @param array $applicant
   * @return string the PDF buffer suitable for display
   */
  public function pdfFromApplicantArray(\Jazzee\Entity\Application $application, array $applicant)
  {
    $this->pdf->set_info("Title", $this->pdf->utf8_to_utf16($applicant["fullName"], '') . ' Application');
    $this->addText($applicant["fullName"] . "\n", 'h1');
    $this->addText('Email Address: ' . $applicant["email"] . "\n", 'p');

    if ($applicant["isLocked"]) {
      switch ($applicant["decision"]["status"]) {
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
    $this->addText("Admission Status: {$status}\n", 'p');
    $this->write();
    
    $pages = array();
    foreach($applicant['pages'] as $pageArray){
      $pages[$pageArray['id']] = $pageArray['answers'];
    }
    foreach ($application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $applicationPath) {
      if ($applicationPath->getJazzeePage() instanceof \Jazzee\Interfaces\PdfPage) {
        $applicationPath->getJazzeePage()->setController($this->_controller);
        $pageData = array_key_exists($applicationPath->getPage()->getId(), $pages)?$pages[$applicationPath->getPage()->getId()]:array();
        $applicationPath->getJazzeePage()->renderPdfSectionFromArray($pageData, $this);
      }
    }
    $this->write();
    $this->pdf->end_page_ext("");

    foreach ( $applicant["attachments"] as $attachment) {
      $blob = \Jazzee\Globals::getFileStore()->getFileContents($attachment["attachmentHash"]);
      $this->addPdf($blob);
      $blob = null;
    }

    $this->attachPdfs();
    $this->pdf->end_document("");

    return $this->pdf->get_buffer();
  }


}