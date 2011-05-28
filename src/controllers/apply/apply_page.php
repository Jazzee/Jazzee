<?php
/**
 * The actual content of the application
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
 
class ApplyPageController extends \Jazzee\ApplyController {  
  const ERROR_MESSAGE = 'There was a problem saving your data on this page.  Please correct the errors below and retry your request.';
  /**
   * Convienece string holding the path to this page
   * @var  string    
   */
  protected $_path;
  
  /**
   * Convience access to $this->pages[$pageId]
   * @var \Jazzee\Page
   */
  protected $_page;
  
  /**
   * Lookup applicant and make sure we are authorized to view the page
   * @see ApplyController::beforeAction()
   */
  public function beforeAction(){
    parent::beforeAction();
    $pageID = $this->actionParams['pageID'];
    if(!array_key_exists($pageID,$this->_pages)){
      $this->addMessage('error', "You are not authorized to view that page.");
      $this->redirectPath("apply/{$this->actionParams['programShortName']}/{$this->actionParams['cycleName']}/applicant/login/");
    }
    if($this->_applicant->isLocked()){
      $this->redirectPath('apply/' . $this->_application->getProgram() . '/' . $this->_application->getCycle()->getTitle() . '/status/');
    }
    $this->addScript($this->path('resource/scripts/controllers/apply_page.controller.js'));
    $this->_page = $this->_pages[$pageID];
    $this->_path = 'apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/page/' . $this->_page->getId();
    $this->setVar('page', $this->_page);
    $this->setVar('currentAnswerID', false);
    $n = $this->getNavigation();
    $this->setLayoutVar('navigation', $this->getNavigation());
  }
  
  /**
   * Get action page
   * 
   * Where to submit forms for Pages
   * @return string
   */
  public function getActionPath(){
    return $this->path($this->_path);
  }
  
  /**
   * Display the page
   */
  public function actionIndex() {
    if(!empty($this->post)){
      if(!$input = $this->_page->getJazzeePage()->validateInput($this->post)){
        $this->addMessage('error', self::ERROR_MESSAGE);
        return false;
      }
      $this->_page->getJazzeePage()->newAnswer($input);
    }
  }
  
  /**
   * Perform a generic ApplyPage specific action
   * Pass the input through to the apply page
   */
  public function actionDo() {
    if(method_exists($this->page, $this->actionParams['doWhat'])){
      if($this->page->{$this->actionParams['doWhat']}($this->actionParams['answerID']))
        $this->messages->write('success', 'Action Completed Successfully');
        $this->redirectPath($this->pagePath);
    }
  }
  
  /**
   * Display an edit page
   * Highlight the answer being edited and fill the form with data from that answer
   */
  public function actionEdit() {
    $this->_page->getJazzeePage()->fill($this->actionParams['answerID']);
    $this->setVar('currentAnswerID', $this->actionParams['answerID']);
    if(!empty($this->post)){
      if(!$input = $this->_page->getJazzeePage()->validateInput($this->post)){
        $this->addMessage('error', self::ERROR_MESSAGE);
      } else {
        $this->_page->getJazzeePage()->updateAnswer($input, $this->actionParams['answerID']);
        $this->setVar('currentAnswerID', null);
      }
    }
    $this->loadView($this->controllerName . '/index');
  }
  
    /**
   * Delete an answer
   */
  public function actionDelete() {
    $this->_page->getJazzeePage()->deleteAnswer($this->actionParams['answerID']);
    $this->loadView($this->controllerName . '/index');
  }
  
  /**
   * Create the navigation from pages
   * @param array $pages
   * @return Navigation
   */
  public function getNavigation(){
    $navigation = new \Foundation\Navigation\Container();
    
    $menu = new \Foundation\Navigation\Menu();
    $navigation->addMenu($menu);
    
    $menu->setTitle('Application Pages');
    foreach($this->_pages as $page){
      $link = new \Foundation\Navigation\Link($page->getTitle());
      $link->setHref($this->path('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/page/' . $page->getId()));
      if($this->_page->getId() == $page->getId()){
        $link->setCurrent(true);
      }
      switch($page->getJazzeePage()->getStatus()){
        case \Jazzee\Page::INCOMPLETE:
          $link->addClass('incomplete');
          break;
        case \Jazzee\Page::COMPLETE:
          $link->addClass('complete');
          break;
        case \Jazzee\Page::SKIPPED:
          $link->addClass('skipped');
          break;
      }
      $menu->addLink($link);
    }
    $applicant_menu = new \Foundation\Navigation\Menu();
    $navigation->addMenu($applicant_menu);
    $applicant_menu->setTitle("User Menu");
    
    $link = new \Foundation\Navigation\Link('Support');
    $link->setHref($this->path('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/support/'));
    $applicant_menu->addLink($link);

    $link = new \Foundation\Navigation\Link('Logout');
    $link->setHref($this->path('apply/' . $this->_application->getProgram()->getShortName() . '/' . $this->_application->getCycle()->getName() . '/applicant/logout/'));
    $applicant_menu->addLink($link);
    
    return $navigation;
  }
}
?>