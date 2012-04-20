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
      $applicationPagesAnswerCount = array();
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
    $applicationPages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$this->_application->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc'));  
    $applicationPagesAnswerCount = array();
    foreach($applicationPages as $applicationPage) $applicationPagesAnswerCount[$applicationPage->getPage()->getId()] = 1;
    foreach($applicants as $applicant){
      foreach($applicationPages as $applicationPage){
        if(count($applicant->findAnswersByPage($applicationPage->getPage())) > $applicationPagesAnswerCount[$applicationPage->getPage()->getId()]) 
            $applicationPagesAnswerCount[$applicationPage->getPage()->getId()] = count($applicant->findAnswersByPage($applicationPage->getPage()));
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
      for($i=1;$i<=$applicationPagesAnswerCount[$applicationPage->getPage()->getId()]; $i++){
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
        for($i=0;$i<$applicationPagesAnswerCount[$applicationPage->getPage()->getId()]; $i++){  
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
    $applicationPages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$this->_application->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc'));  
    $xml = new DOMDocument();
    $xml->formatOutput = true;
    $applicantsXml = $xml->createElement("applicants");
    
    foreach($applicants as $applicant){
      $applicantXml = $xml->createElement("applicant");
      
      $account = $xml->createElement("account");
      $account->appendChild($xml->createElement('id', $applicant->getId()));
      $account->appendChild($xml->createElement('firstName', $applicant->getFirstName()));
      $account->appendChild($xml->createElement('middleName', $applicant->getMiddleName()));
      $account->appendChild($xml->createElement('lastName', $applicant->getLastName()));
      $account->appendChild($xml->createElement('suffix', $applicant->getSuffix()));
      $account->appendChild($xml->createElement('email', $applicant->getEmail()));
      $account->appendChild($xml->createElement('isLocked', $applicant->isLocked()?'yes':'no'));
      $account->appendChild($xml->createElement('lastLogin', $applicant->getLastLogin()->format('c')));
      $account->appendChild($xml->createElement('updatedAt', $applicant->getLastLogin()->format('c')));
      $account->appendChild($xml->createElement('createdAt', $applicant->getLastLogin()->format('c')));
      $applicantXml->appendChild($account);
      
      $decision = $xml->createElement("decision");
      $decision->appendChild($xml->createElement('status', $applicant->getDecision()?$applicant->getDecision()->status():'none'));
      $decision->appendChild($xml->createElement('nominateAdmit', ($applicant->getDecision() and $applicant->getDecision()->getNominateAdmit())?$applicant->getDecision()->getNominateAdmit()->format('c'):''));
      $decision->appendChild($xml->createElement('nominateDeny', ($applicant->getDecision() and $applicant->getDecision()->getNominateDeny())?$applicant->getDecision()->getNominateDeny()->format('c'):''));
      $decision->appendChild($xml->createElement('finalAdmit', ($applicant->getDecision() and $applicant->getDecision()->getFinalAdmit())?$applicant->getDecision()->getFinalAdmit()->format('c'):''));
      $decision->appendChild($xml->createElement('finalDeny', ($applicant->getDecision() and $applicant->getDecision()->getFinalDeny())?$applicant->getDecision()->getFinalDeny()->format('c'):''));
      $decision->appendChild($xml->createElement('acceptOffer', ($applicant->getDecision() and $applicant->getDecision()->getAcceptOffer())?$applicant->getDecision()->getAcceptOffer()->format('c'):''));
      $decision->appendChild($xml->createElement('declineOffer', ($applicant->getDecision() and $applicant->getDecision()->getDeclineOffer())?$applicant->getDecision()->getDeclineOffer()->format('c'):''));
      $applicantXml->appendChild($decision);
      
      $tags = $xml->createElement("tags");
      foreach($applicant->getTags() as $tag){
        $tagXml = $xml->createElement('tag');
        $tagXml->setAttribute('tagId', $tag->getId());
        $tagXml ->appendChild($xml->createCDATASection($tag->getTitle()));
        $tags->appendChild($tagXml);
      }
      $applicantXml->appendChild($tags);
      
      
      $pages = $xml->createElement("pages");
      foreach($applicationPages as $applicationPage){
        $page = $xml->createElement("page");
        $page->setAttribute('title', htmlentities($applicationPage->getTitle(),ENT_COMPAT,'utf-8'));
        $page->setAttribute('pageId', $applicationPage->getPage()->getId());
        $answersXml = $xml->createElement('answers');
        $applicationPage->getJazzeePage()->setApplicant($applicant);
        foreach($applicationPage->getJazzeePage()->getXmlAnswers($xml) as $answerXml){
          $answersXml->appendChild($answerXml);
        }
        $page->appendChild($answersXml);
        $pages->appendChild($page);
      }
      $applicantXml->appendChild($pages);
      $applicantsXml->appendChild($applicantXml);
    }
    $xml->appendChild($applicantsXml);
    $this->setVar('outputType', 'xml');
    $this->setLayout('xml');
    $this->setLayoutVar('filename', $this->_application->getProgram()->getShortName() .  '-' . $this->_application->getCycle()->getName() . date('-mdy'));
    $this->setVar('xml', $xml);
  }
}