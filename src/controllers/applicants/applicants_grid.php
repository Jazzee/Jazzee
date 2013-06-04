<?php

set_time_limit(1200);

/**
 * Applicant Grid
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicantsGridController extends \Jazzee\AdminController
{

  const MENU = 'Applicants';
  const TITLE = 'Grid View';
  const PATH = 'applicants/grid';
  const ACTION_INDEX = 'All Applicants';

  /**
   * Add the required JS
   */
  protected function setUp()
  {
    parent::setUp();
    $this->layout = 'json';
    $this->addCss('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css');
    $this->addCss('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables_themeroller.css');
    $this->addScript('https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js');
    
    $this->addScript($this->path('resource/scripts/classes/Display.class.js'));
    $this->addScript($this->path('resource/scripts/classes/Application.class.js'));
    $this->addScript($this->path('resource/scripts/classes/ApplicantData.class.js'));
    $this->addScript($this->path('resource/scripts/classes/DisplayChooser.class.js'));
    $this->addScript($this->path('resource/scripts/classes/DisplayManager.class.js'));
    $this->addScript($this->path('resource/scripts/classes/Grid.class.js'));
    $this->addScript($this->path('resource/scripts/controllers/applicants_grid.controller.js'));
    $this->addScript($this->path('resource/scripts/controllers/jquery.dataTables.rowSelect.js'));

    $this->addCss($this->path('resource/styles/grid.css'));
    $this->addCss($this->path('resource/styles/displaymanager.css'));

    $scripts = array();
    
    //add all of the JazzeePage scripts for display
    $types = $this->_em->getRepository('\Jazzee\Entity\PageType')->findAll();
    $scripts = array();
    $scripts[] = $this->path('resource/scripts/page_types/JazzeePage.js');
    foreach ($types as $type) {
      $class = $type->getClass();
      $scripts[] = $this->path($class::pageBuilderScriptPath());
    }
    $scripts = array_unique($scripts);
    foreach ($scripts as $path) {
      $this->addScript($path);
    }
    
    //add all of the Jazzee element scripts for data rendering
    $this->addScript($this->path('resource/scripts/element_types/JazzeeElement.js'));

    $types = $this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll();
    $scripts[] = $this->path(\Jazzee\Interfaces\Element::PAGEBUILDER_SCRIPT);
    $scripts[] = $this->path('resource/scripts/element_types/List.js');
    $scripts[] = $this->path('resource/scripts/element_types/FileInput.js');
    foreach ($types as $type) {
      $class = $type->getClass();
      $scripts[] = $this->path($class::PAGEBUILDER_SCRIPT);
    }
    $scripts = array_unique($scripts);
    foreach ($scripts as $path) {
      $this->addScript($path);
    }
  }

  public function getForm($p)
  { 
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path($p));
    $form->setId("gridForm");
    $form->newButton('submit', 'Download Applicants');

    return $form;
  }

  public function actionIndex()
  {
    $this->layout = 'wide';
    $form = $this->getForm('applicants/grid');
    $this->setVar('form', $form);
  }

  /**
   * List all applicants
   */

  public function actionDownload()  {

    $this->layout = 'wide';
    $form = $this->getForm('applicants/grid');

    if ($input = $form->processInput($this->post)) {

      $filters = $input->get('filters');
      $applicationPages = array();
      foreach ($this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $pageEntity) {
        $pageEntity->getJazzeePage()->setController($this);
        $applicationPages[$pageEntity->getId()] = $pageEntity;
      }
      $applicantsArray = array();

      if(isset($this->post["from_date"])){
	$to = (isset($this->post["to_date"])) ? $this->post["to_date"] : null;

        $applicantsArray = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsInDateRange($this->_application, $this->post["from_date"], $to);
      }
      else if($input->get("applicantIds[]") || $input->get("applicantIds")){
	$requestedIds = $input->get("applicantIds");
	$applicantsArray = $requestedIds;

      }else{
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
      }
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
          $this->makePdfArchive($applicantsArray, $display);
          break;
      }
    }
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
      $applicantsDisplayArray = $this->_em->getRepository('Jazzee\Entity\Applicant')->findDisplayArrayByApplication($this->_application, $display, $limitedIds, $this->post);
      foreach($applicantsDisplayArray as $applicantArray){
        $pdf = new \Jazzee\ApplicantPDF($this->_config->getPdflibLicenseKey(), \Jazzee\ApplicantPDF::USLETTER_LANDSCAPE, $this);
        $temp_file_name = tempnam($tmppath, 'JazzeeTempDL');
        $tempFileArray[] = $temp_file_name;
        file_put_contents($temp_file_name, $pdf->pdfFromApplicantArray($this->_application, $applicantArray));

	$extId = ($applicantArray['externalId'] != null) ? $applicantArray['externalId'] : "NO_ID";
        $zip->addFile($temp_file_name, $directoryName . '/' . $applicantArray['lastName'] . ', '.$applicantArray['firstName'].' - '.$extId.'.pdf');
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
  
  /**
   * Get applicant JSON
   */
  public function actionListApplicants(){
    $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findIdsByApplication($this->_application, true);
    $this->setVar('result', $applicants);
    $this->loadView('applicants_single/result');
  }
  
  /**
   * Get applicant JSON
   */
  public function actionGetApplicants(){

    $results = array();
    $display = $this->getDisplay($this->post['display']);
    $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findDisplayArrayByApplication($this->_application, $display, $this->post['applicantIds']);

    $pages = array();
    /* for lor x/y ticket, not working yet
    foreach ($this->_em->getRepository('\Jazzee\Entity\Page')->findByApplication($this->_application) as $page) {
      if($page instanceof Jazzee\Interfaces\DataPage){
	$pages[] = $page->toArray();
      }
    }
*/

    $this->setVar('result', array('applicants' => $applicants,
				  'pages' => $pages));
    $this->loadView('applicants_single/result');
  }

  /**
   * Controll actions with the index action
   * @param string $controller
   * @param string $action
   * @param \Jazzee\Entity\User $user
   * @param \Jazzee\Entity\Program $program
   * @return bool
   */
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null)
  {
    if (in_array($action, array('getApplicants', 'listApplicants', 'describeDisplay', 'download'))) {
      $action = 'index';
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}