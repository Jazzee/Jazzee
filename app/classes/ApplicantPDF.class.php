<?php
/**
 * Create a PDF from an Applicant object
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 */
class ApplicantPDF {
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
   * Constructor
   * @param int $pageType the type and size of the output
   * @param string $key the PDFLib license key we are using
   */
  public function __construct($pageType = self::USLETTER_PORTRAIT, $key = ''){
    $this->pdf = new PDFlib();
    
    if($key){
      try{
        $this->pdf->set_parameter("license", $key);
      } catch (PDFlibException $e){
        trigger_error("Unable to validate PDFLib license key, check that your PDFLib version is compatible with your key: " . $e->getMessage());
      }
    } 
    //This means we must check return values of load_font() etc.
    $this->pdf->set_parameter("errorpolicy", "exception");
    $this->pdf->set_parameter("hypertextencoding", "winansi");
    
    $this->fonts = array(
      'h1' => array('face' => 'Helvetica-Bold', 'size' => '16.0', 'leading' => '100%', 'color' => array(207,102,0)),
      'h3' => array('face' => 'Helvetica-Bold', 'size' => 12.0, 'leading' => '100%','color' => array(119,153,187)),
      'p' => array('face' => 'Helvetica', 'size' => 10.0, 'leading' => '100%','color' => array(0,0,0)),
      'b' => array('face' => 'Helvetica-Bold', 'size' => 10.0, 'leading' => '100%','color' => array(0,0,0)),
      'th' => array('face' => 'Helvetica-Bold', 'size' => 9.0, 'leading' => '100%', 'rowheight' => '10', 'color' => array(0,0,0)),
      'td' => array('face' => 'Helvetica', 'size' => 8.0, 'leading' => '100%', 'rowheight' => '9' ,'color' => array(0,0,0))
    );
    switch($pageType){
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
    //default the current y with a 20 unit margin
    $this->currentY = $this->pageHeight-20;
    
    $this->currentTable = array(
      'table' => 0,
      'currentrow' => 1,
      'currentcolumn' => 1
    );
    
    //  open new PDF file in memory
    $this->pdf->begin_document("", "");
    $this->pdf->set_info("Creator", "Jazzee");
    $this->pdf->set_info("Author", "Jazzee Application Management System");
    $this->currentText  = $this->pdf->create_textflow('', '');
  }
  
  /**
   * PDF a full single applicant
   * @param Application $applicant
   * @return string the PDF buffer suitable for display
   */
  public function pdf(Applicant $applicant){
    $this->pdf->set_info("Title", "{$applicant->firstName} {$applicant->lastName} Application");
        
    $name = "{$applicant->firstName} {$applicant->middleName} {$applicant->lastName}";
    if($applicant->suffix){
      $name .= ", {$applicant->suffix}";
    }
    if($applicant->previousName){
      $name .= " ({$applicant->previousName})";
    }
    
    $this->pdf->begin_page_ext($this->pageWidth, $this->pageHeight, "");
    $this->setFont('p');
    $this->addText($name . "\n", 'h1');
    $this->addText("Email Address: {$applicant->email}\n", 'p');
  
    if($applicant->relatedExists('Decision')){
      //the priority of differnt addmission status from most to least important
      $statusPriority = array(
        'declineOffer' => 'Declined offer of admission',
        'acceptOffer' => 'Accepted offer of admission',
        'decisionLetterViewed' => 'Decision letter recieved',
        'decisionLetterSent' => 'Decision letter sent',
        'finalDeny' => 'Denied (Final)',
        'finalAdmit' => 'Admitted (Final)',
        'nominateDeny' => 'Denied (Preliminary)',
        'nominateAdmit' => 'Admitted (Preliminary)',
      );
      $arr = array();
      //loop through each status and find the one with the highest priority and a timestamp
      foreach($statusPriority as $key => $value){
        if($applicant->Decision->$key){
          $arr[] = $value . ' ' . date('m/d/y', strtotime($applicant->Decision->$key));
        }
      }
      $status = implode(', ', $arr);
    } else {
      $status = 'Under Review';
    }
    $this->addText("Admission Status: {$status}\n", 'p');
    $this->writeTextFlow();
    
    foreach($applicant->Application->Pages as $page){
      $className = $page->Page->class . 'Page';
      if(class_exists($className) AND is_subclass_of($className, 'ApplyPage')){
        $page = new $className($page, $applicant);
        $this->addText($page->title, 'h3');
        if($answers = $page->getAnswers()){
          $this->addText('Page answers go here, but need to be rendered by page type', 'p');
        } else {
          $this->addText('Applicant has not answered this section', 'h3');
        }
      }
      $this->writeTextFlow();
    }

    $this->pdf->end_page_ext("");
    $this->pdf->end_document("");
    return $this->pdf->get_buffer();
  }
  /**
   * Create a properly formated string for the font options
   * @param string $type
   * @return string
   */
  protected function fontOptions($type){
    return 'fontname=' . $this->fonts[$type]['face'] . ' fontsize=' . $this->fonts[$type]['size'] . ' leading=' . $this->fonts[$type]['leading'] . ' encoding=winansi fillcolor={rgb ' . $this->fonts[$type]['color'][0]/255 . ' ' . $this->fonts[$type]['color'][1]/255 . ' ' . $this->fonts[$type]['color'][2]/255 . '}';
  }
  
  /**
   * Set the current font
   * @param string $type
   */
  protected function setFont($type){
    $this->pdf->setfont($this->pdf->load_font($this->fonts[$type]['face'], "winansi", ""), $this->fonts[$type]['size']);
    $this->pdf->setcolor('fillstroke','rgb',$this->fonts[$type]['color'][0]/255,$this->fonts[$type]['color'][1]/255,$this->fonts[$type]['color'][2]/255,0);
  }
  
  /**
   * Add plain text to the current text flow
   * @param string $text
   * @param string $type the font options to use
   */
  protected function addText($text, $type){
    $this->pdf->add_textflow($this->currentText, $text, $this->fontOptions($type));
  }
  
  /**
   * Write out the current text flow adding pages as necessary
   */
  protected function writeTextFlow(){
    do{
      $continue = FALSE;
      $return = $this->pdf->fit_textflow($this->currentText,25,25,$this->pageWidth-20, $this->currentY, '');
      if($return == '_boxfull' || $return == '_nextpage'){
        $this->pdf->end_page_ext("");
        $this->pdf->begin_page_ext($this->pageWidth, $this->pageHeight, "");
        $this->currentY = $this->pageHeight-20;
        $continue = TRUE;
      }
    } while ($continue);
    $this->currentY = $this->pdf->info_textflow($this->currentText, 'textendy');
    $this->pdf->delete_textflow($this->currentText);
    $this->currentText = $this->pdf->create_textflow('', '');
  }

} //end PDFGenerator class
?>
