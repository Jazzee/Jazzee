<?php
/**
 * The actual content of the application
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
 
class ApplyPageController extends ApplyController {
  /**
   * The current page an alias for $this->pages[$this->actionParams['pageID']]
   * @var ApplyPage
   */
  protected $page;
  
  /**
   * Convienece string holding the path to this page
   * @var  string    
   */
  protected $pathPath;
  
  /**
   * Lookup applicant and make sure we are authorized to view the page
   * @see ApplyController::beforeAction()
   */
  public function beforeAction(){
    parent::beforeAction();
    $pageID = $this->actionParams['pageID'];
    if(!array_key_exists($pageID,$this->pages)){
      $this->messages->write('error', "You are not authorized to view that page.");
      $this->redirect($this->path("apply/{$this->actionParams['programShortName']}/{$this->actionParams['cycleName']}/applicant/login/"));
      $this->afterAction();
      exit();
    }
    if($this->applicant->locked){
      $this->redirect($this->path("apply/{$this->actionParams['programShortName']}/{$this->actionParams['cycleName']}/status/"));
      $this->afterAction();
      exit();
    }
    $this->addScript('common/scripts/controllers/apply_page.controller.js');
    $this->page = $this->pages[$pageID];
    $this->pagePath = 'apply/' . $this->application->Program->shortName . '/' . $this->application->Cycle->name . '/page/' . $this->page->id;
    $this->setVar('page', $this->page);
    $this->setVar('currentAnswerID', false);
    $this->setVar('action', $this->path($this->pagePath));
  }
  
  /**
   * Display the page
   */
  public function actionIndex() {
    if(!empty($this->post)){
      if($input = $this->page->validateInput($this->post)){
        $this->page->newAnswer($input);
        $this->messages->write('success', 'Answer Saved Successfully');
        $this->redirectPath($this->pagePath);
      }
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
    if(empty($this->post)){
      $this->setVar('action', $this->path('apply/' . $this->application->Program->shortName . '/' . $this->application->Cycle->name . '/page/' . $this->page->id . '/edit/' . $this->actionParams['answerID']));
      $this->page->fill($this->actionParams['answerID']);
      $this->setVar('currentAnswerID', $this->actionParams['answerID']);
    } else {
      if($input = $this->page->validateInput($this->post)){
        if($this->page->updateAnswer($input,$this->actionParams['answerID'])){
          $this->messages->write('success', 'Answer Updated Successfully');
          $this->redirectPath($this->pagePath);
        }
      }
    }
    $this->loadView($this->controllerName . '/index');
  }
  
    /**
   * Delete an answer
   */
  public function actionDelete() {
    if($this->page->deleteAnswer($this->actionParams['answerID'])){
      $this->messages->write('success', 'Answer Deleted Successfully');
      $this->redirectPath($this->pagePath);
    }
    $this->loadView($this->controllerName . '/index');
  }
  
  /**
   * Create the navigation from pages
   * @param array $pages
   * @return Navigation
   */
  public function getNavigation(){
    $navigation = new Navigation;
    
    $menu = $navigation->newMenu();
    $menu->title = 'Application Pages';
    foreach($this->pages as $id => $page){
      $link = $menu->newLink(array('text'=>$page->title, 'href'=> $this->path('apply/' . $this->application['Program']->shortName . '/' . $this->application['Cycle']->name . '/page/' . $page->id)));
      if($this->page->id == $id){
        $link->current = true;
      }
      switch($page->getStatus()){
        case ApplyPage::INCOMPLETE:
          $link->class = 'incomplete';
          break;
        case ApplyPage::COMPLETE:
          $link->class = 'complete';
          break;
        case ApplyPage::SKIPPED:
          $link->class = 'skipped';
          break;
      }
    }
    $applicant_menu = $navigation->newMenu();
    $applicant_menu->title = "User Menu";
    $applicant_menu->newLink(array('text'=>'Support', 'href'=>$this->path("apply/{$this->application->Program->shortName}/{$this->application->Cycle->name}/support")));
    $applicant_menu->newLink(array('text'=>'Logout', 'href'=>$this->path("apply/{$this->application->Program->shortName}/{$this->application->Cycle->name}/applicant/logout")));
    
    return $navigation;
  }
}
?>