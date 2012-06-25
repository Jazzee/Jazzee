<?php
ini_set('memory_limit', '1g');
set_time_limit('120');
/**
 * Download Applicants in csv format
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage applicants
 */
class ApplicantsDownloadController extends \Jazzee\AdminController {
  const MENU = 'Applicants';
  const TITLE = 'Download';
  const PATH = 'applicants/download';
  
  const ACTION_INDEX = 'All Applicants';

  /**
   * List all applicants
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('applicants/download'));
    $field = $form->newField();
    $field->setLegend('Download Applicants');
    
    $element = $field->newElement('RadioList','type');
    $element->setLabel('Type of Download');
    $element->newItem('xls', 'Excel');
    $element->newItem('xml', 'XML');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('CheckboxList','filters');
    $element->setLabel('Types of applicants');
    $element->newItem('unlocked', 'Incomplete');
    $element->newItem('locked', 'Locked');
    $element->newItem('admitted', 'Admitted');
    $element->newItem('denied', 'Denied');
    $element->newItem('accepted', 'Accepted');
    $element->newItem('declined', 'Declined');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Download Applicants');
    if($input = $form->processInput($this->post)){
      $filters = $input->get('filters');
      $applicationPages = array();
      foreach($this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $pageEntity){
        $pageEntity->getJazzeePage()->setController($this);
        $applicationPages[$pageEntity->getId()] = $pageEntity;
      }
      $applicantsArray = array();
      $applicants = $this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application);
      foreach($applicants as $applicant){
        if((!$applicant->isLocked() and in_array('unlocked', $filters)) 
          or ($applicant->isLocked() and in_array('locked', $filters))  
          or ($applicant->isLocked() and $applicant->getDecision()->getFinalAdmit() and in_array('admitted', $filters))  
          or ($applicant->isLocked() and $applicant->getDecision()->getFinalDeny() and in_array('denied', $filters))
          or ($applicant->isLocked() and $applicant->getDecision()->getAcceptOffer() and in_array('accepted', $filters))
          or ($applicant->isLocked() and $applicant->getDecision()->getDeclineOffer() and in_array('declined', $filters))
        ){
          $applicantsArray[] = $applicant;
        } //end if filter
      } //end foreach applicants
      unset($applicants);
      switch($input->get('type')){
        case 'xls':  
          $this->makeXls($applicantsArray);
          break;
        case 'xml':
          $this->makeXml($applicantsArray);
          break;
      }
    }
    $this->setVar('form', $form);
  }
  
  /**
   * XLS file type
   * @param array \Jazzee\Entity\Applicant $applicants
   */
  protected function makeXls(array $applicants){
    $applicationPages = array();
    $pageAnswerCount = array();
    foreach($this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$this->_application->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc')) as $applicationPage){
      if($applicationPage->getJazzeePage() instanceof \Jazzee\Interfaces\CsvPage){
        $applicationPages[] = $applicationPage;
      }
    }
    foreach($applicationPages as $applicationPage) $pageAnswerCount[$applicationPage->getPage()->getId()] = 1;
    foreach($applicants as $applicant){
      foreach($applicationPages as $applicationPage){
        if(count($applicant->findAnswersByPage($applicationPage->getPage())) > $pageAnswerCount[$applicationPage->getPage()->getId()]) 
            $pageAnswerCount[$applicationPage->getPage()->getId()] = count($applicant->findAnswersByPage($applicationPage->getPage()));
      }
    }
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
    foreach($applicationPages as $applicationPage){
      for($i=1;$i<=$pageAnswerCount[$applicationPage->getPage()->getId()]; $i++){
        foreach($applicationPage->getJazzeePage()->getCsvHeaders() as $title){
          $header[] = $applicationPage->getTitle() . ' ' . $i . ' ' . $title;
        }
      }
    }
    $rows[] = $header;
    foreach($applicants as $applicant){
      $arr = array(
        $applicant->getId(),
        $applicant->getFirstName(),
        $applicant->getMiddleName(),
        $applicant->getLastName(),
        $applicant->getSuffix(),
        $applicant->getEmail(),
        $applicant->isLocked()?'yes':'no',
        $applicant->getLastLogin()->format('c'),
        $applicant->getUpdatedAt()->format('c'),
        $applicant->getCreatedAt()->format('c'),
        $applicant->getDecision()?$applicant->getDecision()->status():'none',
        $applicant->getPercentComplete() * 100 . '%'
      );
      $tags = array();
      foreach($applicant->getTags() as $tag) $tags[] = $tag->getTitle();
      $arr[] = implode(' ', $tags);
      foreach($applicationPages as $applicationPage){
        $applicationPage->getJazzeePage()->setApplicant($applicant);
        for($i=0;$i<$pageAnswerCount[$applicationPage->getPage()->getId()]; $i++){  
          $arr = array_merge($arr, $applicationPage->getJazzeePage()->getCsvAnswer($i));
        }
      }
      $rows[] = $arr;
    }
    unset($applicants);
    unset($applicationPages);
    $string = '';
    foreach($rows as $row){
      foreach($row as $value) $string .= '"' . $value . '"' . "\t";
      $string .= "\n";
    }
    $this->setVar('outputType', 'string');
    $this->setVar('type', 'text/xls');
    $this->setVar('filename', $this->_application->getProgram()->getShortName() .  '-' . $this->_application->getCycle()->getName() . date('-mdy') . '.xls');
    $this->setVar('string', $string);
  }
  
  /**
   * XML file type
   * @param array \Jazzee\Entity\Applicant $applicants
   */
  protected function makeXml(array $applicants){
    $xml = new DOMDocument();
    $xml->formatOutput = true;
    $applicantsXml = $xml->createElement("applicants");
    
    foreach($applicants as $applicant){
      $appXml = $applicant->toXml($this);
      $node = $xml->importNode($appXml->documentElement, true);
      $applicantsXml->appendChild($node);
    }
    $xml->appendChild($applicantsXml);
    $this->setVar('outputType', 'xml');
    $this->setLayout('xml');
    $this->setLayoutVar('filename', $this->_application->getProgram()->getShortName() .  '-' . $this->_application->getCycle()->getName() . date('-mdy'));
    $this->setVar('xml', $xml);
  }
}