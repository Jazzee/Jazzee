<?php

set_time_limit(1200);

/**
 * Download Applicants in csv format
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsDownloadController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'Download';
  const PATH = 'applicants/download';
  const ACTION_INDEX = 'All Applicants';

  /**
   * List all applicants
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/download'));
    $field = $form->newField();
    $field->setLegend('Download Applicants');

    $element = $field->newElement('RadioList', 'type');
    $element->setLabel('Type of Download');
    $element->newItem('xls', 'Excel');
    $element->newItem('xml', 'XML');
    $element->newItem('json', 'JSON');
    $element->newItem('pdfarchive', 'Archive of Multiple PDFs');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('CheckboxList', 'filters');
    $element->setLabel('Types of applicants');
    $element->newItem('unlocked', 'Incomplete');
    $element->newItem('locked', 'Locked');
    $element->newItem('admitted', 'Admitted');
    $element->newItem('denied', 'Denied');
    $element->newItem('accepted', 'Accepted');
    $element->newItem('declined', 'Declined');

    $tags = $this->_em->getRepository('\Jazzee\Entity\Tag')->findByApplication($this->_application);
    foreach ($tags as $tag) {
      $element->newItem($tag->getId(), $tag->getTitle());
    }
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Download Applicants');
    if ($input = $form->processInput($this->post)) {
      $filters = $input->get('filters');
      $applicationPages = array();
      foreach ($this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $pageEntity) {
        $pageEntity->getJazzeePage()->setController($this);
        $applicationPages[$pageEntity->getId()] = $pageEntity;
      }
      $applicantsArray = array();
      $minimalDisplay = new \Jazzee\Display\Minimal($this->_application);
      $ids = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findIdsByApplication($this->_application);
      foreach ($ids as $id) {
        $applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findArray($id, $minimalDisplay);
        $selected = false;
        if (!$applicant['isLocked'] and in_array('unlocked', $filters)) {
          $selected = true;
        }
        if ($applicant['isLocked']) {
          if (in_array('locked', $filters)) {
            $selected = true;
          }
          if ($applicant['decision']['finalAdmit'] and in_array('admitted', $filters)) {
            $selected = true;
          }
          if ($applicant['decision']['finalDeny'] and in_array('denied', $filters)) {
            $selected = true;
          }
          if ($applicant['decision']['acceptOffer'] and in_array('accepted', $filters)) {
            $selected = true;
          }
          if ($applicant['decision']['declineOffer'] and in_array('declined', $filters)) {
            $selected = true;
          }
        }
        if(!$selected){
          $tagIds = array();
          foreach($applicant['tags'] as $arr){
            $tagIds[] = $arr['id'];
          }
          foreach ($filters as $value) {
            if(array_key_exists($value, $tags)){
              $tag = $tags[$value];
              if (in_array($tag->getId(), $tagIds)) {
                $selected = true;
              }
            }
          }
        }
        if ($selected) {
          $applicantsArray[] = $applicant['id'];
        }
      } //end foreach applicants
      //use a full applicant display where display is needed
      $display= new \Jazzee\Display\FullApplication($this->_application);
      unset($ids);
      switch ($input->get('type')) {
        case 'xls':
          $this->makeXls($applicantsArray, $display);
          break;
        case 'xml':
          $this->makeXml($applicantsArray);
          break;
        case 'json':
          $this->makeJson($applicantsArray, $display);
          break;
        case 'pdfarchive':
          $this->makePdfArchive($applicantsArray);
          break;
      }
    }
    $this->setVar('form', $form);
  }

  /**
   * XLS file type
   * @param array \Jazzee\Entity\Applicant $applicants
   */
  protected function makeXls(array $applicants, \Jazzee\Interfaces\Display $display)
  {
    $applicationPages = array();
    $pageAnswerCount = $this->_em->getRepository('Jazzee\Entity\Application')->getPageAnswerCounts($this->_application);

    $rows = array();
    $header = array(
      'ID',
      'First Name',
      'Middle Name',
      'Last Name',
      'Suffix',
      'Email',
      'Locked',
      'Last Login',
      'Last Update',
      'Account Created',
      'Status',
      'Progress',
      'Tags'
    );
    $applicationPages = array();
    foreach ($this->getApplicationPages() as $applicationPage) {
      if($applicationPage->getJazzeePage() instanceof \Jazzee\Interfaces\CsvPage){
        $applicationPages[] = $applicationPage;
        for ($i = 1; $i <= $pageAnswerCount[$applicationPage->getPage()->getId()]; $i++) {
          foreach ($applicationPage->getJazzeePage()->getCsvHeaders() as $title) {
            $header[] = $applicationPage->getTitle() . ' ' . $i . ' ' . $title;
          }
        }
      }
    }
    $fileName = tempnam($this->_config->getVarPath() . '/tmp/', 'applicants_download');
    $handle = fopen($fileName, 'w+');
    $this->writeXlsFile($header, $handle);
    foreach ($applicants as $id) {
      $applicant = $this->_application->formatApplicantArray($this->_em->getRepository('Jazzee\Entity\Applicant')->findArray($id, $display));
      $arr = array(
        $applicant['id'],
        $applicant['firstName'],
        $applicant['middleName'],
        $applicant['lastName'],
        $applicant['suffix'],
        $applicant['email'],
        $applicant['isLocked']? 'yes':'no',
        $applicant['lastLogin']->format('c'),
        $applicant['updatedAt']->format('c'),
        $applicant['createdAt']->format('c'),
        $applicant['decision'] ? $applicant['decision']['status'] : 'none',
        $applicant['percentComplete'] * 100 . '%'
      );
      $tags = array();
      foreach ($applicant['tags'] as $tag) {
        $tags[] = $tag['title'];
      }
      $arr[] = implode(' ', $tags);
      $pages = array();
      foreach($applicationPages as $applicationPage){
        $pages[$applicationPage->getPage()->getId()] = array();
      }
      foreach($applicant['pages'] as $page){
        $pages[$page['id']] = $page;
      }
      foreach ($applicationPages as $applicationPage) {
        for ($i = 0; $i < $pageAnswerCount[$applicationPage->getPage()->getId()]; $i++) {
          $arr = array_merge($arr, $applicationPage->getJazzeePage()->getCsvAnswer($pages[$applicationPage->getPage()->getId()], $i));
        }
      }
      $this->writeXlsFile($arr, $handle);
    }
    rewind($handle);
    fclose($handle);
    $this->setVar('outputType', 'file');
    $this->setVar('type', 'text/xls');
    $this->setVar('filename', $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy') . '.xls');
    $this->setVar('filePath', $fileName);
  }
  
  /**
   * Write array data to csv file
   * @param array $data
   * @param resource $handle
   */
  protected function writeXlsFile(array $data, $handle){
    $string = '';
    foreach ($data as $value) {
      $string .= '"' . $value . '"' . "\t";
    }
    $string .= "\n";
    fwrite($handle, $string);
  }

  /**
   * Get application pages
   * @return array
   */
  protected function getApplicationPages()
  {
    $applicationPages = array();
    foreach ($this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application' => $this->_application->getId(), 'kind' => \Jazzee\Entity\ApplicationPage::APPLICATION), array('weight' => 'asc')) as $applicationPage) {
      if ($applicationPage->getJazzeePage() instanceof \Jazzee\Interfaces\CsvPage) {
        $applicationPage->getJazzeePage()->setController($this);
        $applicationPages[] = $applicationPage;
      }
    }
    return $applicationPages;
  }

  /**
   * XML file type
   * @param array \Jazzee\Entity\Applicant $applicants
   */
  protected function makeXml(array $applicants)
  {
    $xml = new DOMDocument();
    $xml->formatOutput = true;
    $applicantsXml = $xml->createElement("applicants");
    $count = 0;
    foreach ($applicants as $id) {
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->find($id, true);
      $appXml = $applicant->toXml($this);
      $node = $xml->importNode($appXml->documentElement, true);
      $applicantsXml->appendChild($node);
      if ($count > 50) {
        $count = 0;
        $this->_em->clear();
        gc_collect_cycles();
      }
      $count++;
    }
    $xml->appendChild($applicantsXml);
    $this->setVar('outputType', 'xml');
    $this->setLayout('xml');
    $this->setLayoutVar('filename', $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy'));
    $this->setVar('xml', $xml);
  }

  /**
   * JSON file type
   * @param array \Jazzee\Entity\Applicant $applicants
   * @param \Jazzee\Entity\Display $display
   */
  protected function makeJson(array $applicants, \Jazzee\Interfaces\Display $display)
  {
    $applicants = array_slice($applicants, 0, 50);
    $arr = array();
    $count = 0;
    foreach ($applicants as $id) {
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->findArray($id, $display);
      $arr[] = $this->_application->formatApplicantArray($applicant);
      $count++;
    }
    $this->setVar('outputType', 'json');
    $this->setVar('filename', $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy') . '.json');
    $this->setVar('output', array('applicants' => $arr));
  }

  /**
   * PDF file type
   * Create a single large PDF of all applicants
   * @param array \Jazzee\Entity\Applicant $applicants
   */
  protected function makePdfArchive(array $applicants)
  {
    $directoryName = $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy');
    $zipFile = $this->_config->getVarPath() . '/tmp/' . uniqid() . '.zip';
    $zip = new ZipArchive;
    $zip->open($zipFile, ZipArchive::CREATE);
    $zip->addEmptyDir($directoryName);
    $count = 0;

    // we need this b/c otherwise the readfile call (to write the file
    // to the client) may attempt to slurp the whole archive and run out of memory.
    // output buffering on or off does not affect the zip leak issue below.
    if (ob_get_level()) {
      $this->log("output buffering is on! level: ".ob_get_level().", stopping now..");
      ob_end_clean();
    }
    // ensure garbage collection is on
    gc_enable();

    $displays = array();
    foreach($this->listDisplays() as $key => $display){
      $displays[$key] = $display;
    }

    $display = $this->getDisplay($displays[$this->post['display']]);
    $applicantCount = 0;
    $tempFileArray = array();
    $tmppath = $this->_config->getVarPath() . '/tmp';

    foreach ($applicants as $key => $id) {
      $applicantCount++;
      $temp_file_name = tempnam($tmppath, 'JazzeeTempDL');

      $usage = $this->format_mem(memory_get_usage (true));
      $usage2 = $this->format_mem($this->memory_get_sys_usage());
      
      $this->log("ITERATION[$applicantCount] via temp file '$temp_file_name', mem usage: ".$usage." vs. ".$usage2);


      $idsArray = array();
      $idsArray[] = $id;
      // this method will return 1 applicant as the single item in an array.
      // the pdf2 method below will throw an exception if mor are passed.
      $appPDFs = $this->_em->getRepository('Jazzee\Entity\Applicant')->findPDFsForApplication($this->_application, $display, $idsArray);
      // $this->log("Have applicant: ".var_export($appPDFs, true));
      $pdf = new \Jazzee\ApplicantPDF($this->_config->getPdflibLicenseKey(), \Jazzee\ApplicantPDF::USLETTER_LANDSCAPE, $this);

      $fullName = $appPDFs[0]["firstName"]." ".$appPDFs[0]["lastName"];

      // 1. with output buffering turned off, leak still exists
      // 2. just calling this, without writing to the zip, does not leak memory
      try{
      $pdfResult = $pdf->pdf2($this->_application, $appPDFs);
      }catch(Exception $oom){
	$this->_em->clear();
	gc_collect_cycles();
	$this->log("OUT OF MEMORY! ".var_export($appPDFs, true));
      }
      // 3. these both leak
      //    $zip->addFromString($directoryName . '/' . $fullName . '.pdf', $pdf->pdf2($this->_application, $appPDFs));
      //    $zip->addFromString($directoryName . '/' . $fullName . '.pdf', $pdfResult);

      // writing to a temp file first and then using the zip#addFile method
      // does not cause a leak.
      $temp = fopen($temp_file_name, 'w') or die("Unable to open temp file $temp_file_name");
      fwrite($temp, $pdfResult);

      // add file just adds to the queue, the file is not written until later.
      $zip->addFile($temp_file_name, $directoryName . '/' . $fullName . '.pdf');
      // so we add the handle for cleanup after the zip is closed 
      $tempFileArray[] = $temp;

      unset($pdfResult);
      $pdfResult = null;
      unset($pdf);
      $pdf = null;
      unset($appPDFs);
      $appPDFs = null;
      //if ($count > 50) {
       $count = 0;
       $this->_em->clear();
       gc_collect_cycles();
       //}
       $count++;
    }
    $zip->close();

    foreach($tempFileArray as $temp){
       fclose($temp);
       $temp = null;
    }

    header('Content-Type: ' . 'application/zip');
    header('Content-Disposition: attachment; filename='. $directoryName . '.zip');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);
    unlink($zipFile);

    unset($zip);
    $zip = null;
    unset($zipFile);
    $zipFile = null;
    exit(0);
  }

  function format_mem($mem_usage){
    $usage = "";
      if ($mem_usage < 1024) 
	$usage = $mem_usage." bytes"; 
      elseif ($mem_usage < 1048576) 
	$usage = round($mem_usage/1024,2)." kb"; 
      else 
	$usage = round($mem_usage/1048576,2)." mb";

      return $usage;
  }

  function memory_get_sys_usage() 
  { 
    $pid = getmypid(); 
    exec("ps -o rss -p $pid", $output); 
    return $output[1] *1024; 
  } 

}
