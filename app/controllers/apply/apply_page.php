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
   * The id of the current page
   * @var int
   */
  protected $pageID;
  
  /**
   * The current page an alias for $this->pages[$pageID]
   * @var ApplyPage
   */
  protected $page;
  
  
  /**
   * The path to this page
   * @var string
   */
  protected $path;
  
  public function beforeAction(){
    parent::beforeAction();
    $this->pageID = $this->actionParams['pageID'];
    if(!array_key_exists($this->pageID,$this->pages)){
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
    $this->page = $this->pages[$this->pageID];
    $this->path = WWW_ROOT . '/apply/' . $this->application['Program']->shortName . '/' . $this->application['Cycle']->name . '/page/' . $this->pageID;
    $this->setVar('page', $this->page);
    $this->setVar('currentAnswerID', false);
    $this->setVar('form', $this->page->getForm());
    if($form = $this->page->getForm()) $form->action = $this->path;
  }
  
  /**
   * Display the page
   */
  public function actionIndex() {
    if(!empty($this->post)){
      if($input = $this->page->validateInput($this->post)){
        $this->page->newAnswer($input);
        //look for locked applications and redirect them
        //this is cheaper than redirecting for every page load
        if($this->applicant->locked){
          $this->redirect($this->path("apply/{$this->actionParams['programShortName']}/{$this->actionParams['cycleName']}/status/"));
          exit();
        }
        $this->messages->write('success', 'Answer Saved Successfully');
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
      
      //stick this inside the exists block so a 404 will be returned otherwise
      $this->loadView($this->controllerName . '/index');
    }
  }
  
  /**
   * Display an edit page
   * Highlight the answer being edited and fill the form with data from that answer
   */
  public function actionEdit() {
    if(empty($this->post)){
      $this->page->getForm()->action = $this->path . '/edit/' . $this->actionParams['answerID'];
      $this->page->fill($this->actionParams['answerID']);
      $this->setVar('currentAnswerID', $this->actionParams['answerID']);
    } else {
      if($input = $this->page->validateInput($this->post)){
        if($this->page->updateAnswer($input,$this->actionParams['answerID'])){
          $this->messages->write('success', 'Answer Updated Successfully');
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
      if($this->pageID == $id){
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

    
    //Logout Function, if user session is alive (store has a non-empty $data).
    $s_fetch = Session::getInstance();
    $session_fetch = $s_fetch->getStore('apply');
    if (!empty($session_fetch)) {
       $applicant_menu->newLink(array('text'=>'Logout', 'href'=>$this->path("apply/{$this->application['Program']->shortName}/{$this->application['Cycle']->name}/applicant/logout")));
    }
    
    return $navigation;
  }
}
?>
