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

    $element = $field->newElement('RadioList', 'display');
    $element->setLabel('Display');

    $displays = array();
    foreach ($this->listDisplays() as $key => $display) {
      $displays[$key] = $display;
      $element->newItem($key, $display['name']);
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
      $display= $this->getDisplay($displays[$input->get('display')]);
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
          $this->makePdfArchive($applicantsArray, $display);
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
    $pageAnswerCount = $this->_em->getRepository('Jazzee\Entity\Application')->getPageAnswerCounts($this->_application);

    $rows = array();
    $header = array();
    foreach($display->listElements() as $displayElement){
        if($displayElement->getType() == 'applicant'){
            $header[] = $displayElement->getTitle();
        } else {
            $applicationPage = $this->_application->getApplicationPageByChildPageId($displayElement->getPageId());
            for ($i = 1; $i <= $pageAnswerCount[$applicationPage->getPage()->getId()]; $i++) {
              $header[] = $applicationPage->getTitle() . ' ' . $i . ' ' . $displayElement->getTitle();
            }
        }
    }
    $fileName = tempnam($this->_config->getVarPath() . '/tmp/', 'applicants_download');
    $handle = fopen($fileName, 'w+');
    $this->writeXlsFile($header, $handle);
    foreach ($applicants as $id) {
      $applicant = $this->_application->formatApplicantDisplayArray($this->_em->getRepository('Jazzee\Entity\Applicant')->findArray($id, $display));
      $arr = array();
      foreach($display->listElements() as $displayElement){
        if($displayElement->getType() == 'applicant'){
            switch($displayElement->getName()){
                case 'isLocked':
                    $arr['isLocked'] = $applicant['isLocked']? 'yes':'no';
                    break;
                case 'lockedAt':
                   $arr[$displayElement->getName()] = $applicant['decision']? 
                     (is_null($applicant['decision']['lockedAt']) ? '' : $applicant['decision']['lockedAt']->format('c')):'';
                   break;
                case 'hasPaid':
                    $arr['hasPaid'] = $applicant['hasPaid']? 'yes':'no';
                    break;
                case 'lastLogin':
                    $arr['lastLogin'] = !is_null($applicant['lastLogin'])?$applicant['lastLogin']->format('c'):'never';
                    break;
                case 'updatedAt':
                case 'createdAt':
                    $arr[$displayElement->getName()] = $applicant['updatedAt']->format('c');
                    break;
                case 'decision':
                    $arr['decision'] = $applicant['decision']? $applicant['decision']['status']:'none';
                    break;
                case 'percentComplete':
                    $arr['percentComplete'] = $applicant['percentComplete'] * 100 . '%';
                    break;
                case 'attachments':
                    $values = array();
                    foreach($applicant['attachments'] as $attachment){
                        $values[] = $attachment['displayValue'];
                    }
                    $arr[$displayElement->getName()] = implode(',', $values);
                    break;
                default:
                    $arr[$displayElement->getName()] = $applicant[$displayElement->getName()];
            }
            
        } else {
            $applicationPage = $this->_application->getApplicationPageByChildPageId($displayElement->getPageId());
            $pageArr = array();
            foreach($applicant['pages'] as $page){
                if($page['id'] == $applicationPage->getPage()->getId()){
                    $pageArr = $page;
                }
            }

            for ($i = 0; $i < $pageAnswerCount[$applicationPage->getPage()->getId()]; $i++) {
              $value = '';
              if (isset($pageArr['answers']) and array_key_exists($i, $pageArr['answers'])) {
                $value = $applicationPage->getJazzeePage()
                  ->getDisplayElementValueFromArray($pageArr['answers'][$i], $displayElement);
              }
              $arr[$displayElement->getType().$displayElement->getName().$displayElement->getPageId().$i] = $value;
            }
            
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
   * We do this with temporary files because the add from string method leaks
   * These both leak
   * $zip->addFromString($directoryName . '/' . $fullName . '.pdf', $pdfResult);
   * @param array \Jazzee\Entity\Applicant $applicants
   * @param \Jazzee\Interfaces\Display $display
   */
  protected function makePdfArchive(array $applicants, \Jazzee\Interfaces\Display $display)
  {
    // ensure garbage collection is on, we need it
    gc_enable();

    $directoryName = $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy');
    $zipFile = $this->_config->getVarPath() . '/tmp/' . uniqid() . '.zip';
    $zip = new ZipArchive;
    $zip->open($zipFile, ZipArchive::CREATE);
    $zip->addEmptyDir($directoryName);

    $tempFileArray = array();
    $tmppath = $this->_config->getVarPath() . '/tmp';
    foreach(array_chunk($applicants, 20) as $limitedIds){
      $applicantsDisplayArray = $this->_em->getRepository('Jazzee\Entity\Applicant')->findDisplayArrayByApplication($this->_application, $display, $limitedIds);
      foreach($applicantsDisplayArray as $applicantArray){
        $pdf = new \Jazzee\ApplicantPDF($this->_config->getPdflibLicenseKey(), \Jazzee\ApplicantPDF::USLETTER_LANDSCAPE, $this);
        $temp_file_name = tempnam($tmppath, 'JazzeeTempDL');
        $tempFileArray[] = $temp_file_name;
        file_put_contents($temp_file_name, $pdf->pdfFromApplicantArray($this->_application, $applicantArray));
        $zip->addFile($temp_file_name, $directoryName . '/' . $applicantArray['fullName'] . '.pdf');
        unset($pdf);
      }
    }
    $zip->close();

    header('Content-Type: ' . 'application/zip');
    header('Content-Disposition: attachment; filename='. $directoryName . '.zip');
    header('Content-Transfer-Encoding: binary');
    // we need this b/c otherwise the readfile call (to write the file
    // to the client) may attempt to slurp the whole archive and run out of memory.
    if (ob_get_level()) {
      ob_end_clean();
    }
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);
    unlink($zipFile);
    foreach($tempFileArray as $path){
      unlink($path);
    }
    exit(0);
  }

}
