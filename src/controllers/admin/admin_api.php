<?php
ini_set('memory_limit', '1g');
set_time_limit('120');
/**
 * Data API
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminApiController extends \Jazzee\AdminController {
  const REQUIRE_AUTHORIZATION = false;
  const REQUIRE_APPLICATION = false;
  
  public function actionIndex(){
    $this->setLayout('xml');
    $this->setLayoutVar('filename', 'api.xml');
    
    $dom = new DOMDocument();
    $this->setVar('xml', $dom);
    
    if(empty($this->post['apiKey'])){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'No "apiKey" parameter in request.');
      return;
    }
    if(!$this->_user = $this->_em->getRepository('\Jazzee\Entity\User')->findOneBy(array('apiKey'=>$this->post['apiKey']))){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'Invalid api key.');
      sleep(5);
      return;
    }
    
    if(!empty($this->post['applicationId'])){
      if(!$this->_application = $this->_em->getRepository('\Jazzee\Entity\Application')->find($this->post['applicationId'])){
        $this->setLayoutVar('status', 'error');
        $this->addMessage('error', 'Invalid applicationId.');
        return;
      }
    }
    
    if(empty($this->post['type'])){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'No "type" parameter in request.');
      return;
    }
    
    if(!$this->checkIsAllowed($this->controllerName, $this->post['type'])){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'You do not have access to that.');
      return;
    }
    
    switch($this->post['type']){
      case 'listapplicants':
        $this->listApplicants($dom, $this->post);
        break;
      case 'getapplicants':
        $this->getApplicants($dom, $this->post);
        break;
      case 'listapplications':
        $this->listApplications($dom, $this->post);
        break;
      default: 
        $this->setLayoutVar('status', 'error');
        $this->addMessage('error', $this->post['type'] .' is not a recognized api request type');
    }
   
    
	}
  
  /**
   * List all the applicants in a program
   * @param DOMDocument $dom
   * @param array $post
   */
  protected function listApplicants(DOMDocument $dom, array $post){
    if(!$this->_application){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'This request requires an applicationId.');
      return;
    }
    $applicantsXml = $dom->createElement("applicants");
    foreach($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant){
      $applicantsXml->appendChild($this->singleApplicant($dom, $applicant, true));
    }
    $dom->appendChild($applicantsXml);
  }
  
  /**
   * Get details for individual applicants
   * @param DOMDocument $dom
   * @param array $post
   */
  protected function getApplicants(DOMDocument $dom, array $post){
    if(!$this->_application){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'This request requires an applicationId.');
      return;
    }
    $applicantsXml = $dom->createElement("applicants");
    foreach(explode(',',$post['list']) as $id){
      if($applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->find($id))
        $applicantsXml->appendChild($this->singleApplicant($dom, $applicant, false));
    }
    $dom->appendChild($applicantsXml);
  }
  
  /**
   * List all the applications in the system where the user has access
   * @param DOMDocument $dom
   * @param array $post
   */
  protected function listApplications(DOMDocument $dom, array $post){
    $applicationsXml = $dom->createElement("applications");
    if($this->checkIsAllowed('admin_changeprogram', 'anyProgram')){
      $programs = $this->_em->getRepository('\Jazzee\Entity\Program')->findAll();
    } else {
      $arr = $this->_user->getPrograms();
      $programs = array();
      foreach($arr as $id) $programs[] = $this->_em->getRepository('\Jazzee\Entity\Program')->find($id);
    }
    foreach($programs as $program){
      foreach($this->_em->getRepository('Jazzee\Entity\Application')->findByProgram($program) as $application){
        $applicationXml = $dom->createElement('application',$application->getId());
        $applicationXml->setAttribute('cycle', $application->getCycle()->getName());
        $applicationXml->setAttribute('program', $application->getProgram()->getName());
        
        $applicationsXml->appendChild($applicationXml);
      }
    }
    $dom->appendChild($applicationsXml);
  }
  
  /**
   * Single applicants xml
   * @param DomDocument $dom
   * @param \Jazzee\Entity\Applicant $applicant
   * @param boolean $partial - fetch only applicant not answers
   * @return DOMElement
   */
  protected function singleApplicant(DOMDocument $dom, \Jazzee\Entity\Applicant $applicant, $partial = true){
    $applicantXml = $dom->createElement("applicant");
    $account = $dom->createElement("account");
    $account->appendChild($dom->createElement('id', $applicant->getId()));
    $account->appendChild($dom->createElement('firstName', $applicant->getFirstName()));
    $account->appendChild($dom->createElement('middleName', $applicant->getMiddleName()));
    $account->appendChild($dom->createElement('lastName', $applicant->getLastName()));
    $account->appendChild($dom->createElement('suffix', $applicant->getSuffix()));
    $account->appendChild($dom->createElement('email', $applicant->getEmail()));
    $account->appendChild($dom->createElement('isLocked', $applicant->isLocked()?'yes':'no'));
    $account->appendChild($dom->createElement('lastLogin', $applicant->getLastLogin()->format('c')));
    $account->appendChild($dom->createElement('updatedAt', $applicant->getLastLogin()->format('c')));
    $account->appendChild($dom->createElement('createdAt', $applicant->getLastLogin()->format('c')));
    $applicantXml->appendChild($account);
    
    $decision = $dom->createElement("decision");
    $decision->appendChild($dom->createElement('status', $applicant->getDecision()?$applicant->getDecision()->status():'none'));
    $decision->appendChild($dom->createElement('nominateAdmit', ($applicant->getDecision() and $applicant->getDecision()->getNominateAdmit())?$applicant->getDecision()->getNominateAdmit()->format('c'):''));
    $decision->appendChild($dom->createElement('nominateDeny', ($applicant->getDecision() and $applicant->getDecision()->getNominateDeny())?$applicant->getDecision()->getNominateDeny()->format('c'):''));
    $decision->appendChild($dom->createElement('finalAdmit', ($applicant->getDecision() and $applicant->getDecision()->getFinalAdmit())?$applicant->getDecision()->getFinalAdmit()->format('c'):''));
    $decision->appendChild($dom->createElement('finalDeny', ($applicant->getDecision() and $applicant->getDecision()->getFinalDeny())?$applicant->getDecision()->getFinalDeny()->format('c'):''));
    $decision->appendChild($dom->createElement('acceptOffer', ($applicant->getDecision() and $applicant->getDecision()->getAcceptOffer())?$applicant->getDecision()->getAcceptOffer()->format('c'):''));
    $decision->appendChild($dom->createElement('declineOffer', ($applicant->getDecision() and $applicant->getDecision()->getDeclineOffer())?$applicant->getDecision()->getDeclineOffer()->format('c'):''));
    $applicantXml->appendChild($decision);
    
    $tags = $dom->createElement("tags");
    foreach($applicant->getTags() as $tag){
      $tagXml = $dom->createElement('tag');
      $tagXml->setAttribute('tagId', $tag->getId());
      $tagXml->appendChild($dom->createCDATASection($tag->getTitle()));
      $tags->appendChild($tagXml);
    }
    $applicantXml->appendChild($tags);
    if($partial) return $applicantXml;
    
    $applicationPages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$this->_application->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc'));  
    
    $pages = $dom->createElement("pages");
    foreach($applicationPages as $applicationPage){
      $page = $dom->createElement("page");
      $page->setAttribute('title', htmlentities($applicationPage->getTitle()));
      $page->setAttribute('pageId', $applicationPage->getPage()->getId());
      $answersXml = $dom->createElement('answers');
      $applicationPage->getJazzeePage()->setApplicant($applicant);
      foreach($applicationPage->getJazzeePage()->getXmlAnswers($dom) as $answerXml){
        $answersXml->appendChild($answerXml);
      }
      $page->appendChild($answersXml);
      $pages->appendChild($page);
    }
    $applicantXml->appendChild($pages);
    
    return $applicantXml;
  }
}

?>