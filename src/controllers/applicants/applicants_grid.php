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
  const ACTION_INDEX = 'View Applicants';
  const ACTION_DOWNLOADXLS = 'Download Excel Data';
  const ACTION_DOWNLOADJSON = 'Download JSON Data';
  const ACTION_DOWNLOADXML = 'Download XML Data';
  const ACTION_DOWNLOADPDFARCHIVE = 'Download PDF Data';
  const ACTION_SENDMESSAGE = 'Send Applicant Messages';
    
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

    $this->addCss($this->path('resource/styles/grid.css'));
    $this->addCss($this->path('resource/styles/displaymanager.css'));

    $this->addScript($this->path('resource/foundation/scripts/form.js'));

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

  public function actionIndex()
  {
    $this->layout = 'wide';
  }

  /**
   *  Adds Messages entities for the submitted list of applicants
   */	
  public function actionSendMessage()  
  {
    $applicants = explode(',',$this->post['applicantIds']);
    foreach ($applicants as $id) {
      $thread = new \Jazzee\Entity\Thread();
      $thread->setSubject($this->post['subject']);
      $applicant = $this->getApplicantById($id);
      $thread->setApplicant($applicant);

      $message = new \Jazzee\Entity\Message();
      $message->setSender(\Jazzee\Entity\Message::PROGRAM);
      $message->setText($this->post['body']);
      $thread->addMessage($message);
      $this->_em->persist($thread);
      $this->_em->persist($message);
    }

    $this->addMessage('success', count($applicants) . ' messages sent successfully');
    $this->setLayoutVar('status', 'success');
    $this->setVar('result', true);
    $this->loadView('applicants_single/result');
  }
  
  /**
   * List all applicants
   */

  public function actionDownloadXls()  
  {
    $displayObj = json_decode($this->post['display']);
    $display = $this->getDisplay(
        array(
            'id'=>$displayObj->id,
            'type'=>$displayObj->type,
            'class'=>isset($displayObj->class)?$displayObj->class:null
        )
    );
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
    $applicants = explode(',',$this->post['applicantIds']);
    foreach ($applicants as $id) {
      $applicant = $this->_application->formatApplicantDisplayArray($this->_em->getRepository('Jazzee\Entity\Applicant')->findArray($id, $display));
      $arr = array();
      foreach($display->listElements() as $displayElement){
        if($displayElement->getType() == 'applicant'){
            switch($displayElement->getName()){
                case 'isLocked':
                    $arr['isLocked'] = $applicant['isLocked']? 'yes':'no';
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
    $this->loadView('applicants_grid/download');
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
   * List all applicants
   */

  public function actionDownloadJson()  
  {
    //use a full applicant display where display is needed
    $display = new \Jazzee\Display\FullApplication($this->_application);
    $applicants = explode(',',$this->post['applicantIds']);
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
    $this->loadView('applicants_grid/download');
  }
  
  /**
   * List all applicants
   */

  public function actionDownloadXml()  
  {
    $applicants = explode(',',$this->post['applicantIds']);
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
    $this->loadView('applicants_grid/download');
  }
  
  /**
   * List all applicants
   */
  public function actionDownloadPdfArchive()  
  {
    //use a full applicant display where display is needed
    $display = new \Jazzee\Display\FullApplication($this->_application);
    $applicants = explode(',',$this->post['applicantIds']);
    // ensure garbage collection is on, we need it
    gc_enable();

    $directoryName = $this->_application->getProgram()->getShortName() . '-' . $this->_application->getCycle()->getName() . date('-mdy');
    $zipFile = $this->_config->getVarPath() . '/tmp/' . uniqid() . '.zip';
    $zip = new ZipArchive;
    $zip->open($zipFile, ZipArchive::CREATE);
    $zip->addEmptyDir($directoryName);
    $tmppath = $this->_config->getVarPath() . '/tmp';
    
    if($this->post['pdftemplate'] == 'portrait' or $this->post['pdftemplate'] == 'landscape'){
      foreach(array_chunk($applicants, 20) as $limitedIds){
        $applicantsDisplayArray = $this->_em->getRepository('Jazzee\Entity\Applicant')->findDisplayArrayByApplication($this->_application, $display, $limitedIds);
        foreach($applicantsDisplayArray as $applicantArray){
          switch($this->post['pdftemplate']){
            case 'portrait':
              $pdf = new \Jazzee\ApplicantPDF($this->_config->getPdflibLicenseKey(), \Jazzee\ApplicantPDF::USLETTER_PORTRAIT, $this);
              break;
            case 'landscape':
              $pdf = new \Jazzee\ApplicantPDF($this->_config->getPdflibLicenseKey(), \Jazzee\ApplicantPDF::USLETTER_LANDSCAPE, $this);
              break;
          }
          $temp_file_name = tempnam($tmppath, 'JazzeeTempDL');
          file_put_contents($temp_file_name, $pdf->pdfFromApplicantArray($this->_application, $applicantArray));
          $externalIDSuffix = ($applicantArray['externalId'] != null) ? ' - '.$applicantArray['externalId'] : "";
          $zip->addFile($temp_file_name, $directoryName . '/' . $applicantArray['lastName'] . '_' . $applicantArray['firstName'] . $externalIDSuffix . '.pdf');
          unset($pdf);
        }
      }
    } else {
      if(!$template = $this->_application->getTemplateById($this->post['pdftemplate'])){
        throw new \Jazzee\Exception("Invalid template ID: " . $this->post['pdftemplate'] . ' for ' . $this->_application->getProgram()->getName());
      }
      foreach(array_chunk($applicants, 20) as $limitedIds){
        $applicantsPDFTemplateArray = $this->_em->getRepository('Jazzee\Entity\Applicant')->findPDFTemplateArrayByApplication($this->_application, $display, $limitedIds);
        foreach($applicantsPDFTemplateArray as $applicantArray){
          $pdf = new \Jazzee\TemplatePDF($this->_config->getPdflibLicenseKey(), $template, $this);
          $temp_file_name = tempnam($tmppath, 'JazzeeTempDL');
          file_put_contents($temp_file_name, $pdf->pdfFromApplicantArray($this->_application, $applicantArray));
          $externalIDSuffix = ($applicantArray['externalId'] != null) ? ' - '.$applicantArray['externalId'] : "";
          $zip->addFile($temp_file_name, $directoryName . '/' . $applicantArray['lastName'] . '_' . $applicantArray['firstName'] . $externalIDSuffix . '.pdf');
          unset($pdf);
        }
      }
    }

    $zip->close();
    $this->setVar('outputType', 'file');
    $this->setVar('type', 'application/zip');
    $this->setVar('filename', $directoryName . '.zip');
    $this->setVar('filePath', $zipFile);
    $this->loadView('applicants_grid/download');
  }
  
  /**
   * Get applicant JSON
   */
  public function actionListApplicants(){
    $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findIdsByApplication($this->_application, false);
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
    if (in_array($action, array('getApplicants', 'listApplicants', 'describeDisplay'))) {
      $action = 'index';
    }

    return parent::isAllowed($controller, $action, $user, $program, $application);
  }

}