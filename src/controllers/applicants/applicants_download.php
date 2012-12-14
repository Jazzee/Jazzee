<?php

set_time_limit(600);

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

    $element = $field->newElement('RadioList', 'display');
    $element->setLabel('Display');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $displays = array();
    foreach($this->listDisplays() as $key => $display){
      $displays[$key] = $display;
      $element->newItem($key, $display['name']);
    }

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
      $applicants = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findByApplication($this->_application, false);
      foreach ($applicants as $applicant) {
        $selected = false;
        if (!$applicant->isLocked() and in_array('unlocked', $filters)) {
          $selected = true;
        }
        if ($applicant->isLocked()) {
          if (in_array('locked', $filters)) {
            $selected = true;
          }
          if ($applicant->getDecision()->getFinalAdmit() and in_array('admitted', $filters)) {
            $selected = true;
          }
          if ($applicant->getDecision()->getFinalDeny() and in_array('denied', $filters)) {
            $selected = true;
          }
          if ($applicant->getDecision()->getAcceptOffer() and in_array('accepted', $filters)) {
            $selected = true;
          }
          if ($applicant->getDecision()->getDeclineOffer() and in_array('declined', $filters)) {
            $selected = true;
          }
        }
        foreach ($filters as $value) {
          if(!$selected AND array_key_exists($value, $tags)){
            $tag = $tags[$value];
            if ($applicant->hasTag($tag)) {
              $selected = true;
            }
          }
        }
        if ($selected) {
          $applicantsArray[] = $applicant->getId();
        }
      } //end foreach applicants
      $display = $this->getDisplay($displays[$input->get('display')]);
      unset($applicants);
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
    $rows[] = $header;
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
      $rows[] = $arr;
    }
    $string = '';
    foreach ($rows as $row) {
      foreach ($row as $value) {
        $string .= '"' . $value . '"' . "\t";
      }
      $string .= "\n";
    }
    $this->setVar('outputType', 'string');
    $this->setVar('type', 'text/xls');
    $this->setVar('filename', $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy') . '.xls');
    $this->setVar('string', $string);
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
    foreach ($applicants as $key => $id) {
      $applicant = $this->_em->getRepository('Jazzee\Entity\Applicant')->find($id, true);
      $pdf = new \Jazzee\ApplicantPDF($this->_config->getPdflibLicenseKey(), \Jazzee\ApplicantPDF::USLETTER_LANDSCAPE, $this);
      $zip->addFromString($directoryName . '/' . $applicant->getFullName() . '.pdf', $pdf->pdf($applicant));
      if ($count > 50) {
        $count = 0;
        $this->_em->clear();
        gc_collect_cycles();
      }
      $count++;
    }
    $zip->close();
    header('Content-Type: ' . 'application/zip');
    header('Content-Disposition: attachment; filename='. $directoryName . '.zip');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);
    unlink($zipFile);
    exit(0);
  }

}
