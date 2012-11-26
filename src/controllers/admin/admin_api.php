<?php

ini_set('memory_limit', '756M');
set_time_limit('240');

/**
 * Data API
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class AdminApiController extends \Jazzee\AdminController
{

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
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  protected function setUp()
  {
    parent::setUp();
    $this->setLayout('xml');
    $this->setLayoutVar('filename', 'api.xml');
    $this->dom = new DOMDocument('1.0', 'UTF-8');
    $this->setVar('xml', $this->dom);

    $versions = array(1,2);
    if (empty($this->post['version']) or !in_array($this->post['version'], $versions)) {
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'Invalid API Version');
      $this->loadView('admin_api/index');
      exit(0);
    }
    $this->version = $this->post['version'];

    if (empty($this->post['apiKey']) or !$this->_user = $this->_em->getRepository('\Jazzee\Entity\User')->findOneBy(array('apiKey' => $this->post['apiKey']))) {
      sleep(5);
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'Invalid API Key');
      $this->loadView('admin_api/index');
      exit(0);
    }
    if (!empty($this->post['applicationId'])) {
      $userPrograms = $this->_user->getPrograms();
      if (
              !$this->_application = $this->_em->getRepository('\Jazzee\Entity\Application')->find($this->post['applicationId'])
              OR (!$this->checkIsAllowed('admin_changeprogram', 'anyProgram') and !in_array($this->_application->getProgram()->getId(), $userPrograms))
      ) {
        $this->setLayoutVar('status', 'error');
        $this->addMessage('error', 'Invalid Application ID or you do not have access to that application');
        $this->loadView('admin_api/index');
        exit(0);
      }
    }
  }

  public function actionIndex()
  {
    switch ($this->post['type']) {
      case 'listapplicants':
        $this->listApplicants();
          break;
      case 'getapplicants':
        $this->getApplicants($this->post);
          break;
      case 'listapplications':
        $this->listApplications();
          break;
      case 'getapplication':
        $this->getApplication();
          break;
      default:
        $this->setLayoutVar('status', 'error');
        $this->addMessage('error', $this->post['type'] . ' is not a recognized api request type');
    }
  }

  /**
   * List all the applicants in a program
   */
  protected function listApplicants()
  {
    if (!$this->_application) {
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'This request requires an applicationId.');

      return;
    }
    if (!$this->_user->isAllowed('applicants_list', 'index', $this->_application->getProgram())) {
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'You do not have access to that.');

      return;
    }

    $applicantsXml = $this->dom->createElement("applicants");
    foreach ($this->_em->getRepository('\Jazzee\Entity\Applicant')->findByApplication($this->_application, false) as $applicant) {
      $applicantsXml->appendChild($this->singleApplicant($applicant, true));
    }
    $this->dom->appendChild($applicantsXml);
  }

  /**
   * Get details for individual applicants
   * @param array $post
   */
  protected function getApplicants(array $post)
  {
    if (!$this->_application) {
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'This request requires an applicationId.');

      return;
    }
    if (!$this->_user->isAllowed('applicants_single', 'index', $this->_application->getProgram())) {
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'You do not have access to that.');

      return;
    }
    $applicantsXml = $this->dom->createElement("applicants");
    foreach (explode(',', $post['list']) as $id) {
      if ($applicant = $this->_em->getRepository('\Jazzee\Entity\Applicant')->find($id, false)) {
        $applicantsXml->appendChild($this->singleApplicant($applicant, false));
      }
    }
    $this->dom->appendChild($applicantsXml);
  }

  /**
   * List all the applications in the system where the user has access
   */
  protected function listApplications()
  {
    $applicationsXml = $this->dom->createElement("applications");
    if ($this->checkIsAllowed('admin_changeprogram', 'anyProgram')) {
      $programs = $this->_em->getRepository('\Jazzee\Entity\Program')->findAll();
    } else {
      $arr = $this->_user->getPrograms();
      $programs = array();
      foreach ($arr as $id) {
        $programs[] = $this->_em->getRepository('\Jazzee\Entity\Program')->find($id);
      }
    }
    foreach ($programs as $program) {
      foreach ($this->_em->getRepository('Jazzee\Entity\Application')->findByProgram($program, true) as $application) {
        $applicationXml = $this->dom->createElement('application', $application->getId());
        $applicationXml->setAttribute('cycle', $application->getCycle()->getName());
        $applicationXml->setAttribute('program', $application->getProgram()->getShortName());

        $applicationsXml->appendChild($applicationXml);
      }
    }
    $this->dom->appendChild($applicationsXml);
  }

  /**
   * Get application structure
   */
  protected function getApplication()
  {
    if (!$this->_application) {
      $this->setLayoutVar('status', 'error');
      $this->addMessage('error', 'This request requires an applicationId.');

      return;
    }
    $app = $this->dom->createElement("application");
    $applicationPages = $this->dom->createElement("pages");
    foreach ($this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $page) {
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
  protected function singleApplicant(\Jazzee\Entity\Applicant $applicant, $partial = true)
  {
    $xml = $applicant->toXml($this, $partial, $this->version);
    $node = $this->dom->importNode($xml->documentElement, true);

    return $node;
  }

  /**
   * Page XML
   *
   * Calls itself recursivly to capture all children
   * @param DomDocument $dom
   * @param \Jazzee\Entity\ApplicationPage or \Jazzee\Entity\Page $page
   */
  protected function pageXml($page)
  {
    $pxml = $this->dom->createElement('page');
    $pxml->setAttribute('id', $page->getId());
    $pxml->setAttribute('title', htmlentities($page->getTitle(), ENT_COMPAT, 'utf-8'));
    $pxml->setAttribute('min', $page->getMin());
    $pxml->setAttribute('max', $page->getMax());
    $pxml->setAttribute('required', $page->isRequired());
    $pxml->setAttribute('answerStatusDisplay', $page->answerStatusDisplay());
    $pxml->setAttribute('instructions', htmlentities($page->getInstructions(), ENT_COMPAT, 'utf-8'));
    $pxml->setAttribute('leadingText', htmlentities($page->getLeadingText(), ENT_COMPAT, 'utf-8'));
    $pxml->setAttribute('trailingText', htmlentities($page->getTrailingText(), ENT_COMPAT, 'utf-8'));
    if ($page instanceof \Jazzee\Entity\ApplicationPage) {
      $pxml->setAttribute('id', $page->getPage()->getId());
      $pxml->setAttribute('weight', $page->getWeight());
      $pxml->setAttribute('kind', $page->getKind());
      $page = $page->getPage();
      if ($page->isGlobal()) {
        $pxml->setAttribute('globalPageUuid', $page->getUuid());

        return $pxml;
      }
    }
    $pxml->setAttribute('class', $page->getType()->getClass());

    $elements = $pxml->appendChild($this->dom->createElement('elements'));
    foreach ($page->getElements() as $element) {
      $exml = $this->dom->createElement('element');
      $exml->setAttribute('id', $element->getId());
      $exml->setAttribute('title', htmlentities($element->getTitle(), ENT_COMPAT, 'utf-8'));
      $exml->setAttribute('class', $element->getType()->getClass());
      $exml->setAttribute('fixedId', $element->getFixedId());
      $exml->setAttribute('weight', $element->getWeight());
      $exml->setAttribute('min', $element->getMin());
      $exml->setAttribute('max', $element->getMax());
      $exml->setAttribute('required', $element->isRequired());
      $exml->setAttribute('instructions', htmlentities($element->getInstructions(), ENT_COMPAT, 'utf-8'));
      $exml->setAttribute('format', htmlentities($element->getFormat(), ENT_COMPAT, 'utf-8'));
      $exml->setAttribute('defaultValue', $element->getDefaultValue());
      $listItems = $exml->appendChild($this->dom->createElement('listitems'));
      foreach ($element->getListItems() as $item) {
        //only export active items
        if ($item->isActive()) {
          $ixml = $this->dom->createElement('item');
          $ixml->nodeValue = htmlentities($item->getValue(), ENT_COMPAT, 'utf-8');
          $ixml->setAttribute('active', (integer) $item->isActive());
          $ixml->setAttribute('weight', $item->getWeight());
          $listItems->appendChild($ixml);
          unset($ixml);
        }
      }
      $elements->appendChild($exml);
    }
    $children = $pxml->appendChild($this->dom->createElement('children'));
    foreach ($page->getChildren() as $child) {
      $children->appendChild($this->pageXml($child));
    }

    $variables = $pxml->appendChild($this->dom->createElement('variables'));
    foreach ($page->getVariables() as $var) {
      $variable = $this->dom->createElement('variable', (string) $var->getValue());
      $variable->setAttribute('name', $var->getName());
      $variables->appendChild($variable);
    }

    return $pxml;
  }

}