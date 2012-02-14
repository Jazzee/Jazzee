<?php
/**
 * Setup Program Pages
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
class SetupPagesController extends \Jazzee\PageBuilder {
  const MENU = 'Setup';
  const TITLE = 'Pages';
  const PATH = 'setup/pages';
  
  const ACTION_INDEX = 'Edit Program Pages';
  const ACTION_LIVEINDEX = 'Edit Published Application Program Pages';
  
  /**
   * DUmmy function to provide authorization call
   */
  public function actionLiveIndex(){}
  
  /**
   * Add the required JS
   */
  public function setUp(){
    parent::setUp();
    $this->addScript($this->path('resource/scripts/controllers/setup_pages.controller.js'));
    $this->setVar('published', $this->_application->isPublished());
  }
  
  /**
   * List the application Pages
   */
  public function actionListPages(){
    $applicationPages = array();
    foreach($this->_application->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) AS $applicationPage){
      $applicationPages[] = $this->pageArray($applicationPage);
    }
    $this->setVar('result', $applicationPages);
    $this->loadView($this->controllerName . '/result');
  }
  
  /**
   * Save data from editing a page
   * @param integer $pageId
   */
  public function actionSavePage($pageId){
    $data = json_decode($this->post['data']);
    switch($data->status){
      case 'delete':
        if($applicationPage = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $pageId, 'application'=>$this->_application->getId()))){
          if(!$applicationPage->getPage()->isGlobal()) $this->_em->remove($applicationPage->getPage());
          $this->_em->remove($applicationPage);
        }
      break;
      case 'new-global':
        $applicationPage = new \Jazzee\Entity\ApplicationPage();
        $applicationPage->setPage($this->_em->getRepository('\Jazzee\Entity\Page')->findOneBy(array('id'=>$pageId, 'isGlobal'=>true)));
        $applicationPage->setKind(\Jazzee\Entity\ApplicationPage::APPLICATION);
        $applicationPage->setApplication($this->_application);
        $applicationPage->setWeight($data->weight);
        $applicationPage->setTitle($data->title);
        $applicationPage->setMin($data->min);
        $applicationPage->setMax($data->max);
        if($data->isRequired) $applicationPage->required(); else $applicationPage->optional();
        $applicationPage->setInstructions($data->instructions);
        $applicationPage->setLeadingText($data->leadingText);
        $applicationPage->setTrailingText($data->trailingText);
        $this->_em->persist($applicationPage);
        break;
      case 'new':
        $page = new \Jazzee\Entity\Page();
        $page->notGlobal();
        $page->setType($this->_em->getRepository('\Jazzee\Entity\PageType')->find($data->typeId));
        $this->_em->persist($page);
        $applicationPage = new \Jazzee\Entity\ApplicationPage();
        $applicationPage->setPage($page);
        $applicationPage->setKind(\Jazzee\Entity\ApplicationPage::APPLICATION);
        $applicationPage->setWeight($data->weight);
        $applicationPage->setApplication($this->_application);
        $applicationPage->getJazzeePage()->setController($this);
        $applicationPage->getJazzeePage()->setupNewPage();
      default:
        if(!isset($applicationPage)) $applicationPage = $this->_em->getRepository('\Jazzee\Entity\ApplicationPage')->findOneBy(array('page' => $pageId, 'application'=>$this->_application->getId()));
        $this->savePage($applicationPage, $data);
    }
  }
  
  /**
   * List the global Pages
   */
  public function actionListGlobalPages(){
    $pages = array();
    foreach($this->_em->getRepository('\Jazzee\Entity\Page')->findByIsGlobal(true) AS $page){
      $pages[] = $this->pageArray($page);
    }
    $this->setVar('result', $pages);
    $this->loadView($this->controllerName . '/result');
  }
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null){
    if($application and $application->isPublished()) $action = 'liveIndex';
    else $action = 'index';
    //all action authorizations are controlled by the index action
    return parent::isAllowed($controller, $action, $user, $program, $application);
  }
}