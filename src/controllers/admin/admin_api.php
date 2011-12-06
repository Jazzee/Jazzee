<?php
ini_set('memory_limit', '512m');
set_time_limit('240');
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
  
  /**
   * Our DOM
   * @var DOMDocument
   */
  protected $dom;
  
  /**
   * API Version
   * What XML version to return
   * Not currently in use - but available for future use
   * @var integer;
   */
  protected $version;
  
  /**
   * If there is no application then create a new one to work with
   */
  protected function setUp(){
    parent::setUp();
    $this->setLayout('xml');
    $this->setLayoutVar('filename', 'api.xml');
    $this->dom = new DOMDocument();
    $this->setVar('xml', $this->dom);
    
    $versions = array(1);
    if(empty($this->post['version']) or !in_array($this->post['version'], $versions)){
        $this->setLayoutVar('status', 'error');
        $this->addMessage('error', 'Invalid API Version');
        $this->loadView('admin_api/index');
        exit();
    }
    $this->version = $this->post['version'];
    
    if(empty($this->post['apiKey']) or !$this->_user = $this->_em->getRepository('\Jazzee\Entity\User')->findOneBy(array('apiKey'=>$this->post['apiKey']))){
      sleep(5);
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'Invalid API Key');
      $this->loadView('admin_api/index');
      exit();
    }
    if(!empty($this->post['applicationId'])){
      if(!$this->_application = $this->_em->getRepository('\Jazzee\Entity\Application')->find($this->post['applicationId'])){
        $this->setLayoutVar('status', 'error');
        $this->addMessage('error', 'Invalid Application ID');
        $this->loadView('admin_api/index');
        exit();
      }
    }
  }
  
  public function actionIndex(){
    switch($this->post['type']){
      case 'listapplicants':
        $this->listApplicants($this->post);
        break;
      case 'getapplicants':
        $this->getApplicants($this->post);
        break;
      case 'listapplications':
        $this->listApplications($this->post);
        break;
      case 'getapplication':
        $this->getApplication($this->post);
        break;
      default: 
        $this->setLayoutVar('status', 'error');
        $this->addMessage('error', $this->post['type'] .' is not a recognized api request type');
    }
	}
  
  /**
   * List all the applicants in a program
   * @param array $post
   */
  protected function listApplicants(array $post){
    if(!$this->_application){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'This request requires an applicationId.');
      return;
    }
    if(!$this->_user->isAllowed('applicants_list', 'index', $this->_application->getProgram())){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'You do not have access to that.');
      return;
    }
    
    $applicantsXml = $this->dom->createElement("applicants");
    foreach($this->_em->getRepository('\Jazzee\Entity\Applicant')->findApplicantsByName('%', '%', $this->_application) as $applicant){
      $applicantsXml->appendChild($this->singleApplicant($applicant, true));
    }
    $this->dom->appendChild($applicantsXml);
  }
  
  /**
   * Get details for individual applicants
   * @param array $post
   */
  protected function getApplicants(array $post){
    if(!$this->_application){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'This request requires an applicationId.');
      return;
    }
    if(!$this->_user->isAllowed('applicants_single', 'index', $this->_application->getProgram())){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'You do not have access to that.');
      return;
    }
    $applicantsXml = $this->dom->createElement("applicants");
    foreach(explode(',',$post['list']) as $id){
      if($applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->find($id))
        $applicantsXml->appendChild($this->singleApplicant($applicant, false));
    }
    $this->dom->appendChild($applicantsXml);
  }
  
  /**
   * List all the applications in the system where the user has access
   * @param array $post
   */
  protected function listApplications(array $post){
    $applicationsXml = $this->dom->createElement("applications");
    if($this->checkIsAllowed('admin_changeprogram', 'anyProgram')){
      $programs = $this->_em->getRepository('\Jazzee\Entity\Program')->findAll();
    } else {
      $arr = $this->_user->getPrograms();
      $programs = array();
      foreach($arr as $id) $programs[] = $this->_em->getRepository('\Jazzee\Entity\Program')->find($id);
    }
    foreach($programs as $program){
      foreach($this->_em->getRepository('Jazzee\Entity\Application')->findByProgram($program) as $application){
        $applicationXml = $this->dom->createElement('application',$application->getId());
        $applicationXml->setAttribute('cycle', $application->getCycle()->getName());
        $applicationXml->setAttribute('program', $application->getProgram()->getShortName());
        
        $applicationsXml->appendChild($applicationXml);
      }
    }
    $this->dom->appendChild($applicationsXml);
  }
  
  /**
   * Get application structure
   * @param array $post
   */
  protected function getApplication(array $post){
    if(!$this->_application){
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'This request requires an applicationId.');
      return;
    }
    $app = $this->dom->createElement("application");
    $applicationPages = $this->dom->createElement("pages");
    foreach($this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $page){
      $applicationPages->appendChild($this->pageXml($page));
    }
    $app->appendChild($applicationPages);
    $this->dom->appendChild($app);
  }
  
  /**
   * Single applicants xml
   * @param \Jazzee\Entity\Applicant $applicant
   * @param boolean $partial - fetch only applicant not answers
   * @return DOMElement
   */
  protected function singleApplicant(\Jazzee\Entity\Applicant $applicant, $partial = true){
    $applicantXml = $this->dom->createElement("applicant");
    $account = $this->dom->createElement("account");
    $account->appendChild($this->dom->createElement('id', $applicant->getId()));
    $account->appendChild($this->dom->createElement('firstName', $applicant->getFirstName()));
    $account->appendChild($this->dom->createElement('middleName', $applicant->getMiddleName()));
    $account->appendChild($this->dom->createElement('lastName', $applicant->getLastName()));
    $account->appendChild($this->dom->createElement('suffix', $applicant->getSuffix()));
    $account->appendChild($this->dom->createElement('email', $applicant->getEmail()));
    $account->appendChild($this->dom->createElement('isLocked', $applicant->isLocked()?'yes':'no'));
    $account->appendChild($this->dom->createElement('lastLogin', $applicant->getLastLogin()->format('c')));
    $account->appendChild($this->dom->createElement('updatedAt', $applicant->getUpdatedAt()->format('c')));
    $account->appendChild($this->dom->createElement('createdAt', $applicant->getCreatedAt()->format('c')));
    $account->appendChild($this->dom->createElement('percentComplete', $applicant->getPercentComplete()));
    $applicantXml->appendChild($account);
    
    $decision = $this->dom->createElement("decision");
    $decision->appendChild($this->dom->createElement('status', $applicant->getDecision()?$applicant->getDecision()->status():'none'));
    $decision->appendChild($this->dom->createElement('nominateAdmit', ($applicant->getDecision() and $applicant->getDecision()->getNominateAdmit())?$applicant->getDecision()->getNominateAdmit()->format('c'):''));
    $decision->appendChild($this->dom->createElement('nominateDeny', ($applicant->getDecision() and $applicant->getDecision()->getNominateDeny())?$applicant->getDecision()->getNominateDeny()->format('c'):''));
    $decision->appendChild($this->dom->createElement('finalAdmit', ($applicant->getDecision() and $applicant->getDecision()->getFinalAdmit())?$applicant->getDecision()->getFinalAdmit()->format('c'):''));
    $decision->appendChild($this->dom->createElement('finalDeny', ($applicant->getDecision() and $applicant->getDecision()->getFinalDeny())?$applicant->getDecision()->getFinalDeny()->format('c'):''));
    $decision->appendChild($this->dom->createElement('acceptOffer', ($applicant->getDecision() and $applicant->getDecision()->getAcceptOffer())?$applicant->getDecision()->getAcceptOffer()->format('c'):''));
    $decision->appendChild($this->dom->createElement('declineOffer', ($applicant->getDecision() and $applicant->getDecision()->getDeclineOffer())?$applicant->getDecision()->getDeclineOffer()->format('c'):''));
    $applicantXml->appendChild($decision);
    
    $tags = $this->dom->createElement("tags");
    foreach($applicant->getTags() as $tag){
      $tagXml = $this->dom->createElement('tag');
      $tagXml->setAttribute('tagId', $tag->getId());
      $tagXml->appendChild($this->dom->createCDATASection($tag->getTitle()));
      $tags->appendChild($tagXml);
    }
    $applicantXml->appendChild($tags);
    if($partial) return $applicantXml;
    
    $applicationPages = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findBy(array('application'=>$this->_application->getId(), 'kind'=>\Jazzee\Entity\ApplicationPage::APPLICATION), array('weight'=> 'asc'));  
    
    $pages = $this->dom->createElement("pages");
    foreach($this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $applicationPage){
      $page = $this->dom->createElement("page");
      $page->setAttribute('title', htmlentities($applicationPage->getTitle(),ENT_COMPAT,'utf-8'));
      $page->setAttribute('type', htmlentities($applicationPage->getPage()->getType()->getClass(),ENT_COMPAT,'utf-8'));
      $page->setAttribute('pageId', $applicationPage->getPage()->getId());
      $answersXml = $this->dom->createElement('answers');
      $applicationPage->getJazzeePage()->setApplicant($applicant);
      $applicationPage->getJazzeePage()->setController($this);
      foreach($applicationPage->getJazzeePage()->getXmlAnswers($this->dom) as $answerXml){
        $answersXml->appendChild($answerXml);
      }
      $page->appendChild($answersXml);
      $pages->appendChild($page);
    }
    $applicantXml->appendChild($pages);
    
    return $applicantXml;
  }
  
  
  /**
   * Page XML
   * 
   * Calls itself recursivly to capture all children
   * @param DomDocument $dom
   * @param \Jazzee\Entity\Page or \Jazzee\Entity\Page $page
   */
  protected function pageXml($page){
    $pxml = $this->dom->createElement('page');
    $pxml->setAttribute('id', $page->getId());
    $pxml->setAttribute('title', htmlentities($page->getTitle(),ENT_COMPAT,'utf-8'));
    $pxml->setAttribute('min', $page->getMin());
    $pxml->setAttribute('max', $page->getMax());
    $pxml->setAttribute('required', $page->isRequired());
    $pxml->setAttribute('answerStatusDisplay', $page->answerStatusDisplay());
    $pxml->setAttribute('instructions', htmlentities($page->getInstructions(),ENT_COMPAT,'utf-8'));
    $pxml->setAttribute('leadingText', htmlentities($page->getLeadingText(),ENT_COMPAT,'utf-8'));
    $pxml->setAttribute('trailingText', htmlentities($page->getTrailingText(),ENT_COMPAT,'utf-8'));
    if($page instanceof \Jazzee\Entity\ApplicationPage){
      $pxml->setAttribute('id', $page->getPage()->getId());
      $pxml->setAttribute('weight', $page->getWeight());
      $pxml->setAttribute('kind', $page->getKind());
      $page = $page->getPage();
      if($page->isGlobal()){
        $pxml->setAttribute('globalPageUuid', $page->getUuid());
        return $pxml;
      }
    }
    $pxml->setAttribute('class', $page->getType()->getClass());
    
    $elements = $pxml->appendChild($this->dom->createElement('elements'));
    foreach($page->getElements() as $element){
      $exml = $this->dom->createElement('element');
      $exml->setAttribute('id', $element->getId());
      $exml->setAttribute('title', htmlentities($element->getTitle(),ENT_COMPAT,'utf-8'));
      $exml->setAttribute('class', $element->getType()->getClass());
      $exml->setAttribute('fixedId', $element->getFixedId());
      $exml->setAttribute('weight', $element->getWeight());
      $exml->setAttribute('min', $element->getMin());
      $exml->setAttribute('max', $element->getMax());
      $exml->setAttribute('required', $element->isRequired());
      $exml->setAttribute('instructions', htmlentities($element->getInstructions(),ENT_COMPAT,'utf-8'));
      $exml->setAttribute('format', htmlentities($element->getFormat(),ENT_COMPAT,'utf-8'));
      $exml->setAttribute('defaultValue', $element->getDefaultValue());
      $listItems = $exml->appendChild($this->dom->createElement('listitems'));
      foreach($element->getListItems() as $item){
        //only export active items
        if($item->isActive()){
          $ixml = $this->dom->createElement('item');
          $ixml->nodeValue = htmlentities($item->getValue(),ENT_COMPAT,'utf-8');
          $ixml->setAttribute('active', (integer)$item->isActive());
          $ixml->setAttribute('weight', $item->getWeight());
          $listItems->appendChild($ixml);
          unset($ixml);
        }
      }
      $elements->appendChild($exml);
    }
    $children = $pxml->appendChild($this->dom->createElement('children'));
    foreach($page->getChildren() as $child) $children->appendChild($this->pageXml($child));
    
    $variables = $pxml->appendChild($this->dom->createElement('variables'));
    foreach($page->getVariables() as $var){
      $variable = $this->dom->createElement('variable', (string)$var->getValue());
      $variable->setAttribute('name', $var->getName());
      $variables->appendChild($variable);
    } 
    return $pxml;
  }
}

?>