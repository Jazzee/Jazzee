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
        $pdf->set_option("license={$this->_licenseKey}");
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
    $pdf->set_info("Title", $pdf->convert_to_unicode('utf8',$elements['applicant']['fullName'], '') . ' Application');
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
        $blockType = $pdf->pcos_get_string($document,"pages[{$pageNum}]/blocks[{$blockNum}]/Subtype");
        if($this->_template->hasBlock($blockName)){
          $string = '';
          $blockData = $this->_template->getBlock($blockName);
          switch($blockData['type']){
            case 'applicant':
              $string = $elements['applicant'][$blockData['element']];
              break;
            case 'page':
              if(array_key_exists($blockData['pageId'], $elements['pages']) AND array_key_exists($blockData['elementId'], $elements['pages'][$blockData['pageId']])){
                $string = $elements['pages'][$blockData['pageId']][$blockData['elementId']];
              }
              break;
          }
          switch($blockType){
            case 'Text':
              $string = $pdf->convert_to_unicode('utf8',$string, '');
              $length = strlen($string);
              $pdf->fill_textblock($page, $blockName, $string, "encoding=unicode textlen={$length}");
              break;
            case 'PDF':
              if($string){
                $name = '/pvf/pdf/' . uniqid() . '.pdf';
                $pvf = $pdf->create_pvf($name, base64_decode($string), 'copy=true');
                $doc = $pdf->open_pdi_document($name, '');
                $contents = $pdf->open_pdi_page($doc, 1, '');
                $pdf->fill_pdfblock($page, $blockName, $contents, '');
                $pdf->close_pdi_document($doc);
                $pdf->delete_pvf($pvf);
              }
              break;
          }
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
    $elements = array('applicant' => array(), 'pages'=>array());
    $elements['applicant']['firstName'] = $applicant['firstName'];
    $elements['applicant']['lastName'] = $applicant['lastName'];
    $elements['applicant']['middleName'] = $applicant['middleName'];
    $elements['applicant']['fullName'] = $applicant['fullName'];
    $elements['applicant']['suffix'] = $applicant['suffix'];
    $elements['applicant']['email'] = $applicant['email'];
    $elements['applicant']['id'] = $applicant['id'];
    $elements['applicant']['externalid'] = $applicant['externalId'];
    if ($applicant['isLocked']) {
      switch ($applicant['decision']['status']) {
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
    $elements['pages'] = $applicant['pages'];

    return $this->generatePDF($elements);
  }
}