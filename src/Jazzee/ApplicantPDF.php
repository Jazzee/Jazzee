<?php

namespace Jazzee;

/**
 * Create a PDF from an Applicant object
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantPDF 
{
  /**
   * Configuration Constants
   */

  const USLETTER_PORTRAIT = 1;
  const USLETTER_LANDSCAPE = 2;

  /**
   * The PDF we are working on
   * @var PDFLib
   */
  protected $pdf;

  /**
   * Default PDF fonts
   * @var array
   */
  protected $fonts;

  /**
   * The Height of the page
   * @var float
   */
  protected $pageHeight;

  /**
   * The width of the page
   * @var float
   */
  protected $pageWidth;

  /**
   * The current table
   * @var array
   */
  protected $currentTable;

  /**
   * The current table row
   * @var integer
   */
  protected $tableRow;

  /**
   * Queue of binary PDFs which need to be appended
   * @var array
   */
  protected $appendQueue = array();

  /**
   * The current textflow
   * @var int
   */
  protected $currentText;

  /**
   * Our Y-axis position on the PDF page when we finish fitting a table or textflow
   * @var float
   */
  protected $currentY;

  /**
   * The callign controller
   * @var \Jazzee\Controller
   */
  protected $_controller;

  /**
   * Constructor
   * @param string $key the PDFLib license key we are using
   * @param int $pageType the type and size of the output
   */
  public function __construct($key, $pageType, \Jazzee\Controller $controller)
  {
    $this->pdf = new \PDFlib();
    if ($key) {
      try {
        $this->pdf->set_option("license={$key}");
      } catch (PDFlibException $e) {
        throw new Exception("Unable to validate PDFLib license key, check that your PDFLib version is compatible with your key: " . $e->getMessage());
      }
    }
    //This means we must check return values of load_font() etc.
    $this->pdf->set_option("errorpolicy=exception");
    $this->pdf->set_option("hypertextencoding=unicode");

    $this->fonts = array(
      'h1' => array('face' => 'Helvetica-Bold', 'size' => '16.0', 'leading' => '100%', 'color' => array(207, 102, 0)),
      'h3' => array('face' => 'Helvetica-Bold', 'size' => 12.0, 'leading' => '100%', 'color' => array(119, 153, 187)),
      'h5' => array('face' => 'Helvetica-Bold', 'size' => 10.0, 'leading' => '100%', 'color' => array(119, 153, 187)),
      'p' => array('face' => 'Helvetica', 'size' => 10.0, 'leading' => '100%', 'color' => array(0, 0, 0)),
      'b' => array('face' => 'Helvetica-Bold', 'size' => 10.0, 'leading' => '100%', 'color' => array(0, 0, 0)),
      'th' => array('face' => 'Helvetica-Bold', 'size' => 9.0, 'leading' => '100%', 'rowheight' => '10', 'color' => array(0, 0, 0)),
      'td' => array('face' => 'Helvetica', 'size' => 8.0, 'leading' => '100%', 'rowheight' => '9', 'color' => array(0, 0, 0))
    );
    switch ($pageType) {
      case self::USLETTER_PORTRAIT:
        $this->pageWidth = 612;
        $this->pageHeight = 792;
          break;
      case self::USLETTER_LANDSCAPE:
        $this->pageWidth = 792;
        $this->pageHeight = 612;
          break;
      default:
        throw new Jazzee_Exception('Invalid page type supplied for ApplicantPDF constructor');
    }

    //  open new PDF file in memory
    $this->pdf->begin_document("", "");

    //add the first page
    $this->pdf->begin_page_ext($this->pageWidth, $this->pageHeight, "");
    $this->currentY = $this->pageHeight - 20;
    $this->setFont('p');

    $this->pdf->set_info("Creator", "Jazzee");
    $this->pdf->set_info("Author", "Jazzee Open Application Platform");
    $this->currentText = $this->pdf->create_textflow('', '');

    $this->_controller = $controller;
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
   * PDF a full single applicant
   * @param \Jazzee\Entity\Applicant $applicant
   * @return string the PDF buffer suitable for display
   */
  public function pdf(\Jazzee\Entity\Applicant $applicant)
  {
    $this->pdf->set_info("Title", $this->pdf->convert_to_unicode('utf8', $applicant->getFullName(), '') . ' Application');
    $this->addText($applicant->getFullName() . "\n", 'h1');
    $this->addText('Email Address: ' . $applicant->getEmail() . "\n", 'p');

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
    $this->addText("Admission Status: {$status}\n", 'p');
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

  /**
   * PDF a full single applicant using the display array
   * @param \Jazzee\Entity\Application $application
   * @param array $applicant
   * @return string the PDF buffer suitable for display
   */
  public function pdfFromApplicantArray(\Jazzee\Entity\Application $application, array $applicant)
  {
    $this->pdf->set_info("Title", $this->pdf->convert_to_unicode('utf8', $applicant["fullName"], '') . ' Application');
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


  /**
   * Create a properly formated string for the font options
   * @param string $type
   * @return string
   */
  protected function fontOptions($type)
  {
    return 'fontname=' . $this->fonts[$type]['face'] . ' fontsize=' . $this->fonts[$type]['size'] . ' leading=' . $this->fonts[$type]['leading'] . ' encoding=unicode fillcolor={rgb ' . $this->fonts[$type]['color'][0] / 255 . ' ' . $this->fonts[$type]['color'][1] / 255 . ' ' . $this->fonts[$type]['color'][2] / 255 . '}';
  }

  /**
   * Set the current font
   * @param string $type
   */
  protected function setFont($type)
  {
    $this->pdf->setfont($this->pdf->load_font($this->fonts[$type]['face'], "unicode", ""), $this->fonts[$type]['size']);
    $this->pdf->setcolor('fillstroke', 'rgb', $this->fonts[$type]['color'][0] / 255, $this->fonts[$type]['color'][1] / 255, $this->fonts[$type]['color'][2] / 255, 0);
  }

  /**
   * Add plain text to the current text flow
   * @param string $text
   * @param string $type the font options to use
   */
  public function addText($text, $type)
  {
    $this->pdf->add_textflow($this->currentText, $this->pdf->convert_to_unicode('utf8', html_entity_decode($text, ENT_QUOTES, 'UTF-8'), ''), $this->fontOptions($type));
  }

  /**
   * Write out the current text flow adding pages as necessary
   */
  public function write()
  {
    do {
      $continue = false;
      $return = $this->pdf->fit_textflow($this->currentText, 25, 25, $this->pageWidth - 20, $this->currentY, '');
      if ($return == '_boxfull' || $return == '_nextpage') {
        $this->newPage();
        $continue = true;
      }
    } while ($continue);
    $this->currentY = $this->pdf->info_textflow($this->currentText, 'textendy');
    if ($this->currentY < 25) {
      $this->newPage();
    }

    $this->pdf->delete_textflow($this->currentText);
    $this->currentText = $this->pdf->create_textflow('', '');
  }

  /**
   * Add a PDF to the que
   * @param string blob
   */
  public function addPdf($blob)
  {
    $this->appendQueue[] = $blob;
  }

  /**
   * Append all the extra PDFs
   */
  protected function attachPdfs()
  {
    foreach ($this->appendQueue as $blob) {
      $pdfPath = tempnam($this->_controller->getConfig()->getVarPath() . '/tmp/', '') . '.pdf';
      if($pdftkPath = $this->_controller->getConfig()->getPdftkPath()){
        $tmpFile = tempnam($this->_controller->getConfig()->getVarPath() . '/tmp/', '') . '.pdf';
        file_put_contents($tmpFile, $blob);
        $output = array();
        $status = 0;
        $return = exec("{$pdftkPath} {$tmpFile} output {$pdfPath} flatten", $output, $status);
        if($status != 0){
          throw new \Jazzee\Exception("Problem flattening PDF, return: {$return}, status: {$status}, output was: " . var_export($output, true));
        }
        unlink($tmpFile);
      } else {
        $this->_controller->log("You do not have PDFTK installed or you have not set the pdftkPath configuration variable.  Some PDFs may be generated without all of their data.");
        file_put_contents($pdfPath, $blob);
      }
      $doc = $this->pdf->open_pdi_document($pdfPath, 'shrug=true');
      //loop through each page
      for ($i = 1; $i <= $this->pdf->pcos_get_number($doc, "length:pages"); $i++) {
        $page = $this->pdf->open_pdi_page($doc, $i, '');
        $this->pdf->begin_page_ext($this->pageWidth, $this->pageHeight, "");
        $this->pdf->fit_pdi_page($page, 0, 20, 'adjustpage');
        $this->pdf->close_pdi_page($page);
        $this->pdf->end_page_ext('');
      }
      $this->pdf->close_pdi_document($doc);
      unlink($pdfPath);
    }
  }

  /**
   * Start a new page
   */
  protected function newPage()
  {
    $this->pdf->end_page_ext("");
    $this->pdf->begin_page_ext($this->pageWidth, $this->pageHeight, "");
    $this->setFont('p');
    $this->currentY = $this->pageHeight - 20;
  }

  /**
   * Start a table
   *
   */
  public function startTable()
  {
    $this->currentTable = array();
    $this->tableRow = 0;
  }

  /**
   * Start a row in the current table
   */
  public function startTableRow()
  {
    if (!is_array($this->currentTable)) {
      throw new Exception('You must start a table before you start a row.');
    }
    $this->tableRow++;
    $this->currentTable[$this->tableRow] = array();
  }

  /**
   * Add cell to the table row
   * @param string $string
   */
  public function addTableCell($string)
  {
    $this->currentTable[$this->tableRow][] = $this->pdf->convert_to_unicode('utf8', $string, '');
  }

  /**
   * Write the current table
   */
  public function writeTable()
  {
    $table = 0; //initialize table with 0
    foreach ($this->currentTable as $rowId => $columns) {
      foreach ($columns as $columnId => $text) {
        $fontType = $rowId == 1 ? 'th' : 'td';
        $textFlow = $this->pdf->add_textflow(0, $text, $this->fontOptions($fontType));
        $table = $this->pdf->add_table_cell($table, $columnId + 1, $rowId, '', "rowheight={$this->fonts[$fontType]['rowheight']} fittextflow={verticalalign=top} textflow={$textFlow} margin=1");
      }
    }
    if ($table) {
      do {
        $continue = false;
        //If we are closer that one header row from the bottom of the page then create a new page and then place the table
        if ($this->fonts['th']['rowheight'] > ($this->currentY - 50)) {
          //TOO CLOSE
          $this->newPage();
        }
        $return = $this->pdf->fit_table($table, 25, 25, $this->pageWidth - 20, $this->currentY, 'stroke={{line=other}}');
        if ($return == '_boxfull') {
          $this->newPage();
          $continue = true;
        }
      } while ($continue);
      $height = $this->pdf->info_table($table, 'height');
      $this->currentY = $this->currentY - $height;
      if ($this->currentY < 25) {
        $this->newPage();
      }
      $this->pdf->delete_table($table, '');
      $this->currentTable = array();
      $this->tableRow = false;
    }
  }


}